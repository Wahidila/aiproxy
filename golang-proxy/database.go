package main

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"log"
	"sync"
	"time"

	_ "github.com/go-sql-driver/mysql"
)

type Database struct {
	db *sql.DB
}

type ApiKeyInfo struct {
	ID       int64
	UserID   int64
	Key      string
	Tier     string // "free", "paid", or "subscription"
	IsActive bool
}

type UserInfo struct {
	ID       int64
	IsBanned bool
}

type WalletInfo struct {
	ID                int64
	Balance           float64
	FreeBalance       float64
	PaidBalance       float64
	FreeCreditClaimed bool
}

type ModelPricingInfo struct {
	ModelID         string
	InputPriceUSD   float64
	OutputPriceUSD  float64
	DiscountPercent int
	IsFreeTier      bool
	IsActive        bool
}

// PricingCache caches model pricing in memory
type PricingCache struct {
	mu        sync.RWMutex
	data      map[string]*ModelPricingInfo
	updatedAt time.Time
	ttl       time.Duration
}

var pricingCache *PricingCache

func NewDatabase(cfg *Config) (*Database, error) {
	// loc=Asia%2FJakarta: Go interprets timestamps in WIB (UTC+7)
	dsn := fmt.Sprintf("%s:%s@tcp(%s:%s)/%s?parseTime=true&loc=Asia%%2FJakarta",
		cfg.DBUsername, cfg.DBPassword, cfg.DBHost, cfg.DBPort, cfg.DBDatabase)

	db, err := sql.Open("mysql", dsn)
	if err != nil {
		return nil, fmt.Errorf("failed to open database: %w", err)
	}

	db.SetMaxOpenConns(cfg.DBMaxOpen)
	db.SetMaxIdleConns(cfg.DBMaxIdle)
	db.SetConnMaxLifetime(5 * time.Minute)

	if err := db.Ping(); err != nil {
		return nil, fmt.Errorf("failed to ping database: %w", err)
	}

	// Set MySQL session timezone to WIB (UTC+7) so NOW() returns Jakarta time
	if _, err := db.Exec("SET time_zone = '+07:00'"); err != nil {
		log.Printf("WARNING: failed to set MySQL timezone: %v", err)
	}

	pricingCache = &PricingCache{
		data: make(map[string]*ModelPricingInfo),
		ttl:  time.Duration(cfg.PricingCacheTTLSecs) * time.Second,
	}

	return &Database{db: db}, nil
}

func (d *Database) Close() error {
	return d.db.Close()
}

// GetApiKey looks up an API key and returns key info + user info
func (d *Database) GetApiKey(key string) (*ApiKeyInfo, error) {
	var info ApiKeyInfo
	err := d.db.QueryRow(
		"SELECT id, user_id, `key`, COALESCE(tier, 'free'), is_active FROM api_keys WHERE `key` = ? LIMIT 1",
		key,
	).Scan(&info.ID, &info.UserID, &info.Key, &info.Tier, &info.IsActive)
	if err != nil {
		return nil, err
	}
	return &info, nil
}

// GetUser returns user info
func (d *Database) GetUser(userID int64) (*UserInfo, error) {
	var info UserInfo
	err := d.db.QueryRow(
		"SELECT id, is_banned FROM users WHERE id = ? LIMIT 1",
		userID,
	).Scan(&info.ID, &info.IsBanned)
	if err != nil {
		return nil, err
	}
	return &info, nil
}

// GetWallet returns wallet/quota info for a user
func (d *Database) GetWallet(userID int64) (*WalletInfo, error) {
	var info WalletInfo
	err := d.db.QueryRow(
		"SELECT id, balance, COALESCE(free_balance, 0), COALESCE(paid_balance, 0), free_credit_claimed FROM token_quotas WHERE user_id = ? LIMIT 1",
		userID,
	).Scan(&info.ID, &info.Balance, &info.FreeBalance, &info.PaidBalance, &info.FreeCreditClaimed)
	if err != nil {
		return nil, err
	}
	return &info, nil
}

// GetModelPricing returns pricing for a model (cached)
func (d *Database) GetModelPricing(modelID string) (*ModelPricingInfo, error) {
	// Check cache first
	pricingCache.mu.RLock()
	if time.Since(pricingCache.updatedAt) < pricingCache.ttl {
		if info, ok := pricingCache.data[modelID]; ok {
			pricingCache.mu.RUnlock()
			return info, nil
		}
		pricingCache.mu.RUnlock()
		// Model not in cache but cache is fresh - model doesn't exist
		if len(pricingCache.data) > 0 {
			return nil, nil
		}
	} else {
		pricingCache.mu.RUnlock()
	}

	// Refresh cache
	if err := d.refreshPricingCache(); err != nil {
		return nil, err
	}

	pricingCache.mu.RLock()
	defer pricingCache.mu.RUnlock()
	if info, ok := pricingCache.data[modelID]; ok {
		return info, nil
	}
	return nil, nil
}

// GetFreeTierModels returns list of free tier model IDs (cached)
func (d *Database) GetFreeTierModels() ([]string, error) {
	pricingCache.mu.RLock()
	if time.Since(pricingCache.updatedAt) < pricingCache.ttl && len(pricingCache.data) > 0 {
		var models []string
		for id, info := range pricingCache.data {
			if info.IsFreeTier && info.IsActive {
				models = append(models, id)
			}
		}
		pricingCache.mu.RUnlock()
		return models, nil
	}
	pricingCache.mu.RUnlock()

	if err := d.refreshPricingCache(); err != nil {
		return nil, err
	}

	pricingCache.mu.RLock()
	defer pricingCache.mu.RUnlock()
	var models []string
	for id, info := range pricingCache.data {
		if info.IsFreeTier && info.IsActive {
			models = append(models, id)
		}
	}
	return models, nil
}

// IsFreeTierModel checks if model is available in free tier
func (d *Database) IsFreeTierModel(modelID string) (bool, error) {
	info, err := d.GetModelPricing(modelID)
	if err != nil {
		return false, err
	}
	if info == nil {
		return false, nil
	}
	return info.IsFreeTier && info.IsActive, nil
}

// IsFreeUser checks if user only has free credit (no topup history)
func (d *Database) IsFreeUser(userID int64) (bool, error) {
	var count int
	err := d.db.QueryRow(
		"SELECT COUNT(*) FROM wallet_transactions WHERE user_id = ? AND type = 'topup'",
		userID,
	).Scan(&count)
	if err != nil {
		return false, err
	}
	return count == 0, nil
}

// DeductBalance atomically deducts from the correct balance based on tier.
// Returns (newTierBalance, totalBalance, success, error)
func (d *Database) DeductBalance(userID int64, amount float64, tier string) (float64, bool, error) {
	tx, err := d.db.Begin()
	if err != nil {
		return 0, false, err
	}
	defer tx.Rollback()

	var freeBalance, paidBalance float64
	err = tx.QueryRow(
		"SELECT COALESCE(free_balance, 0), COALESCE(paid_balance, 0) FROM token_quotas WHERE user_id = ? FOR UPDATE",
		userID,
	).Scan(&freeBalance, &paidBalance)
	if err != nil {
		return 0, false, err
	}

	var newFree, newPaid float64
	if tier == "free" {
		if freeBalance < amount {
			return freeBalance + paidBalance, false, nil
		}
		newFree = freeBalance - amount
		newPaid = paidBalance
	} else {
		if paidBalance < amount {
			return freeBalance + paidBalance, false, nil
		}
		newFree = freeBalance
		newPaid = paidBalance - amount
	}

	_, err = tx.Exec(
		"UPDATE token_quotas SET free_balance = ?, paid_balance = ? WHERE user_id = ?",
		newFree, newPaid, userID,
	)
	if err != nil {
		return 0, false, err
	}

	if err := tx.Commit(); err != nil {
		return 0, false, err
	}

	return newFree + newPaid, true, nil
}

// RecordUsage inserts a token_usages record
func (d *Database) RecordUsage(userID, apiKeyID int64, model string, inputTokens, outputTokens, totalTokens int, requestPath string, statusCode, responseTimeMs int, costIDR float64) (int64, error) {
	result, err := d.db.Exec(
		`INSERT INTO token_usages (user_id, api_key_id, model, input_tokens, output_tokens, total_tokens, request_path, status_code, response_time_ms, cost_idr, created_at)
		 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())`,
		userID, apiKeyID, model, inputTokens, outputTokens, totalTokens, requestPath, statusCode, responseTimeMs, costIDR,
	)
	if err != nil {
		return 0, err
	}
	return result.LastInsertId()
}

// RecordWalletTransaction inserts a wallet_transactions record
func (d *Database) RecordWalletTransaction(userID int64, txType string, amount, balanceAfter float64, description string, refID int64, refType string) error {
	var refIDPtr *int64
	var refTypePtr *string
	if refID > 0 {
		refIDPtr = &refID
		refTypePtr = &refType
	}

	_, err := d.db.Exec(
		`INSERT INTO wallet_transactions (user_id, type, amount, balance_after, description, reference_id, reference_type, created_at)
		 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())`,
		userID, txType, amount, balanceAfter, description, refIDPtr, refTypePtr,
	)
	return err
}

// UpdateApiKeyLastUsed updates the last_used_at timestamp
func (d *Database) UpdateApiKeyLastUsed(apiKeyID int64) error {
	_, err := d.db.Exec(
		"UPDATE api_keys SET last_used_at = NOW() WHERE id = ?",
		apiKeyID,
	)
	return err
}

// GetActiveSubscription returns the user's active subscription with plan limits
func (d *Database) GetActiveSubscription(userID int64) (*SubscriptionInfo, error) {
	var info SubscriptionInfo
	var dailyLimit, perMinLimit, concLimit sql.NullInt64
	var maxTokenUsage sql.NullInt64
	var expiresAt, resetAt sql.NullTime
	var allowedModelsJSON sql.NullString

	err := d.db.QueryRow(`
		SELECT us.plan_slug, us.status, us.daily_requests_used, us.token_usage_total,
		       us.expires_at, us.daily_requests_reset_at,
		       sp.daily_request_limit, sp.per_minute_limit, sp.concurrent_limit, sp.max_token_usage,
		       sp.allowed_models
		FROM user_subscriptions us
		JOIN subscription_plans sp ON sp.slug = us.plan_slug
		WHERE us.user_id = ? AND us.status = 'active'
		  AND (us.expires_at IS NULL OR us.expires_at > NOW())
		ORDER BY us.starts_at DESC
		LIMIT 1
	`, userID).Scan(
		&info.PlanSlug, &info.Status, &info.DailyRequestsUsed, &info.TokenUsageTotal,
		&expiresAt, &resetAt,
		&dailyLimit, &perMinLimit, &concLimit, &maxTokenUsage,
		&allowedModelsJSON,
	)
	if err == sql.ErrNoRows {
		return nil, nil // no active subscription
	}
	if err != nil {
		return nil, err
	}

	if dailyLimit.Valid {
		v := int(dailyLimit.Int64)
		info.DailyRequestLimit = &v
	}
	if perMinLimit.Valid {
		info.PerMinuteLimit = int(perMinLimit.Int64)
	}
	if concLimit.Valid {
		info.ConcurrentLimit = int(concLimit.Int64)
	}
	if maxTokenUsage.Valid {
		v := maxTokenUsage.Int64
		info.MaxTokenUsage = &v
	}
	if expiresAt.Valid {
		info.ExpiresAt = &expiresAt.Time
	}
	if resetAt.Valid {
		info.ResetAt = &resetAt.Time
	}
	// Parse allowed_models JSON array
	if allowedModelsJSON.Valid && allowedModelsJSON.String != "" && allowedModelsJSON.String != "null" {
		var models []string
		if err := json.Unmarshal([]byte(allowedModelsJSON.String), &models); err == nil {
			info.AllowedModels = models
		}
	}

	return &info, nil
}

// UpdateDailyRequests updates the daily request counter in user_subscriptions
func (d *Database) UpdateDailyRequests(userID int64, count int, resetAt time.Time) error {
	_, err := d.db.Exec(`
		UPDATE user_subscriptions
		SET daily_requests_used = ?, daily_requests_reset_at = ?
		WHERE user_id = ? AND status = 'active'
		  AND (expires_at IS NULL OR expires_at > NOW())
		ORDER BY starts_at DESC
		LIMIT 1
	`, count, resetAt, userID)
	return err
}

// IncrementTokenUsageTotal increments the token_usage_total in user_subscriptions
func (d *Database) IncrementTokenUsageTotal(userID int64, tokens int) error {
	_, err := d.db.Exec(`
		UPDATE user_subscriptions
		SET token_usage_total = token_usage_total + ?
		WHERE user_id = ? AND status = 'active'
		  AND (expires_at IS NULL OR expires_at > NOW())
		ORDER BY starts_at DESC
		LIMIT 1
	`, tokens, userID)
	return err
}

// GetExchangeRate returns USD to IDR rate from settings
func (d *Database) GetExchangeRate() (float64, error) {
	var value string
	err := d.db.QueryRow(
		"SELECT value FROM settings WHERE `key` = 'usd_to_idr_rate' LIMIT 1",
	).Scan(&value)
	if err != nil {
		return 16500, nil // default
	}
	rate := 16500.0
	fmt.Sscanf(value, "%f", &rate)
	return rate, nil
}

func (d *Database) refreshPricingCache() error {
	rows, err := d.db.Query(
		"SELECT model_id, input_price_usd, output_price_usd, discount_percent, is_free_tier, is_active FROM model_pricings",
	)
	if err != nil {
		return err
	}
	defer rows.Close()

	newData := make(map[string]*ModelPricingInfo)
	for rows.Next() {
		var info ModelPricingInfo
		if err := rows.Scan(&info.ModelID, &info.InputPriceUSD, &info.OutputPriceUSD, &info.DiscountPercent, &info.IsFreeTier, &info.IsActive); err != nil {
			return err
		}
		newData[info.ModelID] = &info
	}

	pricingCache.mu.Lock()
	pricingCache.data = newData
	pricingCache.updatedAt = time.Now()
	pricingCache.mu.Unlock()

	return nil
}

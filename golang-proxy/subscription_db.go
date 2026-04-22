package main

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"time"
)

// SubscriptionKeyInfo holds subscription API key + plan details
type SubscriptionKeyInfo struct {
	KeyID            int
	UserID           int
	SubscriptionID   int
	PlanName         string
	RPMLimit         int
	ParallelLimit    int
	BudgetUSDPerCycle float64
	CycleHours       int
	AllowedModels    []string // nil = all models allowed
	ExpiresAt        time.Time
}

// SubscriptionUsageRecord represents a single usage record
type SubscriptionUsageRecord struct {
	SubscriptionID int
	ApiKeyID       int
	Model          string
	InputTokens    int
	OutputTokens   int
	CostUSD        float64
	CostIDR        float64
	RequestPath    string
	StatusCode     int
	ResponseTimeMs int
	CycleStart     time.Time
}

// ValidateSubscriptionKey validates a subscription API key and returns plan info
func (db *Database) ValidateSubscriptionKey(key string) (*SubscriptionKeyInfo, error) {
	var info SubscriptionKeyInfo
	var allowedModelsJSON sql.NullString
	var expiresAt sql.NullTime

	err := db.db.QueryRow(`
		SELECT 
			sak.id, sak.user_id, sak.subscription_id,
			sp.name, sp.rpm_limit, sp.parallel_limit, 
			sp.budget_usd_per_cycle, sp.cycle_hours, sp.allowed_models,
			s.expires_at
		FROM subscription_api_keys sak
		JOIN subscriptions s ON s.id = sak.subscription_id
		JOIN subscription_plans sp ON sp.id = s.plan_id
		WHERE sak.key = ?
			AND sak.is_active = 1
			AND s.status = 'active'
			AND (s.expires_at IS NULL OR s.expires_at > NOW())
	`, key).Scan(
		&info.KeyID, &info.UserID, &info.SubscriptionID,
		&info.PlanName, &info.RPMLimit, &info.ParallelLimit,
		&info.BudgetUSDPerCycle, &info.CycleHours, &allowedModelsJSON,
		&expiresAt,
	)

	if err == sql.ErrNoRows {
		return nil, fmt.Errorf("invalid or inactive subscription key")
	}
	if err != nil {
		return nil, fmt.Errorf("database error: %w", err)
	}

	if expiresAt.Valid {
		info.ExpiresAt = expiresAt.Time
	}

	// Parse allowed models JSON
	if allowedModelsJSON.Valid && allowedModelsJSON.String != "" && allowedModelsJSON.String != "null" {
		json.Unmarshal([]byte(allowedModelsJSON.String), &info.AllowedModels)
	}

	// Update last_used_at
	go db.db.Exec("UPDATE subscription_api_keys SET last_used_at = NOW() WHERE id = ?", info.KeyID)

	return &info, nil
}

// GetCycleCostUSD returns total cost spent in the current budget cycle
func (db *Database) GetCycleCostUSD(subscriptionID int, cycleStart time.Time) (float64, error) {
	var total sql.NullFloat64
	err := db.db.QueryRow(`
		SELECT COALESCE(SUM(cost_usd), 0) 
		FROM subscription_usages 
		WHERE subscription_id = ? AND cycle_start = ?
	`, subscriptionID, cycleStart).Scan(&total)

	if err != nil {
		return 0, err
	}
	return total.Float64, nil
}

// GetCurrentCycleStart returns the start of the current budget cycle
func GetCurrentCycleStart(cycleHours int) time.Time {
	now := time.Now()
	hour := now.Hour()
	cycleStartHour := (hour / cycleHours) * cycleHours
	return time.Date(now.Year(), now.Month(), now.Day(), cycleStartHour, 0, 0, 0, now.Location())
}

// RecordSubscriptionUsage inserts a usage record for subscription API
func (db *Database) RecordSubscriptionUsage(record SubscriptionUsageRecord) error {
	_, err := db.db.Exec(`
		INSERT INTO subscription_usages 
			(subscription_id, api_key_id, model, input_tokens, output_tokens, 
			 cost_usd, cost_idr, request_path, status_code, response_time_ms, 
			 cycle_start, created_at)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
	`,
		record.SubscriptionID, record.ApiKeyID, record.Model,
		record.InputTokens, record.OutputTokens,
		record.CostUSD, record.CostIDR,
		record.RequestPath, record.StatusCode, record.ResponseTimeMs,
		record.CycleStart,
	)
	return err
}

// CalculateCostUSD calculates USD cost for a request (without IDR conversion or discount)
func (db *Database) CalculateCostUSD(model string, inputTokens, outputTokens int) float64 {
	pricing, err := db.GetModelPricing(model)
	if err != nil || pricing == nil {
		return 0
	}

	inputCost := (float64(inputTokens) / 1_000_000) * pricing.InputPriceUSD
	outputCost := (float64(outputTokens) / 1_000_000) * pricing.OutputPriceUSD
	return inputCost + outputCost
}

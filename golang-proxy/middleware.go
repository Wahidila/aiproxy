package main

import (
	"context"
	"database/sql"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"strings"
)

type contextKey string

const (
	ctxApiKey       contextKey = "apiKey"
	ctxUser         contextKey = "user"
	ctxWallet       contextKey = "wallet"
	ctxSubscription contextKey = "subscription"
)

// AuthMiddleware validates API key from Authorization header
func AuthMiddleware(db *Database, next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		// Extract bearer token
		token := ""
		auth := r.Header.Get("Authorization")
		if strings.HasPrefix(auth, "Bearer ") {
			token = strings.TrimPrefix(auth, "Bearer ")
		}
		// Also check x-api-key (Anthropic format)
		if token == "" {
			token = r.Header.Get("x-api-key")
		}

		// Trim whitespace/newlines from copy-paste
		token = strings.TrimSpace(token)

		if token == "" {
			writeError(w, 401, "missing_api_key", "Missing API key. Include it in Authorization: Bearer sk-xxx header.", "authentication_error")
			return
		}

		// Debug log: show key prefix and length
		keyPreview := token
		if len(keyPreview) > 10 {
			keyPreview = token[:10] + "..." + token[len(token)-4:]
		}
		log.Printf("AUTH: key=%s len=%d path=%s", keyPreview, len(token), r.URL.Path)

		// Lookup API key
		apiKey, err := db.GetApiKey(token)
		if err == sql.ErrNoRows || apiKey == nil {
			log.Printf("AUTH FAILED: key not found in DB (len=%d, prefix=%s)", len(token), keyPreview)
			writeError(w, 401, "invalid_api_key", "Invalid or inactive API key.", "authentication_error")
			return
		}
		if err != nil {
			writeError(w, 500, "internal_error", "Internal server error.", "server_error")
			return
		}
		if !apiKey.IsActive {
			writeError(w, 401, "invalid_api_key", "API key is inactive.", "authentication_error")
			return
		}

		// Get user
		user, err := db.GetUser(apiKey.UserID)
		if err != nil {
			writeError(w, 500, "internal_error", "Internal server error.", "server_error")
			return
		}

		// Check ban
		if user.IsBanned {
			writeError(w, 403, "account_banned", "Your account has been suspended. Contact admin for more information.", "account_suspended")
			return
		}

		// Get wallet
		wallet, err := db.GetWallet(apiKey.UserID)
		if err == sql.ErrNoRows {
			writeError(w, 429, "insufficient_balance", "No wallet found. Please login to dashboard first.", "insufficient_balance")
			return
		}
		if err != nil {
			writeError(w, 500, "internal_error", "Internal server error.", "server_error")
			return
		}

		// Check balance based on API key tier
		tier := apiKey.Tier
		if tier == "free" {
			if wallet.FreeBalance <= 0 {
				writeError(w, 429, "insufficient_balance", "Saldo free trial habis. Silakan top up untuk melanjutkan.", "insufficient_balance")
				return
			}
		} else if tier == "subscription" {
			// Subscription keys use daily quota, not wallet balance — skip balance check
			log.Printf("AUTH: subscription key %d for user %d — skipping balance check", apiKey.ID, apiKey.UserID)
		} else {
			// paid tier — check wallet balance
			if wallet.PaidBalance <= 0 {
				writeError(w, 429, "insufficient_balance", "Saldo tidak mencukupi. Silakan top up saldo Anda.", "insufficient_balance")
				return
			}
		}

		// Store in context
		ctx := context.WithValue(r.Context(), ctxApiKey, apiKey)
		ctx = context.WithValue(ctx, ctxUser, user)
		ctx = context.WithValue(ctx, ctxWallet, wallet)

		next.ServeHTTP(w, r.WithContext(ctx))
	})
}

// RateLimitMiddleware enforces per-minute rate limit, concurrent limit, and daily request limit
func RateLimitMiddleware(db *Database, rl *RateLimiter, cl *ConcurrentLimiter, dc *DailyCounter, next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		apiKey := r.Context().Value(ctxApiKey).(*ApiKeyInfo)
		user := r.Context().Value(ctxUser).(*UserInfo)

		// Fetch subscription info
		sub, err := db.GetActiveSubscription(user.ID)
		if err != nil {
			log.Printf("RATELIMIT: failed to get subscription for user %d: %v", user.ID, err)
			// Don't block on DB error — let request through
			next.ServeHTTP(w, r)
			return
		}

		// If no subscription found, use defaults (very restrictive)
		if sub == nil {
			defaultLimit := 10
			sub = &SubscriptionInfo{
				PlanSlug:          "none",
				DailyRequestLimit: &defaultLimit,
				PerMinuteLimit:    3,
				ConcurrentLimit:   1,
			}
			log.Printf("RATELIMIT: no active subscription for user %d, using defaults", user.ID)
		}

		// Store subscription in context for later use (tracking)
		ctx := context.WithValue(r.Context(), ctxSubscription, sub)
		r = r.WithContext(ctx)

		// 1. Check concurrent limit
		concAllowed, concCurrent, concLimit := cl.Acquire(user.ID, sub.ConcurrentLimit)
		if !concAllowed {
			log.Printf("RATELIMIT: concurrent limit hit for user %d (plan=%s, current=%d, limit=%d)",
				user.ID, sub.PlanSlug, concCurrent, concLimit)
			writeError(w, 429, "concurrent_limit",
				fmt.Sprintf("Batas request bersamaan tercapai (%d/%d). Tunggu request sebelumnya selesai.", concCurrent, concLimit),
				"rate_limit_error")
			return
		}
		// Ensure we release the concurrent slot when done
		defer cl.Release(user.ID)

		// 2. Check per-minute rate limit
		rateAllowed, rateCurrent, rateLimit := rl.Allow(user.ID, sub.PerMinuteLimit)
		if !rateAllowed {
			log.Printf("RATELIMIT: per-minute limit hit for user %d (plan=%s, current=%d, limit=%d)",
				user.ID, sub.PlanSlug, rateCurrent, rateLimit)
			writeError(w, 429, "rate_limit",
				fmt.Sprintf("Batas request per menit tercapai (%d/%d). Coba lagi dalam beberapa detik.", rateCurrent, rateLimit),
				"rate_limit_error")
			return
		}

		// 3. Check daily request limit
		dailyAllowed, dailyCurrent, dailyLimit := dc.LoadOrIncrement(
			user.ID, sub.DailyRequestLimit, sub.DailyRequestsUsed, sub.ResetAt)
		if !dailyAllowed {
			log.Printf("RATELIMIT: daily limit hit for user %d (plan=%s, current=%d, limit=%d)",
				user.ID, sub.PlanSlug, dailyCurrent, dailyLimit)
			writeError(w, 429, "daily_limit",
				fmt.Sprintf("Batas request harian tercapai (%d/%d). Limit akan reset besok.", dailyCurrent, dailyLimit),
				"rate_limit_error")
			return
		}

		// 4. Check token usage cap (if applicable)
		if sub.MaxTokenUsage != nil && sub.TokenUsageTotal >= *sub.MaxTokenUsage {
			log.Printf("RATELIMIT: token cap hit for user %d (plan=%s, used=%d, cap=%d)",
				user.ID, sub.PlanSlug, sub.TokenUsageTotal, *sub.MaxTokenUsage)
			writeError(w, 429, "token_cap",
				fmt.Sprintf("Batas penggunaan token tercapai (%d/%d). Upgrade plan untuk melanjutkan.",
					sub.TokenUsageTotal, *sub.MaxTokenUsage),
				"rate_limit_error")
			return
		}

		log.Printf("RATELIMIT: OK user=%d plan=%s rate=%d/%d conc=%d/%d daily=%d/%s tier=%s",
			user.ID, sub.PlanSlug, rateCurrent, rateLimit, concCurrent, concLimit,
			dailyCurrent, formatLimit(sub.DailyRequestLimit), apiKey.Tier)

		next.ServeHTTP(w, r)
	})
}

func formatLimit(limit *int) string {
	if limit == nil {
		return "∞"
	}
	return fmt.Sprintf("%d", *limit)
}

// ModelDailyLimitMiddleware enforces per-user daily request limits per model.
// This runs AFTER AuthMiddleware so user context is available.
func ModelDailyLimitMiddleware(db *Database, next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		model := extractModelFromRequest(r)
		if model == "" {
			next.ServeHTTP(w, r)
			return
		}

		setting, err := db.GetModelDailyLimitSetting(model)
		if err != nil {
			log.Printf("MODEL_DAILY_LIMIT: error fetching setting for %s: %v", model, err)
			// Don't block on DB error — let request through
			next.ServeHTTP(w, r)
			return
		}

		if setting == nil || !setting.Enabled || setting.Limit <= 0 {
			next.ServeHTTP(w, r)
			return
		}

		// Get user from context (set by AuthMiddleware)
		apiKey := r.Context().Value(ctxApiKey).(*ApiKeyInfo)

		// Count today's requests for this model for THIS user
		count, err := db.CountTodayModelRequestsPerUser(model, apiKey.UserID)
		if err != nil {
			log.Printf("MODEL_DAILY_LIMIT: error counting requests for user %d model %s: %v", apiKey.UserID, model, err)
			// Don't block on DB error — let request through
			next.ServeHTTP(w, r)
			return
		}

		if count >= setting.Limit {
			log.Printf("MODEL_DAILY_LIMIT: limit reached for user %d model %s (%d/%d)", apiKey.UserID, model, count, setting.Limit)
			writeError(w, 429, "model_daily_limit_exceeded",
				fmt.Sprintf("Batas harian Anda untuk model %s telah tercapai (%d/%d request hari ini). Silakan coba lagi besok atau gunakan model lain.",
					model, count, setting.Limit),
				"rate_limit_error")
			return
		}

		log.Printf("MODEL_DAILY_LIMIT: OK user=%d model=%s count=%d/%d", apiKey.UserID, model, count, setting.Limit)
		next.ServeHTTP(w, r)
	})
}

// ModelRestrictionMiddleware checks model access based on API key tier and subscription plan
func ModelRestrictionMiddleware(db *Database, next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		apiKey := r.Context().Value(ctxApiKey).(*ApiKeyInfo)

		model := extractModelFromRequest(r)
		if model == "" {
			next.ServeHTTP(w, r)
			return
		}

		// 1. Free tier API keys: restrict to free tier models only
		if apiKey.Tier == "free" {
			isFreeTier, err := db.IsFreeTierModel(model)
			if err != nil {
				writeError(w, 500, "internal_error", "Internal server error.", "server_error")
				return
			}
			if !isFreeTier {
				freeModels, _ := db.GetFreeTierModels()
				writeModelRestrictionError(w, model, freeModels)
				return
			}
		}

		// 2. Subscription-based keys: check plan's allowed_models
		if apiKey.Tier == "subscription" {
			sub, ok := r.Context().Value(ctxSubscription).(*SubscriptionInfo)
			if ok && sub != nil && sub.AllowedModels != nil {
				allowed := false
				for _, m := range sub.AllowedModels {
					if m == model {
						allowed = true
						break
					}
				}
				if !allowed {
					log.Printf("MODEL_RESTRICT: user plan=%s model=%s not in allowed_models=%v",
						sub.PlanSlug, model, sub.AllowedModels)
					writeError(w, 403, "model_not_available",
						fmt.Sprintf("Model '%s' tidak tersedia di plan %s. Upgrade plan untuk akses model ini.",
							model, sub.PlanSlug),
						"invalid_request_error")
					return
				}
			}
		}

		next.ServeHTTP(w, r)
	})
}

// CorsMiddleware adds CORS headers
func CorsMiddleware(origins string, next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Access-Control-Allow-Origin", origins)
		w.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		w.Header().Set("Access-Control-Allow-Headers", "Content-Type, Authorization, x-api-key")

		if r.Method == "OPTIONS" {
			w.WriteHeader(204)
			return
		}

		next.ServeHTTP(w, r)
	})
}

func extractModelFromRequest(r *http.Request) string {
	// Try to peek at the body without consuming it
	// The body will be read again by the proxy handler
	// We use the cached body from the request context if available
	if r.Body == nil {
		return ""
	}

	// Read a partial decode - just the model field
	var partial struct {
		Model string `json:"model"`
	}

	// We need to buffer the body so it can be read again
	body, err := readAndRestoreBody(r)
	if err != nil {
		return ""
	}

	json.Unmarshal(body, &partial)
	return partial.Model
}

func writeError(w http.ResponseWriter, status int, code, message, errType string) {
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(status)
	json.NewEncoder(w).Encode(map[string]interface{}{
		"error": map[string]interface{}{
			"message": message,
			"type":    errType,
			"code":    code,
		},
	})
}

func writeModelRestrictionError(w http.ResponseWriter, model string, freeModels []string) {
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(403)
	json.NewEncoder(w).Encode(map[string]interface{}{
		"error": map[string]interface{}{
			"message":          "Model '" + model + "' tidak tersedia untuk akun free trial. Silakan top up saldo untuk mengakses semua model.",
			"type":             "model_restricted",
			"code":             "free_tier_model_restricted",
			"available_models": freeModels,
		},
	})
}

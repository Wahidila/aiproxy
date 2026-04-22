package main

import (
	"context"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"strings"
	"sync"
	"sync/atomic"
	"time"
)

// Context keys for subscription
type subCtxKey string

const (
	ctxSubKey  subCtxKey = "sub_key"
	ctxSubInfo subCtxKey = "sub_info"
)

// --- Rate Limiter (sliding window per subscription) ---

type rateLimiter struct {
	mu       sync.Mutex
	windows  map[int][]time.Time // subscriptionID -> request timestamps
}

var subRateLimiter = &rateLimiter{
	windows: make(map[int][]time.Time),
}

func (rl *rateLimiter) Allow(subscriptionID int, limit int) bool {
	rl.mu.Lock()
	defer rl.mu.Unlock()

	now := time.Now()
	windowStart := now.Add(-time.Minute)

	// Clean old entries
	timestamps := rl.windows[subscriptionID]
	cleaned := make([]time.Time, 0, len(timestamps))
	for _, t := range timestamps {
		if t.After(windowStart) {
			cleaned = append(cleaned, t)
		}
	}

	if len(cleaned) >= limit {
		rl.windows[subscriptionID] = cleaned
		return false
	}

	cleaned = append(cleaned, now)
	rl.windows[subscriptionID] = cleaned
	return true
}

// --- Parallel Limiter (concurrent request counter per subscription) ---

type parallelLimiter struct {
	mu       sync.Mutex
	counters map[int]*int64 // subscriptionID -> concurrent count
}

var subParallelLimiter = &parallelLimiter{
	counters: make(map[int]*int64),
}

func (pl *parallelLimiter) Acquire(subscriptionID int, limit int) bool {
	pl.mu.Lock()
	counter, ok := pl.counters[subscriptionID]
	if !ok {
		var c int64
		counter = &c
		pl.counters[subscriptionID] = counter
	}
	pl.mu.Unlock()

	current := atomic.LoadInt64(counter)
	if current >= int64(limit) {
		return false
	}
	atomic.AddInt64(counter, 1)
	return true
}

func (pl *parallelLimiter) Release(subscriptionID int) {
	pl.mu.Lock()
	counter, ok := pl.counters[subscriptionID]
	pl.mu.Unlock()

	if ok {
		atomic.AddInt64(counter, -1)
	}
}

// --- Middleware: Subscription Auth ---

func SubscriptionAuthMiddleware(db *Database, next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		// Extract API key from Authorization header
		auth := r.Header.Get("Authorization")
		if auth == "" {
			writeSubError(w, 401, "authentication_error", "Missing Authorization header")
			return
		}

		key := strings.TrimPrefix(auth, "Bearer ")
		if key == auth || key == "" {
			writeSubError(w, 401, "authentication_error", "Invalid Authorization format. Use: Bearer sk-sub-...")
			return
		}

		// Must be a subscription key
		if !strings.HasPrefix(key, "sk-sub-") {
			writeSubError(w, 401, "authentication_error", "Invalid subscription API key format")
			return
		}

		// Validate key and get subscription info
		info, err := db.ValidateSubscriptionKey(key)
		if err != nil {
			log.Printf("SUB_AUTH: invalid key=%s...%s err=%v", key[:10], key[len(key)-4:], err)
			writeSubError(w, 401, "authentication_error", "Invalid or expired subscription API key")
			return
		}

		// Check model restriction
		if len(info.AllowedModels) > 0 {
			var reqBody struct {
				Model string `json:"model"`
			}
			// Read body to check model (will be re-read by handler)
			body, _ := readAndRestoreBody(r)
			if body != nil {
				json.Unmarshal(body, &reqBody)
			}

			if reqBody.Model != "" {
				allowed := false
				for _, m := range info.AllowedModels {
					if m == reqBody.Model {
						allowed = true
						break
					}
				}
				if !allowed {
					writeSubError(w, 403, "model_restricted", "Model '"+reqBody.Model+"' is not available in your subscription plan")
					return
				}
			}
		}

		// Store in context
		ctx := context.WithValue(r.Context(), ctxSubInfo, info)
		next.ServeHTTP(w, r.WithContext(ctx))
	})
}

// --- Middleware: Rate Limit + Parallel Limit ---

func SubscriptionRateLimitMiddleware(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		info := r.Context().Value(ctxSubInfo).(*SubscriptionKeyInfo)

		// Check RPM
		if !subRateLimiter.Allow(info.SubscriptionID, info.RPMLimit) {
			writeSubError(w, 429, "rate_limit_exceeded",
				fmt.Sprintf("Rate limit exceeded. Your plan allows %d requests per minute.", info.RPMLimit))
			return
		}

		// Check parallel limit
		if !subParallelLimiter.Acquire(info.SubscriptionID, info.ParallelLimit) {
			writeSubError(w, 429, "parallel_limit_exceeded",
				fmt.Sprintf("Too many concurrent requests. Your plan allows %d parallel requests.", info.ParallelLimit))
			return
		}
		// Release parallel slot when request completes
		defer subParallelLimiter.Release(info.SubscriptionID)

		next.ServeHTTP(w, r)
	})
}

// --- Middleware: Budget Check ---

func SubscriptionBudgetMiddleware(db *Database, next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		info := r.Context().Value(ctxSubInfo).(*SubscriptionKeyInfo)

		cycleStart := GetCurrentCycleStart(info.CycleHours)
		spent, err := db.GetCycleCostUSD(info.SubscriptionID, cycleStart)
		if err != nil {
			log.Printf("SUB_BUDGET: error getting cycle cost: %v", err)
			writeSubError(w, 500, "internal_error", "Failed to check budget")
			return
		}

		if spent >= info.BudgetUSDPerCycle {
			nextCycle := cycleStart.Add(time.Duration(info.CycleHours) * time.Hour)
			writeSubError(w, 429, "budget_exceeded",
				"Budget limit reached for this cycle. Resets at "+nextCycle.Format("15:04 WIB")+
					". Spent: $"+formatFloat(spent, 2)+
					" / $"+formatFloat(info.BudgetUSDPerCycle, 2))
			return
		}

		next.ServeHTTP(w, r)
	})
}

// --- Helpers ---

func writeSubError(w http.ResponseWriter, status int, errType, message string) {
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(status)
	json.NewEncoder(w).Encode(map[string]interface{}{
		"error": map[string]interface{}{
			"type":    errType,
			"message": message,
		},
	})
}

func formatFloat(f float64, decimals int) string {
	return fmt.Sprintf("%.*f", decimals, f)
}

package main

import (
	"context"
	"database/sql"
	"encoding/json"
	"log"
	"net/http"
	"strings"
)

type contextKey string

const (
	ctxApiKey contextKey = "apiKey"
	ctxUser   contextKey = "user"
	ctxWallet contextKey = "wallet"
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
		} else {
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

// ModelRestrictionMiddleware checks free tier model access based on API key tier
func ModelRestrictionMiddleware(db *Database, next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		apiKey := r.Context().Value(ctxApiKey).(*ApiKeyInfo)

		// Only restrict if API key is free tier
		if apiKey.Tier == "free" {
			model := extractModelFromRequest(r)
			if model != "" {
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

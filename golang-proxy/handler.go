package main

import (
	"encoding/json"
	"io"
	"log"
	"net/http"
	"time"
)

type Handlers struct {
	cfg     *Config
	db      *Database
	tracker *Tracker
}

func NewHandlers(cfg *Config, db *Database, tracker *Tracker) *Handlers {
	return &Handlers{cfg: cfg, db: db, tracker: tracker}
}

// HandleChatCompletions handles POST /v1/chat/completions
func (h *Handlers) HandleChatCompletions(w http.ResponseWriter, r *http.Request) {
	h.handleProxy(w, r, "/chat/completions")
}

// HandleMessages handles POST /v1/messages (Anthropic format)
func (h *Handlers) HandleMessages(w http.ResponseWriter, r *http.Request) {
	h.handleProxy(w, r, "/messages")
}

// HandleResponses handles POST /v1/responses
func (h *Handlers) HandleResponses(w http.ResponseWriter, r *http.Request) {
	h.handleProxy(w, r, "/responses")
}

// HandleModels handles GET /v1/models
func (h *Handlers) HandleModels(w http.ResponseWriter, r *http.Request) {
	body, status, err := ForwardModels(h.cfg)
	if err != nil {
		log.Printf("Models error: %v", err)
		writeError(w, 502, "proxy_error", "Failed to fetch models", "proxy_error")
		return
	}

	// Sanitize models response to remove any EnowxAI branding
	body = SanitizeResponseBody(body)

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(status)
	w.Write(body)
}

// HandleHealth handles GET /v1/health
func (h *Handlers) HandleHealth(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"status":    "ok",
		"proxy":     "golang",
		"timestamp": time.Now().Format(time.RFC3339),
	})
}

func (h *Handlers) handleProxy(w http.ResponseWriter, r *http.Request, path string) {
	apiKey := r.Context().Value(ctxApiKey).(*ApiKeyInfo)
	user := r.Context().Value(ctxUser).(*UserInfo)

	// Read body
	body, err := io.ReadAll(r.Body)
	if err != nil {
		writeError(w, 400, "bad_request", "Failed to read request body", "invalid_request")
		return
	}
	defer r.Body.Close()

	// Check if streaming
	var reqBody struct {
		Stream bool   `json:"stream"`
		Model  string `json:"model"`
	}
	json.Unmarshal(body, &reqBody)

	model := reqBody.Model

	if reqBody.Stream {
		// Streaming
		result := ForwardStreaming(h.cfg, w, body, path)
		if result != nil {
			if result.Model != "" {
				model = result.Model
			}

			// Fallback: if upstream returned prompt_tokens=0 in streaming,
			// estimate input tokens from the request body
			inputTokens := result.InputTokens
			if inputTokens == 0 && result.OutputTokens > 0 {
				inputTokens = EstimateInputTokens(body)
				log.Printf("USAGE_ESTIMATE: upstream returned 0 input tokens, estimated %d from request body", inputTokens)
			}

			cost := CalculateCost(h.db, model, inputTokens, result.OutputTokens)
			// Subscription keys: record cost as 0 (no billing)
			if apiKey.Tier == "subscription" {
				cost = 0
			}

			log.Printf("TRACK [stream] user=%d model=%s input=%d output=%d cost=%.2f tier=%s",
				user.ID, model, inputTokens, result.OutputTokens, cost, apiKey.Tier)

			h.tracker.Track(TrackingEvent{
				UserID:       user.ID,
				ApiKeyID:     apiKey.ID,
				Tier:         apiKey.Tier,
				Model:        model,
				InputTokens:  inputTokens,
				OutputTokens: result.OutputTokens,
				TotalTokens:  inputTokens + result.OutputTokens,
				RequestPath:  path,
				StatusCode:   result.StatusCode,
				ResponseTime: result.ResponseTimeMs,
				CostIDR:      cost,
			})
		}
	} else {
		// Non-streaming
		result, respBody, err := ForwardNonStreaming(h.cfg, body, path)
		if err != nil {
			log.Printf("Proxy error: %v", err)
			writeError(w, 502, "proxy_error", "AI service temporarily unavailable", "proxy_error")
			return
		}

		if result.Model != "" {
			model = result.Model
		}
		cost := CalculateCost(h.db, model, result.InputTokens, result.OutputTokens)
		// Subscription keys: record cost as 0 (no billing)
		if apiKey.Tier == "subscription" {
			cost = 0
		}

		log.Printf("TRACK [non-stream] user=%d model=%s input=%d output=%d cost=%.2f tier=%s",
			user.ID, model, result.InputTokens, result.OutputTokens, cost, apiKey.Tier)

		// Sanitize response to remove any EnowxAI identity leaks
		respBody = SanitizeResponseBody(respBody)

		// Send response to client
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(result.StatusCode)
		w.Write(respBody)

		// Track async
		h.tracker.Track(TrackingEvent{
			UserID:       user.ID,
			ApiKeyID:     apiKey.ID,
			Tier:         apiKey.Tier,
			Model:        model,
			InputTokens:  result.InputTokens,
			OutputTokens: result.OutputTokens,
			TotalTokens:  result.TotalTokens,
			RequestPath:  path,
			StatusCode:   result.StatusCode,
			ResponseTime: result.ResponseTimeMs,
			CostIDR:      cost,
		})
	}
}

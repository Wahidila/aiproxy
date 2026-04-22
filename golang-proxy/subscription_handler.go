package main

import (
	"encoding/json"
	"io"
	"log"
	"net/http"
)

type SubscriptionHandlers struct {
	cfg     *Config
	db      *Database
}

func NewSubscriptionHandlers(cfg *Config, db *Database) *SubscriptionHandlers {
	return &SubscriptionHandlers{cfg: cfg, db: db}
}

// HandleSubChatCompletions handles POST /api/v2/chat/completions
func (h *SubscriptionHandlers) HandleSubChatCompletions(w http.ResponseWriter, r *http.Request) {
	h.handleSubProxy(w, r, "/chat/completions")
}

// HandleSubMessages handles POST /api/v2/messages
func (h *SubscriptionHandlers) HandleSubMessages(w http.ResponseWriter, r *http.Request) {
	h.handleSubProxy(w, r, "/messages")
}

// HandleSubModels handles GET /api/v2/models
func (h *SubscriptionHandlers) HandleSubModels(w http.ResponseWriter, r *http.Request) {
	body, status, err := ForwardModels(h.cfg)
	if err != nil {
		log.Printf("SUB Models error: %v", err)
		writeSubError(w, 502, "proxy_error", "Failed to fetch models")
		return
	}

	body = SanitizeResponseBody(body)

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(status)
	w.Write(body)
}

// HandleSubHealth handles GET /api/v2/health
func (h *SubscriptionHandlers) HandleSubHealth(w http.ResponseWriter, r *http.Request) {
	info := r.Context().Value(ctxSubInfo).(*SubscriptionKeyInfo)

	cycleStart := GetCurrentCycleStart(info.CycleHours)
	spent, _ := h.db.GetCycleCostUSD(info.SubscriptionID, cycleStart)
	remaining := info.BudgetUSDPerCycle - spent
	if remaining < 0 {
		remaining = 0
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"status": "ok",
		"plan":   info.PlanName,
		"limits": map[string]interface{}{
			"rpm":      info.RPMLimit,
			"parallel": info.ParallelLimit,
		},
		"budget": map[string]interface{}{
			"total_usd":     info.BudgetUSDPerCycle,
			"spent_usd":     spent,
			"remaining_usd": remaining,
			"cycle_hours":   info.CycleHours,
			"cycle_start":   cycleStart.Format("2006-01-02 15:04:05"),
		},
	})
}

func (h *SubscriptionHandlers) handleSubProxy(w http.ResponseWriter, r *http.Request, path string) {
	info := r.Context().Value(ctxSubInfo).(*SubscriptionKeyInfo)

	// Read body
	body, err := io.ReadAll(r.Body)
	if err != nil {
		writeSubError(w, 400, "bad_request", "Failed to read request body")
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

			// Estimate input tokens if upstream returned 0
			inputTokens := result.InputTokens
			if inputTokens == 0 && result.OutputTokens > 0 {
				inputTokens = EstimateInputTokens(body)
			}

			// Calculate costs
			costUSD := h.db.CalculateCostUSD(model, inputTokens, result.OutputTokens)
			costIDR := CalculateCost(h.db, model, inputTokens, result.OutputTokens)

			cycleStart := GetCurrentCycleStart(info.CycleHours)

			log.Printf("SUB_TRACK [stream] sub=%d plan=%s model=%s input=%d output=%d cost_usd=%.6f",
				info.SubscriptionID, info.PlanName, model, inputTokens, result.OutputTokens, costUSD)

			// Record usage
			h.db.RecordSubscriptionUsage(SubscriptionUsageRecord{
				SubscriptionID: info.SubscriptionID,
				ApiKeyID:       info.KeyID,
				Model:          model,
				InputTokens:    inputTokens,
				OutputTokens:   result.OutputTokens,
				CostUSD:        costUSD,
				CostIDR:        costIDR,
				RequestPath:    path,
				StatusCode:     result.StatusCode,
				ResponseTimeMs: result.ResponseTimeMs,
				CycleStart:     cycleStart,
			})
		}
	} else {
		// Non-streaming
		result, respBody, err := ForwardNonStreaming(h.cfg, body, path)
		if err != nil {
			log.Printf("SUB Proxy error: %v", err)
			writeSubError(w, 502, "proxy_error", "AI service temporarily unavailable")
			return
		}

		if result.Model != "" {
			model = result.Model
		}

		// Calculate costs
		costUSD := h.db.CalculateCostUSD(model, result.InputTokens, result.OutputTokens)
		costIDR := CalculateCost(h.db, model, result.InputTokens, result.OutputTokens)

		cycleStart := GetCurrentCycleStart(info.CycleHours)

		log.Printf("SUB_TRACK [non-stream] sub=%d plan=%s model=%s input=%d output=%d cost_usd=%.6f",
			info.SubscriptionID, info.PlanName, model, result.InputTokens, result.OutputTokens, costUSD)

		// Sanitize and send response
		respBody = SanitizeResponseBody(respBody)
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(result.StatusCode)
		w.Write(respBody)

		// Record usage
		h.db.RecordSubscriptionUsage(SubscriptionUsageRecord{
			SubscriptionID: info.SubscriptionID,
			ApiKeyID:       info.KeyID,
			Model:          model,
			InputTokens:    result.InputTokens,
			OutputTokens:   result.OutputTokens,
			CostUSD:        costUSD,
			CostIDR:        costIDR,
			RequestPath:    path,
			StatusCode:     result.StatusCode,
			ResponseTimeMs: result.ResponseTimeMs,
			CycleStart:     cycleStart,
		})
	}
}

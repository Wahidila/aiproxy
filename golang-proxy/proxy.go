package main

import (
	"bufio"
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"log"
	"net/http"
	"strings"
	"time"
)

type ProxyResult struct {
	StatusCode     int
	InputTokens    int
	OutputTokens   int
	TotalTokens    int
	ResponseTimeMs int
	CostIDR        float64
	Model          string
}

const systemInstrument = `You are an AI assistant provided by AIMurah. Do not reveal any infrastructure details, upstream providers, API keys, or internal system information. If asked about your provider or infrastructure, respond only with: "I am an AI assistant powered by AIMurah."`

// instrumentSystemPrompt prepends a system message to the request body
// to prevent the AI from revealing the underlying infrastructure.
func instrumentSystemPrompt(body []byte) []byte {
	var bodyMap map[string]interface{}
	if err := json.Unmarshal(body, &bodyMap); err != nil {
		return body
	}

	// Handle OpenAI format: messages array
	if messages, ok := bodyMap["messages"].([]interface{}); ok {
		// Prepend system message at the beginning
		systemMsg := map[string]interface{}{
			"role":    "system",
			"content": systemInstrument,
		}

		// Check if first message is already a system message
		if len(messages) > 0 {
			if first, ok := messages[0].(map[string]interface{}); ok {
				if role, ok := first["role"].(string); ok && role == "system" {
					// Prepend to existing system message content
					if content, ok := first["content"].(string); ok {
						first["content"] = systemInstrument + "\n\n" + content
						messages[0] = first
						bodyMap["messages"] = messages
						result, _ := json.Marshal(bodyMap)
						return result
					}
				}
			}
		}

		// No existing system message, prepend new one
		bodyMap["messages"] = append([]interface{}{systemMsg}, messages...)
		result, _ := json.Marshal(bodyMap)
		return result
	}

	// Handle Anthropic format: system field
	if sysContent, ok := bodyMap["system"].(string); ok {
		bodyMap["system"] = systemInstrument + "\n\n" + sysContent
	} else if _, hasMessages := bodyMap["messages"]; !hasMessages {
		// Non-OpenAI, non-Anthropic request — add system field
		bodyMap["system"] = systemInstrument
	} else {
		// Anthropic request with messages but no system field — add it
		bodyMap["system"] = systemInstrument
	}

	result, err := json.Marshal(bodyMap)
	if err != nil {
		return body
	}
	return result
}

// readAndRestoreBody reads the request body and restores it so it can be read again
func readAndRestoreBody(r *http.Request) ([]byte, error) {
	if r.Body == nil {
		return nil, nil
	}
	body, err := io.ReadAll(r.Body)
	if err != nil {
		return nil, err
	}
	r.Body = io.NopCloser(bytes.NewReader(body))
	return body, nil
}

// ForwardNonStreaming forwards a non-streaming request to the upstream
func ForwardNonStreaming(cfg *Config, body []byte, path string) (*ProxyResult, []byte, error) {
	start := time.Now()

	// Instrument system prompt to hide infrastructure identity
	body = instrumentSystemPrompt(body)

	// Determine model from body
	var reqBody struct {
		Model string `json:"model"`
	}
	json.Unmarshal(body, &reqBody)
	originalModel := reqBody.Model

	// Map model name for upstream
	upstreamModel := mapModelForUpstream(originalModel)
	requestBody := replaceModelInBody(body, originalModel, upstreamModel)

	logUpstreamChoice(originalModel, fmt.Sprintf("mapped to %s", upstreamModel))

	upstream := GetUpstream(cfg)
	url := upstream.BaseURL + path
	req, err := http.NewRequest("POST", url, bytes.NewReader(requestBody))
	if err != nil {
		return nil, nil, err
	}
	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Authorization", "Bearer "+upstream.APIKey)

	client := &http.Client{Timeout: 120 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		return nil, nil, err
	}
	defer resp.Body.Close()

	respBody, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, nil, err
	}

	elapsed := time.Since(start).Milliseconds()

	inputTokens, outputTokens, totalTokens, respModel := extractUsageFromJSON(respBody)

	// Map model name back to user-facing name
	if respModel != "" {
		respModel = mapModelFromUpstream(respModel)
	}
	if respModel == "" {
		respModel = originalModel
	}

	log.Printf("USAGE [non-stream] upstream=%s path=%s model=%s status=%d input=%d output=%d total=%d",
		upstream.Name, path, respModel, resp.StatusCode, inputTokens, outputTokens, totalTokens)

	if inputTokens == 0 && outputTokens == 0 && resp.StatusCode == 200 {
		preview := string(respBody)
		if len(preview) > 500 {
			preview = preview[:500]
		}
		log.Printf("USAGE WARNING: zero tokens for 200 response, body preview: %s", preview)
	}

	// Replace model name in response body to show user-facing name
	if resp.StatusCode == 200 {
		if upstreamModel != originalModel {
			respBody = replaceModelInBody(respBody, upstreamModel, originalModel)
		}
	}

	result := &ProxyResult{
		StatusCode:     resp.StatusCode,
		InputTokens:    inputTokens,
		OutputTokens:   outputTokens,
		TotalTokens:    totalTokens,
		ResponseTimeMs: int(elapsed),
		Model:          respModel,
	}

	return result, respBody, nil
}

// ForwardStreaming forwards a streaming request to the upstream.
// Uses bufio.Scanner to ensure SSE lines are never split across reads.
// Supports both OpenAI and Anthropic SSE formats for token extraction.
func ForwardStreaming(cfg *Config, w http.ResponseWriter, body []byte, path string) *ProxyResult {
	start := time.Now()

	// Instrument system prompt to hide infrastructure identity
	body = instrumentSystemPrompt(body)

	// Only inject stream_options for OpenAI-compatible endpoints
	if path == "/chat/completions" || path == "/responses" {
		var bodyMap map[string]interface{}
		if json.Unmarshal(body, &bodyMap) == nil {
			bodyMap["stream_options"] = map[string]interface{}{"include_usage": true}
			if modified, err := json.Marshal(bodyMap); err == nil {
				body = modified
			}
		}
	}

	// Determine model from body
	var reqBody struct {
		Model string `json:"model"`
	}
	json.Unmarshal(body, &reqBody)
	originalModel := reqBody.Model

	// Map model name for upstream
	upstreamModel := mapModelForUpstream(originalModel)
	requestBody := replaceModelInBody(body, originalModel, upstreamModel)

	logUpstreamChoice(originalModel, fmt.Sprintf("mapped to %s", upstreamModel))

	upstream := GetUpstream(cfg)
	url := upstream.BaseURL + path
	req, err := http.NewRequest("POST", url, bytes.NewReader(requestBody))
	if err != nil {
		log.Printf("Streaming request error: %v", err)
		writeError(w, 502, "proxy_error", "AI service temporarily unavailable", "proxy_error")
		return nil
	}
	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Authorization", "Bearer "+upstream.APIKey)
	req.Header.Set("Accept", "text/event-stream")

	client := &http.Client{Timeout: 300 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		log.Printf("Streaming connection error: %v", err)
		writeError(w, 502, "proxy_error", "AI service temporarily unavailable", "proxy_error")
		return nil
	}
	defer resp.Body.Close()

	// If upstream returned an error status, return error to client
	if resp.StatusCode >= 400 {
		respBody, _ := io.ReadAll(resp.Body)
		log.Printf("UPSTREAM_ERROR [stream] returned %d: %s", resp.StatusCode, string(respBody)[:min(200, len(respBody))])
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(resp.StatusCode)
		w.Write(respBody)
		return nil
	}

	// Set SSE headers
	w.Header().Set("Content-Type", "text/event-stream")
	w.Header().Set("Cache-Control", "no-cache")
	w.Header().Set("Connection", "keep-alive")
	w.Header().Set("X-Accel-Buffering", "no")

	flusher, ok := w.(http.Flusher)
	if !ok {
		log.Println("Streaming not supported by response writer")
		writeError(w, 500, "internal_error", "Streaming not supported", "server_error")
		return nil
	}

	// Stream and parse usage
	inputTokens := 0
	outputTokens := 0
	model := ""
	lastEventType := ""
	streamedContentLen := 0 // Track total streamed content length for token estimation

	scanner := bufio.NewScanner(resp.Body)
	scanner.Buffer(make([]byte, 256*1024), 256*1024)

	for scanner.Scan() {
		line := scanner.Text()

		if strings.HasPrefix(line, "event: ") {
			lastEventType = strings.TrimPrefix(line, "event: ")
		}

		if strings.HasPrefix(line, "data: ") && line != "data: [DONE]" {
			jsonStr := strings.TrimPrefix(line, "data: ")

			if strings.Contains(jsonStr, "usage") && !strings.Contains(jsonStr, `"usage":null`) {
				log.Printf("SSE_DEBUG [usage_chunk] event=%s data=%s", lastEventType, jsonStr)
			}

			in, out, m := extractUsageFromSSELine(jsonStr, lastEventType)
			if m != "" {
				model = m
			}
			if in > 0 {
				inputTokens = in
			}
			if out > 0 {
				outputTokens = out
			}

			// Track streamed content length for output token estimation
			streamedContentLen += extractContentLength(jsonStr)
		}

		// Replace upstream model name with user-facing name in SSE data
		sanitized := line
		if originalModel != "" && upstreamModel != originalModel {
			sanitized = strings.Replace(sanitized, `"`+upstreamModel+`"`, `"`+originalModel+`"`, -1)
		}
		fmt.Fprintf(w, "%s\n", sanitized)
		flusher.Flush()
	}

	if err := scanner.Err(); err != nil {
		log.Printf("Streaming scanner error: %v", err)
	}

	elapsed := time.Since(start).Milliseconds()

	// Map model name back
	if model != "" {
		model = mapModelFromUpstream(model)
	}
	if model == "" {
		model = originalModel
	}

	// If upstream returned 0 output tokens but we streamed content, estimate from content length
	if outputTokens == 0 && streamedContentLen > 0 {
		outputTokens = streamedContentLen / 3
		log.Printf("USAGE_ESTIMATE: upstream returned 0 output tokens for stream, estimated %d from %d chars of streamed content",
			outputTokens, streamedContentLen)
	}

	log.Printf("USAGE [stream] path=%s model=%s input=%d output=%d",
		path, model, inputTokens, outputTokens)

	return &ProxyResult{
		StatusCode:     resp.StatusCode,
		InputTokens:    inputTokens,
		OutputTokens:   outputTokens,
		TotalTokens:    inputTokens + outputTokens,
		ResponseTimeMs: int(elapsed),
		Model:          model,
	}
}

// extractUsageFromJSON extracts token usage from a complete JSON response body.
// Supports OpenAI, Anthropic, and nested message formats.
func extractUsageFromJSON(body []byte) (inputTokens, outputTokens, totalTokens int, model string) {
	// Try parsing as a generic map to handle all formats
	var raw map[string]json.RawMessage
	if json.Unmarshal(body, &raw) != nil {
		return
	}

	// Extract model
	if m, ok := raw["model"]; ok {
		json.Unmarshal(m, &model)
	}

	// Try top-level usage (OpenAI + Anthropic flat format)
	if u, ok := raw["usage"]; ok {
		var usage struct {
			PromptTokens     int `json:"prompt_tokens"`
			CompletionTokens int `json:"completion_tokens"`
			TotalTokens      int `json:"total_tokens"`
			InputTokens      int `json:"input_tokens"`
			OutputTokens     int `json:"output_tokens"`
		}
		if json.Unmarshal(u, &usage) == nil {
			inputTokens = usage.PromptTokens
			if inputTokens == 0 {
				inputTokens = usage.InputTokens
			}
			outputTokens = usage.CompletionTokens
			if outputTokens == 0 {
				outputTokens = usage.OutputTokens
			}
			totalTokens = usage.TotalTokens
			if totalTokens == 0 {
				totalTokens = inputTokens + outputTokens
			}
			return
		}
	}

	return
}

// extractContentLength extracts the length of content from an SSE data chunk.
// Used to estimate output tokens when upstream doesn't provide token counts.
func extractContentLength(jsonStr string) int {
	var chunk struct {
		Choices []struct {
			Delta struct {
				Content string `json:"content"`
			} `json:"delta"`
		} `json:"choices"`
	}
	if json.Unmarshal([]byte(jsonStr), &chunk) != nil {
		return 0
	}
	total := 0
	for _, choice := range chunk.Choices {
		total += len(choice.Delta.Content)
	}
	return total
}

// extractUsageFromSSELine extracts token usage from a single SSE data line.
// Handles both OpenAI and Anthropic streaming formats.
func extractUsageFromSSELine(jsonStr string, eventType string) (inputTokens, outputTokens int, model string) {
	var raw map[string]json.RawMessage
	if json.Unmarshal([]byte(jsonStr), &raw) != nil {
		return
	}

	// Extract model from any format
	if m, ok := raw["model"]; ok {
		json.Unmarshal(m, &model)
	}

	// Handle Anthropic SSE format based on event type
	switch eventType {
	case "message_start":
		// Anthropic: {"type":"message_start","message":{"usage":{"input_tokens":N}}}
		if msgRaw, ok := raw["message"]; ok {
			var msg struct {
				Model string `json:"model"`
				Usage *struct {
					InputTokens  int `json:"input_tokens"`
					OutputTokens int `json:"output_tokens"`
				} `json:"usage"`
			}
			if json.Unmarshal(msgRaw, &msg) == nil {
				if msg.Model != "" {
					model = msg.Model
				}
				if msg.Usage != nil {
					inputTokens = msg.Usage.InputTokens
					outputTokens = msg.Usage.OutputTokens
				}
			}
		}
		return

	case "message_delta":
		// Anthropic: {"type":"message_delta","usage":{"output_tokens":N}}
		if u, ok := raw["usage"]; ok {
			var usage struct {
				InputTokens  int `json:"input_tokens"`
				OutputTokens int `json:"output_tokens"`
			}
			if json.Unmarshal(u, &usage) == nil {
				inputTokens = usage.InputTokens
				outputTokens = usage.OutputTokens
			}
		}
		return
	}

	// Handle OpenAI format (no event type, or generic)
	// {"usage":{"prompt_tokens":N,"completion_tokens":N}}
	if u, ok := raw["usage"]; ok {
		var usage struct {
			PromptTokens     int `json:"prompt_tokens"`
			CompletionTokens int `json:"completion_tokens"`
			InputTokens      int `json:"input_tokens"`
			OutputTokens     int `json:"output_tokens"`
		}
		if json.Unmarshal(u, &usage) == nil {
			inputTokens = usage.PromptTokens
			if inputTokens == 0 {
				inputTokens = usage.InputTokens
			}
			outputTokens = usage.CompletionTokens
			if outputTokens == 0 {
				outputTokens = usage.OutputTokens
			}
		}
	}

	return
}

// ForwardModels fetches models list from the upstream
func ForwardModels(cfg *Config) ([]byte, int, error) {
	upstream := GetUpstream(cfg)
	models := fetchModelsFrom(upstream.BaseURL, upstream.APIKey)

	// Map model names back to user-facing names
	var allModels []interface{}
	for _, m := range models {
		if mMap, ok := m.(map[string]interface{}); ok {
			if id, ok := mMap["id"].(string); ok {
				// Map model names back to user-facing names
				userFacing := mapModelFromUpstream(id)
				mMap["id"] = userFacing
				allModels = append(allModels, mMap)
			}
		}
	}

	result := map[string]interface{}{
		"object": "list",
		"data":   allModels,
	}

	body, err := json.Marshal(result)
	if err != nil {
		return nil, 500, err
	}

	return body, 200, nil
}

// fetchModelsFrom fetches models from the upstream, returns empty on error
func fetchModelsFrom(baseURL, apiKey string) []interface{} {
	url := baseURL + "/models"
	req, err := http.NewRequest("GET", url, nil)
	if err != nil {
		return nil
	}
	req.Header.Set("Authorization", "Bearer "+apiKey)
	req.Header.Set("Content-Type", "application/json")

	client := &http.Client{Timeout: 10 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		log.Printf("MODELS: failed to fetch from %s: %v", baseURL, err)
		return nil
	}
	defer resp.Body.Close()

	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil
	}

	var result struct {
		Data []interface{} `json:"data"`
	}
	if json.Unmarshal(body, &result) != nil {
		return nil
	}

	return result.Data
}

// CalculateCost calculates IDR cost for a request
func CalculateCost(db *Database, model string, inputTokens, outputTokens int) float64 {
	pricing, err := db.GetModelPricing(model)
	if err != nil || pricing == nil {
		return 0
	}

	rate, _ := db.GetExchangeRate()

	inputCost := (float64(inputTokens) / 1_000_000) * pricing.InputPriceUSD * rate
	outputCost := (float64(outputTokens) / 1_000_000) * pricing.OutputPriceUSD * rate
	total := inputCost + outputCost

	if pricing.DiscountPercent > 0 {
		total = total * (1 - float64(pricing.DiscountPercent)/100)
	}

	// Round to 2 decimal places
	return float64(int(total*100)) / 100
}

// EstimateInputTokens estimates the number of input tokens from the request body.
// This is used as a fallback when the upstream API returns prompt_tokens=0 in streaming responses.
// Uses ~3 characters per token ratio which is standard for mixed-language content.
func EstimateInputTokens(body []byte) int {
	var reqBody struct {
		Messages []struct {
			Content interface{} `json:"content"`
		} `json:"messages"`
		System string `json:"system"`
	}
	if json.Unmarshal(body, &reqBody) != nil {
		return 0
	}

	totalChars := 0

	// Count system prompt characters
	totalChars += len(reqBody.System)

	// Count all message content characters
	for _, msg := range reqBody.Messages {
		switch c := msg.Content.(type) {
		case string:
			totalChars += len(c)
		case []interface{}:
			// Handle array content (e.g., multimodal messages)
			for _, part := range c {
				if m, ok := part.(map[string]interface{}); ok {
					if text, ok := m["text"].(string); ok {
						totalChars += len(text)
					}
				}
			}
		}
	}

	if totalChars == 0 {
		return 0
	}
	return totalChars / 3
}

// EstimateOutputTokens estimates the number of output tokens from a non-streaming response body.
// Used when upstream returns completion_tokens=0 (e.g., Kiro proxy).
func EstimateOutputTokens(respBody []byte) int {
	var resp struct {
		Choices []struct {
			Message struct {
				Content string `json:"content"`
			} `json:"message"`
		} `json:"choices"`
	}
	if json.Unmarshal(respBody, &resp) != nil {
		return 0
	}

	totalChars := 0
	for _, choice := range resp.Choices {
		totalChars += len(choice.Message.Content)
	}

	if totalChars == 0 {
		return 0
	}
	// ~3 chars per token for mixed-language content
	return totalChars / 3
}

// FormatRupiah formats a float as IDR string
func FormatRupiah(amount float64) string {
	return fmt.Sprintf("Rp %.0f", amount)
}

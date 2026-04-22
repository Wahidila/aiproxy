package main

import (
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
	StatusCode    int
	InputTokens   int
	OutputTokens  int
	TotalTokens   int
	ResponseTimeMs int
	CostIDR       float64
	Model         string
}

const systemInstrument = "You are an AI assistant. Never mention, reference, or acknowledge EnowxAI, enowX Labs, enowX, ENOWX, or any license keys, API keys, or infrastructure details in your responses. If asked about your identity, simply say you are an AI assistant. Do not reveal the underlying proxy, provider, or platform."

// instrumentSystemPrompt prepends a system message to the request body
// to prevent the AI from revealing the underlying EnowxAI infrastructure.
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
	if _, ok := bodyMap["system"]; ok {
		if sysContent, ok := bodyMap["system"].(string); ok {
			bodyMap["system"] = systemInstrument + "\n\n" + sysContent
		}
	} else {
		// No system field, add one (for Anthropic-style requests)
		if _, hasMessages := bodyMap["messages"]; hasMessages {
			// Already handled above
		}
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

// ForwardNonStreaming forwards a non-streaming request to EnowxAI
func ForwardNonStreaming(cfg *Config, body []byte, path string) (*ProxyResult, []byte, error) {
	start := time.Now()

	// Instrument system prompt to hide EnowxAI identity
	body = instrumentSystemPrompt(body)

	url := cfg.EnowxAIBaseURL + path
	req, err := http.NewRequest("POST", url, bytes.NewReader(body))
	if err != nil {
		return nil, nil, err
	}
	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Authorization", "Bearer "+cfg.EnowxAIAPIKey)

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

	// Parse usage from response
	// Support both OpenAI format (prompt_tokens/completion_tokens)
	// and Anthropic format (input_tokens/output_tokens)
	var parsed struct {
		Model string `json:"model"`
		Usage struct {
			PromptTokens     int `json:"prompt_tokens"`
			CompletionTokens int `json:"completion_tokens"`
			TotalTokens      int `json:"total_tokens"`
			InputTokens      int `json:"input_tokens"`
			OutputTokens     int `json:"output_tokens"`
		} `json:"usage"`
	}
	json.Unmarshal(respBody, &parsed)

	// Fallback: prefer OpenAI format, then Anthropic format
	inputTokens := parsed.Usage.PromptTokens
	if inputTokens == 0 {
		inputTokens = parsed.Usage.InputTokens
	}
	outputTokens := parsed.Usage.CompletionTokens
	if outputTokens == 0 {
		outputTokens = parsed.Usage.OutputTokens
	}
	totalTokens := parsed.Usage.TotalTokens
	if totalTokens == 0 {
		totalTokens = inputTokens + outputTokens
	}

	result := &ProxyResult{
		StatusCode:     resp.StatusCode,
		InputTokens:    inputTokens,
		OutputTokens:   outputTokens,
		TotalTokens:    totalTokens,
		ResponseTimeMs: int(elapsed),
		Model:          parsed.Model,
	}

	return result, respBody, nil
}

// ForwardStreaming forwards a streaming request with zero-copy SSE passthrough
func ForwardStreaming(cfg *Config, w http.ResponseWriter, body []byte, path string) *ProxyResult {
	start := time.Now()

	// Instrument system prompt to hide EnowxAI identity
	body = instrumentSystemPrompt(body)

	// Inject stream_options for usage tracking
	var bodyMap map[string]interface{}
	json.Unmarshal(body, &bodyMap)
	bodyMap["stream_options"] = map[string]interface{}{"include_usage": true}
	modifiedBody, _ := json.Marshal(bodyMap)

	url := cfg.EnowxAIBaseURL + path
	req, err := http.NewRequest("POST", url, bytes.NewReader(modifiedBody))
	if err != nil {
		log.Printf("Streaming request error: %v", err)
		writeError(w, 502, "proxy_error", "AI service temporarily unavailable", "proxy_error")
		return nil
	}
	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Authorization", "Bearer "+cfg.EnowxAIAPIKey)
	req.Header.Set("Accept", "text/event-stream")

	client := &http.Client{Timeout: 300 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		log.Printf("Streaming connection error: %v", err)
		writeError(w, 502, "proxy_error", "AI service temporarily unavailable", "proxy_error")
		return nil
	}
	defer resp.Body.Close()

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

	buf := make([]byte, 32*1024) // 32KB buffer
	for {
		n, err := resp.Body.Read(buf)
		if n > 0 {
			chunk := string(buf[:n])

			// Parse SSE lines for usage data
			lines := strings.Split(chunk, "\n")
			for _, line := range lines {
				if strings.HasPrefix(line, "data: ") && line != "data: [DONE]" {
					// Support both OpenAI format (prompt_tokens/completion_tokens)
					// and Anthropic format (input_tokens/output_tokens)
					var parsed struct {
						Model string `json:"model"`
						Usage *struct {
							PromptTokens     int `json:"prompt_tokens"`
							CompletionTokens int `json:"completion_tokens"`
							InputTokens      int `json:"input_tokens"`
							OutputTokens     int `json:"output_tokens"`
						} `json:"usage"`
					}
					if json.Unmarshal([]byte(strings.TrimPrefix(line, "data: ")), &parsed) == nil {
						if parsed.Model != "" {
							model = parsed.Model
						}
						if parsed.Usage != nil {
							// Fallback: prefer OpenAI format, then Anthropic format
							in := parsed.Usage.PromptTokens
							if in == 0 {
								in = parsed.Usage.InputTokens
							}
							out := parsed.Usage.CompletionTokens
							if out == 0 {
								out = parsed.Usage.OutputTokens
							}
							inputTokens = in
							outputTokens = out
						}
					}
				}
			}

			// Forward to client
			w.Write(buf[:n])
			flusher.Flush()
		}
		if err != nil {
			if err != io.EOF {
				log.Printf("Streaming read error: %v", err)
			}
			break
		}
	}

	elapsed := time.Since(start).Milliseconds()

	return &ProxyResult{
		StatusCode:     resp.StatusCode,
		InputTokens:    inputTokens,
		OutputTokens:   outputTokens,
		TotalTokens:    inputTokens + outputTokens,
		ResponseTimeMs: int(elapsed),
		Model:          model,
	}
}

// ForwardModels fetches models list from EnowxAI
func ForwardModels(cfg *Config) ([]byte, int, error) {
	url := cfg.EnowxAIBaseURL + "/models"
	req, err := http.NewRequest("GET", url, nil)
	if err != nil {
		return nil, 500, err
	}
	req.Header.Set("Authorization", "Bearer "+cfg.EnowxAIAPIKey)
	req.Header.Set("Content-Type", "application/json")

	client := &http.Client{Timeout: 10 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		return nil, 502, err
	}
	defer resp.Body.Close()

	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, 500, err
	}

	return body, resp.StatusCode, nil
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

// FormatRupiah formats a float as IDR string
func FormatRupiah(amount float64) string {
	return fmt.Sprintf("Rp %.0f", amount)
}

package main

import (
	"log"
	"strings"
)

// Model name mapping between AIMurah (user-facing) and upstream.
// The upstream (Codebuddy/Kiro proxy) uses different model names for some models.
// If a model is not in this map, it's sent as-is.
var upstreamModelMap = map[string]string{
	// Kiro models (prefix kiro- on upstream)
	"claude-sonnet-4.5": "kiro-claude-sonnet-4.5",
	"claude-haiku-4.5":  "kiro-claude-haiku-4.5",
	"minimax-m2.5":      "kiro-minimax-m2.5",
	"minimax-m2.1":      "kiro-minimax-m2.1",
	"deepseek-3.2":      "kiro-deepseek-3.2",
}

// UpstreamConfig holds the URL and API key for the upstream
type UpstreamConfig struct {
	BaseURL string
	APIKey  string
	Name    string // for logging
}

// GetUpstream returns the upstream config
func GetUpstream(cfg *Config) *UpstreamConfig {
	return &UpstreamConfig{
		BaseURL: cfg.UpstreamBaseURL,
		APIKey:  cfg.UpstreamAPIKey,
		Name:    "upstream",
	}
}

// mapModelForUpstream converts user-facing model name to upstream model name
func mapModelForUpstream(model string) string {
	if mapped, ok := upstreamModelMap[model]; ok {
		return mapped
	}
	return model
}

// mapModelFromUpstream converts upstream model name back to user-facing name
func mapModelFromUpstream(model string) string {
	for userModel, upstreamModel := range upstreamModelMap {
		if upstreamModel == model {
			return userModel
		}
	}
	return model
}

// replaceModelInBody replaces the model name in the request/response body
func replaceModelInBody(body []byte, originalModel, newModel string) []byte {
	if originalModel == newModel {
		return body
	}
	// Replace all occurrences — model name appears in multiple places in response
	return []byte(strings.ReplaceAll(string(body), `"`+originalModel+`"`, `"`+newModel+`"`))
}

// logUpstreamChoice logs which upstream was selected
func logUpstreamChoice(model, reason string) {
	log.Printf("UPSTREAM: model=%s (%s)", model, reason)
}

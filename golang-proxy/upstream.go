package main

import (
	"log"
	"strings"
)

// Model name mapping between AIMurah (user-facing) and primary upstream.
// The primary upstream (Codebuddy/Kiro proxy) uses different model names.
// If a model is not in this map, it's sent as-is to both upstreams.
var primaryModelMap = map[string]string{
	// Kiro models (prefix kiro- on primary)
	"claude-sonnet-4.5": "kiro-claude-sonnet-4.5",
	"claude-haiku-4.5":  "kiro-claude-haiku-4.5",
	"minimax-m2.5":      "kiro-minimax-m2.5",
	"deepseek-3.2":      "kiro-deepseek-3.2",
}

// Models that should ONLY go to fallback (not available on primary)
var fallbackOnlyModels = map[string]bool{
	// Add models here that only exist on the fallback upstream
}

// Models that should ONLY go to primary (not available on fallback)
var primaryOnlyModels = map[string]bool{
	"kiro-auto":              true,
	"kiro-claude-sonnet-4.5": true,
	"kiro-claude-haiku-4.5":  true,
	"kiro-minimax-m2.5":      true,
	"kiro-deepseek-3.2":      true,
}

// mapModelForPrimary converts user-facing model name to primary upstream model name
func mapModelForPrimary(model string) string {
	if mapped, ok := primaryModelMap[model]; ok {
		return mapped
	}
	return model
}

// mapModelFromPrimary converts primary upstream model name back to user-facing name
func mapModelFromPrimary(model string) string {
	for userModel, primaryModel := range primaryModelMap {
		if primaryModel == model {
			return userModel
		}
	}
	return model
}

// shouldUsePrimaryOnly returns true if the model should only be routed to primary
func shouldUsePrimaryOnly(model string) bool {
	return primaryOnlyModels[model]
}

// shouldUseFallbackOnly returns true if the model should only be routed to fallback
func shouldUseFallbackOnly(model string) bool {
	return fallbackOnlyModels[model]
}

// isUpstreamError checks if an HTTP status code indicates an upstream error
// that should trigger fallback
func isUpstreamError(statusCode int) bool {
	return statusCode >= 500 || statusCode == 429 || statusCode == 0
}

// UpstreamConfig holds the URL and API key for a single upstream
type UpstreamConfig struct {
	BaseURL string
	APIKey  string
	Name    string // for logging
}

// GetPrimaryUpstream returns the primary upstream config
func GetPrimaryUpstream(cfg *Config) *UpstreamConfig {
	return &UpstreamConfig{
		BaseURL: cfg.PrimaryBaseURL,
		APIKey:  cfg.PrimaryAPIKey,
		Name:    "primary",
	}
}

// GetFallbackUpstream returns the fallback upstream config
func GetFallbackUpstream(cfg *Config) *UpstreamConfig {
	return &UpstreamConfig{
		BaseURL: cfg.FallbackBaseURL,
		APIKey:  cfg.FallbackAPIKey,
		Name:    "fallback",
	}
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
func logUpstreamChoice(model, upstream, reason string) {
	log.Printf("UPSTREAM: model=%s -> %s (%s)", model, upstream, reason)
}

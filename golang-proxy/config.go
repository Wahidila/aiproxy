package main

import (
	"os"
	"strconv"

	"github.com/joho/godotenv"
)

type Config struct {
	Port        string
	CorsOrigins string

	DBHost     string
	DBPort     string
	DBDatabase string
	DBUsername string
	DBPassword string
	DBMaxOpen  int
	DBMaxIdle  int

	// Primary upstream (Codebuddy/Kiro proxy)
	PrimaryBaseURL string
	PrimaryAPIKey  string

	// Fallback upstream (EnowxAI)
	FallbackBaseURL string
	FallbackAPIKey  string

	// Legacy aliases (kept for backward compat in proxy.go)
	EnowxAIBaseURL string
	EnowxAIAPIKey  string

	TrackingBufferSize   int
	PricingCacheTTLSecs  int
}

func LoadConfig() *Config {
	_ = godotenv.Load()

	primaryBase := getEnv("PRIMARY_BASE_URL", "http://[REDACTED]:3377/v1")
	primaryKey := getEnv("PRIMARY_API_KEY", "sk-e3ea7fb24c5879d99e3fa94c3c8a9095e5cdc224aa9ce8ce")
	fallbackBase := getEnv("FALLBACK_BASE_URL", getEnv("ENOWXAI_BASE_URL", "http://[REDACTED]:1434/v1"))
	fallbackKey := getEnv("FALLBACK_API_KEY", getEnv("ENOWXAI_API_KEY", ""))

	return &Config{
		Port:        getEnv("PORT", "8080"),
		CorsOrigins: getEnv("CORS_ORIGINS", "*"),

		DBHost:     getEnv("DB_HOST", "127.0.0.1"),
		DBPort:     getEnv("DB_PORT", "3306"),
		DBDatabase: getEnv("DB_DATABASE", "ai_token_dashboard"),
		DBUsername: getEnv("DB_USERNAME", "root"),
		DBPassword: getEnv("DB_PASSWORD", ""),
		DBMaxOpen:  getEnvInt("DB_MAX_OPEN_CONNS", 25),
		DBMaxIdle:  getEnvInt("DB_MAX_IDLE_CONNS", 10),

		PrimaryBaseURL:  primaryBase,
		PrimaryAPIKey:   primaryKey,
		FallbackBaseURL: fallbackBase,
		FallbackAPIKey:  fallbackKey,

		// Legacy aliases
		EnowxAIBaseURL: fallbackBase,
		EnowxAIAPIKey:  fallbackKey,

		TrackingBufferSize:  getEnvInt("TRACKING_BUFFER_SIZE", 10000),
		PricingCacheTTLSecs: getEnvInt("PRICING_CACHE_TTL_SECONDS", 60),
	}
}

func getEnv(key, fallback string) string {
	if v := os.Getenv(key); v != "" {
		return v
	}
	return fallback
}

func getEnvInt(key string, fallback int) int {
	if v := os.Getenv(key); v != "" {
		if i, err := strconv.Atoi(v); err == nil {
			return i
		}
	}
	return fallback
}

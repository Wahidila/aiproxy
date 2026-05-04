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

	// Upstream (AIMurah proxy)
	UpstreamBaseURL string
	UpstreamAPIKey  string

	TrackingBufferSize  int
	PricingCacheTTLSecs int
}

func LoadConfig() *Config {
	_ = godotenv.Load()

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

		// Support both new UPSTREAM_* and legacy PRIMARY_* env var names
		UpstreamBaseURL: getEnv("UPSTREAM_BASE_URL", getEnv("PRIMARY_BASE_URL", "")),
		UpstreamAPIKey:  getEnv("UPSTREAM_API_KEY", getEnv("PRIMARY_API_KEY", "")),

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

package main

import (
	"context"
	"log"
	"net/http"
	"os"
	"os/signal"
	"syscall"
	"time"
)

func main() {
	log.SetFlags(log.LstdFlags | log.Lshortfile)
	log.Println("Starting AI Token Proxy (Golang)...")

	// Load config
	cfg := LoadConfig()
	log.Printf("Config loaded: port=%s, db=%s@%s:%s/%s, enowxai=%s",
		cfg.Port, cfg.DBUsername, cfg.DBHost, cfg.DBPort, cfg.DBDatabase, cfg.EnowxAIBaseURL)

	// Connect to database
	db, err := NewDatabase(cfg)
	if err != nil {
		log.Fatalf("Failed to connect to database: %v", err)
	}
	defer db.Close()
	log.Println("Database connected successfully")

	// Pre-warm pricing cache
	if _, err := db.GetFreeTierModels(); err != nil {
		log.Printf("Warning: failed to pre-warm pricing cache: %v", err)
	} else {
		log.Println("Pricing cache warmed")
	}

	// Start async tracker
	tracker := NewTracker(db, cfg.TrackingBufferSize)
	log.Printf("Tracker started (buffer size: %d)", cfg.TrackingBufferSize)

	// Setup handlers
	handlers := NewHandlers(cfg, db, tracker)
	subHandlers := NewSubscriptionHandlers(cfg, db)

	// Setup routes
	mux := http.NewServeMux()

	// Health check (no auth)
	mux.HandleFunc("/v1/health", handlers.HandleHealth)

	// --- V1: Existing authenticated routes (wallet/token-based) ---
	authRoutes := http.NewServeMux()
	authRoutes.HandleFunc("/v1/chat/completions", handlers.HandleChatCompletions)
	authRoutes.HandleFunc("/v1/messages", handlers.HandleMessages)
	authRoutes.HandleFunc("/v1/responses", handlers.HandleResponses)
	authRoutes.HandleFunc("/v1/models", handlers.HandleModels)

	// Apply middleware chain: CORS -> Auth -> ModelRestriction -> Handler
	authed := AuthMiddleware(db,
		ModelRestrictionMiddleware(db, authRoutes),
	)
	mux.Handle("/v1/chat/completions", authed)
	mux.Handle("/v1/messages", authed)
	mux.Handle("/v1/responses", authed)
	mux.Handle("/v1/models", authed)

	// --- V2: Subscription API routes ---
	subRoutes := http.NewServeMux()
	subRoutes.HandleFunc("/api/v2/chat/completions", subHandlers.HandleSubChatCompletions)
	subRoutes.HandleFunc("/api/v2/messages", subHandlers.HandleSubMessages)
	subRoutes.HandleFunc("/api/v2/responses", subHandlers.HandleSubResponses)
	subRoutes.HandleFunc("/api/v2/models", subHandlers.HandleSubModels)
	subRoutes.HandleFunc("/api/v2/health", subHandlers.HandleSubHealth)

	// Apply subscription middleware chain: Auth -> RateLimit -> Budget -> Handler
	subAuthed := SubscriptionAuthMiddleware(db,
		SubscriptionRateLimitMiddleware(
			SubscriptionBudgetMiddleware(db, subRoutes),
		),
	)
	mux.Handle("/api/v2/chat/completions", subAuthed)
	mux.Handle("/api/v2/messages", subAuthed)
	mux.Handle("/api/v2/responses", subAuthed)
	mux.Handle("/api/v2/models", subAuthed)
	mux.Handle("/api/v2/health", subAuthed)

	// Apply CORS to all routes
	handler := CorsMiddleware(cfg.CorsOrigins, mux)

	// Create server
	server := &http.Server{
		Addr:         ":" + cfg.Port,
		Handler:      handler,
		ReadTimeout:  30 * time.Second,
		WriteTimeout: 300 * time.Second, // Long timeout for streaming
		IdleTimeout:  120 * time.Second,
	}

	// Start server in goroutine
	go func() {
		log.Printf("Server listening on :%s", cfg.Port)
		log.Printf("Endpoints:")
		log.Printf("  GET  /v1/health                (no auth)")
		log.Printf("  GET  /v1/models                (auth required)")
		log.Printf("  POST /v1/chat/completions      (auth required)")
		log.Printf("  POST /v1/messages              (auth required)")
		log.Printf("  POST /v1/responses             (auth required)")
		log.Printf("  --- Subscription API (v2) ---")
		log.Printf("  GET  /api/v2/health            (subscription key)")
		log.Printf("  GET  /api/v2/models            (subscription key)")
		log.Printf("  POST /api/v2/chat/completions  (subscription key)")
		log.Printf("  POST /api/v2/messages          (subscription key)")
		log.Printf("  POST /api/v2/responses         (subscription key)")
		if err := server.ListenAndServe(); err != nil && err != http.ErrServerClosed {
			log.Fatalf("Server error: %v", err)
		}
	}()

	// Graceful shutdown
	quit := make(chan os.Signal, 1)
	signal.Notify(quit, syscall.SIGINT, syscall.SIGTERM)
	<-quit

	log.Println("Shutting down server...")

	ctx, cancel := context.WithTimeout(context.Background(), 30*time.Second)
	defer cancel()

	if err := server.Shutdown(ctx); err != nil {
		log.Printf("Server forced to shutdown: %v", err)
	}

	// Stop tracker (process remaining events)
	log.Println("Flushing tracking events...")
	tracker.Stop()

	log.Println("Server stopped gracefully")
}

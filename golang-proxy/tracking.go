package main

import (
	"fmt"
	"log"
)

// TrackingEvent represents a usage event to be recorded asynchronously
type TrackingEvent struct {
	UserID       int64
	ApiKeyID     int64
	Tier         string // "free" or "paid"
	Model        string
	InputTokens  int
	OutputTokens int
	TotalTokens  int
	RequestPath  string
	StatusCode   int
	ResponseTime int
	CostIDR      float64
}

// Tracker handles async usage recording
type Tracker struct {
	db      *Database
	eventCh chan TrackingEvent
	done    chan struct{}
}

func NewTracker(db *Database, bufferSize int) *Tracker {
	t := &Tracker{
		db:      db,
		eventCh: make(chan TrackingEvent, bufferSize),
		done:    make(chan struct{}),
	}
	go t.processEvents()
	return t
}

// Track queues a tracking event (non-blocking)
func (t *Tracker) Track(event TrackingEvent) {
	select {
	case t.eventCh <- event:
		// queued successfully
	default:
		log.Printf("WARNING: tracking buffer full, dropping event for user %d", event.UserID)
	}
}

// Stop gracefully stops the tracker, processing remaining events
func (t *Tracker) Stop() {
	close(t.eventCh)
	<-t.done
}

func (t *Tracker) processEvents() {
	defer close(t.done)

	for event := range t.eventCh {
		t.processEvent(event)
	}
}

func (t *Tracker) processEvent(event TrackingEvent) {
	defer func() {
		if r := recover(); r != nil {
			log.Printf("ERROR: tracking panic for user %d: %v", event.UserID, r)
		}
	}()

	// 1. Record usage in token_usages
	usageID, err := t.db.RecordUsage(
		event.UserID,
		event.ApiKeyID,
		event.Model,
		event.InputTokens,
		event.OutputTokens,
		event.TotalTokens,
		event.RequestPath,
		event.StatusCode,
		event.ResponseTime,
		event.CostIDR,
	)
	if err != nil {
		log.Printf("ERROR: failed to record usage for user %d: %v", event.UserID, err)
		return
	}

	// 2. Deduct wallet balance based on tier
	if event.CostIDR > 0 {
		newBalance, ok, err := t.db.DeductBalance(event.UserID, event.CostIDR, event.Tier)
		if err != nil {
			log.Printf("ERROR: failed to deduct balance for user %d: %v", event.UserID, err)
			return
		}
		if !ok {
			log.Printf("WARNING: insufficient balance for user %d (cost: %.2f)", event.UserID, event.CostIDR)
			return
		}

		// 3. Record wallet transaction
		tierLabel := "[Paid]"
		if event.Tier == "free" {
			tierLabel = "[Free]"
		}
		description := fmt.Sprintf("%s %s: %d in + %d out = %s",
			tierLabel, event.Model, event.InputTokens, event.OutputTokens, FormatRupiah(event.CostIDR))

		err = t.db.RecordWalletTransaction(
			event.UserID,
			"usage",
			-event.CostIDR,
			newBalance,
			description,
			usageID,
			"App\\Models\\TokenUsage",
		)
		if err != nil {
			log.Printf("ERROR: failed to record wallet transaction for user %d: %v", event.UserID, err)
		}
	}

	// 4. Update API key last used (fire and forget)
	_ = t.db.UpdateApiKeyLastUsed(event.ApiKeyID)
}

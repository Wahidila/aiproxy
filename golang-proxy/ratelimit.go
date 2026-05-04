package main

import (
	"log"
	"sync"
	"time"
)

// SubscriptionInfo holds the user's active subscription + plan limits
type SubscriptionInfo struct {
	PlanSlug          string
	Status            string
	DailyRequestLimit *int   // nil = unlimited
	PerMinuteLimit    int    // 0 = unlimited
	ConcurrentLimit   int    // 0 = unlimited
	MaxTokenUsage     *int64 // nil = unlimited
	DailyRequestsUsed int
	TokenUsageTotal   int64
	ExpiresAt         *time.Time
	ResetAt           *time.Time
	AllowedModels     []string // nil = all models allowed
}

// ── Per-user rate limiter (sliding window per minute) ──────────────

type userRateState struct {
	mu       sync.Mutex
	requests []time.Time // timestamps of recent requests
}

type RateLimiter struct {
	mu    sync.RWMutex
	users map[int64]*userRateState
}

func NewRateLimiter() *RateLimiter {
	rl := &RateLimiter{
		users: make(map[int64]*userRateState),
	}
	// Cleanup stale entries every 5 minutes
	go rl.cleanup()
	return rl
}

// Allow checks if the user can make a request given their per-minute limit.
// Returns (allowed, currentCount, limit).
func (rl *RateLimiter) Allow(userID int64, limit int) (bool, int, int) {
	if limit <= 0 {
		return true, 0, 0 // unlimited
	}

	rl.mu.Lock()
	state, ok := rl.users[userID]
	if !ok {
		state = &userRateState{}
		rl.users[userID] = state
	}
	rl.mu.Unlock()

	state.mu.Lock()
	defer state.mu.Unlock()

	now := time.Now()
	cutoff := now.Add(-1 * time.Minute)

	// Remove old entries (sliding window)
	valid := state.requests[:0]
	for _, t := range state.requests {
		if t.After(cutoff) {
			valid = append(valid, t)
		}
	}
	state.requests = valid

	if len(state.requests) >= limit {
		return false, len(state.requests), limit
	}

	state.requests = append(state.requests, now)
	return true, len(state.requests), limit
}

func (rl *RateLimiter) cleanup() {
	ticker := time.NewTicker(5 * time.Minute)
	defer ticker.Stop()
	for range ticker.C {
		rl.mu.Lock()
		cutoff := time.Now().Add(-2 * time.Minute)
		for uid, state := range rl.users {
			state.mu.Lock()
			if len(state.requests) == 0 || state.requests[len(state.requests)-1].Before(cutoff) {
				delete(rl.users, uid)
			}
			state.mu.Unlock()
		}
		rl.mu.Unlock()
	}
}

// ── Per-user concurrent limiter (semaphore) ────────────────────────

type ConcurrentLimiter struct {
	mu    sync.Mutex
	users map[int64]int // userID -> active request count
}

func NewConcurrentLimiter() *ConcurrentLimiter {
	return &ConcurrentLimiter{
		users: make(map[int64]int),
	}
}

// Acquire tries to acquire a slot. Returns (allowed, current, limit).
func (cl *ConcurrentLimiter) Acquire(userID int64, limit int) (bool, int, int) {
	if limit <= 0 {
		return true, 0, 0 // unlimited
	}

	cl.mu.Lock()
	defer cl.mu.Unlock()

	current := cl.users[userID]
	if current >= limit {
		return false, current, limit
	}

	cl.users[userID] = current + 1
	return true, current + 1, limit
}

// Release releases a concurrent slot.
func (cl *ConcurrentLimiter) Release(userID int64) {
	cl.mu.Lock()
	defer cl.mu.Unlock()

	if cl.users[userID] > 0 {
		cl.users[userID]--
	}
	if cl.users[userID] == 0 {
		delete(cl.users, userID)
	}
}

// ── Daily request counter (in-memory + DB sync) ────────────────────

type dailyState struct {
	mu       sync.Mutex
	count    int
	resetAt  time.Time
	lastSync time.Time
}

type DailyCounter struct {
	mu    sync.RWMutex
	users map[int64]*dailyState
	db    *Database
}

func NewDailyCounter(db *Database) *DailyCounter {
	dc := &DailyCounter{
		users: make(map[int64]*dailyState),
		db:    db,
	}
	// Periodic DB sync every 30 seconds
	go dc.periodicSync()
	return dc
}

// LoadOrIncrement loads the user's daily count from DB (if not cached),
// checks the limit, and increments if allowed.
// Returns (allowed, currentCount, limit).
// Always tracks daily usage even for unlimited plans (for monitoring/dashboard).
func (dc *DailyCounter) LoadOrIncrement(userID int64, limit *int, dbUsed int, dbResetAt *time.Time) (bool, int, int) {
	dc.mu.Lock()
	state, ok := dc.users[userID]
	if !ok {
		// Initialize from DB values
		resetAt := endOfDay()
		if dbResetAt != nil && dbResetAt.After(time.Now()) {
			resetAt = *dbResetAt
		}
		state = &dailyState{
			count:   dbUsed,
			resetAt: resetAt,
		}
		dc.users[userID] = state
	}
	dc.mu.Unlock()

	state.mu.Lock()
	defer state.mu.Unlock()

	// Check if we need to reset (past end of day)
	if time.Now().After(state.resetAt) {
		state.count = 0
		state.resetAt = endOfDay()
		// Sync reset to DB immediately
		go dc.syncToDB(userID, 0, state.resetAt)
	}

	// Always increment counter (for tracking/monitoring)
	state.count++

	// If unlimited, always allow
	if limit == nil {
		return true, state.count, 0
	}

	// Check if over limit
	if state.count > *limit {
		state.count-- // rollback increment
		return false, state.count, *limit
	}

	return true, state.count, *limit
}

// GetCount returns the current daily count for a user (for logging).
func (dc *DailyCounter) GetCount(userID int64) int {
	dc.mu.RLock()
	state, ok := dc.users[userID]
	dc.mu.RUnlock()
	if !ok {
		return 0
	}
	state.mu.Lock()
	defer state.mu.Unlock()
	return state.count
}

func (dc *DailyCounter) syncToDB(userID int64, count int, resetAt time.Time) {
	err := dc.db.UpdateDailyRequests(userID, count, resetAt)
	if err != nil {
		log.Printf("ERROR: failed to sync daily count to DB for user %d: %v", userID, err)
	}
}

func (dc *DailyCounter) periodicSync() {
	ticker := time.NewTicker(30 * time.Second)
	defer ticker.Stop()
	for range ticker.C {
		dc.mu.RLock()
		snapshot := make(map[int64]*dailyState, len(dc.users))
		for uid, state := range dc.users {
			snapshot[uid] = state
		}
		dc.mu.RUnlock()

		for uid, state := range snapshot {
			state.mu.Lock()
			count := state.count
			resetAt := state.resetAt
			lastSync := state.lastSync
			state.mu.Unlock()

			// Only sync if changed since last sync
			if lastSync.IsZero() || time.Since(lastSync) > 25*time.Second {
				dc.syncToDB(uid, count, resetAt)
				state.mu.Lock()
				state.lastSync = time.Now()
				state.mu.Unlock()
			}
		}
	}
}

func endOfDay() time.Time {
	// Jakarta timezone (WIB, UTC+7)
	loc, err := time.LoadLocation("Asia/Jakarta")
	if err != nil {
		loc = time.FixedZone("WIB", 7*60*60)
	}
	now := time.Now().In(loc)
	return time.Date(now.Year(), now.Month(), now.Day(), 23, 59, 59, 0, loc)
}

# Remove Free Tier + Admin Subscription Management

> **Goal:** Hapus free tier API key, hanya ada Paid (pay-as-you-go) dan Subscription. User dengan free balance auto-migrasi ke Free subscription plan. Tambah admin subscription management lengkap.

## Architecture Changes

### Before (3 tiers):
- Free (wallet free_balance) ← HAPUS
- Paid (wallet paid_balance) ← TETAP
- Subscription (plan-based) ← TETAP

### After (2 tiers):
- **Paid** — pay-as-you-go, deduct dari `paid_balance` wallet
- **Subscription** — plan-based (FREE/PRO/PREMIUM/Harian), rate-limited

### Data Migration:
1. Zero-out semua `free_balance` di token_quotas
2. Convert semua `free` tier API keys → `subscription` tier
3. Auto-assign Free subscription plan ke semua users tanpa active subscription
4. Log semua konversi di wallet_transactions

---

## Phase 1: Database Migration

### Task 1.1: Create migration to remove free tier traces
- File: `database/migrations/2026_05_01_200000_remove_free_tier.php`
- Remove `free_balance` default, add index for subscription lookups

### Task 1.2: Update SubscriptionPlanSeeder
- Remove free tier references from features text
- Ensure FREE plan exists with proper limits

---

## Phase 2: Model & Backend Updates

### Task 2.1: Update ApiKey model
- Remove TIER_FREE constant
- Remove `isFree()` method
- Update `isWalletBased()` to only check paid
- Update `getTierLabelAttribute`

### Task 2.2: Update TokenQuota model
- Remove `deductFreeBalance()` method
- Remove `addFreeBalance()` method
- Simplify `deductBalance()` — no more free fallback
- Remove `hasBalanceForTier('free')` logic
- Keep `free_balance` column for historical data but stop using it

### Task 2.3: Update User model
- Update `getOrCreateQuota()` — no more free credit claim
- Ensure `subscribeTo()` works for auto-migration

### Task 2.4: Update CheckTokenQuota middleware
- Remove free tier wallet path
- Paid key: only check paid_balance
- Subscription key: unchanged

### Task 2.5: Update CheckSubscriptionLimits middleware
- No changes needed (already subscription-only)

### Task 2.6: Update ValidateApiKey middleware
- No changes needed

### Task 2.7: Update ProxyController / TokenTrackingService
- Remove free tier deduction logic

---

## Phase 3: Admin Subscription Management

### Task 3.1: Create Admin/SubscriptionPlanController
- index: list all plans with stats
- create/store: new plan form
- edit/update: edit plan
- destroy: delete plan (soft check for active subs)

### Task 3.2: Create Admin/SubscriptionController
- index: list all user subscriptions with filters
- show: subscription detail
- cancel: admin cancel subscription
- assign: admin assign plan to user
- stats: subscription statistics

### Task 3.3: Create admin views
- admin/subscriptions/plans.blade.php — CRUD plans
- admin/subscriptions/index.blade.php — user subscriptions list
- admin/subscriptions/stats.blade.php — statistics dashboard

### Task 3.4: Add admin routes

---

## Phase 4: Frontend Updates

### Task 4.1: Update api-keys/index.blade.php
- Remove "Free Tier" option from create form
- Only show Paid + Subscription options

### Task 4.2: Update dashboard.blade.php
- Remove free balance card
- Update subscription info display

### Task 4.3: Update pricing/index.blade.php
- Remove free tier section
- Show only Paid + Subscription plans

### Task 4.4: Update welcome.blade.php
- Remove free tier pricing
- Update hero/CTA messaging

### Task 4.5: Update subscriptions/index.blade.php
- Clean up for new 2-tier system

---

## Phase 5: Data Migration Script

### Task 5.1: Create artisan command for migration
- `php artisan app:migrate-free-to-subscription`
- Zero free_balance, convert keys, assign plans
- Dry-run mode for safety

# Subscription Plans Implementation Plan

> **For Hermes:** Use subagent-driven-development skill to implement this plan task-by-task.

**Goal:** Menerapkan sistem berlangganan (subscription) dengan 3 paket bulanan (FREE, PRO, PREMIUM) + 1 paket harian, termasuk rate limiting per plan (daily request, per-minute, concurrent), model access control, dan halaman pricing publik.

**Architecture:** Subscription-based access control yang menggantikan sistem tier sederhana (free/paid) saat ini. Setiap user memiliki 1 active subscription plan. Rate limiting diterapkan di middleware API. Model access dikontrol per plan. Billing tetap menggunakan wallet system yang sudah ada (balance-based), subscription hanya mengontrol rate limit & model access.

**Tech Stack:** Laravel 11, MySQL, Blade + Tailwind CSS, Redis (untuk rate limiting counter)

---

## Analisis Sistem Saat Ini

### Yang Sudah Ada:
- **User model** → `role` (admin/user), `is_banned`
- **ApiKey model** → `tier` (free/paid) — mengontrol model access & wallet deduction
- **TokenQuota model** → wallet balance (free_balance, paid_balance)
- **CheckTokenQuota middleware** → cek balance & model restriction berdasarkan `tier`
- **ValidateApiKey middleware** → validasi API key
- **ModelPricing** → `is_free_tier` flag per model

### Yang Perlu Diubah:
- Tier system (free/paid) → Plan system (free/pro/premium/daily)
- Rate limiting → per-plan (daily, per-minute, concurrent)
- Model access → per-plan (bukan hanya free/paid)
- Halaman pricing publik

---

## Database Schema

### Tabel Baru: `subscription_plans` (seeder, bukan user-editable)

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| slug | string unique | free, pro, premium, daily |
| name | string | FREE, PRO, PREMIUM, Harian |
| type | enum | monthly, daily |
| price_idr | integer | 0, 29000, 59000, 29000 |
| daily_request_limit | integer | 50, 3000, 10000, null (unlimited) |
| per_minute_limit | integer | 6, 30, 90, 60 |
| concurrent_limit | integer | 1, 2, 4, 3 |
| max_token_usage | bigint nullable | null, null, null, 100000000 (100M) |
| features | json | list of features for pricing page |
| is_popular | boolean | false, false, true, false |
| sort_order | integer | 1, 2, 3, 4 |
| created_at, updated_at | timestamps | |

### Tabel Baru: `user_subscriptions`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| user_id | bigint FK | |
| plan_slug | string FK | references subscription_plans.slug |
| status | enum | active, expired, cancelled |
| starts_at | datetime | |
| expires_at | datetime nullable | null = forever (free plan) |
| token_usage_total | bigint default 0 | for daily plan token cap |
| daily_requests_used | integer default 0 | reset daily |
| daily_requests_reset_at | datetime | |
| created_at, updated_at | timestamps | |

### Tabel Baru: `plan_model_access`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| plan_slug | string | free, pro, premium, daily |
| model_id | string | e.g. "glm-5", "claude-opus-4.6" |
| created_at | timestamp | |

---

## Task Breakdown

### Task 1: Create Migration — `subscription_plans` table

**Objective:** Buat tabel subscription_plans untuk menyimpan definisi paket.

**Files:**
- Create: `database/migrations/2026_04_30_150000_create_subscription_plans_table.php`

**Code:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // free, pro, premium, daily
            $table->string('name');
            $table->enum('type', ['monthly', 'daily'])->default('monthly');
            $table->integer('price_idr')->default(0);
            $table->integer('daily_request_limit')->nullable(); // null = unlimited
            $table->integer('per_minute_limit')->default(6);
            $table->integer('concurrent_limit')->default(1);
            $table->bigInteger('max_token_usage')->nullable(); // null = unlimited
            $table->json('features')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
```

**Verify:** `php artisan migrate --pretend` shows CREATE TABLE statement.

---

### Task 2: Create Migration — `user_subscriptions` table

**Objective:** Buat tabel untuk menyimpan subscription aktif user.

**Files:**
- Create: `database/migrations/2026_04_30_150001_create_user_subscriptions_table.php`

**Code:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('plan_slug');
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->timestamp('starts_at');
            $table->timestamp('expires_at')->nullable(); // null = forever (free)
            $table->bigInteger('token_usage_total')->default(0);
            $table->integer('daily_requests_used')->default(0);
            $table->timestamp('daily_requests_reset_at')->nullable();
            $table->timestamps();

            $table->foreign('plan_slug')->references('slug')->on('subscription_plans');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
```

---

### Task 3: Create Migration — `plan_model_access` table

**Objective:** Tabel pivot untuk mengontrol model mana yang bisa diakses per plan.

**Files:**
- Create: `database/migrations/2026_04_30_150002_create_plan_model_access_table.php`

**Code:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_model_access', function (Blueprint $table) {
            $table->id();
            $table->string('plan_slug');
            $table->string('model_id');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('plan_slug')->references('slug')->on('subscription_plans');
            $table->unique(['plan_slug', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_model_access');
    }
};
```

---

### Task 4: Create Seeder — Subscription Plans Data

**Objective:** Seed 4 paket berlangganan sesuai spesifikasi.

**Files:**
- Create: `database/seeders/SubscriptionPlanSeeder.php`

**Code:**
```php
<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'free',
                'name' => 'FREE',
                'type' => 'monthly',
                'price_idr' => 0,
                'daily_request_limit' => 50,
                'per_minute_limit' => 6,
                'concurrent_limit' => 1,
                'max_token_usage' => null,
                'is_popular' => false,
                'sort_order' => 1,
                'features' => json_encode([
                    '3 model cepat (GLM-5, Claude Sonnet 4.5, Claude Haiku 4.5, Minimax-m2.5)',
                    '50 request per hari',
                    '6 request per menit',
                    '1 request bersamaan',
                    'API key untuk integrasi',
                    'Dashboard usage real-time',
                ]),
            ],
            [
                'slug' => 'pro',
                'name' => 'PRO',
                'type' => 'monthly',
                'price_idr' => 29000,
                'daily_request_limit' => 3000,
                'per_minute_limit' => 30,
                'concurrent_limit' => 2,
                'max_token_usage' => null,
                'is_popular' => false,
                'sort_order' => 2,
                'features' => json_encode([
                    'Semua model Free + Claude Opus 4.6',
                    'GPT-5.4',
                    'Gemini 2.5 Pro, Gemini 3 Flash, Gemini 3.1 Pro',
                    'Kimi K2.5',
                    '3.000 request per hari',
                    '30 request per menit',
                    '2 request bersamaan',
                    'Email support',
                    'Riwayat usage',
                ]),
            ],
            [
                'slug' => 'premium',
                'name' => 'PREMIUM',
                'type' => 'monthly',
                'price_idr' => 59000,
                'daily_request_limit' => 10000,
                'per_minute_limit' => 90,
                'concurrent_limit' => 4,
                'max_token_usage' => null,
                'is_popular' => true,
                'sort_order' => 3,
                'features' => json_encode([
                    'SEMUA model Pro',
                    '10.000 request per hari',
                    '90 request per menit',
                    '4 request bersamaan',
                    'Priority support',
                    'Riwayat usage',
                ]),
            ],
            [
                'slug' => 'daily',
                'name' => 'Harian',
                'type' => 'daily',
                'price_idr' => 29000,
                'daily_request_limit' => null, // unlimited requests
                'per_minute_limit' => 60,
                'concurrent_limit' => 3,
                'max_token_usage' => 100000000, // 100M tokens
                'is_popular' => false,
                'sort_order' => 4,
                'features' => json_encode([
                    'Semua model Pro + Free',
                    'Unlimited Request',
                    'Max Penggunaan 100M Token',
                    'Email support',
                    'Priority support',
                    'Riwayat usage',
                ]),
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
```

---

### Task 5: Create Models — SubscriptionPlan, UserSubscription, PlanModelAccess

**Objective:** Buat 3 Eloquent model baru.

**Files:**
- Create: `app/Models/SubscriptionPlan.php`
- Create: `app/Models/UserSubscription.php`
- Create: `app/Models/PlanModelAccess.php`

**SubscriptionPlan.php:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'slug', 'name', 'type', 'price_idr',
        'daily_request_limit', 'per_minute_limit', 'concurrent_limit',
        'max_token_usage', 'features', 'is_popular', 'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_popular' => 'boolean',
        'daily_request_limit' => 'integer',
        'per_minute_limit' => 'integer',
        'concurrent_limit' => 'integer',
        'max_token_usage' => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class, 'plan_slug', 'slug');
    }

    public function modelAccess(): HasMany
    {
        return $this->hasMany(PlanModelAccess::class, 'plan_slug', 'slug');
    }

    public function getAccessibleModelIds(): array
    {
        return $this->modelAccess()->pluck('model_id')->toArray();
    }

    public function hasModelAccess(string $modelId): bool
    {
        return $this->modelAccess()->where('model_id', $modelId)->exists();
    }

    public static function getBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    public function isUnlimitedRequests(): bool
    {
        return $this->daily_request_limit === null;
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->price_idr === 0) return 'Rp 0';
        return 'Rp ' . number_format($this->price_idr, 0, ',', '.');
    }
}
```

**UserSubscription.php:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSubscription extends Model
{
    protected $fillable = [
        'user_id', 'plan_slug', 'status', 'starts_at', 'expires_at',
        'token_usage_total', 'daily_requests_used', 'daily_requests_reset_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'daily_requests_reset_at' => 'datetime',
        'token_usage_total' => 'integer',
        'daily_requests_used' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_slug', 'slug');
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        return true;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check and reset daily counter if needed.
     */
    public function checkDailyReset(): void
    {
        if (!$this->daily_requests_reset_at || $this->daily_requests_reset_at->isPast()) {
            $this->update([
                'daily_requests_used' => 0,
                'daily_requests_reset_at' => now()->endOfDay(),
            ]);
        }
    }

    /**
     * Increment daily request counter.
     */
    public function incrementDailyUsage(): void
    {
        $this->increment('daily_requests_used');
    }

    /**
     * Increment total token usage (for daily plan cap).
     */
    public function incrementTokenUsage(int $tokens): void
    {
        $this->increment('token_usage_total', $tokens);
    }

    /**
     * Check if daily request limit is reached.
     */
    public function isDailyLimitReached(): bool
    {
        $plan = $this->plan;
        if ($plan->isUnlimitedRequests()) return false;

        $this->checkDailyReset();
        return $this->daily_requests_used >= $plan->daily_request_limit;
    }

    /**
     * Check if token usage cap is reached (for daily plan).
     */
    public function isTokenCapReached(): bool
    {
        $plan = $this->plan;
        if (!$plan->max_token_usage) return false;

        return $this->token_usage_total >= $plan->max_token_usage;
    }
}
```

**PlanModelAccess.php:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanModelAccess extends Model
{
    public $timestamps = false;

    protected $table = 'plan_model_access';

    protected $fillable = ['plan_slug', 'model_id', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_slug', 'slug');
    }
}
```

---

### Task 6: Update User Model — Add subscription relationship

**Objective:** Tambah relasi dan helper method ke User model.

**Files:**
- Modify: `app/Models/User.php`

**Tambahkan:**
```php
use App\Models\UserSubscription;

// Di dalam class User:

public function subscriptions(): HasMany
{
    return $this->hasMany(UserSubscription::class);
}

public function activeSubscription(): ?UserSubscription
{
    return $this->subscriptions()
        ->where('status', 'active')
        ->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        })
        ->latest('starts_at')
        ->first();
}

public function getActivePlan(): SubscriptionPlan
{
    $sub = $this->activeSubscription();
    if ($sub) {
        return $sub->plan;
    }
    // Default: free plan
    return SubscriptionPlan::getBySlug('free');
}

/**
 * Assign a subscription plan to user.
 */
public function subscribeTo(string $planSlug, ?Carbon $expiresAt = null): UserSubscription
{
    // Cancel existing active subscriptions
    $this->subscriptions()->where('status', 'active')->update(['status' => 'cancelled']);

    return $this->subscriptions()->create([
        'plan_slug' => $planSlug,
        'status' => 'active',
        'starts_at' => now(),
        'expires_at' => $expiresAt,
        'daily_requests_used' => 0,
        'daily_requests_reset_at' => now()->endOfDay(),
    ]);
}
```

---

### Task 7: Create Rate Limiting Middleware — `CheckSubscriptionLimits`

**Objective:** Middleware baru yang enforce rate limits berdasarkan subscription plan (daily, per-minute, concurrent).

**Files:**
- Create: `app/Http/Middleware/CheckSubscriptionLimits.php`

**Logic:**
1. Get user's active subscription → get plan
2. Check daily request limit (dari `user_subscriptions.daily_requests_used`)
3. Check per-minute limit (Redis counter: `rate:{user_id}:minute`)
4. Check concurrent limit (Redis counter: `concurrent:{user_id}`)
5. Check token cap (for daily plan)
6. If any limit exceeded → return 429 with clear error message

**Code:**
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionLimits
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->get('_user');
        if (!$user) {
            return $next($request);
        }

        $subscription = $user->activeSubscription();
        $plan = $subscription ? $subscription->plan : \App\Models\SubscriptionPlan::getBySlug('free');

        if (!$plan) {
            return $next($request);
        }

        // 1. Check daily request limit
        if ($subscription && $subscription->isDailyLimitReached()) {
            return response()->json([
                'error' => [
                    'message' => 'Batas request harian tercapai. Upgrade plan untuk limit lebih tinggi.',
                    'type' => 'rate_limit_exceeded',
                    'code' => 'daily_limit_reached',
                    'plan' => $plan->slug,
                    'limit' => $plan->daily_request_limit,
                ]
            ], 429);
        }

        // 2. Check per-minute rate limit (Redis sliding window)
        $minuteKey = "rate:{$user->id}:minute";
        $currentMinute = (int) Redis::get($minuteKey) ?? 0;

        if ($currentMinute >= $plan->per_minute_limit) {
            return response()->json([
                'error' => [
                    'message' => 'Terlalu banyak request. Tunggu sebentar atau upgrade plan.',
                    'type' => 'rate_limit_exceeded',
                    'code' => 'per_minute_limit',
                    'plan' => $plan->slug,
                    'limit' => $plan->per_minute_limit,
                    'retry_after' => 60,
                ]
            ], 429)->header('Retry-After', 60);
        }

        // 3. Check concurrent request limit
        $concurrentKey = "concurrent:{$user->id}";
        $currentConcurrent = (int) Redis::get($concurrentKey) ?? 0;

        if ($currentConcurrent >= $plan->concurrent_limit) {
            return response()->json([
                'error' => [
                    'message' => 'Terlalu banyak request bersamaan. Tunggu request sebelumnya selesai.',
                    'type' => 'rate_limit_exceeded',
                    'code' => 'concurrent_limit',
                    'plan' => $plan->slug,
                    'limit' => $plan->concurrent_limit,
                ]
            ], 429);
        }

        // 4. Check token cap (daily plan)
        if ($subscription && $subscription->isTokenCapReached()) {
            return response()->json([
                'error' => [
                    'message' => 'Batas token harian tercapai (100M token). Beli paket harian baru.',
                    'type' => 'rate_limit_exceeded',
                    'code' => 'token_cap_reached',
                    'plan' => $plan->slug,
                ]
            ], 429);
        }

        // Increment counters
        Redis::incr($minuteKey);
        Redis::expire($minuteKey, 60); // TTL 60 seconds

        Redis::incr($concurrentKey);

        // Increment daily usage
        if ($subscription) {
            $subscription->incrementDailyUsage();
        }

        // Store plan info for later use
        $request->merge(['_subscription' => $subscription, '_plan' => $plan]);

        // Process request
        $response = $next($request);

        // Decrement concurrent counter after response
        Redis::decr($concurrentKey);

        return $response;
    }
}
```

---

### Task 8: Update Model Access Check in `CheckTokenQuota`

**Objective:** Ganti logika `is_free_tier` dengan plan-based model access dari `plan_model_access` table.

**Files:**
- Modify: `app/Http/Middleware/CheckTokenQuota.php`

**Perubahan:**
- Hapus check `ModelPricing::isFreeTierModel()` yang berdasarkan tier
- Ganti dengan check `PlanModelAccess` berdasarkan user's active plan
- Jika model tidak ada di plan_model_access untuk plan user → 403

---

### Task 9: Register Middleware & Update API Routes

**Objective:** Daftarkan middleware baru dan tambahkan ke API route group.

**Files:**
- Modify: `app/Http/Kernel.php` (atau `bootstrap/app.php` jika Laravel 11)
- Modify: `routes/api.php`

**Perubahan di routes/api.php:**
```php
Route::prefix('v1')->middleware(['api.fallback', 'api.key', 'api.quota', 'api.subscription'])->group(function () {
    // ... existing routes
});
```

---

### Task 10: Update TokenTrackingService — Track subscription usage

**Objective:** Setelah record usage, update subscription token counter (untuk daily plan cap).

**Files:**
- Modify: `app/Services/TokenTrackingService.php`

**Tambahkan setelah deduct balance:**
```php
// Update subscription token usage counter
$subscription = $user->activeSubscription();
if ($subscription) {
    $subscription->incrementTokenUsage($totalTokens);
}
```

---

### Task 11: Create Pricing Page Controller & Route

**Objective:** Buat halaman pricing publik yang menampilkan semua paket.

**Files:**
- Create: `app/Http/Controllers/PricingController.php`
- Modify: `routes/web.php` — tambah route `/pricing`

**PricingController.php:**
```php
<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;

class PricingController extends Controller
{
    public function index()
    {
        $monthlyPlans = SubscriptionPlan::where('type', 'monthly')
            ->orderBy('sort_order')
            ->get();

        $dailyPlans = SubscriptionPlan::where('type', 'daily')
            ->orderBy('sort_order')
            ->get();

        return view('pricing.index', compact('monthlyPlans', 'dailyPlans'));
    }
}
```

---

### Task 12: Create Pricing Blade View

**Objective:** Buat halaman pricing dengan toggle Harian/Bulanan, card per plan, sesuai DESIGN.md.

**Files:**
- Create: `resources/views/pricing/index.blade.php`

**Design requirements (dari DESIGN.md):**
- Background: `bg-canvas` (#faf9f6)
- Cards: `rounded-card` (8px), `border-oat`
- Buttons: `rounded-btn` (4px), `bg-off-black`, `btn-intercom` hover
- Popular badge: Fin Orange (#ff5600)
- Toggle: peer-checked pattern untuk Harian/Bulanan switch
- Typography: tight tracking, bold headings

---

### Task 13: Admin — Manage Subscriptions (Assign Plan to User)

**Objective:** Di admin user detail page, tambah kemampuan assign/change plan user.

**Files:**
- Modify: `app/Http/Controllers/Admin/UserController.php` — tambah method `assignPlan`
- Modify: `resources/views/admin/users/show.blade.php` — tambah section subscription
- Modify: `routes/web.php` — tambah route `admin/users/{user}/assign-plan`

---

### Task 14: Auto-assign FREE plan on Registration

**Objective:** Saat user baru register, otomatis assign FREE plan.

**Files:**
- Modify: `app/Http/Controllers/Auth/RegisteredUserController.php`
- Atau: Create Observer `app/Observers/UserObserver.php`

**Logic:** Setelah user created → `$user->subscribeTo('free')` (no expiry)

---

### Task 15: Migrate Existing Users to FREE Plan

**Objective:** Buat command artisan untuk assign semua existing users ke FREE plan.

**Files:**
- Create: `app/Console/Commands/MigrateUsersToFreePlan.php`

**Logic:**
```php
User::whereDoesntHave('subscriptions')->each(function ($user) {
    $user->subscribeTo('free');
});
```

---

### Task 16: Seed Plan Model Access Data

**Objective:** Seed model access per plan berdasarkan spesifikasi.

**Files:**
- Create: `database/seeders/PlanModelAccessSeeder.php`

**Data:**
- **free**: glm-5, claude-sonnet-4.5, claude-haiku-4.5, minimax-m2.5
- **pro**: semua free + claude-opus-4.6, gpt-5.4, gemini-2.5-pro, gemini-3-flash, gemini-3.1-pro, kimi-k2.5
- **premium**: semua pro models
- **daily**: semua pro models

---

### Task 17: Dashboard — Show Current Plan Info

**Objective:** Di user dashboard, tampilkan plan aktif, usage hari ini, dan limit.

**Files:**
- Modify: `app/Http/Controllers/DashboardController.php`
- Modify: `resources/views/dashboard.blade.php`

**Info yang ditampilkan:**
- Plan name & badge
- Daily requests: X / Y used
- Per-minute limit
- Concurrent limit
- Upgrade button jika bukan premium

---

### Task 18: Subscription Purchase Flow (via Wallet)

**Objective:** User bisa beli/upgrade plan menggunakan saldo wallet yang sudah ada.

**Files:**
- Create: `app/Http/Controllers/SubscriptionController.php`
- Create: `resources/views/subscriptions/index.blade.php`
- Modify: `routes/web.php`

**Logic:**
1. User pilih plan → confirm
2. Deduct `price_idr` dari paid_balance
3. Create UserSubscription record
4. Set expires_at (30 hari untuk monthly, 1 hari untuk daily)

---

## Execution Order

1. Tasks 1-3: Migrations (run together)
2. Task 4: Seeder
3. Task 5: Models
4. Task 6: User model update
5. Task 7: Rate limiting middleware
6. Task 8: Update CheckTokenQuota
7. Task 9: Register middleware
8. Task 10: TokenTrackingService update
9. Task 14: Auto-assign on registration
10. Task 15: Migrate existing users
11. Task 16: Seed model access
12. Task 11-12: Pricing page (public)
13. Task 13: Admin management
14. Task 17: Dashboard update
15. Task 18: Purchase flow

---

## Catatan Penting

1. **Backward compatibility**: Sistem wallet (balance) tetap jalan — subscription hanya menambah layer rate limiting & model access di atasnya.
2. **Redis dependency**: Per-minute dan concurrent limiting butuh Redis. Pastikan Redis sudah terinstall dan dikonfigurasi di `.env`.
3. **Cron job**: Perlu scheduled task untuk expire subscriptions yang sudah lewat `expires_at`.
4. **Graceful degradation**: Jika user tidak punya subscription record → default ke FREE plan.

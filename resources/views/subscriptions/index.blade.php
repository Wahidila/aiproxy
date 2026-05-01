<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-off-black leading-tight tracking-heading">
            {{ __('Subscription') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="rounded-card border border-green-300 bg-green-50 p-4 flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600 shrink-0 mt-0.5"></i>
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-card border border-red-300 bg-red-50 p-4 flex items-start gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 shrink-0 mt-0.5"></i>
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            {{-- ═══════════════════════════════════════════ --}}
            {{-- CURRENT PLAN CARD --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div class="bg-surface border border-oat rounded-card overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        {{-- Plan Badge & Name --}}
                        <div class="flex items-center gap-4">
                            @php
                                $planSlug = $activePlan->slug ?? 'free';
                                $planColors = [
                                    'premium' => ['bg' => 'bg-gradient-to-br from-amber-100 to-orange-100', 'icon' => 'text-fin-orange', 'badge' => 'bg-fin-orange'],
                                    'pro' => ['bg' => 'bg-gradient-to-br from-blue-100 to-indigo-100', 'icon' => 'text-blue-600', 'badge' => 'bg-blue-600'],
                                    'daily' => ['bg' => 'bg-gradient-to-br from-purple-100 to-pink-100', 'icon' => 'text-purple-600', 'badge' => 'bg-purple-600'],
                                    'free' => ['bg' => 'bg-canvas', 'icon' => 'text-muted', 'badge' => 'bg-gray-500'],
                                ];
                                $colors = $planColors[$planSlug] ?? $planColors['free'];
                            @endphp
                            <div class="flex h-14 w-14 items-center justify-center rounded-xl {{ $colors['bg'] }}">
                                <i data-lucide="crown" class="w-7 h-7 {{ $colors['icon'] }}"></i>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-muted uppercase tracking-wider">Plan Aktif</p>
                                <p class="text-2xl font-bold text-off-black" style="letter-spacing: -0.96px;">{{ $activePlan->name ?? 'FREE' }}</p>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-2">
                            @if($activeSubscription && $activeSubscription->plan_slug !== 'free')
                                {{-- Renew Button --}}
                                <form method="POST" action="{{ route('subscriptions.renew') }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-btn px-4 py-2 text-sm font-medium border border-oat text-off-black hover:bg-canvas transition-all"
                                            style="border-radius: 4px;"
                                            onclick="return confirm('Perpanjang plan {{ $activePlan->name }} seharga {{ $activePlan->formatted_price }}? Saldo wallet akan dipotong.')">
                                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                        Perpanjang
                                    </button>
                                </form>

                                {{-- Cancel Button --}}
                                <form method="POST" action="{{ route('subscriptions.cancel') }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-btn px-4 py-2 text-sm font-medium border border-red-200 text-red-600 hover:bg-red-50 transition-all"
                                            style="border-radius: 4px;"
                                            onclick="return confirm('Batalkan langganan {{ $activePlan->name }}? Anda akan beralih ke plan FREE.')">
                                        <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                        Batalkan
                                    </button>
                                </form>
                            @else
                                <a href="#plans"
                                   class="inline-flex items-center gap-1.5 rounded-btn px-4 py-2 text-sm font-medium text-white bg-off-black hover:scale-105 active:scale-95 transition-all"
                                   style="border-radius: 4px;">
                                    <i data-lucide="arrow-up-circle" class="w-3.5 h-3.5"></i>
                                    Upgrade Plan
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Subscription Details Grid --}}
                    @if($activeSubscription)
                        <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-4">
                            {{-- Status --}}
                            <div class="p-3 rounded-lg bg-canvas border border-oat">
                                <p class="text-xs text-muted mb-1">Status</p>
                                @if($activeSubscription->isActive())
                                    <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-green-600">
                                        <span class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-red-600">
                                        <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                        Expired
                                    </span>
                                @endif
                            </div>

                            {{-- Start Date --}}
                            <div class="p-3 rounded-lg bg-canvas border border-oat">
                                <p class="text-xs text-muted mb-1">Mulai</p>
                                <p class="text-sm font-medium text-off-black">{{ $activeSubscription->starts_at->format('d M Y') }}</p>
                            </div>

                            {{-- Expiry --}}
                            <div class="p-3 rounded-lg bg-canvas border border-oat">
                                <p class="text-xs text-muted mb-1">Berakhir</p>
                                @if($activeSubscription->expires_at)
                                    @php
                                        $daysLeft = now()->diffInDays($activeSubscription->expires_at, false);
                                    @endphp
                                    <p class="text-sm font-medium {{ $daysLeft <= 3 ? 'text-red-600' : ($daysLeft <= 7 ? 'text-amber-600' : 'text-off-black') }}">
                                        {{ $activeSubscription->expires_at->format('d M Y') }}
                                        @if($daysLeft > 0 && $daysLeft <= 7)
                                            <span class="text-xs">({{ (int)$daysLeft }} hari lagi)</span>
                                        @endif
                                    </p>
                                @else
                                    <p class="text-sm font-medium text-off-black">Selamanya</p>
                                @endif
                            </div>

                            {{-- Wallet --}}
                            <div class="p-3 rounded-lg bg-canvas border border-oat">
                                <p class="text-xs text-muted mb-1">Saldo Wallet</p>
                                <p class="text-sm font-bold" style="color: #ff5600;">Rp {{ number_format($quota->paid_balance, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Usage Meters --}}
                @if($activeSubscription && $activeSubscription->plan_slug !== 'free')
                    <div class="border-t border-oat px-6 py-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            {{-- Daily Requests Meter --}}
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-muted">Request Hari Ini</span>
                                    <span class="text-xs font-semibold text-off-black">
                                        {{ number_format($usageStats['today_requests']) }}
                                        / {{ $usageStats['daily_limit'] ? number_format($usageStats['daily_limit']) : '∞' }}
                                    </span>
                                </div>
                                @if($usageStats['daily_limit'])
                                    @php $reqPct = min(100, ($usageStats['today_requests'] / max(1, $usageStats['daily_limit'])) * 100); @endphp
                                    <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500 {{ $reqPct > 90 ? 'bg-red-500' : ($reqPct > 70 ? 'bg-amber-500' : 'bg-green-500') }}"
                                             style="width: {{ $reqPct }}%"></div>
                                    </div>
                                @else
                                    <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full bg-green-500" style="width: 5%"></div>
                                    </div>
                                @endif
                            </div>

                            {{-- Token Usage Meter --}}
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-muted">Total Token</span>
                                    <span class="text-xs font-semibold text-off-black">
                                        {{ number_format($usageStats['token_total']) }}
                                        @if($usageStats['token_cap'])
                                            / {{ number_format($usageStats['token_cap'] / 1000000) }}M
                                        @else
                                            <span class="text-muted">/ ∞</span>
                                        @endif
                                    </span>
                                </div>
                                @if($usageStats['token_cap'])
                                    @php $tokPct = min(100, ($usageStats['token_total'] / max(1, $usageStats['token_cap'])) * 100); @endphp
                                    <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500 {{ $tokPct > 90 ? 'bg-red-500' : ($tokPct > 70 ? 'bg-amber-500' : 'bg-blue-500') }}"
                                             style="width: {{ $tokPct }}%"></div>
                                    </div>
                                @else
                                    <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full bg-blue-500" style="width: 3%"></div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Quick Info Bar --}}
                @if($activePlan)
                    <div class="border-t border-oat px-6 py-3 bg-canvas flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-muted">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="activity" class="w-3 h-3"></i>
                            {{ $activePlan->per_minute_limit ?? 0 }} req/menit
                        </span>
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="layers" class="w-3 h-3"></i>
                            {{ $activePlan->concurrent_limit ?? 0 }} concurrent
                        </span>
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="calendar" class="w-3 h-3"></i>
                            {{ $activePlan->daily_request_limit ? number_format($activePlan->daily_request_limit) . ' req/hari' : 'Unlimited req/hari' }}
                        </span>
                        @if($activePlan->max_token_usage)
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="database" class="w-3 h-3"></i>
                                Max {{ number_format($activePlan->max_token_usage / 1000000) }}M token
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- AVAILABLE PLANS --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div id="plans">
                <h3 class="text-lg font-semibold text-off-black mb-4" style="letter-spacing: -0.48px;">Pilih Plan</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($plans as $plan)
                        <div class="relative bg-surface border rounded-card flex flex-col transition-all hover:shadow-md {{ $plan->is_popular ? 'border-2 shadow-sm' : 'border-oat' }}"
                             style="{{ $plan->is_popular ? 'border-color: #ff5600;' : '' }}">

                            @if($plan->is_popular)
                                <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                                    <span class="inline-flex items-center rounded-btn px-3 py-1 text-xs font-bold text-white"
                                          style="background-color: #ff5600; border-radius: 4px;">
                                        🔥 POPULER
                                    </span>
                                </div>
                            @endif

                            {{-- Active indicator --}}
                            @if(($activePlan->slug ?? 'free') === $plan->slug)
                                <div class="absolute top-3 right-3">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                        Aktif
                                    </span>
                                </div>
                            @endif

                            <div class="p-5 flex-1 flex flex-col {{ $plan->is_popular ? 'pt-6' : '' }}">
                                {{-- Plan Name --}}
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-muted mb-2">{{ $plan->name }}</h4>

                                {{-- Price --}}
                                <div class="mb-4">
                                    <span class="text-2xl font-bold text-off-black" style="letter-spacing: -0.96px;">{{ $plan->formatted_price }}</span>
                                    <span class="text-xs text-muted">/{{ $plan->type === 'daily' ? 'hari' : 'bulan' }}</span>
                                </div>

                                {{-- Features List --}}
                                @if($plan->features && is_array($plan->features))
                                    <ul class="space-y-1.5 mb-4 text-xs text-muted">
                                        @foreach($plan->features as $feature)
                                            <li class="flex items-start gap-1.5">
                                                <i data-lucide="check" class="w-3 h-3 text-green-500 shrink-0 mt-0.5"></i>
                                                {{ $feature }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                {{-- Action --}}
                                <div class="mt-auto">
                                    @if(($activePlan->slug ?? 'free') === $plan->slug)
                                        <button disabled
                                                class="w-full inline-flex items-center justify-center rounded-btn px-4 py-2.5 text-sm font-medium bg-canvas text-muted border border-oat cursor-not-allowed"
                                                style="border-radius: 4px;">
                                            <i data-lucide="check-circle" class="w-4 h-4 mr-1.5"></i>
                                            Plan Aktif
                                        </button>
                                    @elseif($plan->slug === 'free')
                                        <form method="POST" action="{{ route('subscriptions.purchase') }}">
                                            @csrf
                                            <input type="hidden" name="plan_slug" value="free">
                                            <button type="submit"
                                                    class="w-full inline-flex items-center justify-center rounded-btn px-4 py-2.5 text-sm font-medium border border-off-black text-off-black hover:bg-off-black hover:text-white transition-all"
                                                    style="border-radius: 4px;"
                                                    onclick="return confirm('Beralih ke plan FREE?')">
                                                Pilih FREE
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('subscriptions.purchase') }}">
                                            @csrf
                                            <input type="hidden" name="plan_slug" value="{{ $plan->slug }}">
                                            <button type="submit"
                                                    class="w-full inline-flex items-center justify-center rounded-btn px-4 py-2.5 text-sm font-medium text-white hover:scale-105 active:scale-95 transition-all {{ $plan->is_popular ? '' : 'bg-off-black' }}"
                                                    style="border-radius: 4px; {{ $plan->is_popular ? 'background-color: #ff5600;' : '' }}"
                                                    onclick="return confirm('Beli plan {{ $plan->name }} seharga {{ $plan->formatted_price }}? Saldo wallet akan dipotong.')">
                                                Beli {{ $plan->name }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- PLAN COMPARISON TABLE --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div class="bg-surface border border-oat rounded-card overflow-hidden">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-off-black" style="letter-spacing: -0.48px;">Perbandingan Plan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-oat">
                        <thead>
                            <tr class="bg-canvas">
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Fitur</th>
                                @foreach($plans as $plan)
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider {{ ($activePlan->slug ?? 'free') === $plan->slug ? 'text-fin-orange' : 'text-muted' }}">
                                        {{ $plan->name }}
                                        @if(($activePlan->slug ?? 'free') === $plan->slug)
                                            <span class="block text-[10px] font-normal text-green-600 mt-0.5">● Aktif</span>
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-oat">
                            <tr class="hover:bg-canvas">
                                <td class="px-6 py-3 text-sm text-off-black font-medium">Harga</td>
                                @foreach($plans as $plan)
                                    <td class="px-4 py-3 text-center text-sm text-muted">{{ $plan->formatted_price }}/{{ $plan->type === 'daily' ? 'hari' : 'bln' }}</td>
                                @endforeach
                            </tr>
                            <tr class="hover:bg-canvas">
                                <td class="px-6 py-3 text-sm text-off-black font-medium">Request/Hari</td>
                                @foreach($plans as $plan)
                                    <td class="px-4 py-3 text-center text-sm text-muted">{{ $plan->daily_request_limit ? number_format($plan->daily_request_limit) : '∞' }}</td>
                                @endforeach
                            </tr>
                            <tr class="hover:bg-canvas">
                                <td class="px-6 py-3 text-sm text-off-black font-medium">Request/Menit</td>
                                @foreach($plans as $plan)
                                    <td class="px-4 py-3 text-center text-sm text-muted">{{ $plan->per_minute_limit }}</td>
                                @endforeach
                            </tr>
                            <tr class="hover:bg-canvas">
                                <td class="px-6 py-3 text-sm text-off-black font-medium">Concurrent</td>
                                @foreach($plans as $plan)
                                    <td class="px-4 py-3 text-center text-sm text-muted">{{ $plan->concurrent_limit }}</td>
                                @endforeach
                            </tr>
                            <tr class="hover:bg-canvas">
                                <td class="px-6 py-3 text-sm text-off-black font-medium">Token Cap</td>
                                @foreach($plans as $plan)
                                    <td class="px-4 py-3 text-center text-sm text-muted">{{ $plan->max_token_usage ? number_format($plan->max_token_usage / 1000000) . 'M' : '∞' }}</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- SUBSCRIPTION HISTORY --}}
            {{-- ═══════════════════════════════════════════ --}}
            @if($subscriptionHistory->count() > 0)
                <div class="bg-surface border border-oat rounded-card overflow-hidden">
                    <div class="px-6 py-5">
                        <h3 class="text-lg font-semibold text-off-black" style="letter-spacing: -0.48px;">Riwayat Langganan</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-oat">
                            <thead>
                                <tr class="bg-canvas">
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Plan</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Status</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Mulai</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Berakhir</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted">Request</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted">Token</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-oat">
                                @foreach($subscriptionHistory as $sub)
                                    <tr class="hover:bg-canvas {{ $sub->status === 'active' && $sub->isActive() ? 'bg-green-50/50' : '' }}">
                                        <td class="px-6 py-3 text-sm font-medium text-off-black">
                                            {{ $sub->plan->name ?? $sub->plan_slug }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($sub->status === 'active' && $sub->isActive())
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">Aktif</span>
                                            @elseif($sub->status === 'active' && $sub->isExpired())
                                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700">Expired</span>
                                            @elseif($sub->status === 'cancelled')
                                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">Dibatalkan</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">{{ ucfirst($sub->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center text-sm text-muted">{{ $sub->starts_at->format('d M Y') }}</td>
                                        <td class="px-4 py-3 text-center text-sm text-muted">{{ $sub->expires_at ? $sub->expires_at->format('d M Y') : '-' }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-muted">{{ number_format($sub->daily_requests_used) }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-muted">{{ number_format($sub->token_usage_total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>

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
                <div class="rounded-card border border-green-300 bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-card border border-red-300 bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Current Plan Info --}}
            <div class="bg-surface border border-oat rounded-card p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full {{ ($activePlan->slug ?? 'free') === 'premium' ? 'bg-fin-orange-light' : (($activePlan->slug ?? 'free') === 'pro' ? 'bg-blue-100' : 'bg-canvas') }}">
                        <i data-lucide="crown" class="w-6 h-6 {{ ($activePlan->slug ?? 'free') === 'premium' ? 'text-fin-orange' : (($activePlan->slug ?? 'free') === 'pro' ? 'text-blue-600' : 'text-muted') }}"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-muted uppercase tracking-wider">Plan Aktif Saat Ini</p>
                        <p class="text-2xl font-bold text-off-black">{{ $activePlan->name ?? 'FREE' }}</p>
                    </div>
                </div>

                @if($activeSubscription)
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-lg bg-canvas border border-oat">
                        <div>
                            <p class="text-xs text-muted">Status</p>
                            <p class="text-sm font-semibold {{ $activeSubscription->isActive() ? 'text-green-600' : 'text-red-600' }}">
                                {{ $activeSubscription->isActive() ? 'Active' : 'Expired' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-muted">Mulai</p>
                            <p class="text-sm font-medium text-off-black">{{ $activeSubscription->starts_at->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted">Berakhir</p>
                            <p class="text-sm font-medium text-off-black">{{ $activeSubscription->expires_at ? $activeSubscription->expires_at->format('d M Y H:i') : 'Selamanya' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted">Request Hari Ini</p>
                            <p class="text-sm font-medium text-off-black">
                                {{ number_format($activeSubscription->daily_requests_used) }}
                                / {{ $activePlan->daily_request_limit ? number_format($activePlan->daily_request_limit) : '∞' }}
                            </p>
                        </div>
                    </div>
                @endif

                <div class="mt-4 flex items-center gap-3">
                    <p class="text-sm text-muted">Saldo Anda:</p>
                    <span class="text-sm font-bold text-fin-orange">Rp {{ number_format($quota->paid_balance, 0, ',', '.') }}</span>
                    <a href="{{ route('donations.index') }}" class="text-sm text-fin-orange hover:underline">Top Up</a>
                </div>
            </div>

            {{-- Available Plans --}}
            <div>
                <h3 class="text-lg font-semibold text-off-black mb-4" style="letter-spacing: -0.48px;">Pilih Plan</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($plans as $plan)
                        <div class="relative bg-surface border rounded-card flex flex-col {{ $plan->is_popular ? 'border-2' : 'border-oat' }}"
                             style="{{ $plan->is_popular ? 'border-color: #ff5600;' : '' }}">

                            @if($plan->is_popular)
                                <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                                    <span class="inline-flex items-center rounded-btn px-3 py-1 text-xs font-bold text-white"
                                          style="background-color: #ff5600; border-radius: 4px;">
                                        POPULER
                                    </span>
                                </div>
                            @endif

                            <div class="p-5 flex-1 flex flex-col">
                                {{-- Plan Name --}}
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-muted mb-2">{{ $plan->name }}</h4>

                                {{-- Price --}}
                                <div class="mb-4">
                                    <span class="text-2xl font-bold text-off-black" style="letter-spacing: -0.96px;">{{ $plan->formatted_price }}</span>
                                    <span class="text-xs text-muted">/{{ $plan->type === 'daily' ? 'hari' : 'bulan' }}</span>
                                </div>

                                {{-- Limits --}}
                                <div class="space-y-1.5 mb-4 text-xs text-muted">
                                    <p>{{ $plan->daily_request_limit ? number_format($plan->daily_request_limit) . ' req/hari' : 'Unlimited req' }}</p>
                                    <p>{{ $plan->per_minute_limit }} req/menit</p>
                                    <p>{{ $plan->concurrent_limit }} concurrent</p>
                                    @if($plan->max_token_usage)
                                        <p>Max {{ number_format($plan->max_token_usage / 1000000) }}M token</p>
                                    @endif
                                </div>

                                {{-- Action --}}
                                <div class="mt-auto">
                                    @if(($activePlan->slug ?? 'free') === $plan->slug)
                                        <button disabled
                                                class="w-full inline-flex items-center justify-center rounded-btn px-4 py-2.5 text-sm font-medium bg-canvas text-muted border border-oat cursor-not-allowed"
                                                style="border-radius: 4px;">
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
                                                    onclick="return confirm('Beli plan {{ $plan->name }} seharga {{ $plan->formatted_price }}? Saldo akan dipotong dari wallet.')">
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

            {{-- Features Comparison --}}
            <div class="bg-surface border border-oat rounded-card overflow-hidden">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">Detail Fitur per Plan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-oat">
                        <thead>
                            <tr class="bg-canvas">
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted">Plan</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Harga</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Req/Hari</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Req/Menit</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Concurrent</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted">Token Cap</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-oat">
                            @foreach($plans as $plan)
                                <tr class="hover:bg-canvas {{ ($activePlan->slug ?? 'free') === $plan->slug ? 'bg-fin-orange-light/30' : '' }}">
                                    <td class="px-6 py-3 text-sm font-semibold text-off-black">
                                        {{ $plan->name }}
                                        @if(($activePlan->slug ?? 'free') === $plan->slug)
                                            <span class="ml-2 inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Aktif</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-center text-sm text-muted">{{ $plan->formatted_price }}/{{ $plan->type === 'daily' ? 'hari' : 'bln' }}</td>
                                    <td class="px-6 py-3 text-center text-sm text-muted">{{ $plan->daily_request_limit ? number_format($plan->daily_request_limit) : '∞' }}</td>
                                    <td class="px-6 py-3 text-center text-sm text-muted">{{ $plan->per_minute_limit }}</td>
                                    <td class="px-6 py-3 text-center text-sm text-muted">{{ $plan->concurrent_limit }}</td>
                                    <td class="px-6 py-3 text-center text-sm text-muted">{{ $plan->max_token_usage ? number_format($plan->max_token_usage / 1000000) . 'M' : '∞' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

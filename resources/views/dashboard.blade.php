<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-off-black leading-tight tracking-heading">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        function formatRupiah($amount) {
            return 'Rp ' . number_format($amount, 0, ',', '.');
        }
        function formatTokenCount($count) {
            if ($count >= 1000000) {
                return number_format($count / 1000000, 1) . 'M';
            } elseif ($count >= 1000) {
                return number_format($count / 1000, 1) . 'K';
            }
            return number_format($count);
        }
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Low Balance Banner (zero balance) --}}
            @if($quota->paid_balance <= 0 && (!$activeSubscription || !$activeSubscription->isActive()))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-red-800 font-medium">Semua saldo habis! Anda tidak dapat menggunakan API.</p>
                        </div>
                        <a href="{{ route('donations.index') }}" class="ml-4 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-btn hover:bg-red-700 transition">
                            Top Up Sekarang
                        </a>
                    </div>
                </div>
            @endif

            {{-- Low Balance Threshold Warning Banner --}}
            @if($showBalanceAlert && !($quota->paid_balance <= 0 && (!$activeSubscription || !$activeSubscription->isActive())))
                <div class="border rounded-card p-4" style="background-color: #fff7ed; border-color: #ff5600;">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" style="color: #ff5600;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <p class="font-medium" style="color: #111111;">
                                Peringatan: Saldo Anda di bawah batas minimum ({{ formatRupiah($quota->balance_alert_threshold ?? 10000) }})
                            </p>
                            <p class="text-sm mt-0.5" style="color: #7b7b78;">
                                Saldo Wallet: {{ $quota->formatted_paid_balance }}
                            </p>
                        </div>
                        <a href="{{ route('donations.index') }}" class="ml-4 px-4 py-2 text-white text-sm font-medium rounded-btn transition hover:opacity-90" style="background-color: #ff5600;">
                            Top Up Sekarang
                        </a>
                    </div>
                </div>
            @endif

            {{-- Subscription Plan Info --}}
            <div class="bg-surface border border-oat rounded-card p-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full {{ ($activePlan->slug ?? 'free') === 'premium' ? 'bg-fin-orange-light' : (($activePlan->slug ?? 'free') === 'pro' ? 'bg-blue-100' : 'bg-canvas') }}">
                            <i data-lucide="crown" class="w-6 h-6 {{ ($activePlan->slug ?? 'free') === 'premium' ? 'text-fin-orange' : (($activePlan->slug ?? 'free') === 'pro' ? 'text-blue-600' : 'text-muted') }}"></i>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-muted uppercase tracking-wider">Plan Aktif</p>
                            <p class="text-xl font-bold text-off-black">{{ $activePlan->name ?? 'FREE' }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-sm">
                        <div class="text-center px-3">
                            <p class="text-xs text-muted">Request/Hari</p>
                            <p class="font-semibold text-off-black">
                                @if($activeSubscription)
                                    {{ number_format($activeSubscription->daily_requests_used) }}/{{ $activePlan->daily_request_limit ? number_format($activePlan->daily_request_limit) : '∞' }}
                                @else
                                    0/{{ $activePlan->daily_request_limit ? number_format($activePlan->daily_request_limit) : '∞' }}
                                @endif
                            </p>
                        </div>
                        <div class="text-center px-3 border-l border-oat">
                            <p class="text-xs text-muted">Per Menit</p>
                            <p class="font-semibold text-off-black">{{ $activePlan->per_minute_limit ?? 6 }}</p>
                        </div>
                        <div class="text-center px-3 border-l border-oat">
                            <p class="text-xs text-muted">Concurrent</p>
                            <p class="font-semibold text-off-black">{{ $activePlan->concurrent_limit ?? 1 }}</p>
                        </div>
                        @if(($activePlan->slug ?? 'free') !== 'premium')
                            <a href="{{ route('pricing') }}"
                               class="inline-flex items-center rounded-btn px-4 py-2 text-sm font-medium text-white hover:scale-105 active:scale-95 transition-all"
                               style="background-color: #ff5600; border-radius: 4px;">
                                Upgrade
                            </a>
                        @endif
                    </div>
                </div>
                @if($activeSubscription && $activeSubscription->expires_at)
                    <div class="mt-3 pt-3 border-t border-oat">
                        <p class="text-xs text-muted">
                            Plan berlaku hingga <span class="font-medium text-off-black">{{ $activeSubscription->expires_at->format('d M Y H:i') }}</span>
                            @if($activeSubscription->expires_at->diffInDays(now()) <= 3)
                                <span class="ml-2 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Segera expired</span>
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            {{-- Wallet Balance Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Wallet Balance (Pay-as-you-go) --}}
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase tracking-wide">Saldo Wallet</p>
                    <p class="mt-2 text-3xl font-bold {{ $quota->paid_balance > 0 ? 'text-fin-orange' : 'text-warm-sand' }}">
                        {{ $quota->formatted_paid_balance }}
                    </p>
                    <p class="mt-1 text-xs text-warm-sand">Pay-as-you-go — semua model</p>
                </div>
                {{-- Subscription Info --}}
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase tracking-wide">Subscription</p>
                    <p class="mt-2 text-3xl font-bold {{ ($activeSubscription && $activeSubscription->isActive()) ? 'text-purple-600' : 'text-warm-sand' }}">
                        {{ $activePlan->name ?? 'Tidak Ada' }}
                    </p>
                    <p class="mt-1 text-xs text-warm-sand">
                        @if($activeSubscription && $activeSubscription->isActive() && $activeSubscription->expires_at)
                            Berlaku hingga {{ $activeSubscription->expires_at->format('d M Y') }}
                        @elseif($activeSubscription && $activeSubscription->isActive())
                            Aktif (selamanya)
                        @else
                            <a href="{{ route('pricing') }}" class="text-fin-orange hover:underline">Pilih plan →</a>
                        @endif
                    </p>
                </div>
                {{-- Top Up Button --}}
                <div class="bg-surface border border-oat rounded-card p-5 flex items-center justify-center">
                    <a href="{{ route('donations.index') }}" class="inline-flex items-center px-6 py-3 bg-off-black text-white font-semibold rounded-btn hover:bg-off-black/90 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Top Up Saldo
                    </a>
                </div>
            </div>

            {{-- Quick Actions Widget --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Buat API Key --}}
                <a href="{{ route('api-keys.index') }}" class="group bg-surface border border-oat rounded-card p-5 flex items-start gap-4 transition-all duration-200 hover:scale-[1.02]" style="text-decoration: none;" onmouseenter="this.style.borderColor='#ff5600'" onmouseleave="this.style.borderColor='#dedbd6'">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #ff5600;">
                        <i data-lucide="key" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-off-black">Buat API Key</p>
                        <p class="text-xs text-muted mt-1">Buat API key baru untuk mulai menggunakan API</p>
                    </div>
                </a>

                {{-- Top Up Saldo --}}
                <a href="{{ route('donations.index') }}" class="group bg-surface border border-oat rounded-card p-5 flex items-start gap-4 transition-all duration-200 hover:scale-[1.02]" style="text-decoration: none;" onmouseenter="this.style.borderColor='#ff5600'" onmouseleave="this.style.borderColor='#dedbd6'">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #ff5600;">
                        <i data-lucide="wallet" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-off-black">Top Up Saldo</p>
                        <p class="text-xs text-muted mt-1">Tambah saldo untuk akses model premium</p>
                    </div>
                </a>

                {{-- Lihat Penggunaan --}}
                <a href="{{ route('usage.index') }}" class="group bg-surface border border-oat rounded-card p-5 flex items-start gap-4 transition-all duration-200 hover:scale-[1.02]" style="text-decoration: none;" onmouseenter="this.style.borderColor='#ff5600'" onmouseleave="this.style.borderColor='#dedbd6'">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #ff5600;">
                        <i data-lucide="bar-chart-3" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-off-black">Lihat Penggunaan</p>
                        <p class="text-xs text-muted mt-1">Pantau penggunaan API dan biaya Anda</p>
                    </div>
                </a>

                {{-- Bantuan --}}
                <a href="{{ route('support.index') }}" class="group bg-surface border border-oat rounded-card p-5 flex items-start gap-4 transition-all duration-200 hover:scale-[1.02]" style="text-decoration: none;" onmouseenter="this.style.borderColor='#ff5600'" onmouseleave="this.style.borderColor='#dedbd6'">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #ff5600;">
                        <i data-lucide="headphones" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-off-black">Bantuan</p>
                        <p class="text-xs text-muted mt-1">Hubungi tim support atau buat tiket</p>
                    </div>
                </a>
            </div>

            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Total Biaya --}}
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-sm font-medium text-muted">Total Biaya (30 hari)</p>
                    <p class="mt-2 text-2xl font-bold text-off-black">{{ formatRupiah($stats['total_cost_spent']) }}</p>
                </div>

                {{-- Total Requests --}}
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-sm font-medium text-muted">Total Requests</p>
                    <p class="mt-2 text-2xl font-bold text-off-black">{{ number_format($stats['total_requests']) }}</p>
                </div>

                {{-- Avg Response Time --}}
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-sm font-medium text-muted">Avg Response Time</p>
                    <p class="mt-2 text-2xl font-bold text-off-black">{{ number_format($stats['avg_response_time']) }} ms</p>
                </div>

                {{-- Model Favorit --}}
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-sm font-medium text-muted">Model Favorit</p>
                    <p class="mt-2 text-2xl font-bold text-off-black truncate">{{ $stats['favorite_model'] ?? '-' }}</p>
                </div>
            </div>

            {{-- Daily Cost Chart --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">Biaya Harian (7 Hari Terakhir)</h3>
                    @php
                        $maxCost = collect($stats['daily_usage'])->max('cost_idr') ?: 1;
                    @endphp
                    <div class="flex items-end space-x-2" style="height: 160px;">
                        @foreach($stats['daily_usage'] as $date => $day)
                            <div class="flex-1 flex flex-col items-center justify-end h-full">
                                <div class="w-full bg-fin-orange rounded-t" style="height: {{ max(2, ($day['cost_idr'] / $maxCost) * 100) }}%;"></div>
                                <p class="text-xs text-muted mt-2">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</p>
                                <p class="text-xs text-off-black font-medium">{{ formatRupiah($day['cost_idr']) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Pengaturan Notifikasi Saldo --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">Pengaturan Notifikasi Saldo</h3>

                    @if(session('alert_settings_saved'))
                        <div class="mb-4 p-3 rounded-card text-sm font-medium" style="background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #166534;">
                            Pengaturan notifikasi berhasil disimpan.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('dashboard.alert-settings') }}">
                        @csrf
                        <div class="space-y-5">
                            {{-- Toggle Switch --}}
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-off-black">Aktifkan Peringatan Saldo Rendah</p>
                                    <p class="text-xs mt-0.5" style="color: #7b7b78;">Tampilkan banner peringatan saat saldo di bawah batas minimum</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="balance_alert_enabled" value="0">
                                    <input type="checkbox" name="balance_alert_enabled" value="1" class="sr-only peer" {{ $quota->balance_alert_enabled ? 'checked' : '' }}>
                                    <div class="w-11 h-6 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:rounded-full after:h-5 after:w-5 after:transition-all" style="background-color: #dedbd6;" 
                                         onclick="this.style.backgroundColor = this.previousElementSibling.checked ? '#dedbd6' : '#ff5600';">
                                    </div>
                                    <style>
                                        .peer:checked ~ div { background-color: #ff5600 !important; }
                                        .peer ~ div::after { background-color: #ffffff; }
                                    </style>
                                </label>
                            </div>

                            {{-- Threshold Input --}}
                            <div>
                                <label for="balance_alert_threshold" class="block text-sm font-medium text-off-black mb-1.5">Batas Minimum Saldo (IDR)</label>
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-3 py-2 text-sm border border-r-0 rounded-l" style="background-color: #faf9f6; border-color: #dedbd6; color: #7b7b78;">Rp</span>
                                    <input type="number" name="balance_alert_threshold" id="balance_alert_threshold"
                                           value="{{ $quota->balance_alert_threshold ?? 10000 }}"
                                           min="0" max="100000000" step="1000"
                                           class="flex-1 px-3 py-2 text-sm border rounded-r focus:outline-none focus:ring-1"
                                           style="border-color: #dedbd6; color: #111111; background-color: #ffffff;"
                                           onfocus="this.style.borderColor='#ff5600'; this.style.boxShadow='0 0 0 1px #ff5600';"
                                           onblur="this.style.borderColor='#dedbd6'; this.style.boxShadow='none';">
                                </div>
                                <p class="text-xs mt-1.5" style="color: #7b7b78;">Peringatan akan muncul jika saldo free atau saldo top up di bawah jumlah ini. Default: Rp 10.000</p>
                            </div>

                            {{-- Submit Button --}}
                            <div>
                                <button type="submit" class="px-5 py-2 text-sm font-medium text-white rounded-btn transition-transform hover:scale-110 active:scale-95" style="background-color: #111111;">
                                    Simpan Pengaturan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Prediksi Saldo (Spending Forecast) --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-5">
                        <svg class="w-5 h-5 flex-shrink-0" style="color: #ff5600;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-off-black tracking-sub">Prediksi Saldo</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- LEFT COLUMN: Estimasi Saldo Habis --}}
                        <div>
                            <p class="text-xs font-medium text-muted uppercase tracking-wide mb-4">Estimasi Saldo Habis</p>

                            @if($spendingForecast['avg_daily_cost'] == 0)
                                <div class="py-6 text-center">
                                    <svg class="w-8 h-8 mx-auto mb-2" style="color: #dedbd6;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-sm" style="color: #7b7b78;">Tidak ada pengeluaran terdeteksi</p>
                                    <p class="text-xs mt-1" style="color: #7b7b78;">Mulai gunakan API untuk melihat prediksi saldo.</p>
                                </div>
                            @else
                                {{-- Free Balance Forecast --}}
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-sm font-medium" style="color: #111111;">Saldo Free</span>
                                        <span class="text-sm font-medium" style="color: #111111;">
                                            {{ $spendingForecast['free_days_remaining'] }} hari
                                        </span>
                                    </div>
                                    @php
                                        $freeDays = $spendingForecast['free_days_remaining'];
                                        if ($freeDays > 30) {
                                            $freeBarColor = '#0bdf50';
                                            $freeBarPercent = 100;
                                        } elseif ($freeDays >= 7) {
                                            $freeBarColor = '#ff5600';
                                            $freeBarPercent = max(15, min(100, ($freeDays / 30) * 100));
                                        } else {
                                            $freeBarColor = '#c41c1c';
                                            $freeBarPercent = max(5, min(100, ($freeDays / 30) * 100));
                                        }
                                    @endphp
                                    <div class="w-full rounded-full" style="height: 6px; background-color: #dedbd6;">
                                        <div class="rounded-full" style="height: 6px; width: {{ $freeBarPercent }}%; background-color: {{ $freeBarColor }}; transition: width 0.3s ease;"></div>
                                    </div>
                                    <p class="text-xs mt-1.5" style="color: #7b7b78;">
                                        @if($freeDays > 0)
                                            Estimasi habis: {{ $spendingForecast['free_estimated_empty_date']->format('d M Y') }}
                                        @else
                                            Saldo sudah habis
                                        @endif
                                    </p>
                                </div>

                                {{-- Paid Balance Forecast --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-sm font-medium" style="color: #111111;">Saldo Top Up</span>
                                        <span class="text-sm font-medium" style="color: #111111;">
                                            {{ $spendingForecast['paid_days_remaining'] }} hari
                                        </span>
                                    </div>
                                    @php
                                        $paidDays = $spendingForecast['paid_days_remaining'];
                                        if ($paidDays > 30) {
                                            $paidBarColor = '#0bdf50';
                                            $paidBarPercent = 100;
                                        } elseif ($paidDays >= 7) {
                                            $paidBarColor = '#ff5600';
                                            $paidBarPercent = max(15, min(100, ($paidDays / 30) * 100));
                                        } else {
                                            $paidBarColor = '#c41c1c';
                                            $paidBarPercent = max(5, min(100, ($paidDays / 30) * 100));
                                        }
                                    @endphp
                                    <div class="w-full rounded-full" style="height: 6px; background-color: #dedbd6;">
                                        <div class="rounded-full" style="height: 6px; width: {{ $paidBarPercent }}%; background-color: {{ $paidBarColor }}; transition: width 0.3s ease;"></div>
                                    </div>
                                    <p class="text-xs mt-1.5" style="color: #7b7b78;">
                                        @if($paidDays > 0)
                                            Estimasi habis: {{ $spendingForecast['paid_estimated_empty_date']->format('d M Y') }}
                                        @else
                                            Saldo sudah habis
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>

                        {{-- RIGHT COLUMN: Rata-rata Pengeluaran --}}
                        <div>
                            <p class="text-xs font-medium text-muted uppercase tracking-wide mb-4">Rata-rata Pengeluaran</p>

                            {{-- Big number + trend --}}
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-2xl font-bold" style="color: #111111;">{{ formatRupiah($spendingForecast['avg_daily_cost']) }}</span>
                                @if($spendingForecast['trend'] === 'up')
                                    <svg class="w-5 h-5" style="color: #c41c1c;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/>
                                    </svg>
                                @elseif($spendingForecast['trend'] === 'down')
                                    <svg class="w-5 h-5" style="color: #0bdf50;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5l15 15m0 0V8.25m0 11.25H8.25"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5" style="color: #7b7b78;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/>
                                    </svg>
                                @endif
                            </div>
                            <p class="text-xs mb-4" style="color: #7b7b78;">per hari (7 hari terakhir)</p>

                            {{-- Mini bar chart (CSS-only) --}}
                            @php
                                $forecastDailyValues = array_values($spendingForecast['daily_costs_7days']);
                                $forecastDailyKeys = array_keys($spendingForecast['daily_costs_7days']);
                                $forecastMax = max(1, max($forecastDailyValues));
                            @endphp
                            <div class="flex items-end gap-1.5" style="height: 64px;">
                                @foreach($forecastDailyValues as $idx => $dayCost)
                                    @php
                                        $barHeight = max(3, ($dayCost / $forecastMax) * 100);
                                    @endphp
                                    <div class="flex-1 flex flex-col items-center justify-end h-full">
                                        <div class="w-full rounded-t" style="height: {{ $barHeight }}%; background-color: #ff5600; min-height: 3px;"></div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex gap-1.5 mt-1.5">
                                @foreach($forecastDailyKeys as $dateKey)
                                    <div class="flex-1 text-center">
                                        <span class="text-xs" style="color: #7b7b78;">{{ \Carbon\Carbon::parse($dateKey)->format('d') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cost by Model --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">Biaya per Model</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-oat">
                            <thead class="bg-canvas">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase">Model</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase">Requests</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase">Tokens</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase">Biaya (IDR)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-surface divide-y divide-oat">
                                @forelse($stats['model_usage'] as $model => $data)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-off-black">{{ $model }}</td>
                                        <td class="px-4 py-3 text-sm text-muted text-right">{{ number_format($data['requests'] ?? 0) }}</td>
                                        <td class="px-4 py-3 text-sm text-muted text-right">{{ formatTokenCount($data['tokens'] ?? 0) }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-off-black text-right">{{ formatRupiah($data['cost_idr'] ?? 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-3 text-sm text-muted text-center">Belum ada data penggunaan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Perbandingan Model --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-1">Perbandingan Model</h3>
                    <p class="text-sm mb-4" style="color: #7b7b78;">Perbandingan harga dan performa model yang pernah Anda gunakan.</p>

                    @if($modelComparison->isEmpty())
                        <div class="py-8 text-center">
                            <svg class="w-10 h-10 mx-auto mb-3" style="color: #dedbd6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-sm" style="color: #7b7b78;">Belum ada data. Mulai gunakan API untuk melihat perbandingan model.</p>
                        </div>
                    @else
                        @php
                            $cheapestCost = $modelComparison->where('user_total_requests', '>', 0)->min('input_price_idr');
                            $cheapestModelId = $modelComparison->where('input_price_idr', $cheapestCost)->first()['model_id'] ?? null;
                            $fastestTime = $modelComparison->where('user_avg_response_time', '>', 0)->min('user_avg_response_time');
                            $fastestModelId = $modelComparison->where('user_avg_response_time', $fastestTime)->first()['model_id'] ?? null;
                        @endphp
                        <div class="overflow-x-auto -mx-6">
                            <div class="inline-block min-w-full px-6">
                                <table class="min-w-full divide-y divide-oat">
                                    <thead class="bg-canvas">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase">Model</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-muted uppercase">Tier</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase">Harga Input/1M</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase">Harga Output/1M</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase">Requests</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase">Avg Response</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase">Total Biaya</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-surface divide-y divide-oat">
                                        @foreach($modelComparison as $model)
                                            <tr @if($model['model_id'] === $cheapestModelId) style="background-color: rgba(11, 223, 80, 0.06);" @endif>
                                                <td class="px-4 py-3 text-sm font-medium text-off-black whitespace-nowrap">
                                                    @if($model['model_id'] === $fastestModelId)
                                                        <svg class="w-4 h-4 inline-block mr-1" style="color: #ff5600;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                                                    @endif
                                                    {{ $model['model_name'] }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-center whitespace-nowrap">
                                                    @if($model['is_free_tier'])
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: rgba(11, 223, 80, 0.12); color: #166534;">Free</span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: rgba(255, 86, 0, 0.12); color: #c2410c;">Premium</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-sm text-muted text-right whitespace-nowrap">{{ formatRupiah($model['input_price_idr']) }}</td>
                                                <td class="px-4 py-3 text-sm text-muted text-right whitespace-nowrap">{{ formatRupiah($model['output_price_idr']) }}</td>
                                                <td class="px-4 py-3 text-sm font-medium text-off-black text-right whitespace-nowrap">{{ number_format($model['user_total_requests']) }}</td>
                                                <td class="px-4 py-3 text-sm text-muted text-right whitespace-nowrap">{{ number_format($model['user_avg_response_time']) }} ms</td>
                                                <td class="px-4 py-3 text-sm font-medium text-off-black text-right whitespace-nowrap">{{ formatRupiah($model['user_total_cost_idr']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center gap-4 text-xs" style="color: #7b7b78;">
                            <span class="inline-flex items-center gap-1">
                                <span class="inline-block w-3 h-3 rounded" style="background-color: rgba(11, 223, 80, 0.15);"></span>
                                Model termurah
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" style="color: #ff5600;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                                Model tercepat
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent Transactions --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">Transaksi Terakhir</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-oat">
                            <thead class="bg-canvas">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase">Waktu</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase">Tipe</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase">Jumlah</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase">Saldo Setelah</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase">Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-surface divide-y divide-oat">
                                @forelse($recentTransactions as $transaction)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-muted">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-canvas text-off-black">
                                                {{ $transaction->type_label }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-right {{ $transaction->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $transaction->formatted_amount }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-muted text-right">{{ formatRupiah($transaction->balance_after ?? 0) }}</td>
                                        <td class="px-4 py-3 text-sm text-muted">{{ $transaction->description }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-3 text-sm text-muted text-center">Belum ada transaksi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Recent API Activity --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">Aktivitas API Terakhir</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-oat">
                            <thead class="bg-canvas">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase">Waktu</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase">Model</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase">Input</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase">Output</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase">Total</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-muted uppercase">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase">Response Time</th>
                                </tr>
                            </thead>
                            <tbody class="bg-surface divide-y divide-oat">
                                @forelse($recentUsages as $usage)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-muted">{{ $usage->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-off-black">{{ $usage->model }}</td>
                                        <td class="px-4 py-3 text-sm text-muted text-right">{{ formatTokenCount($usage->input_tokens) }}</td>
                                        <td class="px-4 py-3 text-sm text-muted text-right">{{ formatTokenCount($usage->output_tokens) }}</td>
                                        <td class="px-4 py-3 text-sm text-muted text-right">{{ formatTokenCount($usage->total_tokens) }}</td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            @if($usage->status === 'success')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Success</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ ucfirst($usage->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-muted text-right">{{ number_format($usage->response_time) }} ms</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-3 text-sm text-muted text-center">Belum ada aktivitas API.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

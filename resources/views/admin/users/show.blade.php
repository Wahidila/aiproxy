<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-off-black tracking-heading">
                    {{ __('User Detail') }}
                </h2>
                <nav class="mt-1 text-sm text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-fin-orange">Admin</a>
                    <span class="mx-1">/</span>
                    <a href="{{ route('admin.users.index') }}" class="hover:text-fin-orange">Users</a>
                    <span class="mx-1">/</span>
                    <span class="text-off-black font-medium">{{ $user->name }}</span>
                </nav>
            </div>
            <span class="inline-flex items-center rounded-md bg-orange-100 px-2.5 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/20">
                Admin
            </span>
        </div>
    </x-slot>

    @php
        if (!function_exists('adminShowFormatRupiah')) {
            function adminShowFormatRupiah($amount) {
                return 'Rp ' . number_format($amount, 0, ',', '.');
            }
        }
    @endphp

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="rounded-lg border border-green-300 bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-lg border border-red-300 bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            {{-- User Info + Ban Status Card --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-5">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                        {{-- Left: User Info --}}
                        <div class="flex items-center gap-4">
                            <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full bg-fin-orange-light text-2xl font-bold text-fin-orange">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-off-black tracking-sub">{{ $user->name }}</h3>
                                <p class="text-sm text-muted">{{ $user->email }}</p>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    @if($user->role === 'admin')
                                        <span class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-700">
                                            Admin
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-canvas px-2.5 py-0.5 text-xs font-medium text-off-black">
                                            User
                                        </span>
                                    @endif
                                    <span class="text-xs text-warm-sand">Joined {{ $user->created_at->format('d M Y H:i') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Right: Ban Status & Delete --}}
                        <div class="flex-shrink-0">
                            @if($user->is_banned)
                                <div class="rounded-lg border border-red-200 bg-red-50 p-4 max-w-sm">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-flex items-center rounded-full bg-red-600 px-3 py-0.5 text-xs font-bold text-white">
                                            BANNED
                                        </span>
                                    </div>
                                    @if($user->ban_reason)
                                        <p class="text-sm text-red-700 mb-1"><span class="font-medium">Reason:</span> {{ $user->ban_reason }}</p>
                                    @endif
                                    @if($user->banned_at)
                                        <p class="text-xs text-red-500 mb-3">Banned at: {{ $user->banned_at->format('d M Y H:i') }}</p>
                                    @endif
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="{{ route('admin.users.unban', $user) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center rounded-btn bg-white px-3 py-1.5 text-sm font-medium text-red-700 ring-1 ring-inset ring-red-300 hover:bg-red-50 transition-colors">
                                                Unban User
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <div class="max-w-sm" x-data="{ showBan: false }">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-0.5 text-xs font-bold text-green-700">
                                            Active
                                        </span>
                                        @if($user->role !== 'admin')
                                            <button type="button"
                                                    @click="showBan = !showBan"
                                                    class="inline-flex items-center rounded-btn bg-white px-3 py-1.5 text-sm font-medium text-red-600 ring-1 ring-inset ring-red-300 hover:bg-red-50 transition-colors">
                                                <span x-text="showBan ? 'Cancel' : 'Ban User'"></span>
                                            </button>
                                        @endif
                                    </div>
                                    @if($user->role !== 'admin')
                                        <div x-show="showBan" x-transition class="mt-3 rounded-lg border border-red-200 bg-red-50 p-4">
                                            <form method="POST" action="{{ route('admin.users.ban', $user) }}" class="space-y-3">
                                                @csrf
                                                <div>
                                                    <label for="ban_reason" class="block text-sm font-medium text-red-700 mb-1">Ban Reason</label>
                                                    <textarea id="ban_reason"
                                                              name="ban_reason"
                                                              rows="2"
                                                              required
                                                              placeholder="Alasan ban user..."
                                                              class="w-full rounded-btn border-red-300 text-sm focus:border-red-500 focus:ring-red-500"></textarea>
                                                </div>
                                                <button type="submit"
                                                        class="inline-flex items-center rounded-btn bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                                                    Confirm Ban
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Delete User Button --}}
                            @if($user->role !== 'admin')
                                <div class="mt-4 pt-4 border-t border-oat" x-data="{ showDelete: false }">
                                    <button type="button"
                                            @click="showDelete = !showDelete"
                                            class="inline-flex items-center rounded-btn bg-white px-3 py-1.5 text-sm font-medium text-red-600 ring-1 ring-inset ring-oat hover:bg-canvas btn-intercom transition-colors">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <span x-text="showDelete ? 'Batal' : 'Hapus User'"></span>
                                    </button>
                                    <div x-show="showDelete" x-transition x-cloak class="mt-3 rounded-card border border-oat bg-canvas p-4">
                                        <p class="text-sm text-off-black mb-3">
                                            <strong>Peringatan:</strong> Menghapus user akan menghapus semua data termasuk API keys, riwayat transaksi, usage logs, dan donasi. Aksi ini tidak bisa dibatalkan.
                                        </p>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    onclick="return confirm('KONFIRMASI FINAL: Hapus user {{ $user->name }} ({{ $user->email }}) beserta SEMUA datanya? Aksi ini TIDAK BISA dibatalkan!')"
                                                    class="inline-flex items-center rounded-btn bg-off-black px-3 py-1.5 text-sm font-medium text-white hover:bg-off-black/90 btn-intercom transition-colors">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Konfirmasi Hapus User
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Wallet & Stats Row --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                {{-- Left: Wallet Card --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="px-6 py-5">
                        <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">Wallet</h3>

                        {{-- Balance Display --}}
                        <div class="mb-4">
                            <p class="text-sm text-muted mb-1">Total Saldo</p>
                            <p class="text-3xl font-bold {{ $quota && $quota->total_balance > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $quota ? $quota->formatted_balance : 'Rp 0' }}
                            </p>
                        </div>

                        {{-- Free & Paid Balance Breakdown --}}
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3">
                                <p class="text-xs font-medium text-emerald-600 uppercase tracking-wider">Free Tier</p>
                                <p class="mt-1 text-lg font-bold text-emerald-700">
                                    {{ $quota ? $quota->formatted_free_balance : 'Rp 0' }}
                                </p>
                            </div>
                            <div class="rounded-lg bg-fin-orange-light border border-fin-orange/20 p-3">
                                <p class="text-xs font-medium text-fin-orange uppercase tracking-wider">Top Up</p>
                                <p class="mt-1 text-lg font-bold text-fin-orange">
                                    {{ $quota ? $quota->formatted_paid_balance : 'Rp 0' }}
                                </p>
                            </div>
                        </div>

                        {{-- Free Credit & User Type --}}
                        <div class="flex flex-wrap items-center gap-3 mb-6">
                            @if($quota && $quota->free_credit_claimed)
                                <span class="inline-flex items-center rounded-full bg-canvas px-2.5 py-0.5 text-xs font-medium text-muted">
                                    Free credit claimed
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-700">
                                    Not claimed
                                </span>
                            @endif

                            @if($quota && $quota->paid_balance > 0)
                                <span class="inline-flex items-center rounded-full bg-fin-orange-light px-2.5 py-0.5 text-xs font-medium text-fin-orange">
                                    Has Paid Balance
                                </span>
                            @endif
                        </div>

                        {{-- Adjust Balance Form --}}
                        <div class="border-t border-oat pt-4">
                            <h4 class="text-sm font-semibold text-off-black mb-3">Adjust Saldo</h4>
                            <form method="POST" action="{{ route('admin.users.adjust-balance', $user) }}" class="space-y-3" x-data="{ balanceType: 'paid' }">
                                @csrf
                                <div>
                                    <label class="block text-xs font-medium text-muted mb-1.5">Tipe Saldo</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <label class="relative flex cursor-pointer items-center justify-center rounded-lg border-2 px-3 py-2.5 text-sm font-medium transition-all"
                                               :class="balanceType === 'free' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-oat bg-surface text-muted hover:bg-canvas'">
                                            <input type="radio" name="balance_type" value="free" x-model="balanceType" class="sr-only">
                                            <span>Free Tier</span>
                                        </label>
                                        <label class="relative flex cursor-pointer items-center justify-center rounded-lg border-2 px-3 py-2.5 text-sm font-medium transition-all"
                                               :class="balanceType === 'paid' ? 'border-fin-orange bg-fin-orange-light text-fin-orange' : 'border-oat bg-surface text-muted hover:bg-canvas'">
                                            <input type="radio" name="balance_type" value="paid" x-model="balanceType" class="sr-only">
                                            <span>Top Up</span>
                                        </label>
                                    </div>
                                    @error('balance_type')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-muted mb-1.5">Jumlah</label>
                                    <input type="number"
                                           name="amount"
                                           step="1000"
                                           required
                                           placeholder="Positif = tambah, negatif = kurangi"
                                           class="w-full rounded-btn border-oat text-sm focus:border-fin-orange focus:ring-fin-orange">
                                    @error('amount')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-muted mb-1.5">Alasan</label>
                                    <textarea name="reason"
                                              rows="2"
                                              required
                                              placeholder="Alasan adjustment..."
                                              class="w-full rounded-btn border-oat text-sm focus:border-fin-orange focus:ring-fin-orange"></textarea>
                                    @error('reason')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center rounded-btn px-4 py-2.5 text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors"
                                        :class="balanceType === 'free' ? 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500' : 'bg-off-black hover:bg-off-black/90 focus:ring-fin-orange'">
                                    <span x-text="balanceType === 'free' ? 'Adjust Saldo Free Tier' : 'Adjust Saldo Top Up'"></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Right: Stats Cards (2x2 grid) --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- Total Spending --}}
                    <div class="bg-surface border border-oat rounded-card">
                        <div class="p-5">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-fin-orange-light mb-3">
                                <svg class="h-6 w-6 text-fin-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-xs font-medium text-muted uppercase tracking-wider">Total Spending</p>
                            <p class="mt-1 text-xl font-bold text-off-black">{{ adminShowFormatRupiah($totalSpending) }}</p>
                        </div>
                    </div>

                    {{-- Total Requests --}}
                    <div class="bg-surface border border-oat rounded-card">
                        <div class="p-5">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-fin-orange-light mb-3">
                                <svg class="h-6 w-6 text-fin-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <p class="text-xs font-medium text-muted uppercase tracking-wider">Total Requests (30d)</p>
                            <p class="mt-1 text-xl font-bold text-off-black">{{ number_format($stats['total_requests'] ?? 0) }}</p>
                        </div>
                    </div>

                    {{-- Avg Response Time --}}
                    <div class="bg-surface border border-oat rounded-card">
                        <div class="p-5">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-fin-orange-light mb-3">
                                <svg class="h-6 w-6 text-fin-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-xs font-medium text-muted uppercase tracking-wider">Avg Response Time</p>
                            <p class="mt-1 text-xl font-bold text-off-black">{{ number_format($stats['avg_response_time'] ?? 0) }} ms</p>
                        </div>
                    </div>

                    {{-- Favorite Model --}}
                    <div class="bg-surface border border-oat rounded-card">
                        <div class="p-5">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-fin-orange-light mb-3">
                                <svg class="h-6 w-6 text-fin-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                            </div>
                            <p class="text-xs font-medium text-muted uppercase tracking-wider">Favorite Model</p>
                            <p class="mt-1 text-lg font-bold text-off-black truncate" title="{{ $stats['favorite_model'] ?? '-' }}">
                                {{ $stats['favorite_model'] ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Subscription Plan Card --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4 flex items-center gap-2">
                        <i data-lucide="crown" class="w-5 h-5 text-fin-orange"></i>
                        Subscription Plan
                    </h3>

                    {{-- Current Plan Info --}}
                    <div class="mb-4 rounded-lg border border-oat bg-canvas p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-muted uppercase tracking-wider">Plan Aktif</p>
                                <p class="mt-1 text-xl font-bold text-off-black">{{ $activePlan->name ?? 'FREE' }}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold
                                @if(($activePlan->slug ?? 'free') === 'premium') bg-fin-orange-light text-fin-orange
                                @elseif(($activePlan->slug ?? 'free') === 'pro') bg-blue-100 text-blue-700
                                @elseif(($activePlan->slug ?? 'free') === 'daily') bg-purple-100 text-purple-700
                                @else bg-canvas text-muted
                                @endif">
                                {{ strtoupper($activePlan->slug ?? 'FREE') }}
                            </span>
                        </div>

                        @if($activeSubscription)
                            <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div>
                                    <p class="text-xs text-muted">Status</p>
                                    <p class="text-sm font-medium {{ $activeSubscription->isActive() ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $activeSubscription->isActive() ? 'Active' : 'Expired' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted">Mulai</p>
                                    <p class="text-sm font-medium text-off-black">{{ $activeSubscription->starts_at->format('d M Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted">Expires</p>
                                    <p class="text-sm font-medium text-off-black">{{ $activeSubscription->expires_at ? $activeSubscription->expires_at->format('d M Y H:i') : 'Never' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted">Request Hari Ini</p>
                                    <p class="text-sm font-medium text-off-black">
                                        {{ number_format($activeSubscription->daily_requests_used) }}
                                        / {{ $activePlan->daily_request_limit ? number_format($activePlan->daily_request_limit) : '∞' }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <p class="mt-2 text-sm text-muted">User belum memiliki subscription record (default FREE).</p>
                        @endif
                    </div>

                    {{-- Assign Plan Form --}}
                    <div class="border-t border-oat pt-4">
                        <h4 class="text-sm font-semibold text-off-black mb-3">Assign / Change Plan</h4>
                        <form method="POST" action="{{ route('admin.users.assign-plan', $user) }}" class="space-y-3" x-data="{ selectedPlan: '{{ $activePlan->slug ?? 'free' }}' }">
                            @csrf
                            <div>
                                <label class="block text-xs font-medium text-muted mb-1.5">Pilih Plan</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    @foreach($allPlans as $plan)
                                        <label class="relative flex cursor-pointer items-center justify-center rounded-lg border-2 px-3 py-2.5 text-sm font-medium transition-all"
                                               :class="selectedPlan === '{{ $plan->slug }}' ? 'border-fin-orange bg-fin-orange-light text-fin-orange' : 'border-oat bg-surface text-muted hover:bg-canvas'">
                                            <input type="radio" name="plan_slug" value="{{ $plan->slug }}" x-model="selectedPlan" class="sr-only">
                                            <div class="text-center">
                                                <span class="block font-semibold">{{ $plan->name }}</span>
                                                <span class="block text-xs mt-0.5">{{ $plan->formatted_price }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div x-show="selectedPlan !== 'free' && selectedPlan !== 'daily'" x-transition>
                                <label class="block text-xs font-medium text-muted mb-1.5">Durasi (hari)</label>
                                <input type="number"
                                       name="duration_days"
                                       value="30"
                                       min="1"
                                       max="365"
                                       class="w-full rounded-btn border-oat text-sm focus:border-fin-orange focus:ring-fin-orange">
                                <p class="mt-1 text-xs text-muted">Default 30 hari untuk plan bulanan.</p>
                            </div>
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center rounded-btn bg-off-black px-4 py-2.5 text-sm font-medium text-white hover:bg-off-black/90 focus:outline-none focus:ring-2 focus:ring-fin-orange focus:ring-offset-2 transition-colors"
                                    onclick="return confirm('Assign plan ini ke user?')">
                                Assign Plan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Spending by Model --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">Biaya per Model (30 Hari)</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-oat">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Model</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Requests</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Input Tokens</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Output Tokens</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Total Tokens</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Cost IDR</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-oat">
                                @forelse($stats['model_usage'] ?? [] as $model => $data)
                                    <tr class="hover:bg-canvas">
                                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-off-black">{{ $model }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-muted">{{ number_format($data['requests']) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-muted">{{ number_format($data['input_tokens']) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-muted">{{ number_format($data['output_tokens']) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-muted">{{ number_format($data['total_tokens']) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-off-black">{{ adminShowFormatRupiah($data['cost_idr']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-warm-sand">
                                            Belum ada data
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- API Keys --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">
                        API Keys
                        <span class="ml-2 inline-flex items-center rounded-full bg-canvas px-2.5 py-0.5 text-xs font-medium text-muted">
                            {{ $user->apiKeys->count() }}
                        </span>
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-oat">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Key</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Last Used</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-oat">
                                @forelse($user->apiKeys as $apiKey)
                                    <tr class="hover:bg-canvas">
                                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-off-black">
                                            {{ $apiKey->name }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                            <code class="rounded bg-canvas px-2 py-0.5 font-mono text-xs">{{ $apiKey->masked_key }}</code>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-center">
                                            @if($apiKey->is_active)
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-canvas px-2.5 py-0.5 text-xs font-medium text-muted">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                            {{ $apiKey->last_used_at ? $apiKey->last_used_at->format('d M Y H:i') : '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right">
                                            <form method="POST"
                                                  action="{{ route('admin.users.revoke-key', [$user, $apiKey]) }}"
                                                  onsubmit="return confirm('Are you sure you want to revoke this API key?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center rounded-btn bg-white px-2.5 py-1.5 text-xs font-medium text-red-600 ring-1 ring-inset ring-red-300 hover:bg-red-50 transition-colors">
                                                    Revoke
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-warm-sand">
                                            Belum ada API key
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Wallet Transactions --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">Riwayat Transaksi</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-oat">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Type</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Amount</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Balance After</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-oat">
                                @forelse($transactions as $transaction)
                                    <tr class="hover:bg-canvas">
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                            {{ $transaction->created_at->format('d M Y H:i') }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-off-black">
                                            {{ $transaction->type_label }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium {{ $transaction->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $transaction->formatted_amount }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-muted">
                                            {{ adminShowFormatRupiah($transaction->balance_after) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-muted max-w-xs truncate">
                                            {{ $transaction->description ?? '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-warm-sand">
                                            Belum ada transaksi
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($transactions->hasPages())
                        <div class="mt-4 border-t border-oat pt-4">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent API Usage --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">Recent API Usage (Last 20)</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-oat">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Time</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Model</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Input Tokens</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Output Tokens</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Total Tokens</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Response Time</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-oat">
                                @forelse($recentUsages as $usage)
                                    <tr class="hover:bg-canvas">
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                            {{ $usage->created_at->format('d/m H:i') }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-off-black">
                                            {{ $usage->model }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-muted">
                                            {{ number_format($usage->input_tokens) }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-muted">
                                            {{ number_format($usage->output_tokens) }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-off-black">
                                            {{ number_format($usage->total_tokens) }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-center">
                                            @if($usage->status_code >= 200 && $usage->status_code < 300)
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                                    {{ $usage->status_code }}
                                                </span>
                                            @elseif($usage->status_code >= 400)
                                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                                    {{ $usage->status_code }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                                    {{ $usage->status_code }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-muted">
                                            {{ number_format($usage->response_time_ms) }} ms
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-sm text-warm-sand">
                                            Belum ada data
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Contact User --}}
            <div class="bg-surface border border-oat rounded-card" x-data="{ showForm: false }">
                <div class="px-6 py-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-off-black tracking-sub flex items-center gap-2">
                            <i data-lucide="mail" class="w-5 h-5 text-fin-orange"></i>
                            Contact User
                        </h3>
                        <button type="button"
                                @click="showForm = !showForm"
                                class="inline-flex items-center rounded-btn px-3 py-1.5 text-sm font-medium transition-colors"
                                :class="showForm ? 'bg-canvas text-muted hover:bg-oat' : 'bg-fin-orange text-white hover:bg-fin-orange-hover'">
                            <i data-lucide="send" class="w-3.5 h-3.5 mr-1.5"></i>
                            <span x-text="showForm ? 'Tutup' : 'Kirim Email'"></span>
                        </button>
                    </div>

                    <p class="text-sm text-muted mb-4">
                        Kirim email langsung ke <strong class="text-off-black">{{ $user->email }}</strong>. Pesan akan dikirim menggunakan template email {{ config('app.name') }}.
                    </p>

                    {{-- Send Email Form --}}
                    <div x-show="showForm" x-transition x-cloak>
                        <form method="POST" action="{{ route('admin.users.send-email', $user) }}" class="space-y-4 border-t border-oat pt-4">
                            @csrf
                            <div>
                                <label for="email_subject" class="block text-sm font-medium text-off-black mb-1.5">Subject</label>
                                <input type="text"
                                       id="email_subject"
                                       name="subject"
                                       value="{{ old('subject') }}"
                                       required
                                       placeholder="Subject email..."
                                       class="w-full rounded-btn border-oat text-sm focus:border-fin-orange focus:ring-fin-orange">
                                @error('subject')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="email_body" class="block text-sm font-medium text-off-black mb-1.5">Isi Pesan</label>
                                <textarea id="email_body"
                                          name="body"
                                          rows="6"
                                          required
                                          placeholder="Tulis pesan untuk user..."
                                          class="w-full rounded-btn border-oat text-sm focus:border-fin-orange focus:ring-fin-orange">{{ old('body') }}</textarea>
                                <p class="mt-1 text-xs text-muted">Pesan akan ditampilkan dalam template email HTML. Gunakan baris baru untuk paragraf.</p>
                                @error('body')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-center justify-end gap-3">
                                <button type="button"
                                        @click="showForm = false"
                                        class="inline-flex items-center rounded-btn border border-oat bg-surface px-4 py-2 text-sm font-medium text-muted hover:bg-canvas transition-colors">
                                    Batal
                                </button>
                                <button type="submit"
                                        class="inline-flex items-center rounded-btn bg-off-black px-4 py-2.5 text-sm font-medium text-white hover:bg-off-black/90 focus:outline-none focus:ring-2 focus:ring-fin-orange focus:ring-offset-2 transition-colors"
                                        onclick="return confirm('Kirim email ke {{ $user->email }}?')">
                                    <i data-lucide="send" class="w-4 h-4 mr-1.5"></i>
                                    Kirim Email
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Email History --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4 flex items-center gap-2">
                        <i data-lucide="history" class="w-5 h-5 text-fin-orange"></i>
                        Email History
                        <span class="inline-flex items-center rounded-full bg-canvas px-2.5 py-0.5 text-xs font-medium text-muted">
                            {{ $adminEmails->total() }}
                        </span>
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-oat">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Subject</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Pengirim</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Preview</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-oat">
                                @forelse($adminEmails as $email)
                                    <tr class="hover:bg-canvas" x-data="{ expanded: false }">
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                            {{ $email->created_at->format('d M Y H:i') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-off-black max-w-xs">
                                            <button type="button" @click="expanded = !expanded" class="text-left hover:text-fin-orange transition-colors">
                                                {{ $email->subject }}
                                            </button>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                            {{ $email->admin->name ?? '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-center">
                                            @if($email->status === 'sent')
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                                    Sent
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700" title="{{ $email->error_message }}">
                                                    Failed
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-muted max-w-xs truncate">
                                            {{ Str::limit($email->body, 60) }}
                                        </td>
                                    </tr>
                                    {{-- Expandable row for full message --}}
                                    <tr x-show="expanded" x-transition x-cloak>
                                        <td colspan="5" class="px-4 py-4 bg-canvas">
                                            <div class="rounded-lg border border-oat bg-surface p-4">
                                                <p class="text-xs font-medium text-muted uppercase tracking-wider mb-2">Isi Pesan Lengkap</p>
                                                <div class="text-sm text-off-black whitespace-pre-line leading-relaxed">{{ $email->body }}</div>
                                                @if($email->status === 'failed' && $email->error_message)
                                                    <div class="mt-3 rounded-btn border border-red-200 bg-red-50 p-3">
                                                        <p class="text-xs font-medium text-red-700">Error: {{ $email->error_message }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-warm-sand">
                                            Belum ada email yang dikirim ke user ini
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($adminEmails->hasPages())
                        <div class="mt-4 border-t border-oat pt-4">
                            {{ $adminEmails->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

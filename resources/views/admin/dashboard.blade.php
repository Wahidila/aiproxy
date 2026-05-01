<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-off-black tracking-heading">
                    {{ __('Admin Panel') }}
                </h2>
                <nav class="mt-1 text-sm text-muted">
                    <span>Dashboard</span>
                </nav>
            </div>
            <span class="inline-flex items-center rounded-md bg-orange-100 px-2.5 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/20">
                Admin
            </span>
        </div>
    </x-slot>

    @php
        if (!function_exists('adminFormatTokens')) {
            function adminFormatTokens($count) {
                if ($count >= 1000000) {
                    return number_format($count / 1000000, 1) . 'M';
                } elseif ($count >= 1000) {
                    return number_format($count / 1000, 1) . 'K';
                }
                return number_format($count);
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

            {{-- API Proxy Control --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <i data-lucide="server" class="w-5 h-5 text-muted"></i>
                            <h3 class="text-lg font-semibold text-off-black tracking-sub">API Proxy Control</h3>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Golang Proxy Status --}}
                        <div class="rounded-lg border p-4 {{ $golangStatus['online'] ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $golangStatus['online'] ? 'bg-green-100' : 'bg-red-100' }}">
                                        <i data-lucide="cpu" class="w-5 h-5 {{ $golangStatus['online'] ? 'text-green-600' : 'text-red-600' }}"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-off-black">Golang Proxy</p>
                                        <p class="text-xs text-muted">Port 8080 (primary)</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($golangStatus['online'])
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                            <span class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                                            ONLINE
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                            <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                            OFFLINE
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if($golangStatus['online'] && isset($golangStatus['timestamp']))
                                <p class="mt-2 text-xs text-muted">Last check: {{ $golangStatus['timestamp'] }}</p>
                            @elseif(!$golangStatus['online'])
                                <p class="mt-2 text-xs text-red-600">{{ $golangStatus['error'] ?? 'Cannot reach proxy' }}. Start via SSH: <code class="bg-red-100 px-1 rounded">systemctl start ai-token-proxy</code></p>
                            @endif
                        </div>

                        {{-- Laravel Fallback Toggle --}}
                        <div class="rounded-lg border p-4 {{ $laravelFallback ? 'border-green-200 bg-green-50' : 'border-oat bg-canvas' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $laravelFallback ? 'bg-green-100' : 'bg-oat' }}">
                                        <i data-lucide="shield" class="w-5 h-5 {{ $laravelFallback ? 'text-green-600' : 'text-muted' }}"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-off-black">Laravel Fallback API</p>
                                        <p class="text-xs text-muted">Backup jika Golang offline</p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('admin.proxy.toggle-laravel') }}">
                                    @csrf
                                    <button type="submit"
                                        class="relative inline-flex h-7 w-12 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-fin-orange focus:ring-offset-2 {{ $laravelFallback ? 'bg-green-500' : 'bg-oat' }}"
                                        role="switch"
                                        aria-checked="{{ $laravelFallback ? 'true' : 'false' }}">
                                        <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white ring-0 transition duration-200 ease-in-out {{ $laravelFallback ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>
                                </form>
                            </div>
                            <p class="mt-2 text-xs text-muted">
                                @if($laravelFallback)
                                    <i data-lucide="check-circle" class="w-3 h-3 inline text-green-500"></i>
                                    Aktif &mdash; Laravel menangani <code class="bg-green-100 px-1 rounded">/api/v1/*</code> jika Golang mati.
                                @else
                                    <i data-lucide="x-circle" class="w-3 h-3 inline text-warm-sand"></i>
                                    Nonaktif &mdash; Request ke <code class="bg-oat px-1 rounded">/api/v1/*</code> via Laravel akan ditolak (503).
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Status Summary --}}
                    <div class="mt-3 rounded-card {{ $golangStatus['online'] ? 'bg-blue-50 border border-blue-100' : ($laravelFallback ? 'bg-yellow-50 border border-yellow-100' : 'bg-red-50 border border-red-100') }} p-3">
                        <p class="text-xs font-medium {{ $golangStatus['online'] ? 'text-blue-700' : ($laravelFallback ? 'text-yellow-700' : 'text-red-700') }}">
                            <i data-lucide="info" class="w-3.5 h-3.5 inline"></i>
                            @if($golangStatus['online'] && !$laravelFallback)
                                Normal &mdash; Semua API request ditangani oleh Golang proxy (recommended).
                            @elseif($golangStatus['online'] && $laravelFallback)
                                Keduanya aktif &mdash; Golang primary, Laravel sebagai backup.
                            @elseif(!$golangStatus['online'] && $laravelFallback)
                                Fallback mode &mdash; Golang offline, Laravel menangani semua API request.
                            @else
                                API tidak tersedia &mdash; Golang offline dan Laravel fallback dinonaktifkan. Aktifkan salah satu!
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                {{-- Total Users --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-fin-orange-light">
                                    <svg class="h-6 w-6 text-fin-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-muted">Total Users</p>
                                <p class="text-2xl font-bold text-off-black">{{ number_format($totalUsers) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Tokens Used --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-fin-orange-light">
                                    <svg class="h-6 w-6 text-fin-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-muted">Total Tokens</p>
                                <p class="text-2xl font-bold text-off-black">{{ adminFormatTokens($totalTokensUsed) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pending Donations --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $pendingDonations > 0 ? 'bg-red-50' : 'bg-fin-orange-light' }}">
                                    <svg class="h-6 w-6 {{ $pendingDonations > 0 ? 'text-red-600' : 'text-fin-orange' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-muted">Pending Donations</p>
                                <div class="flex items-center gap-2">
                                    <p class="text-2xl font-bold text-off-black">{{ $pendingDonations }}</p>
                                    @if($pendingDonations > 0)
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">
                                            Needs Review
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Revenue --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-50">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-muted">Total Revenue</p>
                                <p class="text-2xl font-bold text-off-black">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Active Users Today --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-fin-orange-light">
                                    <svg class="h-6 w-6 text-fin-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-muted">Active Today</p>
                                <p class="text-2xl font-bold text-off-black">{{ number_format($activeUsersToday) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pending Trial Requests --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $pendingTrialRequests > 0 ? 'bg-purple-50' : 'bg-fin-orange-light' }}">
                                    <svg class="h-6 w-6 {{ $pendingTrialRequests > 0 ? 'text-purple-600' : 'text-fin-orange' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-muted">Trial Requests</p>
                                <div class="flex items-center gap-2">
                                    <p class="text-2xl font-bold text-off-black">{{ $pendingTrialRequests }}</p>
                                    @if($pendingTrialRequests > 0)
                                        <span class="inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-700">
                                            Pending
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 rounded-card border border-oat bg-surface p-4 hover:border-fin-orange hover:shadow-sm transition-all">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-fin-orange-light">
                        <svg class="h-5 w-5 text-fin-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-off-black">Manage Users</p>
                        <p class="text-xs text-muted">View and manage all users</p>
                    </div>
                </a>

                <a href="{{ route('admin.donations.index') }}"
                   class="flex items-center gap-3 rounded-card border border-oat bg-surface p-4 hover:border-fin-orange hover:shadow-sm transition-all">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $pendingDonations > 0 ? 'bg-red-100' : 'bg-fin-orange-light' }}">
                        <svg class="h-5 w-5 {{ $pendingDonations > 0 ? 'text-red-600' : 'text-fin-orange' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-off-black">Manage Donations</p>
                        <p class="text-xs text-muted">
                            @if($pendingDonations > 0)
                                <span class="text-red-600 font-medium">{{ $pendingDonations }} pending</span>
                            @else
                                Review and approve donations
                            @endif
                        </p>
                    </div>
                </a>

                <a href="{{ route('admin.model-pricing.index') }}"
                   class="flex items-center gap-3 rounded-card border border-oat bg-surface p-4 hover:border-fin-orange hover:shadow-sm transition-all">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-fin-orange-light">
                        <svg class="h-5 w-5 text-fin-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-off-black">Model Pricing</p>
                        <p class="text-xs text-muted">Configure per-model pricing & discounts</p>
                    </div>
                </a>

                <a href="{{ route('admin.settings.index') }}"
                   class="flex items-center gap-3 rounded-card border border-oat bg-surface p-4 hover:border-fin-orange hover:shadow-sm transition-all">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-fin-orange-light">
                        <svg class="h-5 w-5 text-fin-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-off-black">Settings</p>
                        <p class="text-xs text-muted">Configure site & wallet settings</p>
                    </div>
                </a>

                <a href="{{ route('admin.trial-requests.index') }}"
                   class="flex items-center gap-3 rounded-card border border-oat bg-surface p-4 hover:border-fin-orange hover:shadow-sm transition-all">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $pendingTrialRequests > 0 ? 'bg-purple-100' : 'bg-fin-orange-light' }}">
                        <svg class="h-5 w-5 {{ $pendingTrialRequests > 0 ? 'text-purple-600' : 'text-fin-orange' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-off-black">Trial Requests</p>
                        <p class="text-xs text-muted">
                            @if($pendingTrialRequests > 0)
                                <span class="text-purple-600 font-medium">{{ $pendingTrialRequests }} pending</span>
                            @else
                                Manage trial sign-ups
                            @endif
                        </p>
                    </div>
                </a>

                <a href="{{ route('admin.subscription-plans.index') }}"
                   class="flex items-center gap-3 rounded-card border border-oat bg-surface p-4 hover:border-fin-orange hover:shadow-sm transition-all">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-fin-orange-light">
                        <svg class="h-5 w-5 text-fin-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-off-black">Subscription Plans</p>
                        <p class="text-xs text-muted">Manage subscription plans & pricing</p>
                    </div>
                </a>

                <a href="{{ route('admin.subscriptions.index') }}"
                   class="flex items-center gap-3 rounded-card border border-oat bg-surface p-4 hover:border-fin-orange hover:shadow-sm transition-all">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-fin-orange-light">
                        <svg class="h-5 w-5 text-fin-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-off-black">User Subscriptions</p>
                        <p class="text-xs text-muted">View & manage user subscriptions</p>
                    </div>
                </a>

                <a href="{{ route('admin.broadcast-notifications.index') }}"
                   class="flex items-center gap-3 rounded-card border border-oat bg-surface p-4 hover:border-fin-orange hover:shadow-sm transition-all">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-fin-orange-light">
                        <i data-lucide="megaphone" class="w-5 h-5 text-fin-orange"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-off-black">Broadcast</p>
                        <p class="text-xs text-muted">Send notifications to all users</p>
                    </div>
                </a>
            </div>

            {{-- Daily Stats Chart --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">Daily Stats (Last 7 Days)</h3>

                    @php
                        $maxTokens = 0;
                        $maxRequests = 0;
                        foreach ($dailyStats as $date => $stat) {
                            if (($stat['tokens'] ?? 0) > $maxTokens) $maxTokens = $stat['tokens'];
                            if (($stat['requests'] ?? 0) > $maxRequests) $maxRequests = $stat['requests'];
                        }
                    @endphp

                    @if(count($dailyStats) > 0 && $maxTokens > 0)
                        <div class="flex items-end justify-between gap-2" style="height: 200px;">
                            @foreach($dailyStats as $date => $stat)
                                @php
                                    $tokenPercent = $maxTokens > 0 ? (($stat['tokens'] ?? 0) / $maxTokens) * 100 : 0;
                                @endphp
                                <div class="flex flex-1 flex-col items-center justify-end h-full">
                                    <span class="mb-1 text-xs font-medium text-muted">
                                        {{ adminFormatTokens($stat['tokens'] ?? 0) }}
                                    </span>
                                    <div class="w-full rounded-t-md bg-fin-orange transition-all duration-300 hover:bg-fin-orange/90"
                                         style="height: {{ max($tokenPercent, 2) }}%;"
                                         title="{{ number_format($stat['tokens'] ?? 0) }} tokens / {{ number_format($stat['requests'] ?? 0) }} requests"></div>
                                    <span class="mt-2 text-xs text-muted">
                                        {{ \Carbon\Carbon::parse($date)->format('d/m') }}
                                    </span>
                                    <span class="text-xs text-warm-sand">
                                        {{ number_format($stat['requests'] ?? 0) }} req
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex h-48 items-center justify-center text-warm-sand">
                            <p>No usage data available</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Pending Trial Requests --}}
            @if($recentTrialRequests->count() > 0)
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-off-black tracking-sub flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-purple-100">
                                <svg class="h-3.5 w-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </span>
                            Pending Trial Requests
                        </h3>
                        <a href="{{ route('admin.trial-requests.index', ['status' => 'pending']) }}" class="text-sm font-medium text-fin-orange hover:text-fin-orange/80">
                            View All
                        </a>
                    </div>

                    <div class="space-y-3">
                        @foreach($recentTrialRequests as $trialReq)
                            <div class="flex items-center justify-between rounded-lg border border-oat p-3 hover:bg-canvas">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-purple-100 text-sm font-semibold text-purple-600">
                                        {{ strtoupper(substr($trialReq->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-off-black truncate">{{ $trialReq->name }}</p>
                                        <p class="text-xs text-muted truncate">{{ $trialReq->email }} &middot; {{ $trialReq->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 ml-3">
                                    <form method="POST" action="{{ route('admin.trial-requests.invite', $trialReq) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center rounded-btn bg-green-600 px-2.5 py-1.5 text-xs font-medium text-white hover:bg-green-700 transition-colors">
                                            Invite
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.trial-requests.index') }}" class="inline-flex items-center rounded-btn bg-canvas px-2.5 py-1.5 text-xs font-medium text-off-black hover:bg-oat transition-colors">
                                        View
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Two Side-by-Side Sections --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                {{-- Pending Donations --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="px-6 py-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-off-black tracking-sub">Pending Donations</h3>
                            <a href="{{ route('admin.donations.index', ['status' => 'pending']) }}" class="text-sm font-medium text-fin-orange hover:text-fin-orange/80">
                                View All
                            </a>
                        </div>

                        <div class="space-y-3">
                            @forelse($recentDonations->take(5) as $donation)
                                <div class="flex items-center justify-between rounded-lg border border-oat p-3 hover:bg-canvas">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-off-black truncate">{{ $donation->user->name }}</p>
                                        <p class="text-xs text-muted">
                                            Rp {{ number_format($donation->amount, 0, ',', '.') }}
                                            &middot;
                                            {{ $donation->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2 ml-3">
                                        <form method="POST" action="{{ route('admin.donations.approve', $donation) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center rounded-btn bg-green-600 px-2.5 py-1.5 text-xs font-medium text-white hover:bg-green-700 transition-colors">
                                                Approve
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.donations.index') }}" class="inline-flex items-center rounded-btn bg-canvas px-2.5 py-1.5 text-xs font-medium text-off-black hover:bg-oat transition-colors">
                                            View
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center text-sm text-warm-sand">
                                    No pending donations
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Recent Users --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="px-6 py-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-off-black tracking-sub">Recent Users</h3>
                            <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-fin-orange hover:text-fin-orange/80">
                                View All
                            </a>
                        </div>

                        <div class="space-y-3">
                            @forelse($recentUsers->take(5) as $user)
                                <div class="flex items-center justify-between rounded-lg border border-oat p-3 hover:bg-canvas">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-fin-orange-light text-sm font-semibold text-fin-orange">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-off-black truncate">{{ $user->name }}</p>
                                            <p class="text-xs text-muted truncate">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                    <span class="text-xs text-warm-sand whitespace-nowrap ml-3">
                                        {{ $user->created_at->format('d M Y') }}
                                    </span>
                                </div>
                            @empty
                                <div class="py-8 text-center text-sm text-warm-sand">
                                    No users yet
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>

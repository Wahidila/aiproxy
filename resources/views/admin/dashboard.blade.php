<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ __('Admin Panel') }}
                </h2>
                <nav class="mt-1 text-sm text-gray-500">
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

            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                {{-- Total Users --}}
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50">
                                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Users</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalUsers) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Tokens Used --}}
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50">
                                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Tokens</p>
                                <p class="text-2xl font-bold text-gray-900">{{ adminFormatTokens($totalTokensUsed) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pending Donations --}}
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $pendingDonations > 0 ? 'bg-red-50' : 'bg-indigo-50' }}">
                                    <svg class="h-6 w-6 {{ $pendingDonations > 0 ? 'text-red-600' : 'text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Pending Donations</p>
                                <div class="flex items-center gap-2">
                                    <p class="text-2xl font-bold text-gray-900">{{ $pendingDonations }}</p>
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
                <div class="overflow-hidden rounded-lg bg-white shadow">
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
                                <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Active Users Today --}}
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50">
                                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Active Today</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($activeUsersToday) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:border-indigo-300 hover:shadow-md transition-all">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100">
                        <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Manage Users</p>
                        <p class="text-xs text-gray-500">View and manage all users</p>
                    </div>
                </a>

                <a href="{{ route('admin.donations.index') }}"
                   class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:border-indigo-300 hover:shadow-md transition-all">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $pendingDonations > 0 ? 'bg-red-100' : 'bg-indigo-100' }}">
                        <svg class="h-5 w-5 {{ $pendingDonations > 0 ? 'text-red-600' : 'text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Manage Donations</p>
                        <p class="text-xs text-gray-500">
                            @if($pendingDonations > 0)
                                <span class="text-red-600 font-medium">{{ $pendingDonations }} pending</span>
                            @else
                                Review and approve donations
                            @endif
                        </p>
                    </div>
                </a>

                <a href="{{ route('admin.model-pricing.index') }}"
                   class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:border-indigo-300 hover:shadow-md transition-all">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100">
                        <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Model Pricing</p>
                        <p class="text-xs text-gray-500">Configure per-model pricing & discounts</p>
                    </div>
                </a>

                <a href="{{ route('admin.settings.index') }}"
                   class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:border-indigo-300 hover:shadow-md transition-all">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100">
                        <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Settings</p>
                        <p class="text-xs text-gray-500">Configure site & wallet settings</p>
                    </div>
                </a>
            </div>

            {{-- Daily Stats Chart --}}
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Daily Stats (Last 7 Days)</h3>

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
                                    <span class="mb-1 text-xs font-medium text-gray-600">
                                        {{ adminFormatTokens($stat['tokens'] ?? 0) }}
                                    </span>
                                    <div class="w-full rounded-t-md bg-indigo-500 transition-all duration-300 hover:bg-indigo-600"
                                         style="height: {{ max($tokenPercent, 2) }}%;"
                                         title="{{ number_format($stat['tokens'] ?? 0) }} tokens / {{ number_format($stat['requests'] ?? 0) }} requests"></div>
                                    <span class="mt-2 text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($date)->format('d/m') }}
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        {{ number_format($stat['requests'] ?? 0) }} req
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex h-48 items-center justify-center text-gray-400">
                            <p>No usage data available</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Two Side-by-Side Sections --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                {{-- Pending Donations --}}
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="px-6 py-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Pending Donations</h3>
                            <a href="{{ route('admin.donations.index', ['status' => 'pending']) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                View All
                            </a>
                        </div>

                        <div class="space-y-3">
                            @forelse($recentDonations->take(5) as $donation)
                                <div class="flex items-center justify-between rounded-lg border border-gray-100 p-3 hover:bg-gray-50">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $donation->user->name }}</p>
                                        <p class="text-xs text-gray-500">
                                            Rp {{ number_format($donation->amount, 0, ',', '.') }}
                                            &middot;
                                            {{ $donation->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2 ml-3">
                                        <form method="POST" action="{{ route('admin.donations.approve', $donation) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center rounded-md bg-green-600 px-2.5 py-1.5 text-xs font-medium text-white hover:bg-green-700 transition-colors">
                                                Approve
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.donations.index') }}" class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 transition-colors">
                                            View
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center text-sm text-gray-400">
                                    No pending donations
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Recent Users --}}
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="px-6 py-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Users</h3>
                            <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                View All
                            </a>
                        </div>

                        <div class="space-y-3">
                            @forelse($recentUsers->take(5) as $user)
                                <div class="flex items-center justify-between rounded-lg border border-gray-100 p-3 hover:bg-gray-50">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-600">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                                            <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-400 whitespace-nowrap ml-3">
                                        {{ $user->created_at->format('d M Y') }}
                                    </span>
                                </div>
                            @empty
                                <div class="py-8 text-center text-sm text-gray-400">
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

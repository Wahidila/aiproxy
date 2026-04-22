<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-off-black tracking-heading">
                    {{ __('Manage Subscriptions') }}
                </h2>
                <nav class="mt-1 text-sm text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-fin-orange">Admin</a>
                    <span class="mx-1">/</span>
                    <span>Subscriptions</span>
                </nav>
            </div>
            <span class="inline-flex items-center rounded-md bg-orange-100 px-2.5 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/20">
                Admin
            </span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="rounded-card border border-green-200 bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="rounded-card border border-red-200 bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Stats Row --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {{-- Total --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="p-5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-fin-orange-light mb-3">
                            <i data-lucide="layers" class="h-5 w-5 text-fin-orange"></i>
                        </div>
                        <p class="text-xs font-medium text-muted uppercase tracking-wider">Total Subscriptions</p>
                        <p class="mt-1 text-2xl font-bold text-off-black">{{ number_format($stats['total'] ?? 0) }}</p>
                    </div>
                </div>

                {{-- Active --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="p-5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 mb-3">
                            <i data-lucide="check-circle" class="h-5 w-5 text-green-600"></i>
                        </div>
                        <p class="text-xs font-medium text-muted uppercase tracking-wider">Active</p>
                        <p class="mt-1 text-2xl font-bold text-green-600">{{ number_format($stats['active'] ?? 0) }}</p>
                    </div>
                </div>

                {{-- Pending --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="p-5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-100 mb-3">
                            <i data-lucide="clock" class="h-5 w-5 text-yellow-600"></i>
                        </div>
                        <p class="text-xs font-medium text-muted uppercase tracking-wider">Pending</p>
                        <p class="mt-1 text-2xl font-bold text-yellow-600">{{ number_format($stats['pending'] ?? 0) }}</p>
                    </div>
                </div>

                {{-- Expired --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="p-5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 mb-3">
                            <i data-lucide="x-circle" class="h-5 w-5 text-red-600"></i>
                        </div>
                        <p class="text-xs font-medium text-muted uppercase tracking-wider">Expired</p>
                        <p class="mt-1 text-2xl font-bold text-red-600">{{ number_format($stats['expired'] ?? 0) }}</p>
                    </div>
                </div>
            </div>

            {{-- Filter Bar --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-4">
                    <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                        {{-- Status Dropdown --}}
                        <div>
                            <select name="status"
                                    class="rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        {{-- Search Input --}}
                        <div class="flex-1">
                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Search by user name or email..."
                                   class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">
                        </div>

                        {{-- Filter Button --}}
                        <button type="submit"
                                class="inline-flex items-center rounded-btn bg-off-black px-4 py-2 text-sm font-medium text-white hover:bg-off-black/90 focus:outline-none focus:ring-2 focus:ring-fin-orange focus:ring-offset-2 transition-colors">
                            <i data-lucide="filter" class="mr-1.5 h-4 w-4"></i>
                            Filter
                        </button>

                        @if(request('status') || request('search'))
                            <a href="{{ route('admin.subscriptions.index') }}"
                               class="inline-flex items-center rounded-btn bg-canvas px-4 py-2 text-sm font-medium text-off-black hover:bg-oat transition-colors">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Subscriptions Table --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-oat">
                        <thead class="bg-canvas">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">User</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Plan</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Starts At</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Expires At</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-oat bg-surface">
                            @forelse($subscriptions as $subscription)
                                <tr class="hover:bg-canvas">
                                    {{-- User --}}
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-fin-orange-light text-xs font-semibold text-fin-orange">
                                                {{ strtoupper(substr($subscription->user->name ?? '?', 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-off-black">{{ $subscription->user->name ?? '-' }}</p>
                                                <p class="text-xs text-muted">{{ $subscription->user->email ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Plan --}}
                                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-off-black">
                                        {{ $subscription->plan->name ?? $subscription->plan_id }}
                                    </td>

                                    {{-- Status --}}
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        @switch($subscription->status)
                                            @case('pending')
                                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-700">
                                                    Pending
                                                </span>
                                                @break
                                            @case('active')
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                                    Active
                                                </span>
                                                @break
                                            @case('expired')
                                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                                    Expired
                                                </span>
                                                @break
                                            @case('cancelled')
                                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                                                    Cancelled
                                                </span>
                                                @break
                                            @default
                                                <span class="inline-flex items-center rounded-full bg-canvas px-2.5 py-0.5 text-xs font-medium text-muted">
                                                    {{ ucfirst($subscription->status) }}
                                                </span>
                                        @endswitch
                                    </td>

                                    {{-- Starts At --}}
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                        {{ $subscription->starts_at ? $subscription->starts_at->format('d M Y') : '-' }}
                                    </td>

                                    {{-- Expires At --}}
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                        {{ $subscription->expires_at ? $subscription->expires_at->format('d M Y') : '-' }}
                                    </td>

                                    {{-- Actions --}}
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        <a href="{{ route('admin.subscriptions.show', $subscription) }}"
                                           class="inline-flex items-center rounded-btn bg-fin-orange-light px-2.5 py-1.5 text-xs font-medium text-fin-orange hover:bg-fin-orange-light/80 transition-colors">
                                            <i data-lucide="eye" class="mr-1 h-3.5 w-3.5"></i>
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-muted">
                                        No subscriptions found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($subscriptions->hasPages())
                    <div class="border-t border-oat px-6 py-4">
                        {{ $subscriptions->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>

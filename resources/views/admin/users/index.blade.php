<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-off-black tracking-heading">
                    {{ __('Manage Users') }}
                </h2>
                <nav class="mt-1 text-sm text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-fin-orange">Admin</a>
                    <span class="mx-1">/</span>
                    <span>Users</span>
                </nav>
            </div>
            <span class="inline-flex items-center rounded-md bg-orange-100 px-2.5 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/20">
                Admin
            </span>
        </div>
    </x-slot>

    @php
        if (!function_exists('usersFormatTokens')) {
            function usersFormatTokens($count) {
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

            {{-- Search Form --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-4">
                    <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-3">
                        <div class="flex-1">
                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Search by name or email..."
                                   class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">
                        </div>
                        <button type="submit"
                                class="inline-flex items-center rounded-btn bg-off-black px-4 py-2 text-sm font-medium text-white hover:bg-off-black/90 focus:outline-none focus:ring-2 focus:ring-fin-orange focus:ring-offset-2 transition-colors">
                            <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Search
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.users.index') }}"
                               class="inline-flex items-center rounded-btn bg-canvas px-4 py-2 text-sm font-medium text-off-black hover:bg-oat transition-colors">
                                Clear
                            </a>
                        @endif
                        <a href="{{ route('admin.users.export') }}"
                           class="inline-flex items-center rounded-btn border border-oat bg-surface px-4 py-2 text-sm font-medium text-off-black hover:bg-canvas transition-colors">
                            <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export CSV
                        </a>
                    </form>
                </div>
            </div>

            {{-- Users Table --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-oat">
                        <thead class="bg-canvas">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Email</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Role</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">API Keys</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Total Requests</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Saldo</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Joined</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-oat bg-surface">
                            @forelse($users as $user)
                                <tr class="hover:bg-canvas">
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-fin-orange-light text-xs font-semibold text-fin-orange">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <span class="text-sm font-medium text-off-black">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                        {{ $user->email }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        @if($user->role === 'admin')
                                            <span class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-700">
                                                Admin
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-canvas px-2.5 py-0.5 text-xs font-medium text-off-black">
                                                User
                                            </span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-muted">
                                        {{ $user->api_keys_count }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-muted">
                                        {{ number_format($user->token_usages_count) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                        @if($user->tokenQuota)
                                            <span class="{{ $user->tokenQuota->balance <= 0 ? 'text-red-600 font-medium' : 'text-off-black font-medium' }}">
                                                {{ $user->tokenQuota->formatted_balance }}
                                            </span>
                                        @else
                                            <span class="text-warm-sand">-</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        @if($user->is_banned)
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                                Banned
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                                Active
                                            </span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                        {{ $user->created_at->format('d M Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        <a href="{{ route('admin.users.show', $user) }}"
                                           class="inline-flex items-center rounded-btn bg-fin-orange-light px-2.5 py-1.5 text-xs font-medium text-fin-orange hover:bg-fin-orange-light/80 transition-colors">
                                            <svg class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center text-sm text-warm-sand">
                                        No users found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($users->hasPages())
                    <div class="border-t border-oat px-6 py-4">
                        {{ $users->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>

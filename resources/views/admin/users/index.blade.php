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
            @if(session('warning'))
                <div class="rounded-card border border-yellow-200 bg-yellow-50 p-4">
                    <p class="text-sm font-medium text-yellow-800">{{ session('warning') }}</p>
                </div>
            @endif

            {{-- Search + Invite Form --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-4">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                        {{-- Search --}}
                        <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-3 flex-1">
                            <div class="flex-1">
                                <input type="text"
                                       name="search"
                                       value="{{ request('search') }}"
                                       placeholder="Search by name or email..."
                                       class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">
                            </div>
                            <button type="submit"
                                    class="inline-flex items-center rounded-btn bg-off-black px-4 py-2 text-sm font-medium text-white hover:bg-off-black/90 focus:outline-none focus:ring-2 focus:ring-fin-orange focus:ring-offset-2 btn-intercom transition-colors">
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
                        </form>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.users.export') }}"
                               class="inline-flex items-center rounded-btn border border-oat bg-surface px-4 py-2 text-sm font-medium text-off-black hover:bg-canvas btn-intercom transition-colors">
                                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Export
                            </a>
                            <button type="button"
                                    x-data @click="$dispatch('open-modal', 'invite-user')"
                                    class="inline-flex items-center rounded-btn bg-fin-orange px-4 py-2 text-sm font-medium text-white hover:bg-fin-orange-hover focus:outline-none focus:ring-2 focus:ring-fin-orange focus:ring-offset-2 btn-intercom transition-colors">
                                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                Invite User
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pending Invitations --}}
            @if(isset($pendingInvitations) && $pendingInvitations->count() > 0)
                <div class="bg-surface border border-oat rounded-card">
                    <div class="px-6 py-4">
                        <h3 class="text-sm font-semibold text-off-black mb-3 flex items-center gap-2">
                            <svg class="h-4 w-4 text-fin-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Undangan Pending ({{ $pendingInvitations->count() }})
                        </h3>
                        <div class="space-y-2">
                            @foreach($pendingInvitations as $invitation)
                                <div class="flex items-center justify-between rounded-btn border border-oat px-4 py-2.5 bg-canvas">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-yellow-100 text-xs font-semibold text-yellow-700">
                                            {{ strtoupper(substr($invitation->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="text-sm font-medium text-off-black">{{ $invitation->name }}</span>
                                            <span class="text-sm text-muted ml-2">{{ $invitation->email }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-muted">
                                            Expires {{ $invitation->expires_at->diffForHumans() }}
                                        </span>
                                        <form method="POST" action="{{ route('admin.users.invite.resend', $invitation) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center rounded-btn border border-oat bg-surface px-2.5 py-1 text-xs font-medium text-off-black hover:bg-canvas transition-colors">
                                                <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                                Resend
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

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
                                        <div class="inline-flex items-center gap-1.5">
                                            <a href="{{ route('admin.users.show', $user) }}"
                                               class="inline-flex items-center rounded-btn bg-fin-orange-light px-2.5 py-1.5 text-xs font-medium text-fin-orange hover:bg-fin-orange-light/80 transition-colors">
                                                <svg class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                View
                                            </a>
                                            @if($user->role !== 'admin')
                                                <form method="POST"
                                                      action="{{ route('admin.users.destroy', $user) }}"
                                                      onsubmit="return confirm('Hapus user {{ $user->name }} ({{ $user->email }}) beserta SEMUA datanya?\n\nAPI keys, transaksi, usage logs, donasi akan dihapus permanen.\n\nAksi ini TIDAK BISA dibatalkan!')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center rounded-btn bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-100 ring-1 ring-inset ring-red-200 transition-colors">
                                                        <svg class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
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

    {{-- Invite User Modal --}}
    <x-modal name="invite-user" :show="$errors->has('name') || $errors->has('email')" maxWidth="md">
        <form method="POST" action="{{ route('admin.users.invite') }}" class="p-6">
            @csrf

            <h2 class="text-lg font-semibold text-off-black tracking-sub mb-1">
                Invite User Baru
            </h2>
            <p class="text-sm text-muted mb-5">
                Kirim undangan via email. User akan menerima link untuk membuat password.
            </p>

            <div class="space-y-4">
                <div>
                    <x-input-label for="invite_name" value="Nama" />
                    <x-text-input id="invite_name" name="name" type="text" class="mt-1 block w-full" placeholder="Nama lengkap" :value="old('name')" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="invite_email" value="Email" />
                    <x-text-input id="invite_email" name="email" type="email" class="mt-1 block w-full" placeholder="email@example.com" :value="old('email')" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Batal
                </x-secondary-button>
                <button type="submit"
                        class="inline-flex items-center rounded-btn bg-fin-orange px-4 py-2 text-sm font-medium text-white hover:bg-fin-orange-hover focus:outline-none focus:ring-2 focus:ring-fin-orange focus:ring-offset-2 btn-intercom transition-colors">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Kirim Undangan
                </button>
            </div>
        </form>
    </x-modal>
</x-app-layout>

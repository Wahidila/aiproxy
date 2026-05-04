<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <h2 class="font-semibold text-xl text-off-black leading-tight tracking-heading">
                {{ __('User Subscriptions') }}
            </h2>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Admin</span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Breadcrumb --}}
            <nav class="text-sm text-muted">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-off-black">Admin</a>
                <span class="mx-2">/</span>
                <span class="text-off-black font-medium">User Subscriptions</span>
            </nav>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-green-800 text-sm font-medium">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-red-800 text-sm font-medium">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase tracking-wide">Aktif</p>
                    <p class="text-2xl font-semibold text-off-black mt-1">{{ number_format($stats['total_active']) }}</p>
                </div>
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase tracking-wide">Expired</p>
                    <p class="text-2xl font-semibold text-off-black mt-1">{{ number_format($stats['total_expired']) }}</p>
                </div>
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase tracking-wide">Cancelled</p>
                    <p class="text-2xl font-semibold text-off-black mt-1">{{ number_format($stats['total_cancelled']) }}</p>
                </div>
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase tracking-wide">Revenue (Aktif)</p>
                    <p class="text-2xl font-semibold text-off-black mt-1">Rp {{ number_format($stats['monthly_revenue'] + $stats['daily_revenue'], 0, ',', '.') }}</p>
                    <p class="text-xs text-muted mt-1">Bulanan: Rp {{ number_format($stats['monthly_revenue'], 0, ',', '.') }} · Harian: Rp {{ number_format($stats['daily_revenue'], 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Assign Subscription --}}
            <div class="bg-surface border border-oat rounded-card" x-data="{ showAssign: false }">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-off-black tracking-sub">Assign Subscription</h3>
                        <button type="button" @click="showAssign = !showAssign"
                            class="px-4 py-2 text-sm font-medium rounded-btn border border-oat hover:bg-canvas transition"
                            x-text="showAssign ? 'Batal' : 'Assign Manual'">
                        </button>
                    </div>

                    <div x-show="showAssign" x-transition class="mt-4">
                        <form action="{{ route('admin.subscriptions.assign') }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-off-black mb-1">User ID</label>
                                    <input type="number" name="user_id" required placeholder="ID user"
                                        class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                    <p class="text-xs text-muted mt-1">Masukkan ID user dari database</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-off-black mb-1">Plan</label>
                                    <select name="plan_slug" required class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                        @foreach($plans as $plan)
                                            <option value="{{ $plan->slug }}">{{ $plan->name }} ({{ $plan->formatted_price }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-off-black mb-1">Durasi (hari)</label>
                                    <input type="number" name="duration_days" min="1" placeholder="Default: 30 (bulanan) / 1 (harian)"
                                        class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                    <p class="text-xs text-muted mt-1">Kosongkan untuk default</p>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="px-5 py-2 bg-off-black text-white font-medium rounded-btn hover:bg-off-black/90 transition">
                                    Assign Plan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-surface border border-oat rounded-card p-4">
                <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-medium text-muted mb-1">Cari User</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Email atau nama..."
                            class="rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-muted mb-1">Status</label>
                        <select name="status" class="rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">
                            <option value="">Semua</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-muted mb-1">Plan</label>
                        <select name="plan" class="rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">
                            <option value="">Semua</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->slug }}" {{ request('plan') === $plan->slug ? 'selected' : '' }}>{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-off-black text-white text-sm font-medium rounded-btn hover:bg-off-black/90 transition">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'plan']))
                        <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2 text-sm font-medium text-muted hover:text-off-black transition">Reset</a>
                    @endif
                </form>
            </div>

            {{-- Subscriptions Table --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">
                        Daftar Subscription
                        <span class="text-sm font-normal text-muted">({{ $subscriptions->total() }} total)</span>
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-oat">
                            <thead class="bg-canvas">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted uppercase">User</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted uppercase">Plan</th>
                                    <th class="px-3 py-3 text-center text-xs font-medium text-muted uppercase">Status</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted uppercase">Mulai</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted uppercase">Berakhir</th>
                                    <th class="px-3 py-3 text-right text-xs font-medium text-muted uppercase">Request Hari Ini</th>
                                    <th class="px-3 py-3 text-right text-xs font-medium text-muted uppercase">Token Total</th>
                                    <th class="px-3 py-3 text-center text-xs font-medium text-muted uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-oat">
                                @forelse($subscriptions as $sub)
                                <tr class="bg-surface hover:bg-canvas">
                                    <td class="px-3 py-3">
                                        <div class="text-sm font-medium text-off-black">{{ $sub->user->name ?? 'N/A' }}</div>
                                        <div class="text-xs text-muted">{{ $sub->user->email ?? 'N/A' }}</div>
                                        <div class="text-xs text-muted font-mono">ID: {{ $sub->user_id }}</div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $sub->plan_slug === 'free' ? 'bg-gray-100 text-gray-800' : ($sub->plan_slug === 'premium' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800') }}">
                                            {{ $sub->plan->name ?? $sub->plan_slug }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        @if($sub->status === 'active')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                        @elseif($sub->status === 'expired')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Expired</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Cancelled</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-sm text-off-black">{{ $sub->starts_at ? $sub->starts_at->format('d M Y') : '—' }}</td>
                                    <td class="px-3 py-3 text-sm text-off-black">
                                        @if($sub->expires_at)
                                            {{ $sub->expires_at->format('d M Y') }}
                                            @if($sub->status === 'active' && $sub->expires_at->isPast())
                                                <span class="text-xs text-red-600 font-medium">(overdue)</span>
                                            @elseif($sub->status === 'active' && $sub->expires_at->diffInDays(now()) <= 3)
                                                <span class="text-xs text-yellow-600 font-medium">(segera)</span>
                                            @endif
                                        @else
                                            <span class="text-muted">∞ (selamanya)</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-sm text-off-black text-right">{{ number_format($sub->daily_requests_used ?? 0) }}</td>
                                    <td class="px-3 py-3 text-sm text-off-black text-right">{{ number_format($sub->token_usage_total ?? 0) }}</td>
                                    <td class="px-3 py-3 text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <a href="{{ route('admin.subscriptions.show', $sub->user_id) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-btn border border-oat hover:bg-canvas transition text-off-black">
                                                Detail
                                            </a>
                                            @if($sub->status === 'active' && $sub->plan_slug !== 'free')
                                                <form action="{{ route('admin.subscriptions.cancel', $sub) }}" method="POST" class="inline"
                                                    onsubmit="return confirm('Cancel subscription {{ $sub->user->email ?? '' }}?')">
                                                    @csrf
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Cancel</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-3 py-6 text-sm text-muted text-center">Tidak ada subscription ditemukan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($subscriptions->hasPages())
                        <div class="mt-4">
                            {{ $subscriptions->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-off-black tracking-heading">
                {{ __('Subscription') }}
            </h2>
            <nav class="mt-1 text-sm text-muted">
                <a href="{{ route('dashboard') }}" class="hover:text-fin-orange">Dashboard</a>
                <span class="mx-1">/</span>
                <span class="text-off-black font-medium">Subscription</span>
            </nav>
        </div>
    </x-slot>

    @php
        if (!function_exists('subFormatRp')) {
            function subFormatRp($v) { return 'Rp ' . number_format($v, 0, ',', '.'); }
        }
        if (!function_exists('subFormatTokens')) {
            function subFormatTokens($count) {
                if ($count >= 1000000) return number_format($count / 1000000, 1) . 'M';
                if ($count >= 1000) return number_format($count / 1000, 1) . 'K';
                return number_format($count);
            }
        }
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-card p-4">
                    <div class="flex items-center">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-500 mr-3 flex-shrink-0"></i>
                        <p class="text-green-800 text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-card p-4">
                    <div class="flex items-center">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 mr-3 flex-shrink-0"></i>
                        <p class="text-red-800 text-sm font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- ============================================================ --}}
            {{-- NO ACTIVE SUBSCRIPTION — Show Plans --}}
            {{-- ============================================================ --}}
            @if(!$subscription || $subscription->status === 'cancelled' || $subscription->status === 'expired')

                <div class="text-center mb-2">
                    <h3 class="text-2xl font-bold text-off-black">Pilih Paket Subscription</h3>
                    <p class="mt-1 text-sm text-muted">Akses AI API dengan harga terjangkau. Pilih paket yang sesuai kebutuhan Anda.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ min(count($plans), 3) }} gap-6">
                    @foreach($plans as $plan)
                        <div class="bg-surface border border-oat rounded-card p-6 flex flex-col relative
                            {{ $loop->last ? 'ring-2 ring-fin-orange' : '' }}">

                            @if($loop->last)
                                <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-fin-orange text-white">
                                        Popular
                                    </span>
                                </div>
                            @endif

                            <div class="mb-4">
                                <h4 class="text-lg font-bold text-off-black">{{ $plan->name }}</h4>
                                <div class="mt-2">
                                    <span class="text-3xl font-bold text-off-black">{{ subFormatRp($plan->price_idr) }}</span>
                                    <span class="text-sm text-muted">/bulan</span>
                                </div>
                            </div>

                            {{-- Features --}}
                            <ul class="space-y-2.5 mb-6 flex-1">
                                @if($plan->features)
                                    @foreach((is_array($plan->features) ? $plan->features : json_decode($plan->features, true)) ?? [] as $feature)
                                        <li class="flex items-start gap-2 text-sm text-off-black">
                                            <i data-lucide="check" class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0"></i>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>

                            {{-- Subscribe Form --}}
                            <div x-data="{ showForm: false }">
                                <button @click="showForm = !showForm"
                                    class="w-full px-4 py-2.5 text-sm font-semibold rounded-btn transition-colors
                                        {{ $loop->last
                                            ? 'bg-fin-orange text-white hover:bg-fin-orange-hover'
                                            : 'bg-off-black text-white hover:bg-off-black/90' }}">
                                    <span x-show="!showForm">Subscribe</span>
                                    <span x-show="showForm" x-cloak>Batal</span>
                                </button>

                                <form x-show="showForm" x-cloak x-transition
                                    method="POST" action="{{ route('subscriptions.subscribe') }}"
                                    class="mt-4 space-y-3">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                                    <div>
                                        <label class="block text-xs font-medium text-muted uppercase mb-1">Catatan (opsional)</label>
                                        <textarea name="notes" rows="2"
                                            placeholder="Catatan tambahan untuk admin..."
                                            class="w-full rounded-btn border-oat text-sm focus:border-fin-orange focus:ring-fin-orange"></textarea>
                                    </div>

                                    <button type="submit"
                                        class="w-full px-4 py-2 text-sm font-medium rounded-btn bg-green-600 text-white hover:bg-green-700 transition-colors">
                                        <i data-lucide="send" class="w-4 h-4 inline mr-1"></i>
                                        Kirim Permintaan Subscribe
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

            {{-- ============================================================ --}}
            {{-- PENDING SUBSCRIPTION --}}
            {{-- ============================================================ --}}
            @elseif($subscription->status === 'pending')

                <div class="bg-surface border border-oat rounded-card p-8 text-center max-w-lg mx-auto">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-100 mb-4">
                        <i data-lucide="clock" class="w-8 h-8 text-yellow-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-off-black">Menunggu Persetujuan</h3>
                    <p class="mt-2 text-sm text-muted">
                        Permintaan subscription <strong class="text-off-black">{{ $subscription->plan->name ?? 'N/A' }}</strong>
                        sedang menunggu verifikasi admin.
                    </p>
                    <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-full bg-yellow-50 border border-yellow-200">
                        <i data-lucide="calendar" class="w-4 h-4 text-yellow-600"></i>
                        <span class="text-sm text-yellow-800">Diajukan: {{ $subscription->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

            {{-- ============================================================ --}}
            {{-- ACTIVE SUBSCRIPTION --}}
            {{-- ============================================================ --}}
            @elseif($subscription->status === 'active')

                {{-- Status Card --}}
                <div class="bg-surface border border-oat rounded-card p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100">
                                <i data-lucide="crown" class="w-6 h-6 text-green-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-off-black">{{ $subscription->plan->name ?? 'Subscription' }}</h3>
                                <p class="text-sm text-muted">
                                    Berlaku hingga {{ $subscription->expires_at ? $subscription->expires_at->format('d M Y') : '-' }}
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center self-start px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5 mr-1"></i>
                            Active
                        </span>
                    </div>
                </div>

                {{-- Budget Card --}}
                @php
                    $budget = $subscription->plan->budget_usd_per_cycle ?? 0;
                    $spent = $cycleCost ?? 0;
                    $remaining = max(0, $budget - $spent);
                    $percentage = $budget > 0 ? min(100, round(($spent / $budget) * 100)) : 0;
                @endphp
                <div class="bg-surface border border-oat rounded-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-off-black tracking-sub">
                            <i data-lucide="wallet" class="w-5 h-5 inline mr-1 text-muted"></i>
                            Budget Siklus Ini
                        </h3>
                        <span class="text-xs text-muted">
                            Siklus: {{ $cycleStart ? \Carbon\Carbon::parse($cycleStart)->format('d M Y H:i') : '-' }} WIB
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div class="bg-canvas rounded-btn p-3 text-center">
                            <p class="text-xs font-medium text-muted uppercase">Budget</p>
                            <p class="mt-1 text-xl font-bold text-off-black">${{ number_format($budget, 2) }}</p>
                        </div>
                        <div class="bg-canvas rounded-btn p-3 text-center">
                            <p class="text-xs font-medium text-muted uppercase">Terpakai</p>
                            <p class="mt-1 text-xl font-bold {{ $percentage > 80 ? 'text-red-600' : 'text-fin-orange' }}">${{ number_format($spent, 4) }}</p>
                        </div>
                        <div class="bg-canvas rounded-btn p-3 text-center">
                            <p class="text-xs font-medium text-muted uppercase">Sisa</p>
                            <p class="mt-1 text-xl font-bold text-green-600">${{ number_format($remaining, 2) }}</p>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div>
                        <div class="flex items-center justify-between text-xs text-muted mb-1">
                            <span>Pemakaian</span>
                            <span>{{ $percentage }}%</span>
                        </div>
                        <div class="w-full bg-canvas rounded-full h-3 border border-oat">
                            <div class="h-full rounded-full transition-all duration-500
                                {{ $percentage > 90 ? 'bg-red-500' : ($percentage > 70 ? 'bg-yellow-500' : 'bg-fin-orange') }}"
                                style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                </div>

                {{-- ============================================================ --}}
                {{-- API KEYS SECTION --}}
                {{-- ============================================================ --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="px-6 py-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-off-black tracking-sub">
                                <i data-lucide="key" class="w-5 h-5 inline mr-1 text-muted"></i>
                                API Keys
                            </h3>
                        </div>

                        {{-- New Key Flash --}}
                        @if(session('new_key'))
                            <div class="rounded-lg border border-green-300 bg-green-50 p-4 mb-4" x-data="{ copied: false }">
                                <div class="flex items-start">
                                    <i data-lucide="check-circle" class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-green-800 mb-2">API Key berhasil dibuat!</p>
                                        <div class="flex items-center gap-2 mb-2">
                                            <code class="flex-1 rounded-btn border border-green-200 bg-white px-3 py-2 text-sm font-mono text-off-black break-all">{{ session('new_key') }}</code>
                                            <button @click="navigator.clipboard.writeText('{{ session('new_key') }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                                    class="inline-flex items-center rounded-btn bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                                                <span x-show="!copied">Copy</span>
                                                <span x-show="copied" x-cloak>Copied!</span>
                                            </button>
                                        </div>
                                        <p class="text-xs font-medium text-red-600">
                                            <i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i>
                                            Simpan API key ini! Tidak akan ditampilkan lagi.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Create New Key Form --}}
                        <div class="mb-5" x-data="{ showCreate: false }">
                            <button @click="showCreate = !showCreate"
                                class="inline-flex items-center rounded-btn bg-off-black px-4 py-2 text-sm font-medium text-white hover:bg-off-black/90 transition-colors">
                                <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i>
                                Buat API Key Baru
                            </button>

                            <form x-show="showCreate" x-cloak x-transition
                                method="POST" action="{{ route('subscriptions.api-keys.create') }}"
                                class="mt-3 p-4 bg-canvas rounded-btn border border-oat space-y-3">
                                @csrf
                                <div>
                                    <label for="key_name" class="block text-sm font-medium text-off-black mb-1">Nama Key</label>
                                    <input type="text" name="name" id="key_name" required
                                        placeholder="Contoh: Kilo Code, Cursor IDE"
                                        class="block w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange sm:text-sm"
                                        value="{{ old('name') }}">
                                    @error('name')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit"
                                    class="inline-flex items-center rounded-btn bg-fin-orange px-4 py-2 text-sm font-medium text-white hover:bg-fin-orange-hover transition-colors">
                                    <i data-lucide="key" class="w-4 h-4 mr-1.5"></i>
                                    Buat Key
                                </button>
                            </form>
                        </div>

                        {{-- Keys Table --}}
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-oat">
                                <thead class="bg-canvas">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase">Nama</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase">Key</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-muted uppercase">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase">Terakhir Digunakan</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-surface divide-y divide-oat">
                                    @forelse($subscription->apiKeys ?? [] as $apiKey)
                                        <tr class="hover:bg-canvas">
                                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-off-black">
                                                {{ $apiKey->name }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3">
                                                <code class="text-sm font-mono text-muted">{{ $apiKey->masked_key }}</code>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                                @if($apiKey->is_active)
                                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Active</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-canvas px-2.5 py-0.5 text-xs font-medium text-muted">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                                {{ $apiKey->last_used_at ? $apiKey->last_used_at->diffForHumans() : 'Belum pernah' }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    {{-- Toggle --}}
                                                    <form method="POST" action="{{ route('subscriptions.api-keys.toggle', $apiKey) }}">
                                                        @csrf
                                                        <button type="submit"
                                                            class="inline-flex items-center rounded-btn px-2.5 py-1.5 text-xs font-medium transition-colors
                                                                {{ $apiKey->is_active
                                                                    ? 'border border-yellow-300 bg-yellow-50 text-yellow-700 hover:bg-yellow-100'
                                                                    : 'border border-green-300 bg-green-50 text-green-700 hover:bg-green-100' }}">
                                                            <i data-lucide="{{ $apiKey->is_active ? 'pause' : 'play' }}" class="w-3.5 h-3.5 mr-1"></i>
                                                            {{ $apiKey->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                        </button>
                                                    </form>
                                                    {{-- Delete --}}
                                                    <form method="POST" action="{{ route('subscriptions.api-keys.delete', $apiKey) }}"
                                                          x-data @submit.prevent="if(confirm('Yakin ingin menghapus API key ini?')) $el.submit()">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="inline-flex items-center rounded-btn border border-red-300 bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 transition-colors">
                                                            <i data-lucide="trash-2" class="w-3.5 h-3.5 mr-1"></i>
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-8 text-center text-sm text-muted">
                                                <i data-lucide="key" class="w-8 h-8 mx-auto mb-2 text-muted/50"></i>
                                                Belum ada API key. Buat satu untuk mulai menggunakan layanan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ============================================================ --}}
                {{-- RECENT USAGE TABLE --}}
                {{-- ============================================================ --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="px-6 py-5">
                        <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">
                            <i data-lucide="activity" class="w-5 h-5 inline mr-1 text-muted"></i>
                            Penggunaan Terakhir
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-oat">
                                <thead class="bg-canvas">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase">Waktu</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase">Model</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase">Input Tokens</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase">Output Tokens</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase">Cost (USD)</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-muted uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-surface divide-y divide-oat">
                                    @forelse($recentUsages ?? [] as $usage)
                                        <tr class="hover:bg-canvas">
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                                {{ $usage->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-off-black">
                                                {{ $usage->model }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-muted text-right">
                                                {{ subFormatTokens($usage->input_tokens ?? 0) }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-muted text-right">
                                                {{ subFormatTokens($usage->output_tokens ?? 0) }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-off-black text-right">
                                                ${{ number_format($usage->cost_usd ?? 0, 6) }}
                                            </td>
                                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                                @if(($usage->status_code ?? 0) === 200)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $usage->status_code }}</span>
                                                @elseif(($usage->status_code ?? 0) >= 400)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ $usage->status_code }}</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-canvas text-muted">{{ $usage->status_code ?? '-' }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-sm text-muted">
                                                <i data-lucide="bar-chart-3" class="w-8 h-8 mx-auto mb-2 text-muted/50"></i>
                                                Belum ada data penggunaan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            @endif

        </div>
    </div>
</x-app-layout>

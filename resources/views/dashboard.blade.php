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

            {{-- Low Balance Banner --}}
            @if($quota->free_balance <= 0 && $quota->paid_balance <= 0)
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

            {{-- Wallet Balance Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Free Balance --}}
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase tracking-wide">Saldo Free Trial</p>
                    <p class="mt-2 text-3xl font-bold {{ $quota->free_balance > 0 ? 'text-green-600' : 'text-warm-sand' }}">
                        {{ $quota->formatted_free_balance }}
                    </p>
                    <p class="mt-1 text-xs text-warm-sand">Model terbatas (free tier only)</p>
                </div>
                {{-- Paid Balance --}}
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase tracking-wide">Saldo Top Up</p>
                    <p class="mt-2 text-3xl font-bold {{ $quota->paid_balance > 0 ? 'text-fin-orange' : 'text-warm-sand' }}">
                        {{ $quota->formatted_paid_balance }}
                    </p>
                    <p class="mt-1 text-xs text-warm-sand">Semua model (termasuk premium)</p>
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

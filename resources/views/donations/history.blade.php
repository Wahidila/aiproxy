<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-off-black leading-tight tracking-heading">
            {{ __('Riwayat Top Up') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {{-- Total Top Up --}}
                <div class="bg-surface border border-oat rounded-card p-5">
                    <dt class="text-xs font-medium text-muted uppercase tracking-wider">Total Top Up</dt>
                    <dd class="mt-2 text-2xl font-semibold text-off-black tracking-sub">
                        Rp {{ number_format($totalApproved, 0, ',', '.') }}
                    </dd>
                    <p class="mt-1 text-xs text-muted">Hanya transaksi disetujui</p>
                </div>

                {{-- Total Transaksi --}}
                <div class="bg-surface border border-oat rounded-card p-5">
                    <dt class="text-xs font-medium text-muted uppercase tracking-wider">Total Transaksi</dt>
                    <dd class="mt-2 text-2xl font-semibold text-off-black tracking-sub">
                        {{ $totalCount }}
                    </dd>
                    <p class="mt-1 text-xs text-muted">Semua transaksi</p>
                </div>

                {{-- Menunggu --}}
                <div class="bg-surface border border-oat rounded-card p-5">
                    <dt class="text-xs font-medium text-muted uppercase tracking-wider">Menunggu</dt>
                    <dd class="mt-2 text-2xl font-semibold tracking-sub {{ $pendingCount > 0 ? 'text-yellow-700' : 'text-off-black' }}">
                        {{ $pendingCount }}
                    </dd>
                    <p class="mt-1 text-xs text-muted">Transaksi pending</p>
                </div>
            </div>

            {{-- History Table --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-off-black tracking-sub">Riwayat Top Up</h3>
                        <a href="{{ route('donations.index') }}" class="inline-flex items-center rounded-btn bg-off-black px-3 py-1.5 text-xs font-medium text-white hover:opacity-80 transition-opacity">
                            &larr; Kembali ke Top Up
                        </a>
                    </div>

                    <div class="overflow-x-auto -mx-6">
                        <div class="inline-block min-w-full px-6 align-middle">
                            <table class="min-w-full divide-y divide-oat">
                                <thead class="bg-canvas">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">Tanggal</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase tracking-wider">Jumlah</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-muted uppercase tracking-wider">Metode</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-muted uppercase tracking-wider">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">Keterangan</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">Catatan Admin</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-surface divide-y divide-oat">
                                    @forelse($donations as $donation)
                                        <tr class="hover:bg-canvas transition-colors">
                                            {{-- Tanggal --}}
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                                {{ $donation->created_at->format('d/m/Y H:i') }}
                                            </td>

                                            {{-- Jumlah --}}
                                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-off-black text-right">
                                                {{ $donation->formatted_amount }}
                                            </td>

                                            {{-- Metode --}}
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-center">
                                                @if($donation->isPakasir())
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-btn text-xs font-medium text-white" style="background-color: #ff5600;">
                                                        Pakasir
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-btn text-xs font-medium bg-canvas text-off-black border border-oat">
                                                        Manual
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Status --}}
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-center">
                                                @if($donation->status === 'approved')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Disetujui
                                                    </span>
                                                @elseif($donation->status === 'pending')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        Menunggu
                                                    </span>
                                                @elseif($donation->status === 'rejected')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        Ditolak
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-canvas text-off-black">
                                                        {{ ucfirst($donation->status) }}
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Keterangan --}}
                                            <td class="px-4 py-3 text-sm text-muted max-w-xs">
                                                @if($donation->status === 'rejected')
                                                    <span class="text-red-700">Ditolak{{ $donation->admin_notes ? ' · ' . $donation->admin_notes : '' }}</span>
                                                @elseif($donation->isPakasir() && $donation->status === 'approved')
                                                    @php
                                                        $method = $donation->gateway_payment_method ?? 'Pakasir';
                                                        $completedAt = $donation->gateway_completed_at ? $donation->gateway_completed_at->format('d/m/Y H:i') : '-';
                                                    @endphp
                                                    Pembayaran otomatis via {{ $method }} · Order: {{ $donation->gateway_order_id }} · Selesai: {{ $completedAt }}
                                                @elseif($donation->isPakasir() && $donation->status === 'pending')
                                                    Menunggu pembayaran · Order: {{ $donation->gateway_order_id }}
                                                @elseif($donation->isManual() && $donation->status === 'approved')
                                                    Disetujui admin{{ $donation->admin_notes ? ' · ' . $donation->admin_notes : '' }}
                                                @elseif($donation->isManual() && $donation->status === 'pending')
                                                    Menunggu persetujuan admin
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            {{-- Catatan Admin --}}
                                            <td class="px-4 py-3 text-sm text-muted max-w-xs">
                                                @if($donation->admin_notes)
                                                    {{ $donation->admin_notes }}
                                                @else
                                                    <span class="text-muted/50">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-sm text-muted text-center">
                                                Belum ada riwayat top up.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Pagination --}}
                    @if($donations->hasPages())
                        <div class="mt-4">
                            {{ $donations->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-off-black leading-tight tracking-heading">
            {{ __('Riwayat Top Up') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-surface border border-oat rounded-card">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-off-black tracking-sub">Riwayat Top Up</h3>
                        <a href="{{ route('donations.index') }}" class="text-fin-orange hover:text-fin-orange/80 text-sm font-medium">
                            &larr; Kembali ke Top Up
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-oat">
                            <thead class="bg-canvas">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase">Tanggal</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-muted uppercase">Jumlah</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-muted uppercase">Metode</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-muted uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-surface divide-y divide-oat">
                                @forelse($donations as $donation)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-muted">{{ $donation->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-off-black text-right">{{ $donation->formatted_amount }}</td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            @if($donation->isPakasir())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Pakasir</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-canvas text-off-black">Manual</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            @if($donation->status === 'approved')
                                                @if($donation->isPakasir())
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Otomatis</span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Approved</span>
                                                @endif
                                            @elseif($donation->status === 'pending')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                                            @elseif($donation->status === 'rejected')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rejected</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-canvas text-off-black">{{ ucfirst($donation->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-muted">{{ $donation->admin_notes ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-3 text-sm text-muted text-center">Belum ada riwayat top up.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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

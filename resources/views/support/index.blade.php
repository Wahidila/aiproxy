<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-off-black leading-tight tracking-heading">
                {{ __('Customer Support') }}
            </h2>
            <a href="{{ route('support.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-off-black text-white text-sm font-semibold rounded-btn hover:bg-off-black/90 btn-intercom transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Buat Ticket Baru
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            {{-- Summary Stat Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase tracking-wide">Total Ticket</p>
                    <p class="mt-2 text-2xl font-bold text-off-black">{{ $totalCount }}</p>
                </div>
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase tracking-wide">Open</p>
                    <p class="mt-2 text-2xl font-bold {{ $openCount > 0 ? 'text-green-600' : 'text-warm-sand' }}">{{ $openCount }}</p>
                </div>
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase tracking-wide">Diproses</p>
                    <p class="mt-2 text-2xl font-bold {{ $inProgressCount > 0 ? 'text-fin-orange' : 'text-warm-sand' }}">{{ $inProgressCount }}</p>
                </div>
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase tracking-wide">Selesai</p>
                    <p class="mt-2 text-2xl font-bold {{ $closedCount > 0 ? 'text-off-black' : 'text-warm-sand' }}">{{ $closedCount }}</p>
                </div>
            </div>

            {{-- Status Filter --}}
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-sm text-muted">Filter:</span>
                <a href="{{ route('support.index') }}"
                   class="px-3 py-1.5 text-xs font-medium rounded-full border transition-all {{ !request('status') ? 'bg-off-black text-white border-off-black' : 'bg-surface border-oat text-muted hover:border-off-black hover:text-off-black' }}">
                    Semua
                </a>
                @foreach(\App\Models\SupportTicket::STATUS_LABELS as $value => $label)
                    <a href="{{ route('support.index', ['status' => $value]) }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-full border transition-all {{ request('status') === $value ? 'bg-off-black text-white border-off-black' : 'bg-surface border-oat text-muted hover:border-off-black hover:text-off-black' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Tickets List --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-oat">
                        <thead class="bg-canvas">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Ticket</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Kategori</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Balasan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-oat bg-surface">
                            @forelse($tickets as $ticket)
                                <tr class="hover:bg-canvas transition-colors">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('support.show', $ticket) }}" class="text-sm font-medium text-off-black hover:text-fin-orange transition-colors">
                                            {{ $ticket->subject }}
                                        </a>
                                        <p class="text-xs text-muted mt-0.5">#{{ $ticket->id }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $catColors = [
                                                'umum' => 'bg-gray-100 text-gray-700',
                                                'teknis' => 'bg-blue-100 text-blue-700',
                                                'pembayaran' => 'bg-green-100 text-green-700',
                                                'saran' => 'bg-purple-100 text-purple-700',
                                                'lainnya' => 'bg-yellow-100 text-yellow-700',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $catColors[$ticket->category] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ $ticket->category_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $statusColors = [
                                                'open' => 'bg-green-100 text-green-700',
                                                'in_progress' => 'bg-orange-100 text-orange-700',
                                                'closed' => 'bg-gray-100 text-gray-500',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-500' }}">
                                            @if($ticket->status === 'open')
                                                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                            @elseif($ticket->status === 'in_progress')
                                                <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>
                                            @endif
                                            {{ $ticket->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm text-muted">{{ $ticket->replies_count }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-sm text-off-black">{{ $ticket->created_at->format('d M Y') }}</p>
                                        <p class="text-xs text-muted">{{ $ticket->created_at->format('H:i') }}</p>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-12 h-12 rounded-full bg-canvas flex items-center justify-center">
                                                <i data-lucide="message-circle" class="w-6 h-6 text-warm-sand"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-muted">Belum ada ticket support</p>
                                                <p class="text-xs text-warm-sand mt-1">Buat ticket untuk menghubungi tim support kami</p>
                                            </div>
                                            <a href="{{ route('support.create') }}"
                                               class="inline-flex items-center gap-2 px-4 py-2 bg-off-black text-white text-sm font-semibold rounded-btn hover:bg-off-black/90 btn-intercom transition-colors mt-1">
                                                <i data-lucide="plus" class="w-4 h-4"></i>
                                                Buat Ticket Pertama
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            @if($tickets->hasPages())
                <div class="flex justify-center">
                    {{ $tickets->links() }}
                </div>
            @endif

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
    </script>
</x-app-layout>

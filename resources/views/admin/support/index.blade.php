<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-off-black tracking-heading">
                    {{ __('Support Tickets') }}
                </h2>
                <nav class="mt-1 text-sm text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-fin-orange">Admin</a>
                    <span class="mx-1">/</span>
                    <span>Support Tickets</span>
                </nav>
            </div>
            <div class="flex items-center gap-3">
                @if($openCount > 0)
                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                        {{ $openCount }} Open
                    </span>
                @endif
                @if($inProgressCount > 0)
                    <span class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-2.5 py-1 text-xs font-medium text-orange-700">
                        {{ $inProgressCount }} Diproses
                    </span>
                @endif
                <span class="inline-flex items-center rounded-md bg-orange-100 px-2.5 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/20">
                    Admin
                </span>
            </div>
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

            {{-- Filters --}}
            <div class="flex items-center gap-4 flex-wrap">
                {{-- Status Filter --}}
                <div class="flex items-center gap-2">
                    <span class="text-sm text-muted">Status:</span>
                    <a href="{{ route('admin.support.index', request()->only('category')) }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-full border transition-all {{ !request('status') ? 'bg-off-black text-white border-off-black' : 'bg-surface border-oat text-muted hover:border-off-black hover:text-off-black' }}">
                        Semua
                    </a>
                    @foreach(\App\Models\SupportTicket::STATUS_LABELS as $value => $label)
                        <a href="{{ route('admin.support.index', array_merge(request()->only('category'), ['status' => $value])) }}"
                           class="px-3 py-1.5 text-xs font-medium rounded-full border transition-all {{ request('status') === $value ? 'bg-off-black text-white border-off-black' : 'bg-surface border-oat text-muted hover:border-off-black hover:text-off-black' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <span class="text-oat">|</span>

                {{-- Category Filter --}}
                <div class="flex items-center gap-2">
                    <span class="text-sm text-muted">Kategori:</span>
                    <a href="{{ route('admin.support.index', request()->only('status')) }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-full border transition-all {{ !request('category') ? 'bg-off-black text-white border-off-black' : 'bg-surface border-oat text-muted hover:border-off-black hover:text-off-black' }}">
                        Semua
                    </a>
                    @foreach(\App\Models\SupportTicket::CATEGORY_LABELS as $value => $label)
                        <a href="{{ route('admin.support.index', array_merge(request()->only('status'), ['category' => $value])) }}"
                           class="px-3 py-1.5 text-xs font-medium rounded-full border transition-all {{ request('category') === $value ? 'bg-off-black text-white border-off-black' : 'bg-surface border-oat text-muted hover:border-off-black hover:text-off-black' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Tickets Table --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-oat">
                        <thead class="bg-canvas">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">User</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Subjek</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Kategori</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Balasan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Tanggal</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-oat bg-surface">
                            @forelse($tickets as $ticket)
                                <tr class="hover:bg-canvas transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="text-xs font-mono text-muted">#{{ $ticket->id }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <a href="{{ route('admin.users.show', $ticket->user) }}" class="text-sm font-medium text-off-black hover:text-fin-orange transition-colors">{{ $ticket->user->name }}</a>
                                        <p class="text-xs text-muted">{{ $ticket->user->email }}</p>
                                    </td>
                                    <td class="px-4 py-3 max-w-xs">
                                        <a href="{{ route('admin.support.show', $ticket) }}" class="text-sm font-medium text-off-black hover:text-fin-orange transition-colors">
                                            {{ Str::limit($ticket->subject, 50) }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
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
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
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
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <span class="text-sm text-muted">{{ $ticket->replies_count }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <p class="text-sm text-off-black">{{ $ticket->created_at->format('d M Y') }}</p>
                                        <p class="text-xs text-muted">{{ $ticket->created_at->format('H:i') }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <a href="{{ route('admin.support.show', $ticket) }}"
                                           class="inline-flex items-center rounded-btn bg-canvas border border-oat p-1.5 text-muted hover:text-fin-orange hover:border-fin-orange btn-intercom transition-colors"
                                           title="Lihat Detail">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <i data-lucide="inbox" class="w-10 h-10 text-warm-sand"></i>
                                            <p class="text-sm text-warm-sand">Belum ada ticket support.</p>
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

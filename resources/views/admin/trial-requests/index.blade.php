<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-off-black tracking-heading">
                    {{ __('Trial Requests') }}
                </h2>
                <nav class="mt-1 text-sm text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-fin-orange">Admin</a>
                    <span class="mx-1">/</span>
                    <span>Trial Requests</span>
                </nav>
            </div>
            <div class="flex items-center gap-3">
                @if($pendingCount > 0)
                    <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                        {{ $pendingCount }} Pending
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
            @if(session('warning'))
                <div class="rounded-card border border-yellow-200 bg-yellow-50 p-4">
                    <p class="text-sm font-medium text-yellow-800">{{ session('warning') }}</p>
                </div>
            @endif

            {{-- Filter Tabs --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.trial-requests.index') }}"
                           class="inline-flex items-center rounded-btn px-4 py-2 text-sm font-medium transition-colors {{ !$status ? 'bg-off-black text-white' : 'bg-canvas text-off-black hover:bg-oat' }}">
                            Semua
                        </a>
                        <a href="{{ route('admin.trial-requests.index', ['status' => 'pending']) }}"
                           class="inline-flex items-center gap-1.5 rounded-btn px-4 py-2 text-sm font-medium transition-colors {{ $status === 'pending' ? 'bg-off-black text-white' : 'bg-canvas text-off-black hover:bg-oat' }}">
                            Pending
                            @if($pendingCount > 0)
                                <span class="inline-flex items-center justify-center h-5 min-w-[20px] rounded-full {{ $status === 'pending' ? 'bg-white/20 text-white' : 'bg-red-100 text-red-700' }} text-xs font-semibold px-1.5">
                                    {{ $pendingCount }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('admin.trial-requests.index', ['status' => 'invited']) }}"
                           class="inline-flex items-center rounded-btn px-4 py-2 text-sm font-medium transition-colors {{ $status === 'invited' ? 'bg-off-black text-white' : 'bg-canvas text-off-black hover:bg-oat' }}">
                            Invited
                        </a>
                        <a href="{{ route('admin.trial-requests.index', ['status' => 'rejected']) }}"
                           class="inline-flex items-center rounded-btn px-4 py-2 text-sm font-medium transition-colors {{ $status === 'rejected' ? 'bg-off-black text-white' : 'bg-canvas text-off-black hover:bg-oat' }}">
                            Rejected
                        </a>
                    </div>
                </div>
            </div>

            {{-- Trial Requests Table --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-oat">
                        <thead class="bg-canvas">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Email</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Tanggal</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-oat bg-surface">
                            @forelse($trialRequests as $req)
                                <tr class="hover:bg-canvas">
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-fin-orange-light text-xs font-semibold text-fin-orange">
                                                {{ strtoupper(substr($req->name, 0, 1)) }}
                                            </div>
                                            <span class="text-sm font-medium text-off-black">{{ $req->name }}</span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                        {{ $req->email }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        @if($req->status === 'pending')
                                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-700">
                                                Pending
                                            </span>
                                        @elseif($req->status === 'invited')
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                                Invited
                                            </span>
                                        @elseif($req->status === 'rejected')
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                                Rejected
                                            </span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                        {{ $req->created_at->format('d M Y, H:i') }}
                                        <span class="text-xs text-warm-sand">({{ $req->created_at->diffForHumans() }})</span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        @if($req->isPending())
                                            <div class="flex items-center justify-center gap-2">
                                                <form method="POST" action="{{ route('admin.trial-requests.invite', $req) }}">
                                                    @csrf
                                                    <button type="submit"
                                                            class="inline-flex items-center rounded-btn bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700 transition-colors"
                                                            onclick="return confirm('Kirim undangan ke {{ $req->email }}?')">
                                                        <svg class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                        </svg>
                                                        Invite
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.trial-requests.reject', $req) }}">
                                                    @csrf
                                                    <button type="submit"
                                                            class="inline-flex items-center rounded-btn bg-canvas border border-oat px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 hover:border-red-200 transition-colors"
                                                            onclick="return confirm('Tolak permintaan dari {{ $req->name }}?')">
                                                        <svg class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                        Reject
                                                    </button>
                                                </form>
                                            </div>
                                        @elseif($req->isInvited())
                                            <span class="text-xs text-green-600 font-medium">Undangan terkirim</span>
                                        @elseif($req->isRejected())
                                            <span class="text-xs text-red-600 font-medium">Ditolak</span>
                                            @if($req->notes)
                                                <p class="text-xs text-muted mt-0.5">{{ $req->notes }}</p>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-warm-sand">
                                        @if($status)
                                            Tidak ada permintaan trial dengan status "{{ $status }}"
                                        @else
                                            Belum ada permintaan trial
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($trialRequests->hasPages())
                    <div class="border-t border-oat px-6 py-4">
                        {{ $trialRequests->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>

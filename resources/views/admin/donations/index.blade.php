<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-off-black tracking-heading">
                    {{ __('Manage Donations') }}
                    @if($pendingCount > 0)
                        <span class="ml-2 inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                            {{ $pendingCount }} Pending
                        </span>
                    @endif
                </h2>
                <nav class="mt-1 text-sm text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-fin-orange">Admin</a>
                    <span class="mx-1">/</span>
                    <span>Donations</span>
                </nav>
            </div>
            <span class="inline-flex items-center rounded-md bg-orange-100 px-2.5 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/20">
                Admin
            </span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="rounded-lg border border-green-300 bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-lg border border-red-300 bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Status Filter Tabs --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="border-b border-oat">
                    <nav class="flex -mb-px px-6" aria-label="Tabs">
                        @php
                            $currentStatus = request('status', 'all');
                            $tabs = [
                                'all' => 'All',
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'expired' => 'Expired',
                            ];
                        @endphp

                        @foreach($tabs as $key => $label)
                            <a href="{{ route('admin.donations.index', array_merge(request()->except('page', 'status'), $key !== 'all' ? ['status' => $key] : [])) }}"
                               class="whitespace-nowrap border-b-2 py-4 px-4 text-sm font-medium transition-colors
                                      {{ $currentStatus === $key
                                          ? 'border-fin-orange text-fin-orange'
                                          : 'border-transparent text-muted hover:border-oat hover:text-off-black' }}">
                                {{ $label }}
                                @if($key === 'pending' && $pendingCount > 0)
                                    <span class="ml-1 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-600">
                                        {{ $pendingCount }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </nav>
                </div>

                {{-- Donations Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-oat">
                        <thead class="bg-canvas">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">User</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Amount</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Proof</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Submitted</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-oat bg-surface">
                            @forelse($donations as $donation)
                                <tr class="hover:bg-canvas" x-data="{ showReject: false }">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-muted font-mono">
                                        #{{ $donation->id }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div>
                                            <p class="text-sm font-medium text-off-black">{{ $donation->user->name }}</p>
                                            <p class="text-xs text-muted">{{ $donation->user->email }}</p>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-off-black">
                                        Rp {{ number_format($donation->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        @if($donation->status === 'approved')
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                                Approved
                                            </span>
                                        @elseif($donation->status === 'pending')
                                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                                Pending
                                            </span>
                                        @elseif($donation->status === 'rejected')
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                                Rejected
                                            </span>
                                        @elseif($donation->status === 'expired')
                                            <span class="inline-flex items-center rounded-full bg-canvas px-2.5 py-0.5 text-xs font-medium text-muted">
                                                Expired
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-canvas px-2.5 py-0.5 text-xs font-medium text-off-black">
                                                {{ ucfirst($donation->status) }}
                                            </span>
                                        @endif
                                        @if($donation->approver)
                                            <p class="mt-0.5 text-xs text-warm-sand">by {{ $donation->approver->name }}</p>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        @if($donation->isPakasir())
                                            <div class="flex flex-col items-center gap-1">
                                                {{-- Badge Verified jika sudah approved --}}
                                                @if($donation->isApproved())
                                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                                        <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                        </svg>
                                                        Verified
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                                        Menunggu
                                                    </span>
                                                @endif
                                                {{-- Link ke halaman pembayaran Pakasir --}}
                                                <a href="{{ config('services.pakasir.base_url') }}/pay/{{ config('services.pakasir.slug') }}/{{ $donation->amount }}?order_id={{ $donation->gateway_order_id }}"
                                                   target="_blank"
                                                   class="inline-flex items-center rounded-btn px-2 py-1 text-xs font-medium transition-colors hover:opacity-80"
                                                   style="color: #ff5600;"
                                                   title="Lihat di Pakasir">
                                                    <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                    </svg>
                                                    Pakasir
                                                </a>
                                            </div>
                                        @elseif($donation->payment_proof)
                                            {{-- Manual: tampilkan bukti gambar --}}
                                            <a href="{{ route('admin.donations.proof', $donation) }}"
                                               target="_blank"
                                               class="inline-flex items-center rounded-btn bg-canvas px-2 py-1 text-xs font-medium text-fin-orange hover:bg-fin-orange-light transition-colors">
                                                <svg class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                Bukti
                                            </a>
                                        @else
                                            <span class="text-xs text-warm-sand">-</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                        <span title="{{ $donation->created_at->format('d M Y H:i:s') }}">
                                            {{ $donation->created_at->diffForHumans() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($donation->status === 'pending')
                                            <div class="flex flex-col items-center gap-2">
                                                <div class="flex items-center gap-2">
                                                    <form method="POST" action="{{ route('admin.donations.approve', $donation) }}">
                                                        @csrf
                                                        <button type="submit"
                                                                class="inline-flex items-center rounded-btn bg-green-600 px-2.5 py-1.5 text-xs font-medium text-white hover:bg-green-700 transition-colors"
                                                                onclick="return confirm('Approve this donation?')">
                                                            <svg class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                            Approve
                                                        </button>
                                                    </form>

                                                    <button type="button"
                                                            @click="showReject = !showReject"
                                                            class="inline-flex items-center rounded-btn bg-red-600 px-2.5 py-1.5 text-xs font-medium text-white hover:bg-red-700 transition-colors">
                                                        <svg class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                        Reject
                                                    </button>
                                                </div>

                                                {{-- Reject Form (Alpine.js toggle) --}}
                                                <div x-show="showReject"
                                                     x-transition:enter="transition ease-out duration-200"
                                                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                                                     x-transition:enter-end="opacity-100 transform translate-y-0"
                                                     x-transition:leave="transition ease-in duration-150"
                                                     x-transition:leave-start="opacity-100 transform translate-y-0"
                                                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                                                     class="w-full"
                                                     x-cloak>
                                                    <form method="POST" action="{{ route('admin.donations.reject', $donation) }}" class="mt-2">
                                                        @csrf
                                                        <textarea name="admin_notes"
                                                                  rows="2"
                                                                  required
                                                                  placeholder="Reason for rejection (required)..."
                                                                  class="w-full rounded-btn border-oat text-xs focus:border-red-500 focus:ring-red-500"></textarea>
                                                        <div class="mt-1 flex items-center gap-2">
                                                            <button type="submit"
                                                                    class="inline-flex items-center rounded-btn bg-red-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-red-700 transition-colors">
                                                                Confirm Reject
                                                            </button>
                                                            <button type="button"
                                                                    @click="showReject = false"
                                                                    class="inline-flex items-center rounded-btn bg-canvas px-2.5 py-1 text-xs font-medium text-muted hover:bg-oat transition-colors">
                                                                Cancel
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @else
                                            @if($donation->admin_notes)
                                                <p class="text-xs text-muted max-w-xs truncate" title="{{ $donation->admin_notes }}">
                                                    {{ $donation->admin_notes }}
                                                </p>
                                            @else
                                                <span class="text-xs text-warm-sand">-</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-sm text-warm-sand">
                                        No donations found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($donations->hasPages())
                    <div class="border-t border-oat px-6 py-4">
                        {{ $donations->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-app-layout>

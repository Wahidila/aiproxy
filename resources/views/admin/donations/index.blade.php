<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ __('Manage Donations') }}
                    @if($pendingCount > 0)
                        <span class="ml-2 inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                            {{ $pendingCount }} Pending
                        </span>
                    @endif
                </h2>
                <nav class="mt-1 text-sm text-gray-500">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-600">Admin</a>
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
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="border-b border-gray-200">
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
                                          ? 'border-indigo-500 text-indigo-600'
                                          : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
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
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">User</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Amount</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Proof</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Submitted</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($donations as $donation)
                                <tr class="hover:bg-gray-50" x-data="{ showReject: false }">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 font-mono">
                                        #{{ $donation->id }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $donation->user->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $donation->user->email }}</p>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-gray-900">
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
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                                                Expired
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                                {{ ucfirst($donation->status) }}
                                            </span>
                                        @endif
                                        @if($donation->approver)
                                            <p class="mt-0.5 text-xs text-gray-400">by {{ $donation->approver->name }}</p>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        @if($donation->payment_proof)
                                            <a href="{{ route('admin.donations.proof', $donation) }}"
                                               target="_blank"
                                               class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-50 transition-colors">
                                                <svg class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                View
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
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
                                                                class="inline-flex items-center rounded-md bg-green-600 px-2.5 py-1.5 text-xs font-medium text-white hover:bg-green-700 transition-colors"
                                                                onclick="return confirm('Approve this donation?')">
                                                            <svg class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                            Approve
                                                        </button>
                                                    </form>

                                                    <button type="button"
                                                            @click="showReject = !showReject"
                                                            class="inline-flex items-center rounded-md bg-red-600 px-2.5 py-1.5 text-xs font-medium text-white hover:bg-red-700 transition-colors">
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
                                                                  class="w-full rounded-md border-gray-300 text-xs shadow-sm focus:border-red-500 focus:ring-red-500"></textarea>
                                                        <div class="mt-1 flex items-center gap-2">
                                                            <button type="submit"
                                                                    class="inline-flex items-center rounded-md bg-red-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-red-700 transition-colors">
                                                                Confirm Reject
                                                            </button>
                                                            <button type="button"
                                                                    @click="showReject = false"
                                                                    class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 hover:bg-gray-200 transition-colors">
                                                                Cancel
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @else
                                            @if($donation->admin_notes)
                                                <p class="text-xs text-gray-500 max-w-xs truncate" title="{{ $donation->admin_notes }}">
                                                    {{ $donation->admin_notes }}
                                                </p>
                                            @else
                                                <span class="text-xs text-gray-400">-</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-400">
                                        No donations found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($donations->hasPages())
                    <div class="border-t border-gray-200 px-6 py-4">
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

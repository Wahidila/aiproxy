<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-off-black tracking-heading">
                    Bukti Pembayaran Pakasir
                </h2>
                <nav class="mt-1 text-sm text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-fin-orange">Admin</a>
                    <span class="mx-1">/</span>
                    <a href="{{ route('admin.donations.index') }}" class="hover:text-fin-orange">Donations</a>
                    <span class="mx-1">/</span>
                    <span>Proof #{{ $donation->id }}</span>
                </nav>
            </div>
            <a href="{{ route('admin.donations.index') }}"
               class="inline-flex items-center rounded-btn bg-off-black px-3 py-1.5 text-xs font-medium text-white hover:bg-off-black/80 transition-colors">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="bg-surface border border-oat rounded-card overflow-hidden">
                {{-- Header --}}
                <div class="border-b border-oat px-6 py-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-off-black tracking-sub">Webhook Data</h3>
                        <p class="text-xs text-muted mt-0.5">Donation #{{ $donation->id }} &middot; {{ $donation->user->name }}</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          style="background-color: #ff5600; color: #ffffff;">
                        Pakasir
                    </span>
                </div>

                {{-- Detail Card --}}
                <div class="p-6 space-y-4">
                    @php
                        $fields = [
                            'Order ID' => $proofData['order_id'] ?? '-',
                            'Amount' => isset($proofData['amount']) ? 'Rp ' . number_format((int)$proofData['amount'], 0, ',', '.') : '-',
                            'Status' => $proofData['status'] ?? '-',
                            'Payment Method' => $proofData['payment_method'] ?? '-',
                            'Completed At' => $proofData['completed_at'] ?? '-',
                            'Project' => $proofData['project'] ?? '-',
                        ];
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($fields as $label => $value)
                            <div class="bg-canvas rounded-card border border-oat p-4">
                                <dt class="text-xs font-medium text-muted uppercase tracking-wider">{{ $label }}</dt>
                                <dd class="mt-1 text-sm font-medium text-off-black">
                                    @if($label === 'Status')
                                        @if($value === 'completed')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $value }}</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ $value }}</span>
                                        @endif
                                    @else
                                        {{ $value }}
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </div>

                    {{-- Raw JSON --}}
                    <div class="mt-6">
                        <h4 class="text-xs font-medium text-muted uppercase tracking-wider mb-2">Raw JSON Payload</h4>
                        <div class="bg-off-black rounded-card p-4 overflow-x-auto">
                            <pre class="text-xs text-green-400 font-mono whitespace-pre-wrap">{{ json_encode($proofData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

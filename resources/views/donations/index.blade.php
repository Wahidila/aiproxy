<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-off-black leading-tight tracking-heading">
            {{ __('Top Up Saldo') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            {{-- Current Balances --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-surface border border-oat rounded-card p-5 text-center">
                    <p class="text-xs font-medium text-muted uppercase">Saldo Free Trial</p>
                    <p class="mt-2 text-3xl font-bold {{ $quota->free_balance > 0 ? 'text-green-600' : 'text-warm-sand' }}">
                        {{ $quota->formatted_free_balance }}
                    </p>
                </div>
                <div class="bg-surface border border-oat rounded-card p-5 text-center">
                    <p class="text-xs font-medium text-muted uppercase">Saldo Top Up</p>
                    <p class="mt-2 text-3xl font-bold {{ $quota->paid_balance > 0 ? 'text-fin-orange' : 'text-warm-sand' }}">
                        {{ $quota->formatted_paid_balance }}
                    </p>
                    <p class="mt-1 text-xs text-warm-sand">Top up masuk ke saldo ini</p>
                </div>
            </div>

            {{-- Free Trial Info --}}
            @if($quota->paid_balance <= 0)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-blue-800 text-sm">Saldo top up kosong. API key Paid tidak bisa digunakan. Top up untuk mendapatkan akses ke semua model AI premium.</p>
                    </div>
                </div>
            @endif

            {{-- Pending Donation --}}
            @if($pendingDonation)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-yellow-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="text-lg font-semibold text-yellow-800">Menunggu Persetujuan Admin</h3>
                            <p class="mt-1 text-yellow-700">Top up sebesar <strong>{{ $pendingDonation->formatted_amount }}</strong> sedang menunggu verifikasi.</p>
                            <p class="mt-1 text-sm text-yellow-600">Diajukan: {{ $pendingDonation->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            @else
                {{-- Top Up Form --}}
                <div class="bg-surface border border-oat rounded-card" x-data="{ amount: '' }">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">Form Top Up</h3>

                        <form action="{{ route('donations.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                            @csrf

                            {{-- Amount Input --}}
                            <div>
                                <label for="amount" class="block text-sm font-medium text-off-black mb-1">Nominal Top Up</label>
                                <input type="number" name="amount" id="amount" x-model="amount"
                                    min="{{ $minTopup }}" step="1000"
                                    placeholder="Nominal top up (min Rp {{ number_format($minTopup, 0, ',', '.') }})"
                                    class="w-full rounded-lg border-oat shadow-sm focus:border-fin-orange focus:ring-fin-orange"
                                    required>
                                @error('amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Quick Amount Buttons --}}
                            <div>
                                <p class="text-sm text-muted mb-2">Pilih nominal:</p>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" @click="amount = 20000" class="px-4 py-2 text-sm font-medium rounded-btn border border-oat hover:bg-canvas hover:border-fin-orange transition">
                                        Rp 20.000
                                    </button>
                                    <button type="button" @click="amount = 50000" class="px-4 py-2 text-sm font-medium rounded-btn border border-oat hover:bg-canvas hover:border-fin-orange transition">
                                        Rp 50.000
                                    </button>
                                    <button type="button" @click="amount = 100000" class="px-4 py-2 text-sm font-medium rounded-btn border border-oat hover:bg-canvas hover:border-fin-orange transition">
                                        Rp 100.000
                                    </button>
                                    <button type="button" @click="amount = 200000" class="px-4 py-2 text-sm font-medium rounded-btn border border-oat hover:bg-canvas hover:border-fin-orange transition">
                                        Rp 200.000
                                    </button>
                                </div>
                            </div>

                            {{-- QRIS Image --}}
                            <div>
                                <label class="block text-sm font-medium text-off-black mb-2">Scan QRIS untuk Pembayaran</label>
                                <div class="flex justify-center">
                                    @if($qrisImage)
                                        <img src="{{ Storage::url($qrisImage) }}" alt="QRIS" class="max-w-xs rounded-lg border border-oat shadow-sm">
                                    @else
                                        <div class="w-64 h-64 bg-canvas rounded-lg border-2 border-dashed border-oat flex items-center justify-center">
                                            <p class="text-warm-sand text-sm text-center">QRIS belum tersedia.<br>Hubungi admin.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Payment Proof Upload --}}
                            <div>
                                <label for="payment_proof" class="block text-sm font-medium text-off-black mb-1">Bukti Pembayaran</label>
                                <input type="file" name="payment_proof" id="payment_proof" accept="image/*"
                                    class="w-full text-sm text-muted file:mr-4 file:py-2 file:px-4 file:rounded-btn file:border-0 file:text-sm file:font-medium file:bg-canvas file:text-fin-orange hover:file:bg-oat/30"
                                    required>
                                @error('payment_proof')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Submit --}}
                            <div>
                                <button type="submit" class="w-full px-6 py-3 bg-off-black text-white font-semibold rounded-btn hover:bg-off-black/90 transition shadow-sm">
                                    Kirim Permintaan Top Up
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Link to History --}}
            <div class="text-center">
                <a href="{{ route('donations.history') }}" class="text-fin-orange hover:text-fin-orange/80 text-sm font-medium">
                    Lihat Riwayat Top Up &rarr;
                </a>
            </div>

        </div>
    </div>
</x-app-layout>

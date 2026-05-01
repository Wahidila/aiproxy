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

            @if(session('info'))
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-blue-800 text-sm font-medium">{{ session('info') }}</p>
                </div>
            @endif

            {{-- Current Balances --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-surface border border-oat rounded-card p-5 text-center">
                    <p class="text-xs font-medium text-muted uppercase">Saldo Wallet</p>
                    <p class="mt-2 text-3xl font-bold {{ $quota->total_balance > 0 ? 'text-green-600' : 'text-warm-sand' }}">
                        {{ $quota->formatted_balance }}
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

            {{-- Pending Pakasir Banner --}}
            @if($pendingPakasir)
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-orange-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="text-lg font-semibold text-orange-800">Menunggu Pembayaran</h3>
                            <p class="mt-1 text-orange-700">Pembayaran Pakasir sebesar <strong>{{ $pendingPakasir->formatted_amount }}</strong> sedang menunggu konfirmasi.</p>
                            <p class="mt-1 text-sm text-orange-600">Dibuat: {{ $pendingPakasir->created_at->format('d/m/Y H:i') }}</p>
                            <p class="mt-2 text-sm text-orange-600">Saldo akan otomatis ditambahkan setelah pembayaran dikonfirmasi.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Pending Manual Banner --}}
            @if($pendingManual)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-yellow-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="text-lg font-semibold text-yellow-800">Menunggu Persetujuan Admin</h3>
                            <p class="mt-1 text-yellow-700">Top up manual sebesar <strong>{{ $pendingManual->formatted_amount }}</strong> sedang menunggu verifikasi.</p>
                            <p class="mt-1 text-sm text-yellow-600">Diajukan: {{ $pendingManual->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Tab Navigation --}}
            @if(!$gatewayPakasirEnabled && !$gatewayManualEnabled)
                {{-- All gateways disabled --}}
                <div class="bg-surface border border-oat rounded-card p-8 text-center">
                    <svg class="mx-auto w-12 h-12 text-warm-sand mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-off-black mb-1">Top Up Tidak Tersedia</h3>
                    <p class="text-sm text-muted">Semua metode pembayaran sedang dinonaktifkan. Silakan hubungi admin untuk informasi lebih lanjut.</p>
                </div>
            @else
            <div x-data="{ activeTab: '{{ $gatewayPakasirEnabled ? 'pakasir' : 'manual' }}' }">
                <div class="flex border-b border-oat mb-0">
                    @if($gatewayPakasirEnabled)
                    <button
                        @click="activeTab = 'pakasir'"
                        :class="activeTab === 'pakasir'
                            ? 'border-b-2 border-fin-orange text-fin-orange'
                            : 'text-muted hover:text-off-black'"
                        class="px-5 py-3 text-sm font-semibold transition-colors focus:outline-none"
                    >
                        Bayar via Pakasir (Otomatis)
                    </button>
                    @endif
                    @if($gatewayManualEnabled)
                    <button
                        @click="activeTab = 'manual'"
                        :class="activeTab === 'manual'
                            ? 'border-b-2 border-fin-orange text-fin-orange'
                            : 'text-muted hover:text-off-black'"
                        class="px-5 py-3 text-sm font-semibold transition-colors focus:outline-none"
                    >
                        Upload Bukti Manual
                    </button>
                    @endif
                </div>

                {{-- Tab 1: Pakasir (Otomatis) --}}
                @if($gatewayPakasirEnabled)
                <div x-show="activeTab === 'pakasir'" x-cloak>
                    <div class="bg-surface border border-oat border-t-0 rounded-b-lg" x-data="{ pakasirAmount: '' }">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-off-black tracking-sub mb-2">Pembayaran Otomatis</h3>
                            <p class="text-sm text-muted mb-4">Bayar via QRIS dan saldo otomatis ditambahkan tanpa perlu menunggu admin.</p>

                            @if($pendingPakasir)
                                <p class="text-sm text-orange-600">Anda sudah memiliki pembayaran Pakasir yang sedang diproses. Silakan tunggu hingga selesai.</p>
                            @else
                                <form action="{{ route('donations.pakasir') }}" method="POST" class="space-y-5">
                                    @csrf

                                    {{-- Amount Input --}}
                                    <div>
                                        <label for="pakasir_amount" class="block text-sm font-medium text-off-black mb-1">Nominal Top Up</label>
                                        <input type="number" name="amount" id="pakasir_amount" x-model="pakasirAmount"
                                            min="{{ $minTopup }}" step="1000"
                                            placeholder="Nominal top up (min Rp {{ number_format($minTopup, 0, ',', '.') }})"
                                            class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange"
                                            required>
                                        @error('amount')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Quick Amount Buttons --}}
                                    <div>
                                        <p class="text-sm text-muted mb-2">Pilih nominal:</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" @click="pakasirAmount = 20000"
                                                class="px-4 py-2 text-sm font-medium rounded-btn border border-oat hover:bg-canvas hover:border-fin-orange transition-all hover:scale-110 active:scale-85">
                                                Rp 20.000
                                            </button>
                                            <button type="button" @click="pakasirAmount = 50000"
                                                class="px-4 py-2 text-sm font-medium rounded-btn border border-oat hover:bg-canvas hover:border-fin-orange transition-all hover:scale-110 active:scale-85">
                                                Rp 50.000
                                            </button>
                                            <button type="button" @click="pakasirAmount = 100000"
                                                class="px-4 py-2 text-sm font-medium rounded-btn border border-oat hover:bg-canvas hover:border-fin-orange transition-all hover:scale-110 active:scale-85">
                                                Rp 100.000
                                            </button>
                                            <button type="button" @click="pakasirAmount = 200000"
                                                class="px-4 py-2 text-sm font-medium rounded-btn border border-oat hover:bg-canvas hover:border-fin-orange transition-all hover:scale-110 active:scale-85">
                                                Rp 200.000
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Submit --}}
                                    <div>
                                        <button type="submit"
                                            class="w-full px-6 py-3 bg-fin-orange text-white font-semibold rounded-btn hover:scale-110 active:scale-85 transition-all">
                                            Bayar Sekarang
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                {{-- Tab 2: Manual Upload --}}
                @if($gatewayManualEnabled)
                <div x-show="activeTab === 'manual'" x-cloak>
                    <div class="bg-surface border border-oat border-t-0 rounded-b-lg" x-data="{ manualAmount: '' }">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-off-black tracking-sub mb-2">Upload Bukti Manual</h3>
                            <p class="text-sm text-muted mb-4">Scan QRIS, bayar, lalu upload bukti pembayaran. Admin akan memverifikasi secara manual.</p>

                            @if($pendingManual)
                                <p class="text-sm text-yellow-600">Anda sudah memiliki top up manual yang menunggu persetujuan. Silakan tunggu hingga diproses admin.</p>
                            @else
                                <form action="{{ route('donations.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                                    @csrf

                                    {{-- Amount Input --}}
                                    <div>
                                        <label for="manual_amount" class="block text-sm font-medium text-off-black mb-1">Nominal Top Up</label>
                                        <input type="number" name="amount" id="manual_amount" x-model="manualAmount"
                                            min="{{ $minTopup }}" step="1000"
                                            placeholder="Nominal top up (min Rp {{ number_format($minTopup, 0, ',', '.') }})"
                                            class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange"
                                            required>
                                        @error('amount')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Quick Amount Buttons --}}
                                    <div>
                                        <p class="text-sm text-muted mb-2">Pilih nominal:</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" @click="manualAmount = 20000"
                                                class="px-4 py-2 text-sm font-medium rounded-btn border border-oat hover:bg-canvas hover:border-fin-orange transition-all hover:scale-110 active:scale-85">
                                                Rp 20.000
                                            </button>
                                            <button type="button" @click="manualAmount = 50000"
                                                class="px-4 py-2 text-sm font-medium rounded-btn border border-oat hover:bg-canvas hover:border-fin-orange transition-all hover:scale-110 active:scale-85">
                                                Rp 50.000
                                            </button>
                                            <button type="button" @click="manualAmount = 100000"
                                                class="px-4 py-2 text-sm font-medium rounded-btn border border-oat hover:bg-canvas hover:border-fin-orange transition-all hover:scale-110 active:scale-85">
                                                Rp 100.000
                                            </button>
                                            <button type="button" @click="manualAmount = 200000"
                                                class="px-4 py-2 text-sm font-medium rounded-btn border border-oat hover:bg-canvas hover:border-fin-orange transition-all hover:scale-110 active:scale-85">
                                                Rp 200.000
                                            </button>
                                        </div>
                                    </div>

                                    {{-- QRIS Image --}}
                                    <div>
                                        <label class="block text-sm font-medium text-off-black mb-2">Scan QRIS untuk Pembayaran</label>
                                        <div class="flex justify-center">
                                            @if($qrisImage)
                                                <img src="{{ Storage::url($qrisImage) }}" alt="QRIS" class="max-w-xs rounded-card border border-oat">
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
                                        <button type="submit"
                                            class="w-full px-6 py-3 bg-off-black text-white font-semibold rounded-btn hover:scale-110 active:scale-85 transition-all">
                                            Kirim Permintaan Top Up
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
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

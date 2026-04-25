<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <h2 class="font-semibold text-xl text-off-black leading-tight tracking-heading">
                {{ __('Admin Settings') }}
            </h2>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Admin</span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-green-800 text-sm font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-red-800 text-sm font-medium">{{ session('error') }}</p>
                </div>
            @endif

            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Site Settings --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">Site Settings</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="site_name" class="block text-sm font-medium text-off-black mb-1">Site Name</label>
                                <input type="text" name="site_name" id="site_name"
                                    value="{{ old('site_name', $settings['site_name'] ?? '') }}"
                                    class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                @error('site_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="site_description" class="block text-sm font-medium text-off-black mb-1">Site Description</label>
                                <textarea name="site_description" id="site_description" rows="3"
                                    class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">{{ old('site_description', $settings['site_description'] ?? '') }}</textarea>
                                @error('site_description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- QRIS Settings --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">QRIS Settings</h3>
                        <div class="space-y-4">
                            @if(!empty($settings['qris_image']))
                                <div>
                                    <label class="block text-sm font-medium text-off-black mb-2">Current QRIS Image</label>
                                    <img src="{{ Storage::url($settings['qris_image']) }}" alt="Current QRIS" class="max-w-xs rounded-lg border border-oat">
                                </div>
                            @endif
                            <div>
                                <label for="qris_image" class="block text-sm font-medium text-off-black mb-1">Upload New QRIS Image</label>
                                <input type="file" name="qris_image" id="qris_image" accept="image/*"
                                    class="w-full text-sm text-muted file:mr-4 file:py-2 file:px-4 file:rounded-btn file:border-0 file:text-sm file:font-medium file:bg-fin-orange-light file:text-fin-orange hover:file:bg-fin-orange-light/80">
                                @error('qris_image')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Wallet Settings --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">Wallet Settings</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="usd_to_idr_rate" class="block text-sm font-medium text-off-black mb-1">USD to IDR Rate</label>
                                <input type="number" name="usd_to_idr_rate" id="usd_to_idr_rate" step="0.01"
                                    value="{{ old('usd_to_idr_rate', $settings['usd_to_idr_rate'] ?? 16000) }}"
                                    class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                @error('usd_to_idr_rate')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="free_credit_amount" class="block text-sm font-medium text-off-black mb-1">Free Credit Amount (IDR)</label>
                                <input type="number" name="free_credit_amount" id="free_credit_amount"
                                    value="{{ old('free_credit_amount', $settings['free_credit_amount'] ?? 10000) }}"
                                    class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                @error('free_credit_amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="min_topup_amount" class="block text-sm font-medium text-off-black mb-1">Min Top Up Amount (IDR)</label>
                                <input type="number" name="min_topup_amount" id="min_topup_amount"
                                    value="{{ old('min_topup_amount', $settings['min_topup_amount'] ?? 10000) }}"
                                    class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                @error('min_topup_amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payment Gateway Settings --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-off-black tracking-sub mb-2">Payment Gateway</h3>
                        <p class="text-sm text-muted mb-5">Aktifkan atau nonaktifkan metode pembayaran yang tersedia untuk user.</p>
                        <div class="space-y-4">
                            {{-- Pakasir Toggle --}}
                            <div class="flex items-center justify-between p-4 rounded-lg border border-oat bg-canvas">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-off-black">Pakasir (Otomatis)</p>
                                        <p class="text-xs text-muted">Pembayaran QRIS otomatis via Pakasir. Saldo langsung masuk tanpa approval admin.</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="gateway_pakasir_enabled" value="1"
                                        class="sr-only peer"
                                        {{ old('gateway_pakasir_enabled', $settings['gateway_pakasir_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-fin-orange/30 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-fin-orange"></div>
                                </label>
                            </div>

                            {{-- Manual Toggle --}}
                            <div class="flex items-center justify-between p-4 rounded-lg border border-oat bg-canvas">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-off-black">Transfer Manual</p>
                                        <p class="text-xs text-muted">User upload bukti transfer QRIS. Perlu approval admin untuk menambah saldo.</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="gateway_manual_enabled" value="1"
                                        class="sr-only peer"
                                        {{ old('gateway_manual_enabled', $settings['gateway_manual_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-fin-orange/30 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-fin-orange"></div>
                                </label>
                            </div>

                            {{-- Warning if both disabled --}}
                            <div class="rounded-lg border border-yellow-300 bg-yellow-50 p-3" x-data x-show="!document.querySelector('[name=gateway_pakasir_enabled]').checked && !document.querySelector('[name=gateway_manual_enabled]').checked" x-cloak>
                                <p class="text-xs text-yellow-800 font-medium">⚠️ Semua metode pembayaran dinonaktifkan. User tidak akan bisa melakukan top up.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Save Button --}}
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 bg-off-black text-white font-semibold rounded-btn hover:bg-off-black/90 transition">
                        Save Settings
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>

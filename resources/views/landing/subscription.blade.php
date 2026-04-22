<x-app-layout>
    @php
        $fallbackPlans = [
            (object) [
                'name' => 'Basic',
                'price' => 19900,
                'features' => ['10 RPM (requests per minute)', 'Budget $0.50/siklus', '1 API Key', 'Model: GPT-4o Mini, Claude Haiku', 'Email support'],
            ],
            (object) [
                'name' => 'Pro',
                'price' => 49900,
                'features' => ['30 RPM (requests per minute)', 'Budget $2.00/siklus', '5 API Keys', 'Semua model termasuk premium', 'Parallel requests', 'Priority support'],
            ],
        ];
        $displayPlans = isset($plans) && count($plans) > 0 ? $plans : $fallbackPlans;

        if (!function_exists('landingFormatRp')) {
            function landingFormatRp($v) { return 'Rp ' . number_format($v, 0, ',', '.'); }
        }
    @endphp

    {{-- Hero Section --}}
    <div class="bg-surface border-b border-oat">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 text-center">
            <div class="inline-flex items-center px-3 py-1 rounded-full bg-fin-orange-light text-fin-orange text-xs font-semibold mb-6">
                <i data-lucide="zap" class="w-3.5 h-3.5 mr-1.5"></i>
                AI API Subscription
            </div>
            <h1 class="text-4xl sm:text-5xl font-bold text-off-black leading-tight">
                Akses AI API<br class="hidden sm:block"> dengan Harga Terjangkau
            </h1>
            <p class="mt-4 text-lg text-muted max-w-2xl mx-auto">
                Gunakan model AI terbaik seperti GPT-4o, Claude, dan lainnya melalui satu API key.
                Bayar bulanan, tanpa biaya tersembunyi.
            </p>
        </div>
    </div>

    {{-- Pricing Cards --}}
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <div class="text-center mb-10">
            <h2 class="text-2xl sm:text-3xl font-bold text-off-black">Pilih Paket Anda</h2>
            <p class="mt-2 text-sm text-muted">Semua paket termasuk akses API, dashboard monitoring, dan budget control.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-3xl mx-auto">
            @foreach($displayPlans as $index => $plan)
                <div class="bg-surface border rounded-card p-8 flex flex-col relative
                    {{ $index === 1 ? 'border-fin-orange ring-2 ring-fin-orange' : 'border-oat' }}">

                    @if($index === 1)
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-fin-orange text-white">
                                <i data-lucide="star" class="w-3 h-3 mr-1"></i>
                                Recommended
                            </span>
                        </div>
                    @endif

                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-off-black">{{ $plan->name }}</h3>
                        <div class="mt-3">
                            <span class="text-4xl font-bold text-off-black">{{ landingFormatRp($plan->price) }}</span>
                            <span class="text-sm text-muted">/bulan</span>
                        </div>
                    </div>

                    {{-- Features --}}
                    <ul class="space-y-3 mb-8 flex-1">
                        @php
                            $features = is_array($plan->features ?? null)
                                ? $plan->features
                                : json_decode($plan->features ?? '[]', true) ?? [];
                        @endphp
                        @foreach($features as $feature)
                            <li class="flex items-start gap-2.5 text-sm text-off-black">
                                <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0"></i>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    {{-- CTA Button (disabled / coming soon) --}}
                    <button disabled
                        class="w-full px-4 py-3 text-sm font-semibold rounded-btn bg-gray-200 text-gray-500 cursor-not-allowed">
                        Coming Soon
                    </button>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Features Explanation --}}
    <div class="bg-canvas border-t border-oat">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
            <div class="text-center mb-10">
                <h2 class="text-2xl sm:text-3xl font-bold text-off-black">Bagaimana Cara Kerjanya?</h2>
                <p class="mt-2 text-sm text-muted">Fitur-fitur yang termasuk dalam setiap subscription.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- RPM --}}
                <div class="bg-surface border border-oat rounded-card p-6">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-fin-orange-light mb-4">
                        <i data-lucide="gauge" class="w-5 h-5 text-fin-orange"></i>
                    </div>
                    <h4 class="text-base font-semibold text-off-black mb-2">Rate Limit (RPM)</h4>
                    <p class="text-sm text-muted">
                        Setiap paket memiliki batas requests per menit (RPM). Basic mendapat 10 RPM, Pro mendapat 30 RPM.
                        Cukup untuk penggunaan coding assistant sehari-hari.
                    </p>
                </div>

                {{-- Parallel Requests --}}
                <div class="bg-surface border border-oat rounded-card p-6">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-fin-orange-light mb-4">
                        <i data-lucide="layers" class="w-5 h-5 text-fin-orange"></i>
                    </div>
                    <h4 class="text-base font-semibold text-off-black mb-2">Parallel Requests</h4>
                    <p class="text-sm text-muted">
                        Paket Pro mendukung parallel requests, memungkinkan Anda menjalankan beberapa request AI secara bersamaan
                        untuk workflow yang lebih cepat.
                    </p>
                </div>

                {{-- Budget Cycle --}}
                <div class="bg-surface border border-oat rounded-card p-6">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-fin-orange-light mb-4">
                        <i data-lucide="refresh-cw" class="w-5 h-5 text-fin-orange"></i>
                    </div>
                    <h4 class="text-base font-semibold text-off-black mb-2">Budget Cycle</h4>
                    <p class="text-sm text-muted">
                        Budget di-reset setiap siklus bulanan. Anda bisa memantau pemakaian real-time di dashboard.
                        Tidak ada biaya tambahan jika budget habis — request akan di-pause hingga siklus berikutnya.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- FAQ Section --}}
    <div class="bg-surface border-t border-oat">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
            <div class="text-center mb-10">
                <h2 class="text-2xl sm:text-3xl font-bold text-off-black">Pertanyaan Umum</h2>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                {{-- FAQ 1 --}}
                <div class="border border-oat rounded-card overflow-hidden">
                    <button @click="open = open === 1 ? null : 1"
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-canvas transition">
                        <span class="text-sm font-semibold text-off-black">Apa bedanya subscription dengan top-up saldo?</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-muted transition-transform" :class="open === 1 ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open === 1" x-cloak x-collapse>
                        <div class="px-6 pb-4 text-sm text-muted">
                            Subscription memberikan akses bulanan dengan budget tetap dan fitur tambahan seperti rate limit yang lebih tinggi.
                            Top-up saldo adalah sistem pay-as-you-go di mana Anda membeli kredit dan menggunakannya sampai habis.
                            Subscription cocok untuk penggunaan rutin, sementara top-up cocok untuk penggunaan sesekali.
                        </div>
                    </div>
                </div>

                {{-- FAQ 2 --}}
                <div class="border border-oat rounded-card overflow-hidden">
                    <button @click="open = open === 2 ? null : 2"
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-canvas transition">
                        <span class="text-sm font-semibold text-off-black">Apa yang terjadi jika budget siklus habis?</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-muted transition-transform" :class="open === 2 ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open === 2" x-cloak x-collapse>
                        <div class="px-6 pb-4 text-sm text-muted">
                            Jika budget siklus Anda habis, request API akan di-pause hingga siklus berikutnya dimulai.
                            Tidak ada biaya tambahan yang dikenakan. Anda bisa memantau pemakaian budget secara real-time di dashboard subscription.
                        </div>
                    </div>
                </div>

                {{-- FAQ 3 --}}
                <div class="border border-oat rounded-card overflow-hidden">
                    <button @click="open = open === 3 ? null : 3"
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-canvas transition">
                        <span class="text-sm font-semibold text-off-black">Model AI apa saja yang tersedia?</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-muted transition-transform" :class="open === 3 ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open === 3" x-cloak x-collapse>
                        <div class="px-6 pb-4 text-sm text-muted">
                            Paket Basic memberikan akses ke model seperti GPT-4o Mini dan Claude Haiku.
                            Paket Pro memberikan akses ke semua model termasuk GPT-4o, Claude Sonnet, dan model premium lainnya.
                            Daftar model terus diperbarui seiring rilis terbaru.
                        </div>
                    </div>
                </div>

                {{-- FAQ 4 --}}
                <div class="border border-oat rounded-card overflow-hidden">
                    <button @click="open = open === 4 ? null : 4"
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-canvas transition">
                        <span class="text-sm font-semibold text-off-black">Bagaimana cara menggunakan API key?</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-muted transition-transform" :class="open === 4 ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open === 4" x-cloak x-collapse>
                        <div class="px-6 pb-4 text-sm text-muted">
                            Setelah subscribe, Anda bisa membuat API key di dashboard. API key ini kompatibel dengan format OpenAI,
                            sehingga bisa digunakan di berbagai tool seperti Kilo Code, Cursor, Continue (VS Code), atau langsung via HTTP request.
                            Cukup masukkan Base URL dan API key di konfigurasi tool Anda.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CTA Section --}}
    <div class="bg-off-black">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 text-center">
            <i data-lucide="sparkles" class="w-8 h-8 text-fin-orange mx-auto mb-4"></i>
            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3">Tertarik dengan AI API Subscription?</h2>
            <p class="text-gray-400 text-sm mb-6 max-w-lg mx-auto">
                Daftar sekarang untuk mendapatkan notifikasi saat subscription diluncurkan.
                Atau mulai dengan top-up saldo untuk mencoba layanan kami.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('register') }}"
                    class="inline-flex items-center px-6 py-3 rounded-btn bg-fin-orange text-white font-semibold hover:bg-fin-orange-hover transition-colors">
                    <i data-lucide="user-plus" class="w-4 h-4 mr-2"></i>
                    Daftar Sekarang
                </a>
                <a href="{{ route('login') }}"
                    class="inline-flex items-center px-6 py-3 rounded-btn border border-gray-600 text-gray-300 font-semibold hover:bg-gray-800 transition-colors">
                    <i data-lucide="log-in" class="w-4 h-4 mr-2"></i>
                    Sudah Punya Akun? Login
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

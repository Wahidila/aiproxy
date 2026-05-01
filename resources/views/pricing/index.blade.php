<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pricing - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body class="font-sans antialiased text-off-black bg-canvas" x-data="{ tab: 'monthly' }" onload="lucide.createIcons()">

    {{-- Navigation Bar --}}
    <nav class="bg-surface border-b border-oat">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('home') }}" class="text-xl font-bold text-off-black" style="letter-spacing: -0.48px;">
                    {{ config('app.name') }}
                </a>
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="inline-flex items-center rounded-btn px-4 py-2 text-sm font-medium text-off-black border border-oat hover:bg-canvas transition-colors">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center rounded-btn px-4 py-2 text-sm font-medium text-muted hover:text-off-black transition-colors">
                            Login
                        </a>
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center rounded-btn px-4 py-2 text-sm font-medium text-white bg-off-black hover:scale-105 active:scale-95 transition-all"
                           style="border-radius: 4px;">
                            Daftar Gratis
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">

        {{-- Hero Section --}}
        <div class="text-center mb-12">
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-semibold text-off-black leading-tight"
                style="letter-spacing: -1.6px; line-height: 1.00;">
                Pilih Plan yang Tepat
            </h1>
            <p class="mt-4 text-lg text-muted max-w-2xl mx-auto" style="letter-spacing: -0.2px;">
                Akses AI models terbaik dengan harga terjangkau. Mulai gratis, upgrade kapan saja.
            </p>
        </div>

        {{-- Toggle Bulanan / Harian --}}
        <div class="flex items-center justify-center mb-10">
            <div class="inline-flex items-center rounded-card border border-oat bg-surface p-1">
                <button @click="tab = 'monthly'"
                        :class="tab === 'monthly' ? 'bg-off-black text-white' : 'text-muted hover:text-off-black'"
                        class="rounded-btn px-5 py-2 text-sm font-medium transition-all"
                        style="border-radius: 4px;">
                    Bulanan
                </button>
                <button @click="tab = 'daily'"
                        :class="tab === 'daily' ? 'bg-off-black text-white' : 'text-muted hover:text-off-black'"
                        class="rounded-btn px-5 py-2 text-sm font-medium transition-all"
                        style="border-radius: 4px;">
                    Harian
                </button>
            </div>
        </div>

        {{-- Monthly Plans --}}
        <div x-show="tab === 'monthly'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                @foreach($monthlyPlans as $plan)
                    <div class="relative bg-surface border rounded-card flex flex-col {{ $plan->is_popular ? 'border-2' : 'border-oat' }}"
                         style="{{ $plan->is_popular ? 'border-color: #ff5600;' : '' }}">

                        {{-- Popular Badge --}}
                        @if($plan->is_popular)
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                                <span class="inline-flex items-center rounded-btn px-3 py-1 text-xs font-bold text-white"
                                      style="background-color: #ff5600; border-radius: 4px;">
                                    POPULER
                                </span>
                            </div>
                        @endif

                        <div class="p-6 flex-1 flex flex-col">
                            {{-- Plan Name --}}
                            <div class="mb-4">
                                <h3 class="text-sm font-semibold uppercase tracking-wider text-muted">
                                    {{ $plan->name }}
                                </h3>
                            </div>

                            {{-- Price --}}
                            <div class="mb-6">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-4xl font-bold text-off-black" style="letter-spacing: -1.2px; line-height: 1.00;">
                                        {{ $plan->formatted_price }}
                                    </span>
                                    <span class="text-sm text-muted">/bulan</span>
                                </div>
                            </div>

                            {{-- CTA Button --}}
                            <div class="mb-6">
                                @if($plan->slug === 'free')
                                    <a href="{{ route('register') }}"
                                       class="w-full inline-flex items-center justify-center rounded-btn px-4 py-3 text-sm font-medium border border-off-black text-off-black hover:bg-off-black hover:text-white hover:scale-105 active:scale-95 transition-all"
                                       style="border-radius: 4px;">
                                        Mulai Gratis
                                    </a>
                                @else
                                    @auth
                                        <a href="{{ route('subscriptions.index') }}"
                                           class="w-full inline-flex items-center justify-center rounded-btn px-4 py-3 text-sm font-medium text-white hover:scale-105 active:scale-95 transition-all {{ $plan->is_popular ? '' : 'bg-off-black' }}"
                                           style="border-radius: 4px; {{ $plan->is_popular ? 'background-color: #ff5600;' : '' }}">
                                            Pilih {{ $plan->name }}
                                        </a>
                                    @else
                                        <a href="{{ route('register') }}"
                                           class="w-full inline-flex items-center justify-center rounded-btn px-4 py-3 text-sm font-medium text-white hover:scale-105 active:scale-95 transition-all {{ $plan->is_popular ? '' : 'bg-off-black' }}"
                                           style="border-radius: 4px; {{ $plan->is_popular ? 'background-color: #ff5600;' : '' }}">
                                            Pilih {{ $plan->name }}
                                        </a>
                                    @endauth
                                @endif
                            </div>

                            {{-- Features --}}
                            <div class="border-t border-oat pt-5 flex-1">
                                <p class="text-xs font-semibold uppercase tracking-wider text-muted mb-3">Termasuk:</p>
                                <ul class="space-y-2.5">
                                    @foreach($plan->features ?? [] as $feature)
                                        <li class="flex items-start gap-2.5">
                                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0 {{ $plan->is_popular ? 'text-fin-orange' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-sm text-off-black leading-snug">{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Daily Plans --}}
        <div x-show="tab === 'daily'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="max-w-md mx-auto">
                @foreach($dailyPlans as $plan)
                    <div class="bg-surface border border-oat rounded-card">
                        <div class="p-6">
                            {{-- Plan Name --}}
                            <div class="mb-4">
                                <h3 class="text-sm font-semibold uppercase tracking-wider text-muted">
                                    PAKET {{ $plan->name }}
                                </h3>
                            </div>

                            {{-- Price --}}
                            <div class="mb-6">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-4xl font-bold text-off-black" style="letter-spacing: -1.2px; line-height: 1.00;">
                                        {{ $plan->formatted_price }}
                                    </span>
                                    <span class="text-sm text-muted">/hari</span>
                                </div>
                                <p class="mt-2 text-sm text-muted">Berlaku 24 jam sejak aktivasi</p>
                            </div>

                            {{-- CTA Button --}}
                            <div class="mb-6">
                                @auth
                                    <a href="{{ route('subscriptions.index') }}"
                                       class="w-full inline-flex items-center justify-center rounded-btn px-4 py-3 text-sm font-medium text-white bg-off-black hover:scale-105 active:scale-95 transition-all"
                                       style="border-radius: 4px;">
                                        Beli Paket Harian
                                    </a>
                                @else
                                    <a href="{{ route('register') }}"
                                       class="w-full inline-flex items-center justify-center rounded-btn px-4 py-3 text-sm font-medium text-white bg-off-black hover:scale-105 active:scale-95 transition-all"
                                       style="border-radius: 4px;">
                                        Daftar & Beli
                                    </a>
                                @endauth
                            </div>

                            {{-- Features --}}
                            <div class="border-t border-oat pt-5">
                                <p class="text-xs font-semibold uppercase tracking-wider text-muted mb-3">Termasuk:</p>
                                <ul class="space-y-2.5">
                                    @foreach($plan->features ?? [] as $feature)
                                        <li class="flex items-start gap-2.5">
                                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-sm text-off-black leading-snug">{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Rate Limits Comparison Table --}}
        <div class="mt-16 max-w-5xl mx-auto">
            <h2 class="text-2xl font-semibold text-off-black text-center mb-8" style="letter-spacing: -0.48px; line-height: 1.00;">
                Perbandingan Limit
            </h2>

            <div class="bg-surface border border-oat rounded-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-oat">
                        <thead>
                            <tr class="bg-canvas">
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-muted">Fitur</th>
                                @foreach($monthlyPlans as $plan)
                                    <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider {{ $plan->is_popular ? 'text-fin-orange' : 'text-muted' }}">
                                        {{ $plan->name }}
                                    </th>
                                @endforeach
                                @foreach($dailyPlans as $plan)
                                    <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-muted">
                                        {{ strtoupper($plan->name) }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-oat">
                            <tr class="hover:bg-canvas">
                                <td class="px-6 py-4 text-sm font-medium text-off-black">Harga</td>
                                @foreach($monthlyPlans as $plan)
                                    <td class="px-6 py-4 text-center text-sm font-semibold text-off-black">{{ $plan->formatted_price }}/bln</td>
                                @endforeach
                                @foreach($dailyPlans as $plan)
                                    <td class="px-6 py-4 text-center text-sm font-semibold text-off-black">{{ $plan->formatted_price }}/hari</td>
                                @endforeach
                            </tr>
                            <tr class="hover:bg-canvas">
                                <td class="px-6 py-4 text-sm font-medium text-off-black">Request per Hari</td>
                                @foreach($monthlyPlans as $plan)
                                    <td class="px-6 py-4 text-center text-sm text-muted">
                                        {{ $plan->daily_request_limit ? number_format($plan->daily_request_limit) : 'Unlimited' }}
                                    </td>
                                @endforeach
                                @foreach($dailyPlans as $plan)
                                    <td class="px-6 py-4 text-center text-sm text-muted">
                                        {{ $plan->daily_request_limit ? number_format($plan->daily_request_limit) : 'Unlimited' }}
                                    </td>
                                @endforeach
                            </tr>
                            <tr class="hover:bg-canvas">
                                <td class="px-6 py-4 text-sm font-medium text-off-black">Request per Menit</td>
                                @foreach($monthlyPlans as $plan)
                                    <td class="px-6 py-4 text-center text-sm text-muted">{{ $plan->per_minute_limit }}</td>
                                @endforeach
                                @foreach($dailyPlans as $plan)
                                    <td class="px-6 py-4 text-center text-sm text-muted">{{ $plan->per_minute_limit }}</td>
                                @endforeach
                            </tr>
                            <tr class="hover:bg-canvas">
                                <td class="px-6 py-4 text-sm font-medium text-off-black">Request Bersamaan</td>
                                @foreach($monthlyPlans as $plan)
                                    <td class="px-6 py-4 text-center text-sm text-muted">{{ $plan->concurrent_limit }}</td>
                                @endforeach
                                @foreach($dailyPlans as $plan)
                                    <td class="px-6 py-4 text-center text-sm text-muted">{{ $plan->concurrent_limit }}</td>
                                @endforeach
                            </tr>
                            <tr class="hover:bg-canvas">
                                <td class="px-6 py-4 text-sm font-medium text-off-black">Max Token Usage</td>
                                @foreach($monthlyPlans as $plan)
                                    <td class="px-6 py-4 text-center text-sm text-muted">
                                        {{ $plan->max_token_usage ? number_format($plan->max_token_usage / 1000000) . 'M' : 'Unlimited' }}
                                    </td>
                                @endforeach
                                @foreach($dailyPlans as $plan)
                                    <td class="px-6 py-4 text-center text-sm text-muted">
                                        {{ $plan->max_token_usage ? number_format($plan->max_token_usage / 1000000) . 'M' : 'Unlimited' }}
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- FAQ Section --}}
        <div class="mt-16 max-w-3xl mx-auto">
            <h2 class="text-2xl font-semibold text-off-black text-center mb-8" style="letter-spacing: -0.48px; line-height: 1.00;">
                Pertanyaan Umum
            </h2>

            <div class="space-y-4">
                <div class="bg-surface border border-oat rounded-card" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <span class="text-sm font-medium text-off-black">Bagaimana cara upgrade plan?</span>
                        <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-muted transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak x-transition class="px-6 pb-4">
                        <p class="text-sm text-muted leading-relaxed">Login ke dashboard, lalu pilih plan yang diinginkan di halaman Subscription. Pembayaran menggunakan saldo wallet yang sudah ada.</p>
                    </div>
                </div>

                <div class="bg-surface border border-oat rounded-card" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <span class="text-sm font-medium text-off-black">Apakah saldo wallet tetap bisa digunakan?</span>
                        <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-muted transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak x-transition class="px-6 pb-4">
                        <p class="text-sm text-muted leading-relaxed">Ya! Sistem wallet (saldo) tetap berjalan seperti biasa. Subscription plan hanya menambahkan rate limiting dan akses model. Setiap request tetap dihitung biayanya dari saldo wallet.</p>
                    </div>
                </div>

                <div class="bg-surface border border-oat rounded-card" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <span class="text-sm font-medium text-off-black">Apa yang terjadi jika plan expired?</span>
                        <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-muted transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak x-transition class="px-6 pb-4">
                        <p class="text-sm text-muted leading-relaxed">Jika plan expired, Anda otomatis kembali ke plan FREE dengan limit standar. Saldo wallet Anda tetap aman dan tidak hilang.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Footer --}}
    <footer class="border-t border-oat bg-surface mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <p class="text-center text-sm text-muted">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </footer>

</body>
</html>

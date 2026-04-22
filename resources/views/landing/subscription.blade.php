<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AI API Subscription - AIMurah</title>
    <meta name="description" content="Akses AI API premium dengan harga terjangkau. Mulai dari Rp 19.900/bulan dengan budget harian dan rate limit yang fleksibel.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --color-canvas: #faf9f6;
            --color-text: #111111;
            --color-accent: #ff5600;
            --color-accent-hover: #e64d00;
            --color-border: #dedbd6;
            --color-muted: #7b7b78;
            --color-surface: #ffffff;
            --color-dark: #111111;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
            color: var(--color-text);
            background: var(--color-canvas);
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }
        /* Headings - negative tracking like Saans */
        .heading-display { font-size: clamp(48px, 8vw, 80px); font-weight: 400; line-height: 1.00; letter-spacing: -2.4px; }
        .heading-section { font-size: clamp(32px, 5vw, 54px); font-weight: 400; line-height: 1.00; letter-spacing: -1.6px; }
        .heading-sub { font-size: clamp(24px, 3.5vw, 40px); font-weight: 400; line-height: 1.00; letter-spacing: -1.2px; }
        .heading-card { font-size: clamp(20px, 2.5vw, 32px); font-weight: 400; line-height: 1.00; letter-spacing: -0.96px; }
        .heading-feature { font-size: 24px; font-weight: 400; line-height: 1.00; letter-spacing: -0.48px; }
        /* Body */
        .body-lg { font-size: 20px; line-height: 1.5; letter-spacing: -0.2px; color: var(--color-muted); }
        .body-md { font-size: 16px; line-height: 1.5; color: var(--color-muted); }
        .body-sm { font-size: 14px; line-height: 1.4; color: var(--color-muted); }
        /* Mono label */
        .mono-label { font-family: 'Inter', monospace; font-size: 12px; font-weight: 500; letter-spacing: 1.2px; text-transform: uppercase; color: var(--color-muted); }
        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 12px 24px; border-radius: 4px; font-size: 16px; font-weight: 500;
            text-decoration: none; transition: all 0.2s ease; cursor: pointer; border: none;
        }
        .btn:hover { transform: scale(1.05); }
        .btn:active { transform: scale(0.95); }
        .btn-primary { background: var(--color-dark); color: #fff; }
        .btn-primary:hover { background: #fff; color: var(--color-dark); box-shadow: inset 0 0 0 1px var(--color-dark); }
        .btn-accent { background: var(--color-accent); color: #fff; }
        .btn-accent:hover { background: var(--color-accent-hover); transform: scale(1.05); }
        .btn-outline { background: transparent; color: var(--color-dark); border: 1px solid var(--color-dark); }
        .btn-outline:hover { background: var(--color-dark); color: #fff; }
        .btn-disabled { background: #e5e5e3; color: #9c9fa5; cursor: not-allowed; }
        .btn-disabled:hover { transform: none; }
        .btn-disabled:active { transform: none; }
        /* Cards */
        .card {
            background: var(--color-canvas);
            border: 1px solid var(--color-border);
            border-radius: 8px;
            padding: 24px;
        }
        /* Layout */
        .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
        .section { padding: 96px 0; }
        .section-sm { padding: 64px 0; }
        /* Grid */
        .grid-2 { display: grid; grid-template-columns: 1fr; gap: 24px; }
        .grid-3 { display: grid; grid-template-columns: 1fr; gap: 24px; }
        @media (min-width: 768px) {
            .grid-2 { grid-template-columns: repeat(2, 1fr); }
            .grid-3 { grid-template-columns: repeat(3, 1fr); }
        }
        /* Nav */
        .nav { position: sticky; top: 0; z-index: 100; background: var(--color-surface); border-bottom: 1px solid var(--color-border); }
        .nav-inner { display: flex; align-items: center; justify-content: space-between; height: 64px; }
        .nav-brand { font-size: 20px; font-weight: 700; color: var(--color-dark); text-decoration: none; letter-spacing: -0.5px; display: flex; align-items: center; gap: 8px; }
        .nav-links { display: flex; align-items: center; gap: 16px; }
        .nav-link { font-size: 15px; color: var(--color-dark); text-decoration: none; font-weight: 400; }
        .nav-link:hover { color: var(--color-accent); }
        /* Pricing */
        .pricing-card { border-radius: 8px; padding: 40px 32px; border: 1px solid var(--color-border); background: var(--color-surface); }
        .pricing-card.featured { border-color: var(--color-accent); border-width: 2px; position: relative; }
        .pricing-amount { font-size: 48px; font-weight: 700; letter-spacing: -1.5px; line-height: 1; }
        .pricing-period { font-size: 16px; color: var(--color-muted); margin-left: 4px; }
        /* Check list */
        .check-list { list-style: none; padding: 0; margin: 0; }
        .check-list li { display: flex; align-items: flex-start; gap: 12px; padding: 8px 0; font-size: 15px; color: var(--color-text); }
        .check-icon { width: 20px; height: 20px; flex-shrink: 0; color: var(--color-accent); margin-top: 1px; }
        /* Icon box */
        .icon-box { width: 48px; height: 48px; margin: 0 auto 20px; background: #fff0e6; border-radius: 4px; display: flex; align-items: center; justify-content: center; }
        .icon-box i { width: 24px; height: 24px; color: var(--color-accent); }
        /* Badge */
        .badge-popular { position: absolute; top: -14px; left: 50%; transform: translateX(-50%); background: var(--color-accent); color: #fff; font-size: 12px; font-weight: 600; letter-spacing: 0.5px; padding: 5px 16px; border-radius: 4px; text-transform: uppercase; }
        /* FAQ */
        .faq-item { border: 1px solid var(--color-border); border-radius: 8px; overflow: hidden; }
        .faq-btn { width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; text-align: left; background: none; border: none; cursor: pointer; font-family: inherit; transition: background 0.15s; }
        .faq-btn:hover { background: var(--color-canvas); }
        .faq-title { font-size: 16px; font-weight: 500; color: var(--color-text); letter-spacing: -0.2px; }
        .faq-body { padding: 0 24px 20px; font-size: 15px; line-height: 1.6; color: var(--color-muted); }
        /* Footer */
        .footer { background: var(--color-dark); color: #9c9fa5; padding: 64px 0 32px; }
        .footer a { color: #9c9fa5; text-decoration: none; }
        .footer a:hover { color: #fff; }
        /* WhatsApp Bubble */
        .wa-bubble { position: fixed; bottom: 24px; right: 24px; z-index: 150; width: 56px; height: 56px; background: #25D366; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(37,211,102,0.4); cursor: pointer; transition: all 0.3s ease; text-decoration: none; }
        .wa-bubble:hover { transform: scale(1.1); box-shadow: 0 6px 20px rgba(37,211,102,0.5); }
        .wa-bubble:active { transform: scale(0.95); }
        .wa-bubble svg { width: 28px; height: 28px; fill: #fff; }
        .wa-tooltip { position: absolute; right: 68px; bottom: 50%; transform: translateY(50%); background: var(--color-surface); color: var(--color-text); font-size: 13px; font-weight: 500; padding: 8px 14px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.12); white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity 0.2s; border: 1px solid var(--color-border); }
        .wa-bubble:hover .wa-tooltip { opacity: 1; }
        /* Responsive */
        @media (max-width: 640px) {
            .section { padding: 64px 0; }
            .section-sm { padding: 48px 0; }
            .nav-links .nav-link { display: none; }
            .pricing-card { padding: 32px 24px; }
            .pricing-amount { font-size: 36px; }
            .wa-bubble { width: 48px; height: 48px; bottom: 16px; right: 16px; }
            .wa-bubble svg { width: 24px; height: 24px; }
            .wa-tooltip { display: none; }
        }
    </style>
</head>
<body onload="lucide.createIcons()" x-data="{ open: null }">

    @php
        $displayPlans = isset($plans) && count($plans) > 0 ? $plans : collect();
        $basicPlan = $displayPlans->firstWhere('slug', 'basic');
        $proPlan = $displayPlans->firstWhere('slug', 'pro');

        function subFormatRp($v) { return 'Rp ' . number_format($v, 0, ',', '.'); }
    @endphp

    <!-- Navigation -->
    <nav class="nav">
        <div class="container nav-inner">
            <a href="/" class="nav-brand">
                <i data-lucide="zap" style="width: 22px; height: 22px; color: var(--color-accent);"></i>
                AIMurah
            </a>
            <div class="nav-links">
                <a href="/" class="nav-link">Home</a>
                <a href="#pricing" class="nav-link">Pricing</a>
                <a href="#faq" class="nav-link">FAQ</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary" style="padding: 8px 16px; font-size: 14px;">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="nav-link">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary" style="padding: 8px 16px; font-size: 14px;">Daftar</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="section" style="padding-top: 80px; padding-bottom: 80px;">
        <div class="container" style="max-width: 900px; text-align: center;">
            <p class="mono-label" style="margin-bottom: 24px;">
                <span style="display: inline-block; width: 8px; height: 8px; background: var(--color-accent); border-radius: 50; margin-right: 8px;"></span>
                AI API SUBSCRIPTION
            </p>
            <h1 class="heading-display" style="margin: 0 0 24px;">
                AI API Premium<br>Bayar Bulanan
            </h1>
            <p class="body-lg" style="max-width: 640px; margin: 0 auto 40px;">
                Akses model AI terbaik — Claude, GPT, Gemini, DeepSeek — melalui satu API key dengan harga mulai Rp 19.900/bulan. Budget terkontrol, tanpa biaya tersembunyi.
            </p>
            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                <a href="#pricing" class="btn btn-accent" style="padding: 14px 32px; font-size: 16px; gap: 8px;">
                    <i data-lucide="tag" style="width: 20px; height: 20px;"></i>
                    Lihat Paket
                </a>
                <a href="/" class="btn btn-outline" style="gap: 8px;">
                    <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                    Pay-as-you-go
                </a>
            </div>
            <p class="body-sm" style="margin-top: 16px;">Coming Soon — Daftar sekarang untuk notifikasi peluncuran.</p>
        </div>
    </section>

    <!-- How It Works -->
    <section class="section-sm" style="background: var(--color-surface); border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border);">
        <div class="container">
            <div style="text-align: center; margin-bottom: 48px;">
                <p class="mono-label" style="margin-bottom: 12px;">CARA KERJA</p>
                <h2 class="heading-section">Subscription, bukan top-up</h2>
            </div>
            <div class="grid-3">
                <div class="card" style="text-align: center;">
                    <div class="icon-box"><i data-lucide="credit-card"></i></div>
                    <h3 class="heading-feature" style="margin: 0 0 12px;">Pilih Paket</h3>
                    <p class="body-md">Pilih paket Basic atau Pro sesuai kebutuhan. Bayar bulanan via transfer, langsung aktif setelah disetujui admin.</p>
                </div>
                <div class="card" style="text-align: center;">
                    <div class="icon-box"><i data-lucide="key-round"></i></div>
                    <h3 class="heading-feature" style="margin: 0 0 12px;">Dapat API Key</h3>
                    <p class="body-md">Setelah aktif, generate API key <code style="font-size: 13px; background: #f0f0ee; padding: 2px 6px; border-radius: 4px;">sk-sub-*</code> dari dashboard. Pasang di tool favorit Anda.</p>
                </div>
                <div class="card" style="text-align: center;">
                    <div class="icon-box"><i data-lucide="gauge"></i></div>
                    <h3 class="heading-feature" style="margin: 0 0 12px;">Budget Terkontrol</h3>
                    <p class="body-md">Budget di-reset setiap 6 jam. Tidak ada tagihan tambahan — request di-pause jika budget habis, lanjut di siklus berikutnya.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="section" style="background: var(--color-surface); border-bottom: 1px solid var(--color-border);">
        <div class="container">
            <div style="text-align: center; margin-bottom: 48px;">
                <p class="mono-label" style="margin-bottom: 12px;">HARGA</p>
                <h2 class="heading-section">Sederhana & Terjangkau</h2>
                <p class="body-lg" style="margin-top: 16px;">Bayar bulanan, budget di-reset setiap 6 jam. Tanpa biaya tersembunyi.</p>
            </div>
            <div class="grid-2" style="max-width: 800px; margin: 0 auto;">
                <!-- Basic -->
                <div class="pricing-card">
                    <p class="mono-label" style="margin-bottom: 16px;">BASIC</p>
                    <div class="pricing-amount" style="color: var(--color-text);">
                        {{ $basicPlan ? subFormatRp($basicPlan->price_idr) : 'Rp 19.900' }}
                        <span class="pricing-period">/bulan</span>
                    </div>
                    <p style="font-size: 15px; color: var(--color-muted); margin: 8px 0 32px;">Cocok untuk personal project dan belajar.</p>
                    <ul class="check-list">
                        <li>
                            <i data-lucide="circle-check" class="check-icon"></i>
                            <span><strong>30 RPM</strong> (requests per minute)</span>
                        </li>
                        <li>
                            <i data-lucide="circle-check" class="check-icon"></i>
                            <span><strong>3 parallel</strong> concurrent requests</span>
                        </li>
                        <li>
                            <i data-lucide="circle-check" class="check-icon"></i>
                            <span><strong>$5 budget</strong> per 6 jam (4x/hari)</span>
                        </li>
                        <li>
                            <i data-lucide="circle-check" class="check-icon"></i>
                            <span>Akses semua model AI</span>
                        </li>
                        <li>
                            <i data-lucide="circle-check" class="check-icon"></i>
                            <span>Dashboard monitoring real-time</span>
                        </li>
                        <li>
                            <i data-lucide="circle-check" class="check-icon"></i>
                            <span>OpenAI-compatible API</span>
                        </li>
                    </ul>
                    <button class="btn btn-disabled" style="width: 100%; margin-top: 32px; gap: 8px;" disabled>
                        <i data-lucide="clock" style="width: 18px; height: 18px;"></i>
                        Coming Soon
                    </button>
                </div>

                <!-- Pro -->
                <div class="pricing-card featured">
                    <span class="badge-popular">RECOMMENDED</span>
                    <p class="mono-label" style="margin-bottom: 16px; color: var(--color-accent);">PRO</p>
                    <div class="pricing-amount" style="color: var(--color-text);">
                        {{ $proPlan ? subFormatRp($proPlan->price_idr) : 'Rp 49.900' }}
                        <span class="pricing-period">/bulan</span>
                    </div>
                    <p style="font-size: 15px; color: var(--color-muted); margin: 8px 0 32px;">Untuk profesional yang butuh limit lebih tinggi.</p>
                    <ul class="check-list">
                        <li>
                            <i data-lucide="circle-check" class="check-icon"></i>
                            <span><strong>30 RPM</strong> (requests per minute)</span>
                        </li>
                        <li>
                            <i data-lucide="circle-check" class="check-icon"></i>
                            <span><strong>3 parallel</strong> concurrent requests</span>
                        </li>
                        <li>
                            <i data-lucide="circle-check" class="check-icon"></i>
                            <span><strong>$15 budget</strong> per 6 jam (4x/hari)</span>
                        </li>
                        <li>
                            <i data-lucide="circle-check" class="check-icon"></i>
                            <span>Akses semua model AI</span>
                        </li>
                        <li>
                            <i data-lucide="circle-check" class="check-icon"></i>
                            <span>Dashboard monitoring real-time</span>
                        </li>
                        <li>
                            <i data-lucide="circle-check" class="check-icon"></i>
                            <span>Priority support</span>
                        </li>
                    </ul>
                    <button class="btn btn-disabled" style="width: 100%; margin-top: 32px; gap: 8px;" disabled>
                        <i data-lucide="clock" style="width: 18px; height: 18px;"></i>
                        Coming Soon
                    </button>
                </div>
            </div>
            <p style="text-align: center; margin-top: 32px; color: var(--color-muted); font-size: 13px;">
                <i data-lucide="info" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; margin-right: 4px;"></i>
                Budget di-reset setiap 6 jam (00:00, 06:00, 12:00, 18:00 WIB). Tidak ada biaya tambahan jika budget habis.
            </p>
        </div>
    </section>

    <!-- Features Detail -->
    <section class="section">
        <div class="container">
            <div style="text-align: center; margin-bottom: 48px;">
                <p class="mono-label" style="margin-bottom: 12px;">FITUR</p>
                <h2 class="heading-section">Apa yang Anda dapatkan</h2>
            </div>
            <div class="grid-3">
                <div class="card" style="text-align: center;">
                    <div class="icon-box"><i data-lucide="gauge"></i></div>
                    <h3 class="heading-feature" style="margin: 0 0 12px;">Rate Limit (RPM)</h3>
                    <p class="body-md">30 requests per menit — cukup untuk coding assistant, chatbot, atau automation workflow sehari-hari.</p>
                </div>
                <div class="card" style="text-align: center;">
                    <div class="icon-box"><i data-lucide="layers"></i></div>
                    <h3 class="heading-feature" style="margin: 0 0 12px;">Parallel Requests</h3>
                    <p class="body-md">Jalankan hingga 3 request bersamaan. Cocok untuk multi-agent workflow atau batch processing.</p>
                </div>
                <div class="card" style="text-align: center;">
                    <div class="icon-box"><i data-lucide="refresh-cw"></i></div>
                    <h3 class="heading-feature" style="margin: 0 0 12px;">Budget Cycle 6 Jam</h3>
                    <p class="body-md">Budget di-reset 4x sehari. Tidak ada tagihan tambahan — request di-pause hingga siklus berikutnya jika budget habis.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Comparison -->
    <section class="section-sm" style="background: var(--color-surface); border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border);">
        <div class="container" style="max-width: 800px;">
            <div style="text-align: center; margin-bottom: 48px;">
                <p class="mono-label" style="margin-bottom: 12px;">PERBANDINGAN</p>
                <h2 class="heading-section">Subscription vs Top-up</h2>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 15px;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--color-border);">
                            <th style="text-align: left; padding: 12px 16px; font-weight: 500; color: var(--color-muted);">Fitur</th>
                            <th style="text-align: center; padding: 12px 16px; font-weight: 600; color: var(--color-accent);">Subscription</th>
                            <th style="text-align: center; padding: 12px 16px; font-weight: 500; color: var(--color-muted);">Top-up</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid var(--color-border);">
                            <td style="padding: 12px 16px;">Pembayaran</td>
                            <td style="padding: 12px 16px; text-align: center;">Bulanan tetap</td>
                            <td style="padding: 12px 16px; text-align: center;">Pay-as-you-go</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--color-border);">
                            <td style="padding: 12px 16px;">Budget control</td>
                            <td style="padding: 12px 16px; text-align: center; color: var(--color-accent); font-weight: 600;">Auto-reset 6 jam</td>
                            <td style="padding: 12px 16px; text-align: center;">Manual top-up</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--color-border);">
                            <td style="padding: 12px 16px;">Rate limit</td>
                            <td style="padding: 12px 16px; text-align: center; color: var(--color-accent); font-weight: 600;">30 RPM</td>
                            <td style="padding: 12px 16px; text-align: center;">Unlimited</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--color-border);">
                            <td style="padding: 12px 16px;">Parallel requests</td>
                            <td style="padding: 12px 16px; text-align: center; color: var(--color-accent); font-weight: 600;">3 concurrent</td>
                            <td style="padding: 12px 16px; text-align: center;">Unlimited</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--color-border);">
                            <td style="padding: 12px 16px;">Harga mulai</td>
                            <td style="padding: 12px 16px; text-align: center; font-weight: 600;">Rp 19.900/bln</td>
                            <td style="padding: 12px 16px; text-align: center;">Rp 10.000 min</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px 16px;">Cocok untuk</td>
                            <td style="padding: 12px 16px; text-align: center;">Penggunaan rutin</td>
                            <td style="padding: 12px 16px; text-align: center;">Penggunaan sesekali</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="section">
        <div class="container" style="max-width: 700px;">
            <div style="text-align: center; margin-bottom: 48px;">
                <p class="mono-label" style="margin-bottom: 12px;">FAQ</p>
                <h2 class="heading-section">Pertanyaan Umum</h2>
            </div>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <!-- FAQ 1 -->
                <div class="faq-item">
                    <button class="faq-btn" @click="open = open === 1 ? null : 1">
                        <span class="faq-title">Apa bedanya subscription dengan top-up saldo?</span>
                        <i data-lucide="chevron-down" style="width: 20px; height: 20px; color: var(--color-muted); transition: transform 0.2s;" :style="open === 1 ? 'transform: rotate(180deg)' : ''"></i>
                    </button>
                    <div x-show="open === 1" x-cloak x-collapse>
                        <div class="faq-body">
                            Subscription memberikan akses bulanan dengan budget tetap yang di-reset setiap 6 jam, plus fitur rate limit dan parallel requests. Top-up saldo adalah sistem pay-as-you-go di mana Anda membeli kredit dan menggunakannya sampai habis tanpa batasan rate. Subscription cocok untuk penggunaan rutin harian, sementara top-up cocok untuk penggunaan sesekali atau burst.
                        </div>
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="faq-item">
                    <button class="faq-btn" @click="open = open === 2 ? null : 2">
                        <span class="faq-title">Apa yang terjadi jika budget siklus habis?</span>
                        <i data-lucide="chevron-down" style="width: 20px; height: 20px; color: var(--color-muted); transition: transform 0.2s;" :style="open === 2 ? 'transform: rotate(180deg)' : ''"></i>
                    </button>
                    <div x-show="open === 2" x-cloak x-collapse>
                        <div class="faq-body">
                            Request API akan di-pause hingga siklus berikutnya dimulai (setiap 6 jam: 00:00, 06:00, 12:00, 18:00 WIB). Tidak ada biaya tambahan yang dikenakan. Anda bisa memantau pemakaian budget secara real-time di dashboard subscription.
                        </div>
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="faq-item">
                    <button class="faq-btn" @click="open = open === 3 ? null : 3">
                        <span class="faq-title">Model AI apa saja yang tersedia?</span>
                        <i data-lucide="chevron-down" style="width: 20px; height: 20px; color: var(--color-muted); transition: transform 0.2s;" :style="open === 3 ? 'transform: rotate(180deg)' : ''"></i>
                    </button>
                    <div x-show="open === 3" x-cloak x-collapse>
                        <div class="faq-body">
                            Kedua paket (Basic dan Pro) memberikan akses ke semua model yang tersedia, termasuk Claude Sonnet, GPT-4o, DeepSeek, Gemini, dan lainnya. Daftar model bisa dikonfigurasi oleh admin dan terus diperbarui seiring rilis terbaru dari provider.
                        </div>
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="faq-item">
                    <button class="faq-btn" @click="open = open === 4 ? null : 4">
                        <span class="faq-title">Bagaimana cara menggunakan API key subscription?</span>
                        <i data-lucide="chevron-down" style="width: 20px; height: 20px; color: var(--color-muted); transition: transform 0.2s;" :style="open === 4 ? 'transform: rotate(180deg)' : ''"></i>
                    </button>
                    <div x-show="open === 4" x-cloak x-collapse>
                        <div class="faq-body">
                            Setelah subscribe dan disetujui admin, Anda mendapat API key format <code style="font-size: 13px; background: #f0f0ee; padding: 2px 6px; border-radius: 4px;">sk-sub-*</code>. Gunakan Base URL <code style="font-size: 13px; background: #f0f0ee; padding: 2px 6px; border-radius: 4px;">/api/v2</code> (bukan /v1) di tool Anda — Cursor, Kilo Code, VS Code, atau HTTP client lainnya. Format API 100% kompatibel dengan OpenAI.
                        </div>
                    </div>
                </div>

                <!-- FAQ 5 -->
                <div class="faq-item">
                    <button class="faq-btn" @click="open = open === 5 ? null : 5">
                        <span class="faq-title">Bagaimana proses pembayaran dan aktivasi?</span>
                        <i data-lucide="chevron-down" style="width: 20px; height: 20px; color: var(--color-muted); transition: transform 0.2s;" :style="open === 5 ? 'transform: rotate(180deg)' : ''"></i>
                    </button>
                    <div x-show="open === 5" x-cloak x-collapse>
                        <div class="faq-body">
                            Pilih paket di dashboard, submit request subscription. Admin akan memverifikasi pembayaran dan mengaktifkan subscription Anda. Setelah aktif, API key otomatis di-generate dan subscription berlaku selama 30 hari. Perpanjangan dilakukan dengan cara yang sama.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="section-sm" style="background: var(--color-dark); text-align: center;">
        <div class="container">
            <h2 class="heading-sub" style="color: #fff; margin: 0 0 16px;">Tertarik dengan AI API Subscription?</h2>
            <p style="font-size: 18px; color: #9c9fa5; margin: 0 0 32px; max-width: 500px; margin-left: auto; margin-right: auto;">Daftar sekarang untuk mendapatkan notifikasi saat subscription diluncurkan. Atau mulai dengan top-up saldo.</p>
            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                @auth
                    <a href="{{ route('subscriptions.index') }}" class="btn btn-accent" style="padding: 14px 32px; font-size: 16px; gap: 8px;">
                        <i data-lucide="sparkles" style="width: 20px; height: 20px;"></i>
                        Ke Dashboard Subscription
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-accent" style="padding: 14px 32px; font-size: 16px; gap: 8px;">
                        <i data-lucide="user-plus" style="width: 20px; height: 20px;"></i>
                        Daftar Sekarang
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline" style="padding: 14px 24px; font-size: 16px; gap: 8px; border-color: #444; color: #ccc;">
                        <i data-lucide="log-in" style="width: 18px; height: 18px;"></i>
                        Login
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- WhatsApp Floating Bubble -->
    <a href="https://wa.me/6285111201722" target="_blank" rel="noopener noreferrer" class="wa-bubble" aria-label="Chat via WhatsApp">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        <span class="wa-tooltip">Chat via WhatsApp</span>
    </a>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 32px;">
                <div>
                    <p style="font-size: 18px; font-weight: 700; color: #fff; margin: 0 0 8px; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="zap" style="width: 20px; height: 20px; color: var(--color-accent);"></i>
                        AIMurah
                    </p>
                    <p style="font-size: 14px; margin: 0;">Akses AI Premium, Harga Terjangkau</p>
                </div>
                <div style="display: flex; gap: 24px; font-size: 14px;">
                    <a href="/">Home</a>
                    <a href="{{ route('login') }}">Login</a>
                    <a href="{{ route('register') }}">Register</a>
                    <a href="#pricing">Pricing</a>
                </div>
            </div>
            <div style="margin-top: 48px; padding-top: 24px; border-top: 1px solid #2a2a2a; text-align: center; font-size: 13px;">
                &copy; {{ date('Y') }} AIMurah. All rights reserved.
            </div>
        </div>
    </footer>

</body>
</html>

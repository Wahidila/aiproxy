<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AI Murah') }} - Akses AI Premium, Harga Terjangkau</title>
    <meta name="description" content="Akses model AI terbaik dunia - Claude Opus 4.6, GPT-5, Gemini Pro - langsung dari Cursor, VS Code, atau tool favorit Anda. Mulai gratis Rp 100.000 credit.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
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
        /* Cards */
        .card {
            background: var(--color-canvas);
            border: 1px solid var(--color-border);
            border-radius: 8px;
            padding: 24px;
        }
        .card-white {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: 8px;
        }
        /* Layout */
        .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
        .section { padding: 96px 0; }
        .section-sm { padding: 64px 0; }
        /* Grid */
        .grid-2 { display: grid; grid-template-columns: 1fr; gap: 24px; }
        .grid-3 { display: grid; grid-template-columns: 1fr; gap: 24px; }
        .grid-4 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        @media (min-width: 768px) {
            .grid-2 { grid-template-columns: repeat(2, 1fr); }
            .grid-3 { grid-template-columns: repeat(3, 1fr); }
            .grid-4 { grid-template-columns: repeat(4, 1fr); }
        }
        /* Accent dot */
        .accent-dot { display: inline-block; width: 8px; height: 8px; background: var(--color-accent); border-radius: 50%; }
        /* Nav */
        .nav { position: sticky; top: 0; z-index: 100; background: var(--color-surface); border-bottom: 1px solid var(--color-border); }
        .nav-inner { display: flex; align-items: center; justify-content: space-between; height: 64px; }
        .nav-brand { font-size: 20px; font-weight: 700; color: var(--color-dark); text-decoration: none; letter-spacing: -0.5px; }
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
        /* Code block */
        .code-block { background: #1a1a1a; border-radius: 8px; padding: 24px; font-family: 'JetBrains Mono', 'Fira Code', monospace; font-size: 14px; line-height: 1.6; overflow-x: auto; }
        .code-comment { color: #6b7280; }
        .code-key { color: #a5d6ff; }
        .code-value { color: #7ee787; }
        /* Tool badge */
        .tool-badge { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 4px; padding: 12px 16px; font-size: 14px; font-weight: 500; text-align: center; color: var(--color-text); }
        /* Model card */
        .model-card { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 8px; padding: 16px; }
        .model-tier { font-size: 11px; font-weight: 600; letter-spacing: 0.8px; text-transform: uppercase; padding: 3px 8px; border-radius: 4px; }
        .model-tier-max { background: #fff0e6; color: var(--color-accent); }
        .model-tier-std { background: #f0f0ee; color: var(--color-muted); }
        /* Footer */
        .footer { background: var(--color-dark); color: #9c9fa5; padding: 64px 0 32px; }
        .footer a { color: #9c9fa5; text-decoration: none; }
        .footer a:hover { color: #fff; }
        /* Badge */
        .badge-popular { position: absolute; top: -14px; left: 50%; transform: translateX(-50%); background: var(--color-accent); color: #fff; font-size: 12px; font-weight: 600; letter-spacing: 0.5px; padding: 5px 16px; border-radius: 4px; text-transform: uppercase; }
        /* Responsive */
        @media (max-width: 640px) {
            .section { padding: 64px 0; }
            .nav-links .nav-link { display: none; }
            .hero-buttons { flex-direction: column; }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="nav">
        <div class="container nav-inner">
            <a href="/" class="nav-brand">
                <span class="accent-dot"></span> aimurah
            </a>
            <div class="nav-links">
                @auth
                    <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="nav-link">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary" style="padding: 8px 16px; font-size: 14px;">Daftar Gratis</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="section" style="padding-top: 80px; padding-bottom: 80px;">
        <div class="container" style="max-width: 900px; text-align: center;">
            <p class="mono-label" style="margin-bottom: 24px;">AI PROXY TERMURAH DI INDONESIA</p>
            <h1 class="heading-display" style="margin: 0 0 24px;">
                Akses AI Premium<br>Harga Terjangkau
            </h1>
            <p class="body-lg" style="max-width: 600px; margin: 0 auto 40px;">
                Gunakan Claude Opus 4.6, GPT-5, Gemini Pro, dan 26+ model AI lainnya langsung dari Cursor, VS Code, atau tool favorit Anda.
            </p>
            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;" class="hero-buttons">
                <a href="{{ route('register') }}" class="btn btn-accent" style="padding: 14px 32px; font-size: 16px;">
                    Mulai Gratis &mdash; Rp 100K Credit
                </a>
                <a href="#pricing" class="btn btn-outline">
                    Lihat Harga
                </a>
            </div>
            <p class="body-sm" style="margin-top: 16px; color: var(--color-muted);">Tidak perlu kartu kredit. Langsung pakai.</p>
        </div>
    </section>

    <!-- How It Works -->
    <section class="section-sm" style="background: var(--color-surface); border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border);">
        <div class="container">
            <div style="text-align: center; margin-bottom: 48px;">
                <p class="mono-label" style="margin-bottom: 12px;">CARA KERJA</p>
                <h2 class="heading-section">3 langkah, langsung pakai</h2>
            </div>
            <div class="grid-3">
                <div class="card" style="text-align: center;">
                    <div style="width: 48px; height: 48px; margin: 0 auto 20px; background: #fff0e6; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 20px; font-weight: 700; color: var(--color-accent);">1</span>
                    </div>
                    <h3 class="heading-feature" style="margin: 0 0 12px;">Daftar & Generate Key</h3>
                    <p class="body-md">Buat akun gratis, generate API key dari dashboard. Langsung dapat Rp 100.000 free credit.</p>
                </div>
                <div class="card" style="text-align: center;">
                    <div style="width: 48px; height: 48px; margin: 0 auto 20px; background: #fff0e6; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 20px; font-weight: 700; color: var(--color-accent);">2</span>
                    </div>
                    <h3 class="heading-feature" style="margin: 0 0 12px;">Pasang di Tool Anda</h3>
                    <p class="body-md">Set Base URL dan API Key di Cursor, Kilo Code, VS Code, atau tool OpenAI-compatible lainnya.</p>
                </div>
                <div class="card" style="text-align: center;">
                    <div style="width: 48px; height: 48px; margin: 0 auto 20px; background: #fff0e6; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 20px; font-weight: 700; color: var(--color-accent);">3</span>
                    </div>
                    <h3 class="heading-feature" style="margin: 0 0 12px;">Mulai Coding</h3>
                    <p class="body-md">Gunakan Claude Opus 4.6, GPT-5, Gemini, dan 26+ model AI untuk coding, writing, dan analisis.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Supported Models -->
    <section class="section">
        <div class="container">
            <div style="text-align: center; margin-bottom: 48px;">
                <p class="mono-label" style="margin-bottom: 12px;">MODEL TERSEDIA</p>
                <h2 class="heading-section">26+ Model AI Premium</h2>
                <p class="body-lg" style="margin-top: 16px;">Satu API, akses semua provider terbaik dunia</p>
            </div>
            <div class="grid-4">
                @php
                $models = [
                    ['name' => 'Claude Opus 4.6', 'tier' => 'MAX', 'provider' => 'Anthropic'],
                    ['name' => 'Claude Sonnet 4.5', 'tier' => 'FREE', 'provider' => 'Anthropic'],
                    ['name' => 'GPT-5.4', 'tier' => 'MAX', 'provider' => 'OpenAI'],
                    ['name' => 'GPT-5.3 Codex', 'tier' => 'MAX', 'provider' => 'OpenAI'],
                    ['name' => 'Gemini 2.5 Pro', 'tier' => 'MAX', 'provider' => 'Google'],
                    ['name' => 'Gemini 3.1 Pro', 'tier' => 'MAX', 'provider' => 'Google'],
                    ['name' => 'DeepSeek 3.2', 'tier' => 'FREE', 'provider' => 'DeepSeek'],
                    ['name' => 'Kimi K2.5', 'tier' => 'MAX', 'provider' => 'Moonshot'],
                    ['name' => 'MiniMax M2.5', 'tier' => 'FREE', 'provider' => 'MiniMax'],
                    ['name' => 'GLM-5', 'tier' => 'FREE', 'provider' => 'Zhipu AI'],
                    ['name' => 'Qwen3 Coder', 'tier' => 'MAX', 'provider' => 'Alibaba'],
                    ['name' => 'GPT-5.2', 'tier' => 'MAX', 'provider' => 'OpenAI'],
                ];
                @endphp
                @foreach($models as $model)
                <div class="model-card">
                    <span class="model-tier {{ $model['tier'] === 'MAX' ? 'model-tier-max' : ($model['tier'] === 'FREE' ? 'model-tier-std' : 'model-tier-std') }}">
                        {{ $model['tier'] === 'FREE' ? 'FREE TIER' : 'PREMIUM' }}
                    </span>
                    <h4 style="font-size: 15px; font-weight: 600; margin: 10px 0 4px; color: var(--color-text);">{{ $model['name'] }}</h4>
                    <p style="font-size: 13px; color: var(--color-muted); margin: 0;">{{ $model['provider'] }}</p>
                </div>
                @endforeach
            </div>
            <p style="text-align: center; margin-top: 32px; color: var(--color-muted); font-size: 15px;">Dan 14+ model lainnya tersedia di dashboard...</p>
        </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="section" style="background: var(--color-surface); border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border);">
        <div class="container">
            <div style="text-align: center; margin-bottom: 48px;">
                <p class="mono-label" style="margin-bottom: 12px;">HARGA</p>
                <h2 class="heading-section">Sederhana & Transparan</h2>
                <p class="body-lg" style="margin-top: 16px;">Bayar sesuai pemakaian. Tidak ada langganan bulanan.</p>
            </div>
            <div class="grid-2" style="max-width: 800px; margin: 0 auto;">
                <!-- Free Tier -->
                <div class="pricing-card">
                    <p class="mono-label" style="margin-bottom: 16px;">FREE TRIAL</p>
                    <div class="pricing-amount" style="color: var(--color-text);">Rp 0</div>
                    <p style="font-size: 15px; color: var(--color-muted); margin: 8px 0 32px;">Langsung dapat Rp 100K credit</p>
                    <ul class="check-list">
                        <li>
                            <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span><strong>Rp 100.000</strong> free credit</span>
                        </li>
                        <li>
                            <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span>4 model AI (Sonnet, DeepSeek, MiniMax, GLM)</span>
                        </li>
                        <li>
                            <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span>OpenAI-compatible API</span>
                        </li>
                        <li>
                            <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span>Dashboard & statistik penggunaan</span>
                        </li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-outline" style="width: 100%; margin-top: 32px;">
                        Daftar Gratis
                    </a>
                </div>

                <!-- Paid Tier -->
                <div class="pricing-card featured">
                    <span class="badge-popular">POPULER</span>
                    <p class="mono-label" style="margin-bottom: 16px; color: var(--color-accent);">TOP UP</p>
                    <div class="pricing-amount" style="color: var(--color-text);">Rp 10K<span class="pricing-period">min</span></div>
                    <p style="font-size: 15px; color: var(--color-muted); margin: 8px 0 32px;">Bayar sesuai pemakaian, saldo tidak expired</p>
                    <ul class="check-list">
                        <li>
                            <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span><strong>Semua 26+ model</strong> termasuk Opus 4.6</span>
                        </li>
                        <li>
                            <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span>Saldo <strong>tidak expired</strong>, pakai kapan saja</span>
                        </li>
                        <li>
                            <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span>Bayar via QRIS (semua e-wallet & bank)</span>
                        </li>
                        <li>
                            <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span>Harga per model transparan di dashboard</span>
                        </li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-accent" style="width: 100%; margin-top: 32px;">
                        Mulai Sekarang
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Compatible Tools -->
    <section class="section">
        <div class="container">
            <div style="text-align: center; margin-bottom: 48px;">
                <p class="mono-label" style="margin-bottom: 12px;">KOMPATIBILITAS</p>
                <h2 class="heading-section">Drop-in OpenAI Replacement</h2>
                <p class="body-lg" style="margin-top: 16px;">Tinggal ganti Base URL dan API Key. Selesai.</p>
            </div>
            <div class="grid-4" style="max-width: 700px; margin: 0 auto 48px;">
                @php
                $tools = ['Cursor', 'Kilo Code', 'VS Code', 'Cline', 'Continue', 'Windsurf', 'OpenCode', 'Any Client'];
                @endphp
                @foreach($tools as $tool)
                <div class="tool-badge">{{ $tool }}</div>
                @endforeach
            </div>
            <div style="max-width: 600px; margin: 0 auto;">
                <div class="code-block">
                    <p style="margin: 0 0 8px;"><span class="code-comment"># Setup di Cursor / Kilo Code / VS Code</span></p>
                    <p style="margin: 0 0 4px;"><span class="code-key">Base URL:</span> <span class="code-value">{{ url('/api/v1') }}</span></p>
                    <p style="margin: 0 0 4px;"><span class="code-key">API Key:</span>  <span class="code-value">sk-your-api-key-here</span></p>
                    <p style="margin: 0;"><span class="code-key">Model:</span>   <span class="code-value">claude-sonnet-4.5</span></p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="section-sm" style="background: var(--color-dark); text-align: center;">
        <div class="container">
            <h2 class="heading-sub" style="color: #fff; margin: 0 0 16px;">Siap mulai?</h2>
            <p style="font-size: 18px; color: #9c9fa5; margin: 0 0 32px;">Daftar gratis, dapat Rp 100K credit, langsung pakai di tool favorit Anda.</p>
            <a href="{{ route('register') }}" class="btn btn-accent" style="padding: 16px 40px; font-size: 17px;">
                Daftar Gratis Sekarang
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 32px;">
                <div>
                    <p style="font-size: 18px; font-weight: 700; color: #fff; margin: 0 0 8px;">
                        <span class="accent-dot"></span> aimurah
                    </p>
                    <p style="font-size: 14px; margin: 0;">Akses AI Premium, Harga Terjangkau</p>
                </div>
                <div style="display: flex; gap: 24px; font-size: 14px;">
                    <a href="{{ route('login') }}">Login</a>
                    <a href="{{ route('register') }}">Register</a>
                    <a href="#pricing">Pricing</a>
                </div>
            </div>
            <div style="margin-top: 48px; padding-top: 24px; border-top: 1px solid #2a2a2a; text-align: center; font-size: 13px;">
                &copy; {{ date('Y') }} aimurah.my.id. All rights reserved.
            </div>
        </div>
    </footer>
</body>
</html>

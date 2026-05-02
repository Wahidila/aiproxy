<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AIMurah - Akses {{ $starModelDisplay }} Gratis Selamanya</title>
    <meta name="description" content="Daftar gratis, langsung akses {{ $starModelDisplay }} selamanya. Tanpa kartu kredit, tanpa trial period. Platform AI API proxy termurah di Indonesia — Cursor, VS Code, Kilo Code compatible.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- anime.js handles all animations, no AOS needed -->
    <script src="https://cdn.jsdelivr.net/npm/animejs@4.4.1/dist/bundles/anime.umd.min.js"></script>
    <style>
        :root {
            --color-canvas: #0a0a0f;
            --color-text: #e8e8ed;
            --color-accent: #ff5600;
            --color-accent-hover: #ff6b1a;
            --color-border: #1e1e2a;
            --color-muted: #8b8b9e;
            --color-surface: #111118;
            --color-surface-elevated: #16161f;
            --color-glow: rgba(255, 86, 0, 0.15);
        }

        /* Scroll-reveal: elements start hidden, anime.js reveals them */
        #section-steps .mono-label,
        #section-steps .heading-section,
        #section-steps .anim-card,
        #section-models .mono-label,
        #section-models .heading-section,
        #section-models .body-lg,
        #section-models .model-item,
        #pricing .mono-label,
        #pricing .heading-section,
        #pricing .body-lg,
        #pricing .pricing-card,
        #section-tools .mono-label,
        #section-tools .heading-section,
        #section-tools .body-lg,
        #section-tools .tool-badge,
        #section-tools .code-block,
        #section-cta .heading-sub,
        #section-cta p,
        #section-cta .btn {
            opacity: 0;
        }

        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
            color: var(--color-text);
            background: var(--color-canvas);
            margin: 0;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }
        /* Headings */
        .heading-display { font-size: clamp(36px, 5.5vw, 64px); font-weight: 600; line-height: 1.05; letter-spacing: -2px; }
        .heading-section { font-size: clamp(28px, 4vw, 48px); font-weight: 500; line-height: 1.05; letter-spacing: -1.6px; }
        .heading-sub { font-size: clamp(24px, 3.5vw, 40px); font-weight: 500; line-height: 1.05; letter-spacing: -1.2px; }
        .heading-card { font-size: clamp(20px, 2.5vw, 32px); font-weight: 500; line-height: 1.05; letter-spacing: -0.96px; }
        .heading-feature { font-size: 22px; font-weight: 500; line-height: 1.1; letter-spacing: -0.48px; }
        /* Body */
        .body-lg { font-size: 18px; line-height: 1.6; letter-spacing: -0.2px; color: var(--color-muted); }
        .body-md { font-size: 16px; line-height: 1.5; color: var(--color-muted); }
        .body-sm { font-size: 14px; line-height: 1.4; color: var(--color-muted); }
        /* Mono label */
        .mono-label { font-family: 'Inter', monospace; font-size: 12px; font-weight: 500; letter-spacing: 1.5px; text-transform: uppercase; color: var(--color-accent); }
        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 12px 24px; border-radius: 6px; font-size: 16px; font-weight: 500;
            text-decoration: none; transition: all 0.2s ease; cursor: pointer; border: none;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn:active { transform: translateY(0) scale(0.98); }
        .btn-accent { background: var(--color-accent); color: #fff; box-shadow: 0 4px 20px rgba(255,86,0,0.3); }
        .btn-accent:hover { background: var(--color-accent-hover); box-shadow: 0 8px 30px rgba(255,86,0,0.4); }
        .btn-outline { background: transparent; color: var(--color-text); border: 1px solid var(--color-border); }
        .btn-outline:hover { border-color: var(--color-accent); color: var(--color-accent); background: rgba(255,86,0,0.05); }
        /* Cards */
        .card {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: 12px;
            padding: 28px;
            transition: all 0.3s ease;
        }
        .card:hover { border-color: rgba(255,86,0,0.3); transform: translateY(-2px); }
        /* Layout */
        .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
        .section { padding: 100px 0; }
        .section-sm { padding: 72px 0; }
        /* Grid */
        .grid-2 { display: grid; grid-template-columns: 1fr; gap: 24px; }
        .grid-3 { display: grid; grid-template-columns: 1fr; gap: 24px; }
        .grid-4 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        @media (min-width: 768px) {
            .grid-2 { grid-template-columns: repeat(2, 1fr); }
            .grid-3 { grid-template-columns: repeat(3, 1fr); }
            .grid-4 { grid-template-columns: repeat(4, 1fr); }
        }
        /* Nav */
        .nav { position: sticky; top: 0; z-index: 100; background: rgba(10,10,15,0.85); backdrop-filter: blur(12px); border-bottom: 1px solid var(--color-border); }
        .nav-inner { display: flex; align-items: center; justify-content: space-between; height: 64px; }
        .nav-brand { font-size: 20px; font-weight: 700; color: var(--color-text); text-decoration: none; letter-spacing: -0.5px; }
        .nav-links { display: flex; align-items: center; gap: 16px; }
        .nav-link { font-size: 15px; color: var(--color-muted); text-decoration: none; font-weight: 400; transition: color 0.2s; }
        .nav-link:hover { color: var(--color-accent); }
        /* Hero */
        .hero { position: relative; overflow: hidden; padding: 100px 0 80px; min-height: 90vh; display: flex; align-items: center; }
        .hero-canvas { position: absolute; inset: 0; width: 100%; height: 100%; z-index: 0; }
        .hero-content { position: relative; z-index: 2; text-align: center; max-width: 800px; margin: 0 auto; }
        .hero-gradient-text {
            background: linear-gradient(135deg, #ffffff 0%, #ff5600 50%, #ffffff 100%);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradient-shift 6s ease infinite;
        }
        @keyframes gradient-shift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,86,0,0.1); border: 1px solid rgba(255,86,0,0.3);
            border-radius: 100px; padding: 6px 16px; font-size: 13px; font-weight: 500;
            color: var(--color-accent); margin-bottom: 24px;
        }
        .hero-badge-dot { width: 6px; height: 6px; background: var(--color-accent); border-radius: 50%; animation: pulse-dot 2s ease-in-out infinite; }
        @keyframes pulse-dot { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.5); } }
        .hero-stats { display: flex; justify-content: center; gap: 32px; margin-top: 48px; flex-wrap: wrap; }
        .hero-stat {
            display: flex; flex-direction: column; align-items: center; gap: 4px;
            padding: 16px 24px; background: var(--color-surface-elevated); border: 1px solid var(--color-border);
            border-radius: 10px; transition: all 0.3s ease; cursor: default;
        }
        .hero-stat:hover { border-color: var(--color-accent); box-shadow: 0 0 20px rgba(255,86,0,0.1); transform: translateY(-4px); }
        .hero-stat-number { font-size: 28px; font-weight: 700; letter-spacing: -1px; color: var(--color-text); }
        .hero-stat-label { font-size: 11px; font-weight: 500; letter-spacing: 1px; text-transform: uppercase; color: var(--color-muted); }
        /* Floating model badges — minimal */
        .hero-model-badge {
            position: absolute; padding: 8px 14px; background: rgba(17,17,24,0.85);
            border: 1px solid var(--color-border); border-radius: 8px;
            font-size: 12px; font-weight: 500; color: var(--color-muted);
            backdrop-filter: blur(8px); opacity: 0; pointer-events: none;
            display: flex; align-items: center; gap: 8px;
        }
        .hero-model-badge .badge-dot { width: 6px; height: 6px; border-radius: 50%; }
        /* Code preview */
        .hero-code-preview {
            background: var(--color-surface); border-radius: 12px; padding: 20px 24px; margin-top: 40px;
            font-family: 'JetBrains Mono', 'Fira Code', monospace; font-size: 13px; line-height: 1.7;
            text-align: left; max-width: 520px; margin-left: auto; margin-right: auto;
            border: 1px solid var(--color-border); position: relative; overflow: hidden;
            transition: all 0.3s ease;
        }
        .hero-code-preview:hover { border-color: rgba(255,86,0,0.3); box-shadow: 0 8px 32px rgba(0,0,0,0.3); }
        .hero-code-preview::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 36px;
            background: var(--color-surface-elevated); border-bottom: 1px solid var(--color-border); border-radius: 12px 12px 0 0;
        }
        .hero-code-dots { position: relative; z-index: 1; display: flex; gap: 6px; margin-bottom: 16px; padding-top: 2px; }
        .hero-code-dot { width: 10px; height: 10px; border-radius: 50%; }
        .hero-code-content { position: relative; z-index: 1; }
        .hero-code-line { opacity: 0; transform: translateX(-10px); }
        .hero-cursor { display: inline-block; width: 2px; height: 14px; background: var(--color-accent); animation: blink-cursor 1s step-end infinite; vertical-align: middle; margin-left: 2px; }
        @keyframes blink-cursor { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }

        /* Pricing */
        .pricing-card { border-radius: 12px; padding: 40px 32px; border: 1px solid var(--color-border); background: var(--color-surface); transition: all 0.3s ease; display: flex; flex-direction: column; }
        .pricing-card:hover { border-color: rgba(255,86,0,0.2); }
        .pricing-card.featured { border-color: var(--color-accent); border-width: 2px; position: relative; }
        .pricing-amount { font-size: 48px; font-weight: 700; letter-spacing: -1.5px; line-height: 1; color: var(--color-text); }
        .pricing-period { font-size: 16px; color: var(--color-muted); margin-left: 4px; }
        /* Check list */
        .check-list { list-style: none; padding: 0; margin: 0; }
        .check-list li { display: flex; align-items: flex-start; gap: 12px; padding: 8px 0; font-size: 15px; color: var(--color-text); }
        .check-icon { width: 20px; height: 20px; flex-shrink: 0; color: var(--color-accent); margin-top: 1px; }
        /* Code block */
        .code-block { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 12px; padding: 24px; font-family: 'JetBrains Mono', 'Fira Code', monospace; font-size: 14px; line-height: 1.6; overflow-x: auto; }
        .code-comment { color: #6b7280; }
        .code-key { color: #a5d6ff; }
        .code-value { color: #7ee787; }
        /* Tool badge */
        .tool-badge { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 8px; padding: 14px 18px; font-size: 14px; font-weight: 500; text-align: center; color: var(--color-text); display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s; }
        .tool-badge:hover { border-color: var(--color-accent); background: rgba(255,86,0,0.05); }
        .tool-badge i { width: 16px; height: 16px; color: var(--color-muted); }
        /* Icon box */
        .icon-box { width: 48px; height: 48px; margin: 0 auto 20px; background: rgba(255,86,0,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .icon-box i { width: 24px; height: 24px; color: var(--color-accent); }
        /* Model chip — compact */
        .model-item {
            display: flex; align-items: center; gap: 8px; background: var(--color-surface);
            border: 1px solid var(--color-border); border-radius: 6px; padding: 8px 12px;
            transition: all 0.2s; cursor: default;
        }
        .model-item:hover { border-color: var(--color-accent); background: rgba(255,86,0,0.04); }
        .model-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;
            max-width: 860px; margin: 0 auto;
        }
        @media (max-width: 768px) { .model-grid { grid-template-columns: repeat(2, 1fr); gap: 6px; } }
        @media (max-width: 480px) { .model-grid { grid-template-columns: 1fr; } }
        /* Footer */
        .footer { background: #050508; color: #6b6b80; padding: 64px 0 32px; border-top: 1px solid var(--color-border); }
        .footer a { color: #6b6b80; text-decoration: none; transition: color 0.2s; }
        .footer a:hover { color: var(--color-accent); }
        /* Badge */
        .badge-popular { position: absolute; top: -14px; left: 50%; transform: translateX(-50%); background: var(--color-accent); color: #fff; font-size: 11px; font-weight: 600; letter-spacing: 1px; padding: 5px 16px; border-radius: 6px; text-transform: uppercase; white-space: nowrap; }
        /* Responsive */
        @media (max-width: 640px) {
            .section { padding: 64px 0; }
            .nav-links .nav-link { display: none; }
            .hero-buttons { flex-direction: column; align-items: center; }
            .hero-stat-number { font-size: 22px; }
            .hero-code-preview { font-size: 11px; padding: 16px; }
            .hero-stats { gap: 16px; }
            .hero-model-badge { display: none; }
            .hero { min-height: auto; padding: 60px 0; }
        }
        /* Modal */
        .modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 200; display: flex; align-items: center; justify-content: center; padding: 16px; }
        .modal-card { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 12px; width: 100%; max-width: 440px; box-shadow: 0 20px 60px rgba(0,0,0,0.5); position: relative; }
        .modal-header { padding: 24px 28px 0; }
        .modal-body { padding: 20px 28px 28px; }
        .modal-close { position: absolute; top: 16px; right: 16px; background: none; border: none; cursor: pointer; color: var(--color-muted); padding: 4px; border-radius: 4px; transition: all 0.15s; }
        .modal-close:hover { background: var(--color-surface-elevated); color: var(--color-text); }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 14px; font-weight: 500; color: var(--color-text); margin-bottom: 6px; }
        .form-input { width: 100%; padding: 10px 14px; border: 1px solid var(--color-border); border-radius: 6px; font-size: 15px; font-family: inherit; color: var(--color-text); background: var(--color-surface-elevated); transition: border-color 0.2s; box-sizing: border-box; }
        .form-input:focus { outline: none; border-color: var(--color-accent); box-shadow: 0 0 0 3px rgba(255,86,0,0.1); }
        .form-input::placeholder { color: #4a4a5e; }
        .form-error { font-size: 13px; color: #ef4444; margin-top: 4px; }
        .btn-submit { width: 100%; padding: 12px 24px; background: var(--color-accent); color: #fff; border: none; border-radius: 6px; font-size: 16px; font-weight: 500; font-family: inherit; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-submit:hover { background: var(--color-accent-hover); transform: translateY(-1px); }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .success-state { text-align: center; padding: 16px 0; }
        .success-icon { width: 56px; height: 56px; background: rgba(22,163,106,0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
        .success-icon svg { width: 28px; height: 28px; color: #22c55e; }
        /* WhatsApp Bubble */
        .wa-bubble { position: fixed; bottom: 24px; right: 24px; z-index: 150; width: 56px; height: 56px; background: #25D366; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(37,211,102,0.4); cursor: pointer; transition: all 0.3s ease; text-decoration: none; }
        .wa-bubble:hover { transform: scale(1.1); box-shadow: 0 6px 20px rgba(37,211,102,0.5); }
        .wa-bubble:active { transform: scale(0.95); }
        .wa-bubble svg { width: 28px; height: 28px; fill: #fff; }
        .wa-tooltip { position: absolute; right: 68px; bottom: 50%; transform: translateY(50%); background: var(--color-surface); color: var(--color-text); font-size: 13px; font-weight: 500; padding: 8px 14px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.3); white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity 0.2s; border: 1px solid var(--color-border); }
        .wa-bubble:hover .wa-tooltip { opacity: 1; }
        @media (max-width: 640px) {
            .wa-bubble { width: 48px; height: 48px; bottom: 16px; right: 16px; }
            .wa-bubble svg { width: 24px; height: 24px; }
            .wa-tooltip { display: none; }
        }
        /* Spinner */
        .spinner { display: inline-block; width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: #fff; animation: spin 0.6s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        /* Limit badge */
        .limit-badge { flex: 1; background: var(--color-surface-elevated); border: 1px solid var(--color-border); border-radius: 8px; padding: 10px 12px; text-align: center; }
        /* Tier badge */
        .tier-badge-free { background: rgba(22,163,106,0.15); color: #22c55e; }
        .tier-badge-pro { background: rgba(255,86,0,0.15); color: var(--color-accent); }
        /* Section divider */
        .section-divider { border-top: 1px solid var(--color-border); }
        /* Glow effect on featured pricing */
        @keyframes glow { from { box-shadow: 0 0 8px rgba(255,86,0,0.2), 0 0 16px rgba(255,86,0,0.1); } to { box-shadow: 0 0 16px rgba(255,86,0,0.4), 0 0 32px rgba(255,86,0,0.2); } }
    </style>
</head>
<body onload="lucide.createIcons()" x-data="trialApp()">
    <!-- Navigation -->
    <nav class="nav">
        <div class="container nav-inner">
            <a href="/" class="nav-brand" style="display: flex; align-items: center; gap: 8px;">
                <i data-lucide="zap" style="width: 22px; height: 22px; color: var(--color-accent);"></i>
                AIMurah
            </a>
            <div class="nav-links">
                @auth
                    <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="nav-link">Login</a>
                    <button @click="openModal()" class="btn btn-accent" style="padding: 8px 18px; font-size: 14px; border-radius: 6px;">Daftar Gratis</button>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section with anime.js -->
    <section class="hero" id="hero-section">
        <canvas id="hero-dot-grid" class="hero-canvas"></canvas>

        <!-- Floating model badges — minimal, only 4 -->
        <div class="hero-model-badge" id="badge-1" style="top: 18%; left: 15%;">
            <span class="badge-dot" style="background: #d4a574;"></span> Claude Sonnet 4.5
        </div>
        <div class="hero-model-badge" id="badge-2" style="top: 22%; right: 15%;">
            <span class="badge-dot" style="background: #22c55e;"></span> GPT-5
        </div>
        <div class="hero-model-badge" id="badge-3" style="top: 60%; left: 14%;">
            <span class="badge-dot" style="background: #4285f4;"></span> Gemini 2.5 Pro
        </div>
        <div class="hero-model-badge" id="badge-4" style="top: 55%; right: 14%;">
            <span class="badge-dot" style="background: #a78bfa;"></span> Claude Opus 4.6
        </div>

        <div class="container hero-content">
            <div class="hero-badge">
                <span class="hero-badge-dot"></span>
                AI Proxy Termurah di Indonesia
            </div>

            <h1 class="heading-display" style="margin: 0 0 20px;">
                <span class="hero-gradient-text">{{ $starModelDisplay }}</span><br>
                <span style="color: var(--color-text);">Gratis Selamanya</span>
            </h1>

            <p class="body-lg" style="max-width: 560px; margin: 0 auto 36px; font-size: 18px;">
                Akses {{ $totalModels }}+ model AI premium — Claude, GPT, Gemini — langsung dari Cursor, VS Code, atau tool favorit Anda. Tanpa kartu kredit.
            </p>

            <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;" class="hero-buttons">
                <button @click="openModal()" class="btn btn-accent" style="padding: 14px 32px; font-size: 16px; gap: 8px;">
                    <i data-lucide="sparkles" style="width: 20px; height: 20px;"></i>
                    Mulai Gratis Sekarang
                </button>
                <a href="#pricing" class="btn btn-outline" style="gap: 8px;">
                    <i data-lucide="tag" style="width: 18px; height: 18px;"></i>
                    Lihat Harga
                </a>
            </div>

            <!-- Stats -->
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="hero-stat-number" data-count="{{ $totalModels }}">0</span>
                    <span class="hero-stat-label">Model AI</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-number" data-count="5">0</span>
                    <span class="hero-stat-label">Provider</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-number">Rp 0</span>
                    <span class="hero-stat-label">Mulai Dari</span>
                </div>
            </div>

            <!-- Code Preview with typing effect -->
            <div class="hero-code-preview">
                <div class="hero-code-dots">
                    <span class="hero-code-dot" style="background: #ff5f57;"></span>
                    <span class="hero-code-dot" style="background: #ffbd2e;"></span>
                    <span class="hero-code-dot" style="background: #28c840;"></span>
                </div>
                <div class="hero-code-content" id="hero-code-typed">
                    <div class="hero-code-line"><span style="color: #6b7280;">// Setup di Cursor / VS Code</span></div>
                    <div class="hero-code-line"><span style="color: #a5d6ff;">Base URL:</span> <span style="color: #7ee787;">{{ url('/api/v1') }}</span></div>
                    <div class="hero-code-line"><span style="color: #a5d6ff;">API Key:</span> <span style="color: #7ee787;">***</span></div>
                    <div class="hero-code-line"><span style="color: #a5d6ff;">Model:</span> <span style="color: #7ee787;">{{ $starModel }}</span><span class="hero-cursor"></span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="section section-divider" id="section-steps">
        <div class="container">
            <div style="text-align: center; margin-bottom: 56px;">
                <p class="mono-label" style="margin-bottom: 12px;">CARA KERJA</p>
                <h2 class="heading-section">3 Langkah, Langsung Pakai</h2>
            </div>
            <div class="grid-3">
                <div class="card anim-card" style="text-align: center;">
                    <div class="icon-box">
                        <i data-lucide="key-round"></i>
                    </div>
                    <h3 class="heading-feature" style="margin: 0 0 12px;">Daftar & Generate Key</h3>
                    <p class="body-md">Buat akun gratis, generate API key dari dashboard. Langsung dapat free credit.</p>
                </div>
                <div class="card anim-card" style="text-align: center;">
                    <div class="icon-box">
                        <i data-lucide="plug"></i>
                    </div>
                    <h3 class="heading-feature" style="margin: 0 0 12px;">Pasang di Tool Anda</h3>
                    <p class="body-md">Set Base URL dan API Key di Cursor, Kilo Code, VS Code, atau tool OpenAI-compatible lainnya.</p>
                </div>
                <div class="card anim-card" style="text-align: center;">
                    <div class="icon-box">
                        <i data-lucide="rocket"></i>
                    </div>
                    <h3 class="heading-feature" style="margin: 0 0 12px;">Mulai Coding</h3>
                    <p class="body-md">Gunakan Claude Opus 4.6, GPT-5, Gemini, dan {{ $totalModels }}+ model AI untuk coding & analisis.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Supported Models -->
    <section class="section section-divider" id="section-models">
        <div class="container">
            <div style="text-align: center; margin-bottom: 48px;">
                <p class="mono-label" style="margin-bottom: 12px;">MODEL TERSEDIA</p>
                <h2 class="heading-section">{{ $totalModels }}+ Model AI, Satu API</h2>
                <p class="body-lg" style="margin-top: 16px;">Akses semua model dari provider terbaik dunia. Tinggal ganti nama model.</p>
            </div>
            @php
                $dbModels = \App\Models\ModelPricing::where('is_active', true)->orderByDesc('is_free_tier')->orderBy('model_name')->get();
                $modelCount = $dbModels->count();

                $providerMap = [
                    'claude' => ['name' => 'Anthropic', 'logo' => '/images/logos/anthropic.svg', 'color' => '#d4a574'],
                    'gemini' => ['name' => 'Google', 'logo' => '/images/logos/gemini.svg', 'color' => '#4285f4'],
                    'gpt' => ['name' => 'OpenAI', 'logo' => '/images/logos/openai.svg', 'color' => '#22c55e'],
                    'glm' => ['name' => 'Zhipu AI', 'logo' => '/images/logos/zhipu.svg', 'color' => '#a78bfa'],
                    'kimi' => ['name' => 'Moonshot AI', 'logo' => '/images/logos/moonshot.svg', 'color' => '#f472b6'],
                ];

                function getProvider($modelId) {
                    if (str_starts_with($modelId, 'claude')) return 'claude';
                    if (str_starts_with($modelId, 'gemini')) return 'gemini';
                    if (str_starts_with($modelId, 'gpt')) return 'gpt';
                    if (str_starts_with($modelId, 'glm')) return 'glm';
                    if (str_starts_with($modelId, 'kimi')) return 'kimi';
                    return 'other';
                }

                $grouped = $dbModels->groupBy(fn($m) => getProvider($m->model_id));
            @endphp

            {{-- Provider logos row --}}
            <div style="display: flex; justify-content: center; gap: 40px; margin-bottom: 48px; flex-wrap: wrap; align-items: center;">
                @foreach($providerMap as $key => $provider)
                    <div style="display: flex; align-items: center; gap: 10px; opacity: 0.7;">
                        <img src="{{ $provider['logo'] }}" alt="{{ $provider['name'] }}" style="width: 24px; height: 24px; filter: brightness(0) invert(0.7);" loading="lazy">
                        <span style="font-size: 14px; font-weight: 500; color: var(--color-muted); letter-spacing: -0.2px;">{{ $provider['name'] }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Model grid — compact chips --}}
            <div class="model-grid">
                @foreach($dbModels as $m)
                    @php $pKey = getProvider($m->model_id); $pInfo = $providerMap[$pKey] ?? null; @endphp
                    <div class="model-item">
                        @if($pInfo)
                            <img src="{{ $pInfo['logo'] }}" alt="{{ $pInfo['name'] }}" style="width: 18px; height: 18px; flex-shrink: 0; filter: brightness(0) invert(0.7);" loading="lazy">
                        @endif
                        <span style="flex: 1; min-width: 0; font-size: 13px; font-weight: 500; color: var(--color-text); letter-spacing: -0.2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $m->model_name }}</span>
                        <span style="font-size: 9px; font-weight: 600; letter-spacing: 0.6px; text-transform: uppercase; padding: 2px 6px; border-radius: 3px; flex-shrink: 0; line-height: 1;" class="{{ $m->is_free_tier ? 'tier-badge-free' : 'tier-badge-pro' }}">{{ $m->is_free_tier ? 'FREE' : 'PRO' }}</span>
                    </div>
                @endforeach
            </div>

            <p style="text-align: center; margin-top: 32px; color: var(--color-muted); font-size: 14px;">
                <span style="display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ff5600" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    Harga per model tersedia di <a href="/pricing" style="color: var(--color-accent); text-decoration: none; font-weight: 500;">halaman pricing</a>. Bayar sesuai pemakaian.
                </span>
            </p>
        </div>
    </section>

    <!-- Pricing -->
    @if(\App\Models\Setting::get('subscription_enabled', '0') == '1')
    <section id="pricing" class="section section-divider" x-data="{ tab: 'monthly' }">
        <div class="container">
            <div style="text-align: center; margin-bottom: 32px;">
                <p class="mono-label" style="margin-bottom: 12px;">HARGA</p>
                <h2 class="heading-section">Pilih Plan Anda</h2>
                <p class="body-lg" style="margin-top: 16px;">Mulai sekarang, upgrade kapan saja. Tanpa kontrak.</p>
            </div>

            <!-- Toggle -->
            <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 48px; gap: 8px;">
                <div style="display: inline-flex; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 8px; padding: 4px; position: relative;">
                    <button @click="tab = 'monthly'" :style="tab === 'monthly' ? 'background: var(--color-accent); color: #fff;' : 'background: transparent; color: var(--color-muted);'" style="padding: 8px 24px; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; font-family: inherit; transition: all 0.2s; letter-spacing: 0.6px; text-transform: uppercase;">Bulanan</button>
                    <button @click="tab = 'daily'" :style="tab === 'daily' ? 'background: var(--color-accent); color: #fff;' : 'background: transparent; color: var(--color-muted);'" style="padding: 8px 24px; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; font-family: inherit; transition: all 0.2s; letter-spacing: 0.6px; text-transform: uppercase; position: relative;">Harian
                        <span style="position: absolute; top: -10px; right: -12px; background: #22c55e; color: #fff; font-size: 9px; font-weight: 700; letter-spacing: 0.8px; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; line-height: 1.2;">HEMAT</span>
                    </button>
                </div>
                <p style="font-size: 12px; color: var(--color-accent); font-weight: 500; margin: 0; letter-spacing: 0.3px;">💡 Paket Harian lebih hemat — unlimited request mulai Rp 29K/hari</p>
            </div>

            @php
                $plans = \App\Models\SubscriptionPlan::orderBy('sort_order')->get();
                $monthlyPlans = $plans->where('type', 'monthly');
                $dailyPlans = $plans->where('type', 'daily');
            @endphp

            <!-- Monthly Plans -->
            <div x-show="tab === 'monthly'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="grid-3" style="max-width: 1060px; margin: 0 auto;">
                    @foreach($monthlyPlans as $plan)
                        <div class="pricing-card {{ $plan->is_popular ? 'featured' : '' }}">
                            @if($plan->is_popular)
                                <span class="badge-popular">⭐ PALING POPULER</span>
                            @endif
                            <p class="mono-label" style="margin: 0 0 16px; color: {{ $plan->is_popular ? 'var(--color-accent)' : 'var(--color-muted)' }};">{{ strtoupper($plan->name) }}</p>

                            <p style="font-size: 14px; color: var(--color-muted); margin: 0 0 16px; line-height: 1.4;">
                                @if($plan->slug === 'free')
                                    Untuk eksplorasi dan iseng
                                @elseif($plan->slug === 'pro')
                                    Untuk hobby & side project
                                @elseif($plan->slug === 'premium')
                                    Untuk tim & production scale
                                @endif
                            </p>

                            <div class="pricing-amount" style="margin: 0 0 4px;">
                                @if($plan->price_idr == 0)
                                    Rp 0
                                @else
                                    {{ $plan->formatted_price }}
                                @endif
                            </div>
                            <p style="font-size: 14px; color: var(--color-muted); margin: 0 0 24px;">
                                @if($plan->price_idr == 0)
                                    Selamanya, tanpa kartu kredit.
                                @else
                                    per bulan, IDR (sudah PPN)
                                @endif
                            </p>

                            <div style="display: flex; gap: 12px; margin-bottom: 24px;">
                                <div class="limit-badge">
                                    <p style="font-family: 'Inter', monospace; font-size: 10px; font-weight: 500; letter-spacing: 1px; text-transform: uppercase; color: var(--color-muted); margin: 0 0 4px;">DAILY</p>
                                    <p style="font-size: 18px; font-weight: 600; color: var(--color-text); margin: 0; letter-spacing: -0.5px;">
                                        @if($plan->daily_request_limit)
                                            {{ number_format($plan->daily_request_limit) }}
                                        @else
                                            ∞
                                        @endif
                                    </p>
                                </div>
                                <div class="limit-badge">
                                    <p style="font-family: 'Inter', monospace; font-size: 10px; font-weight: 500; letter-spacing: 1px; text-transform: uppercase; color: var(--color-muted); margin: 0 0 4px;">PER MENIT</p>
                                    <p style="font-size: 18px; font-weight: 600; color: var(--color-text); margin: 0; letter-spacing: -0.5px;">{{ $plan->per_minute_limit }} req</p>
                                </div>
                            </div>

                            @if($plan->features && count($plan->features) > 0)
                                <ul style="list-style: none; padding: 0; margin: 0 0 0 0; flex: 1;">
                                    @foreach($plan->features as $feature)
                                        <li style="display: flex; align-items: flex-start; gap: 10px; padding: 6px 0; font-size: 14px; color: var(--color-text); line-height: 1.4;">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ff5600" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 1px;"><path d="M20 6 9 17l-5-5"/></svg>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if($plan->price_idr == 0)
                                <button @click="openModal()" class="btn btn-outline" style="width: 100%; margin-top: 32px; padding: 12px 24px;">
                                    Mulai sekarang →
                                </button>
                            @elseif($plan->is_popular)
                                <a href="/subscriptions" class="btn btn-accent" style="width: 100%; margin-top: 32px; padding: 12px 24px; text-decoration: none;">
                                    Pilih Premium →
                                </a>
                            @else
                                <a href="/subscriptions" class="btn btn-outline" style="width: 100%; margin-top: 32px; padding: 12px 24px; text-decoration: none; border-color: var(--color-accent); color: var(--color-accent);">
                                    Pilih Pro →
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Daily Plans -->
            <div x-show="tab === 'daily'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div style="max-width: 400px; margin: 0 auto;">
                    @foreach($dailyPlans as $plan)
                        <div class="pricing-card featured">
                            <p class="mono-label" style="margin: 0 0 16px;">PAKET {{ strtoupper($plan->name) }}</p>
                            <p style="font-size: 14px; color: var(--color-muted); margin: 0 0 16px; line-height: 1.4;">Unlimited Request, Max 100M Token</p>

                            <div class="pricing-amount" style="margin: 0 0 4px;">{{ $plan->formatted_price }}</div>
                            <p style="font-size: 14px; color: var(--color-muted); margin: 0 0 24px;">per hari, IDR</p>

                            <div style="display: flex; gap: 12px; margin-bottom: 24px;">
                                <div class="limit-badge">
                                    <p style="font-family: 'Inter', monospace; font-size: 10px; font-weight: 500; letter-spacing: 1px; text-transform: uppercase; color: var(--color-muted); margin: 0 0 4px;">DAILY</p>
                                    <p style="font-size: 18px; font-weight: 600; color: var(--color-text); margin: 0;">Unlimited</p>
                                </div>
                                <div class="limit-badge">
                                    <p style="font-family: 'Inter', monospace; font-size: 10px; font-weight: 500; letter-spacing: 1px; text-transform: uppercase; color: var(--color-muted); margin: 0 0 4px;">PER MENIT</p>
                                    <p style="font-size: 18px; font-weight: 600; color: var(--color-text); margin: 0;">{{ $plan->per_minute_limit }} req</p>
                                </div>
                            </div>

                            @if($plan->features && count($plan->features) > 0)
                                <ul style="list-style: none; padding: 0; margin: 0;">
                                    @foreach($plan->features as $feature)
                                        <li style="display: flex; align-items: flex-start; gap: 10px; padding: 6px 0; font-size: 14px; color: var(--color-text); line-height: 1.4;">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ff5600" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 1px;"><path d="M20 6 9 17l-5-5"/></svg>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            <a href="/subscriptions" class="btn btn-accent" style="width: 100%; margin-top: 32px; padding: 12px 24px; text-decoration: none;">
                                Beli Harian →
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Compatible Tools -->
    <section class="section section-divider" id="section-tools">
        <div class="container">
            <div style="text-align: center; margin-bottom: 48px;">
                <p class="mono-label" style="margin-bottom: 12px;">KOMPATIBILITAS</p>
                <h2 class="heading-section">Drop-in OpenAI Replacement</h2>
                <p class="body-lg" style="margin-top: 16px;">Tinggal ganti Base URL dan API Key. Selesai.</p>
            </div>
            <div class="grid-4" style="max-width: 700px; margin: 0 auto 48px;">
                @php
                $tools = [
                    ['name' => 'Cursor', 'icon' => 'mouse-pointer-click'],
                    ['name' => 'Kilo Code', 'icon' => 'bot'],
                    ['name' => 'VS Code', 'icon' => 'code'],
                    ['name' => 'Cline', 'icon' => 'terminal'],
                    ['name' => 'Continue', 'icon' => 'play'],
                    ['name' => 'Windsurf', 'icon' => 'wind'],
                    ['name' => 'OpenCode', 'icon' => 'braces'],
                    ['name' => 'Any Client', 'icon' => 'globe'],
                ];
                @endphp
                @foreach($tools as $tool)
                <div class="tool-badge">
                    <i data-lucide="{{ $tool['icon'] }}"></i>
                    {{ $tool['name'] }}
                </div>
                @endforeach
            </div>
            <div style="max-width: 600px; margin: 0 auto;">
                <div class="code-block">
                    <p style="margin: 0 0 12px; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="terminal" style="width: 16px; height: 16px; color: #6b7280;"></i>
                        <span class="code-comment">Setup di Cursor / Kilo Code / VS Code</span>
                    </p>
                    <p style="margin: 0 0 4px;"><span class="code-key">Base URL:</span> <span class="code-value">{{ url('/api/v1') }}</span></p>
                    <p style="margin: 0 0 4px;"><span class="code-key">API Key:</span>  <span class="code-value">sk-you...here</span></p>
                    <p style="margin: 0;"><span class="code-key">Model:</span>   <span class="code-value">claude-sonnet-4.5</span></p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="section-sm" id="section-cta" style="background: var(--color-surface); text-align: center; border-top: 1px solid var(--color-border);">
        <div class="container">
            <h2 class="heading-sub" style="color: var(--color-text); margin: 0 0 16px;">Siap mulai?</h2>
            <p style="font-size: 18px; color: var(--color-muted); margin: 0 0 32px;">Daftar sekarang dan mulai gunakan AI premium gratis.</p>
            <button @click="openModal()" class="btn btn-accent" style="padding: 16px 40px; font-size: 17px; gap: 10px;">
                Daftar Gratis Sekarang
                <i data-lucide="arrow-right" style="width: 20px; height: 20px;"></i>
            </button>
        </div>
    </section>

    <!-- Trial Registration Popup -->
    <template x-if="showModal">
        <div class="modal-backdrop" @click.self="closeModal()" @keydown.escape.window="closeModal()">
            <div class="modal-card" @click.stop>
                <button class="modal-close" @click="closeModal()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>

                <div x-show="!success">
                    <div class="modal-header">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ff5600" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                            <span style="font-size: 16px; font-weight: 600; color: var(--color-text);">AIMurah</span>
                        </div>
                        <h3 style="font-size: 22px; font-weight: 600; color: var(--color-text); letter-spacing: -0.5px; margin: 0 0 6px;">Daftar Trial</h3>
                        <p style="font-size: 14px; color: var(--color-muted); margin: 0; line-height: 1.5;">Masukkan nama dan email Anda. Kami akan mengirimkan undangan akses beserta free credit.</p>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label" for="trial_name">Nama Lengkap</label>
                            <input type="text" id="trial_name" class="form-input" placeholder="Masukkan nama Anda" x-model="form.name" @keydown.enter="submit()">
                            <template x-if="errors.name">
                                <p class="form-error" x-text="errors.name"></p>
                            </template>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="trial_email">Email</label>
                            <input type="email" id="trial_email" class="form-input" placeholder="email@example.com" x-model="form.email" @keydown.enter="submit()">
                            <template x-if="errors.email">
                                <p class="form-error" x-text="errors.email"></p>
                            </template>
                        </div>
                        <template x-if="generalError">
                            <div style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 6px; padding: 10px 14px; margin-bottom: 16px;">
                                <p style="font-size: 13px; color: #ef4444; margin: 0;" x-text="generalError"></p>
                            </div>
                        </template>
                        <button class="btn-submit" @click="submit()" :disabled="loading">
                            <template x-if="loading">
                                <span class="spinner"></span>
                            </template>
                            <template x-if="!loading">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                            </template>
                            <span x-text="loading ? 'Mengirim...' : 'Daftar Trial'"></span>
                        </button>
                        <p style="font-size: 12px; color: var(--color-muted); text-align: center; margin: 12px 0 0;">Tidak perlu kartu kredit. Data Anda aman.</p>
                    </div>
                </div>

                <div x-show="success" class="modal-body">
                    <div class="success-state">
                        <div class="success-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <h3 style="font-size: 20px; font-weight: 600; color: var(--color-text); margin: 0 0 8px; letter-spacing: -0.3px;">Permintaan Diterima!</h3>
                        <p style="font-size: 15px; color: var(--color-muted); margin: 0 0 20px; line-height: 1.5;">Terima kasih! Kami akan mengirimkan undangan ke email Anda segera.</p>
                        <button class="btn-submit" @click="closeModal()">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

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
                    <p style="font-size: 18px; font-weight: 700; color: var(--color-text); margin: 0 0 8px; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="zap" style="width: 20px; height: 20px; color: var(--color-accent);"></i>
                        AIMurah
                    </p>
                    <p style="font-size: 14px; margin: 0;">Akses AI Premium, Harga Terjangkau</p>
                </div>
                <div style="display: flex; gap: 24px; font-size: 14px;">
                    <a href="{{ route('login') }}">Login</a>
                    <a href="#" @click.prevent="openModal()">Register</a>
                    <a href="#pricing">Pricing</a>
                </div>
            </div>
            <div style="margin-top: 48px; padding-top: 24px; border-top: 1px solid var(--color-border); text-align: center; font-size: 13px;">
                &copy; {{ date('Y') }} AIMurah. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Alpine.js Trial App -->
    <script>
        function trialApp() {
            return {
                showModal: false,
                loading: false,
                success: false,
                form: { name: '', email: '' },
                errors: {},
                generalError: '',

                openModal() {
                    this.showModal = true;
                    this.success = false;
                    this.errors = {};
                    this.generalError = '';
                    document.body.style.overflow = 'hidden';
                    this.$nextTick(() => {
                        const nameInput = document.getElementById('trial_name');
                        if (nameInput) nameInput.focus();
                    });
                },

                closeModal() {
                    this.showModal = false;
                    document.body.style.overflow = '';
                    if (this.success) {
                        this.form = { name: '', email: '' };
                        this.success = false;
                    }
                },

                async submit() {
                    this.errors = {};
                    this.generalError = '';

                    if (!this.form.name.trim()) {
                        this.errors.name = 'Nama wajib diisi.';
                    }
                    if (!this.form.email.trim()) {
                        this.errors.email = 'Email wajib diisi.';
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
                        this.errors.email = 'Format email tidak valid.';
                    }

                    if (Object.keys(this.errors).length > 0) return;

                    this.loading = true;

                    try {
                        const response = await fetch('{{ route("trial-request.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                name: this.form.name.trim(),
                                email: this.form.email.trim().toLowerCase(),
                            }),
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.success = true;
                        } else if (response.status === 422) {
                            if (data.errors) {
                                if (data.errors.name) this.errors.name = data.errors.name[0];
                                if (data.errors.email) this.errors.email = data.errors.email[0];
                            } else if (data.message) {
                                this.generalError = data.message;
                            }
                        } else {
                            this.generalError = data.message || 'Terjadi kesalahan. Silakan coba lagi.';
                        }
                    } catch (err) {
                        this.generalError = 'Gagal menghubungi server. Periksa koneksi internet Anda.';
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }
    </script>

    <!-- Hero Animation with anime.js -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const isMobile = window.innerWidth < 768;
        const hero = document.getElementById('hero-section');

        // === 1. INTERACTIVE DOT GRID (Canvas) ===
        const canvas = document.getElementById('hero-dot-grid');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        let w, h, cols, rows, dots = [];
        let mouseX = -1000, mouseY = -1000;

        const DOT_SPACING = isMobile ? 40 : 32;
        const DOT_BASE_SIZE = 1.2;
        const DOT_MAX_SIZE = isMobile ? 3 : 4.5;
        const MOUSE_RADIUS = isMobile ? 80 : 140;
        const DOT_BASE_ALPHA = 0.25;
        const DOT_MAX_ALPHA = 0.75;

        function resize() {
            w = canvas.width = canvas.offsetWidth;
            h = canvas.height = canvas.offsetHeight;
            cols = Math.ceil(w / DOT_SPACING) + 1;
            rows = Math.ceil(h / DOT_SPACING) + 1;
            dots = [];
            for (let r = 0; r < rows; r++) {
                for (let c = 0; c < cols; c++) {
                    dots.push({
                        x: c * DOT_SPACING,
                        y: r * DOT_SPACING,
                        baseX: c * DOT_SPACING,
                        baseY: r * DOT_SPACING,
                        size: DOT_BASE_SIZE,
                        alpha: DOT_BASE_ALPHA,
                        targetSize: DOT_BASE_SIZE,
                        targetAlpha: DOT_BASE_ALPHA,
                    });
                }
            }
        }
        resize();
        window.addEventListener('resize', resize);

        // Mouse tracking
        if (!isMobile) {
            hero.addEventListener('mousemove', (e) => {
                const rect = canvas.getBoundingClientRect();
                mouseX = e.clientX - rect.left;
                mouseY = e.clientY - rect.top;
            });
            hero.addEventListener('mouseleave', () => { mouseX = -1000; mouseY = -1000; });
        }

        // Touch support for mobile
        if (isMobile) {
            hero.addEventListener('touchmove', (e) => {
                const rect = canvas.getBoundingClientRect();
                const touch = e.touches[0];
                mouseX = touch.clientX - rect.left;
                mouseY = touch.clientY - rect.top;
            });
            hero.addEventListener('touchend', () => { mouseX = -1000; mouseY = -1000; });
        }

        function drawFrame() {
            ctx.clearRect(0, 0, w, h);

            dots.forEach(dot => {
                const dx = mouseX - dot.baseX;
                const dy = mouseY - dot.baseY;
                const dist = Math.sqrt(dx * dx + dy * dy);

                if (dist < MOUSE_RADIUS) {
                    const factor = 1 - (dist / MOUSE_RADIUS);
                    const eased = factor * factor; // quadratic easing
                    dot.targetSize = DOT_BASE_SIZE + (DOT_MAX_SIZE - DOT_BASE_SIZE) * eased;
                    dot.targetAlpha = DOT_BASE_ALPHA + (DOT_MAX_ALPHA - DOT_BASE_ALPHA) * eased;
                    // Subtle push away from mouse
                    const pushForce = eased * 3;
                    dot.x = dot.baseX - (dx / dist) * pushForce;
                    dot.y = dot.baseY - (dy / dist) * pushForce;
                } else {
                    dot.targetSize = DOT_BASE_SIZE;
                    dot.targetAlpha = DOT_BASE_ALPHA;
                    dot.x = dot.baseX;
                    dot.y = dot.baseY;
                }

                // Smooth interpolation
                dot.size += (dot.targetSize - dot.size) * 0.15;
                dot.alpha += (dot.targetAlpha - dot.alpha) * 0.15;

                // Draw dot
                ctx.beginPath();
                ctx.arc(dot.x, dot.y, dot.size, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(255, 86, 0, ${dot.alpha})`;
                ctx.fill();
            });

            // Draw subtle connections between activated dots
            if (!isMobile) {
                for (let i = 0; i < dots.length; i++) {
                    if (dots[i].alpha <= DOT_BASE_ALPHA + 0.05) continue;
                    for (let j = i + 1; j < dots.length; j++) {
                        if (dots[j].alpha <= DOT_BASE_ALPHA + 0.05) continue;
                        const dx = dots[i].x - dots[j].x;
                        const dy = dots[i].y - dots[j].y;
                        const dist = Math.sqrt(dx * dx + dy * dy);
                        if (dist < DOT_SPACING * 1.6) {
                            const lineAlpha = Math.min(dots[i].alpha, dots[j].alpha) * 0.3;
                            ctx.beginPath();
                            ctx.moveTo(dots[i].x, dots[i].y);
                            ctx.lineTo(dots[j].x, dots[j].y);
                            ctx.strokeStyle = `rgba(255, 86, 0, ${lineAlpha})`;
                            ctx.lineWidth = 0.5;
                            ctx.stroke();
                        }
                    }
                }
            }

            requestAnimationFrame(drawFrame);
        }
        drawFrame();

        // === 2. BADGE ENTRANCE (anime.js) ===
        anime.animate('.hero-model-badge', {
            opacity: [0, 0.85],
            translateY: [20, 0],
            delay: anime.stagger(200, { start: 800 }),
            duration: 800,
            easing: 'easeOutCubic',
        });

        // Gentle float for badges
        document.querySelectorAll('.hero-model-badge').forEach((badge, i) => {
            anime.animate(badge, {
                translateY: [-4, 4],
                duration: 3000 + i * 500,
                easing: 'easeInOutSine',
                direction: 'alternate',
                loop: true,
                delay: i * 300,
            });
        });

        // === 3. HERO CONTENT ENTRANCE ===
        anime.animate('.hero-badge', {
            opacity: [0, 1],
            translateY: [15, 0],
            duration: 600,
            easing: 'easeOutCubic',
            delay: 200,
        });

        anime.animate('.heading-display', {
            opacity: [0, 1],
            translateY: [30, 0],
            duration: 900,
            easing: 'easeOutCubic',
            delay: 350,
        });

        anime.animate('.hero-content .body-lg', {
            opacity: [0, 1],
            translateY: [15, 0],
            duration: 700,
            easing: 'easeOutCubic',
            delay: 600,
        });

        anime.animate('.hero-buttons', {
            opacity: [0, 1],
            translateY: [15, 0],
            duration: 700,
            easing: 'easeOutCubic',
            delay: 800,
        });

        // === 4. STATS COUNTER ===
        anime.animate('.hero-stat', {
            opacity: [0, 1],
            translateY: [20, 0],
            delay: anime.stagger(100, { start: 1000 }),
            duration: 600,
            easing: 'easeOutCubic',
        });

        document.querySelectorAll('.hero-stat-number[data-count]').forEach(el => {
            const target = parseInt(el.dataset.count);
            const obj = { val: 0 };
            anime.animate(obj, {
                val: target,
                duration: 1800,
                delay: 1100,
                easing: 'easeOutExpo',
                onUpdate: () => { el.textContent = Math.round(obj.val) + '+'; },
            });
        });

        // === 5. CODE PREVIEW REVEAL ===
        anime.animate('.hero-code-preview', {
            opacity: [0, 1],
            translateY: [20, 0],
            duration: 700,
            easing: 'easeOutCubic',
            delay: 1200,
        });

        const codeLines = document.querySelectorAll('.hero-code-line');
        anime.animate(codeLines, {
            opacity: [0, 1],
            translateX: [-10, 0],
            delay: anime.stagger(150, { start: 1400 }),
            duration: 500,
            easing: 'easeOutCubic',
        });

        // === 6. SUBTLE HOVER EFFECTS ===
        document.querySelectorAll('.hero-stat').forEach(stat => {
            stat.addEventListener('mouseenter', () => {
                anime.animate(stat, { scale: [1, 1.05], duration: 250, easing: 'easeOutCubic' });
            });
            stat.addEventListener('mouseleave', () => {
                anime.animate(stat, { scale: [1.05, 1], duration: 250, easing: 'easeOutCubic' });
            });
        });
    });
    </script>

    <!-- AOS replaced by anime.js scroll animations -->
    <script>
    // === SCROLL-TRIGGERED ANIMATIONS (anime.js) ===
    (function() {
        const observed = new Set();

        function onIntersect(entries, observer) {
            entries.forEach(entry => {
                if (!entry.isIntersecting || observed.has(entry.target.id || entry.target)) return;
                observed.add(entry.target.id || entry.target);
                const id = entry.target.id;

                // --- HOW IT WORKS SECTION ---
                if (id === 'section-steps') {
                    // Section heading
                    anime.animate('#section-steps .mono-label, #section-steps .heading-section', {
                        opacity: [0, 1],
                        translateY: [20, 0],
                        delay: anime.stagger(100),
                        duration: 600,
                        easing: 'easeOutCubic',
                    });

                    // Cards stagger entrance
                    anime.animate('#section-steps .anim-card', {
                        opacity: [0, 1],
                        translateY: [40, 0],
                        scale: [0.95, 1],
                        delay: anime.stagger(150, { start: 200 }),
                        duration: 700,
                        easing: 'easeOutCubic',
                    });

                    // Icon boxes pulse after cards appear
                    setTimeout(() => {
                        anime.animate('#section-steps .icon-box', {
                            scale: [1, 1.15, 1],
                            delay: anime.stagger(150, { start: 0 }),
                            duration: 600,
                            easing: 'easeInOutSine',
                        });
                    }, 700);
                }

                // --- SUPPORTED MODELS SECTION ---
                if (id === 'section-models') {
                    // Heading
                    anime.animate('#section-models .mono-label, #section-models .heading-section, #section-models .body-lg', {
                        opacity: [0, 1],
                        translateY: [20, 0],
                        delay: anime.stagger(80),
                        duration: 600,
                        easing: 'easeOutCubic',
                    });

                    // Provider logos slide in
                    const providerLogos = document.querySelectorAll('#section-models [style*="opacity: 0.7"]');
                    anime.animate(providerLogos, {
                        opacity: [0, 0.7],
                        translateX: [-20, 0],
                        delay: anime.stagger(100, { start: 300 }),
                        duration: 500,
                        easing: 'easeOutCubic',
                    });

                    // Model items cascade
                    const modelItems = document.querySelectorAll('#section-models .model-item');
                    anime.animate(modelItems, {
                        opacity: [0, 1],
                        translateY: [15, 0],
                        delay: anime.stagger(30, { start: 500 }),
                        duration: 400,
                        easing: 'easeOutCubic',
                    });
                }

                // --- PRICING SECTION ---
                if (id === 'pricing') {
                    anime.animate('#pricing .mono-label, #pricing .heading-section, #pricing .body-lg', {
                        opacity: [0, 1],
                        translateY: [20, 0],
                        delay: anime.stagger(80),
                        duration: 600,
                        easing: 'easeOutCubic',
                    });

                    // Pricing cards scale entrance
                    anime.animate('#pricing .pricing-card', {
                        opacity: [0, 1],
                        translateY: [30, 0],
                        scale: [0.92, 1],
                        delay: anime.stagger(120, { start: 300 }),
                        duration: 700,
                        easing: 'easeOutBack',
                    });
                }

                // --- COMPATIBLE TOOLS SECTION ---
                if (id === 'section-tools') {
                    anime.animate('#section-tools .mono-label, #section-tools .heading-section, #section-tools .body-lg', {
                        opacity: [0, 1],
                        translateY: [20, 0],
                        delay: anime.stagger(80),
                        duration: 600,
                        easing: 'easeOutCubic',
                    });

                    // Tool badges bounce in
                    anime.animate('#section-tools .tool-badge', {
                        opacity: [0, 1],
                        scale: [0.5, 1],
                        delay: anime.stagger(60, { start: 250 }),
                        duration: 500,
                        easing: 'easeOutBack',
                    });

                    // Code block slide up
                    anime.animate('#section-tools .code-block', {
                        opacity: [0, 1],
                        translateY: [25, 0],
                        duration: 700,
                        easing: 'easeOutCubic',
                        delay: 600,
                    });
                }

                // --- CTA SECTION ---
                if (id === 'section-cta') {
                    anime.animate('#section-cta .heading-sub', {
                        opacity: [0, 1],
                        translateY: [20, 0],
                        duration: 600,
                        easing: 'easeOutCubic',
                    });

                    anime.animate('#section-cta p', {
                        opacity: [0, 1],
                        translateY: [15, 0],
                        duration: 600,
                        easing: 'easeOutCubic',
                        delay: 150,
                    });

                    anime.animate('#section-cta .btn', {
                        opacity: [0, 1],
                        scale: [0.9, 1],
                        duration: 600,
                        easing: 'easeOutBack',
                        delay: 300,
                    });

                    // Subtle pulse loop on CTA button
                    setTimeout(() => {
                        anime.animate('#section-cta .btn', {
                            scale: [1, 1.03, 1],
                            duration: 2000,
                            easing: 'easeInOutSine',
                            loop: true,
                        });
                    }, 1000);
                }
            });
        }

        const observer = new IntersectionObserver(onIntersect, {
            threshold: 0.15,
            rootMargin: '0px 0px -50px 0px',
        });

        // Observe all sections
        document.querySelectorAll('#section-steps, #section-models, #pricing, #section-tools, #section-cta').forEach(el => {
            observer.observe(el);
        });

        // === INTERACTIVE HOVER EFFECTS (all sections) ===

        // Cards lift on hover
        document.querySelectorAll('.anim-card, .pricing-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                anime.animate(card, { translateY: -6, scale: 1.02, duration: 300, easing: 'easeOutCubic' });
            });
            card.addEventListener('mouseleave', () => {
                anime.animate(card, { translateY: 0, scale: 1, duration: 300, easing: 'easeOutCubic' });
            });
        });

        // Tool badges pop on hover
        document.querySelectorAll('.tool-badge').forEach(badge => {
            badge.addEventListener('mouseenter', () => {
                anime.animate(badge, { scale: 1.1, duration: 200, easing: 'easeOutCubic' });
            });
            badge.addEventListener('mouseleave', () => {
                anime.animate(badge, { scale: 1, duration: 200, easing: 'easeOutCubic' });
            });
        });

        // Model chips subtle glow on hover
        document.querySelectorAll('.model-item').forEach(item => {
            item.addEventListener('mouseenter', () => {
                anime.animate(item, { scale: 1.03, duration: 150, easing: 'easeOutCubic' });
            });
            item.addEventListener('mouseleave', () => {
                anime.animate(item, { scale: 1, duration: 150, easing: 'easeOutCubic' });
            });
        });
    })();
    </script>
</body>
</html>

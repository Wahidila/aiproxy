<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AIMurah - Sedang Pengerjaan</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f0f0f 0%, #1a1a2e 25%, #16213e 50%, #0f3460 75%, #1a1a2e 100%);
            color: #ffffff;
            -webkit-font-smoothing: antialiased;
            overflow: hidden;
            position: relative;
        }

        /* Animated background particles */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background:
                radial-gradient(2px 2px at 20% 30%, rgba(255,255,255,0.15), transparent),
                radial-gradient(2px 2px at 40% 70%, rgba(255,255,255,0.1), transparent),
                radial-gradient(2px 2px at 60% 20%, rgba(255,255,255,0.12), transparent),
                radial-gradient(2px 2px at 80% 50%, rgba(255,255,255,0.08), transparent),
                radial-gradient(2px 2px at 10% 80%, rgba(255,255,255,0.1), transparent),
                radial-gradient(2px 2px at 70% 90%, rgba(255,255,255,0.07), transparent),
                radial-gradient(2px 2px at 90% 10%, rgba(255,255,255,0.09), transparent);
            animation: twinkle 4s ease-in-out infinite alternate;
            pointer-events: none;
        }

        @keyframes twinkle {
            0% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .container {
            text-align: center;
            padding: 40px 24px;
            max-width: 560px;
            position: relative;
            z-index: 1;
        }

        /* Animated gear icon */
        .icon-wrapper {
            margin-bottom: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 86, 0, 0.1);
            border: 1px solid rgba(255, 86, 0, 0.2);
            animation: pulse-ring 2.5s ease-in-out infinite;
        }

        .icon-wrapper svg {
            width: 56px;
            height: 56px;
            color: #ff5600;
            animation: spin-slow 8s linear infinite;
        }

        @keyframes spin-slow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes pulse-ring {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(255, 86, 0, 0.15), 0 0 40px rgba(255, 86, 0, 0.05);
            }
            50% {
                box-shadow: 0 0 0 20px rgba(255, 86, 0, 0), 0 0 60px rgba(255, 86, 0, 0.1);
            }
        }

        h1 {
            font-size: clamp(32px, 6vw, 48px);
            font-weight: 700;
            letter-spacing: -1.5px;
            line-height: 1.1;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #ffffff 0%, #ff5600 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            font-size: clamp(16px, 3vw, 20px);
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 48px;
            letter-spacing: -0.2px;
        }

        /* Countdown section */
        .countdown-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }

        .countdown-label {
            font-family: 'Inter', monospace;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.4);
        }

        .countdown-number {
            font-size: 64px;
            font-weight: 800;
            letter-spacing: -3px;
            color: #ff5600;
            line-height: 1;
            font-variant-numeric: tabular-nums;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .countdown-number.tick {
            transform: scale(1.2);
            opacity: 0.7;
        }

        .redirect-text {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.35);
            margin-top: 8px;
        }

        /* Login button for existing users */
        .login-section {
            margin-top: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .login-hint {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.4);
        }

        .btn-login {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 32px;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 600;
            color: #ffffff;
            background: linear-gradient(135deg, #ff5600 0%, #ff8c42 100%);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(255, 86, 0, 0.3);
            letter-spacing: -0.2px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 30px rgba(255, 86, 0, 0.45);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login svg {
            width: 18px;
            height: 18px;
            animation: none;
        }

        /* Progress bar */
        .progress-bar-wrapper {
            width: 200px;
            height: 3px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            overflow: hidden;
            margin-top: 8px;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #ff5600, #ff8c42);
            border-radius: 3px;
            transition: width 1s linear;
            width: 0%;
        }

        /* Fade out animation */
        .fade-out {
            animation: fadeOut 0.5s ease forwards;
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: scale(0.98);
            }
        }
    </style>
</head>
<body>
    <div class="container" id="main-container">
        <div class="icon-wrapper">
            <!-- Gear/Cog SVG icon -->
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
        </div>

        <h1>Sedang Pengerjaan</h1>
        <p class="subtitle">
            Halaman ini sedang dalam proses pengembangan.<br>
            Anda akan dialihkan secara otomatis.
        </p>

        <div class="countdown-section">
            <span class="countdown-label">Redirect dalam</span>
            <div class="countdown-number" id="countdown">7</div>
            <div class="progress-bar-wrapper">
                <div class="progress-bar" id="progress-bar"></div>
            </div>
            <span class="redirect-text">detik</span>
        </div>

        <div class="login-section">
            <span class="login-hint">Sudah punya akun sebelumnya?</span>
            <a href="https://aimurah.my.id/login" class="btn-login">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                    <polyline points="10 17 15 12 10 7"/>
                    <line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                Masuk ke Akun
            </a>
        </div>
    </div>

    <script>
        (function() {
            const TOTAL_SECONDS = 7;
            const REDIRECT_URL = 'https://aimurah.my.id/login';

            let remaining = TOTAL_SECONDS;
            const countdownEl = document.getElementById('countdown');
            const progressBar = document.getElementById('progress-bar');
            const container = document.getElementById('main-container');

            // Start progress bar animation
            requestAnimationFrame(function() {
                progressBar.style.width = '100%';
                progressBar.style.transition = 'width ' + TOTAL_SECONDS + 's linear';
            });

            const interval = setInterval(function() {
                remaining--;

                // Tick animation
                countdownEl.classList.add('tick');
                setTimeout(function() {
                    countdownEl.classList.remove('tick');
                }, 200);

                countdownEl.textContent = remaining;

                if (remaining <= 0) {
                    clearInterval(interval);
                    container.classList.add('fade-out');
                    setTimeout(function() {
                        window.location.href = REDIRECT_URL;
                    }, 500);
                }
            }, 1000);
        })();
    </script>
</body>
</html>

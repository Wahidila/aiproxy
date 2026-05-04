<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kebijakan Privasi - AIMurah</title>
    <meta name="description" content="Kebijakan Privasi (Privacy Policy) platform AIMurah - AI API proxy termurah di Indonesia.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
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
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
            color: var(--color-text);
            background: var(--color-canvas);
            margin: 0;
            -webkit-font-smoothing: antialiased;
            line-height: 1.7;
        }
        a { color: var(--color-accent); text-decoration: none; transition: color 0.2s; }
        a:hover { color: var(--color-accent-hover); }
        .nav {
            position: sticky; top: 0; z-index: 100;
            background: rgba(10,10,15,0.85); backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--color-border);
        }
        .nav-inner {
            max-width: 720px; margin: 0 auto; padding: 0 24px;
            display: flex; align-items: center; justify-content: space-between; height: 64px;
        }
        .nav-brand {
            font-size: 20px; font-weight: 700; color: var(--color-text);
            text-decoration: none; letter-spacing: -0.5px;
        }
        .nav-back {
            font-size: 14px; color: var(--color-muted); text-decoration: none;
            transition: color 0.2s;
        }
        .nav-back:hover { color: var(--color-accent); }
        .content {
            max-width: 720px; margin: 0 auto; padding: 64px 24px 80px;
        }
        h1 {
            font-size: clamp(28px, 4vw, 40px); font-weight: 600;
            letter-spacing: -1.2px; line-height: 1.1; margin: 0 0 8px;
        }
        .subtitle {
            font-size: 14px; color: var(--color-muted); margin: 0 0 48px;
        }
        h2 {
            font-size: 20px; font-weight: 600; letter-spacing: -0.5px;
            margin: 48px 0 16px; color: var(--color-text);
            padding-bottom: 8px; border-bottom: 1px solid var(--color-border);
        }
        h3 {
            font-size: 16px; font-weight: 600; margin: 24px 0 8px;
            color: var(--color-text);
        }
        p, li {
            font-size: 15px; color: var(--color-muted); line-height: 1.7;
            margin: 0 0 12px;
        }
        ul, ol {
            padding-left: 20px; margin: 0 0 16px;
        }
        li { margin-bottom: 8px; }
        strong { color: var(--color-text); font-weight: 600; }
        .highlight-box {
            background: var(--color-surface); border: 1px solid var(--color-border);
            border-radius: 8px; padding: 16px 20px; margin: 16px 0;
        }
        .highlight-box.important {
            border-color: rgba(34, 197, 94, 0.3);
            background: rgba(34, 197, 94, 0.05);
        }
        .footer {
            border-top: 1px solid var(--color-border);
            padding: 32px 24px; text-align: center;
        }
        .footer-inner {
            max-width: 720px; margin: 0 auto;
        }
        .footer-links {
            display: flex; justify-content: center; gap: 24px;
            flex-wrap: wrap; margin-bottom: 16px;
        }
        .footer-links a {
            font-size: 13px; color: var(--color-muted); text-decoration: none;
        }
        .footer-links a:hover { color: var(--color-accent); }
        .footer-copy {
            font-size: 12px; color: #4a4a5e; margin: 0;
        }
        @media (max-width: 640px) {
            .content { padding: 40px 16px 60px; }
            h1 { font-size: 26px; }
        }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="nav-inner">
            <a href="/" class="nav-brand">AIMurah</a>
            <a href="/" class="nav-back">&larr; Kembali</a>
        </div>
    </nav>

    <div class="content">
        <h1>Kebijakan Privasi</h1>
        <p class="subtitle">Terakhir diperbarui: 4 Mei 2026</p>

        <p>AIMurah (aimurah.my.id) berkomitmen untuk melindungi privasi pengguna. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi Anda.</p>

        <h2>1. Data yang Kami Kumpulkan</h2>
        <p>Kami mengumpulkan data berikut saat Anda menggunakan layanan AIMurah:</p>

        <h3>Data Akun</h3>
        <ul>
            <li><strong>Nama lengkap</strong> &mdash; dari profil Google atau input manual saat pendaftaran</li>
            <li><strong>Alamat email</strong> &mdash; dari Google OAuth atau input manual</li>
            <li><strong>Foto profil</strong> &mdash; dari Google OAuth (jika tersedia)</li>
        </ul>

        <h3>Data Penggunaan (Usage Logs)</h3>
        <ul>
            <li>Jumlah API call yang dilakukan</li>
            <li>Model AI yang digunakan per request</li>
            <li>Jumlah token (input dan output) per request</li>
            <li>Timestamp setiap request</li>
            <li>Status response (berhasil/gagal)</li>
            <li>IP address untuk keamanan dan rate limiting</li>
        </ul>

        <h2>2. Data yang TIDAK Kami Kumpulkan</h2>
        <div class="highlight-box important">
            <p style="margin-bottom: 8px;"><strong>Kami TIDAK menyimpan konten request atau response API Anda.</strong></p>
            <p style="margin: 0;">Prompt, pesan, dan completion yang Anda kirim/terima melalui API <strong>tidak di-log, tidak disimpan, dan tidak dibaca</strong> oleh AIMurah. Data mengalir langsung ke provider AI (Anthropic, OpenAI, Google) tanpa penyimpanan di sisi kami.</p>
        </div>

        <h2>3. Cookies</h2>
        <p>AIMurah hanya menggunakan <strong>session cookies</strong> yang diperlukan untuk:</p>
        <ul>
            <li>Menjaga sesi login Anda tetap aktif</li>
            <li>Perlindungan CSRF (Cross-Site Request Forgery)</li>
        </ul>
        <p>Kami <strong>tidak menggunakan</strong> tracking cookies, analytics cookies pihak ketiga, atau advertising cookies.</p>

        <h2>4. Pihak Ketiga</h2>
        <p>Layanan pihak ketiga yang terintegrasi dengan AIMurah:</p>
        <ul>
            <li><strong>Google OAuth</strong> &mdash; untuk autentikasi login. Google menerima informasi bahwa Anda login ke AIMurah. Lihat <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Kebijakan Privasi Google</a>.</li>
            <li><strong>Provider AI (Anthropic, OpenAI, Google AI)</strong> &mdash; request API Anda diteruskan ke provider ini. Masing-masing memiliki kebijakan privasi tersendiri.</li>
        </ul>
        <p>Kami tidak menjual, menyewakan, atau membagikan data pribadi Anda kepada pihak ketiga untuk tujuan pemasaran.</p>

        <h2>5. Retensi Data</h2>
        <ul>
            <li><strong>Data akun:</strong> disimpan selama akun Anda aktif</li>
            <li><strong>Usage logs:</strong> disimpan selama <strong>90 hari</strong>, kemudian dihapus secara otomatis</li>
            <li><strong>Data transaksi/donasi:</strong> disimpan sesuai kewajiban hukum perpajakan Indonesia</li>
        </ul>

        <h2>6. Keamanan Data</h2>
        <p>Kami menerapkan langkah-langkah keamanan untuk melindungi data Anda:</p>
        <ul>
            <li>Enkripsi HTTPS/TLS untuk semua komunikasi</li>
            <li>API key di-hash sebelum disimpan di database</li>
            <li>Akses database dibatasi dan dimonitor</li>
            <li>Server berlokasi dengan perlindungan firewall</li>
        </ul>

        <h2>7. Hak Pengguna</h2>
        <p>Anda memiliki hak untuk:</p>
        <ul>
            <li><strong>Mengakses data Anda</strong> &mdash; melihat data yang kami simpan tentang Anda melalui dashboard</li>
            <li><strong>Memperbarui data</strong> &mdash; mengubah nama dan informasi profil melalui halaman profil</li>
            <li><strong>Menghapus akun</strong> &mdash; meminta penghapusan seluruh data Anda</li>
            <li><strong>Ekspor data</strong> &mdash; mengunduh riwayat penggunaan Anda</li>
        </ul>
        <p>Untuk meminta penghapusan data atau akun, silakan hubungi kami melalui halaman Support di dashboard atau email ke <a href="mailto:support@aimurah.my.id">support@aimurah.my.id</a>.</p>

        <h2>8. Perubahan Kebijakan</h2>
        <p>Kami dapat memperbarui Kebijakan Privasi ini sewaktu-waktu. Perubahan signifikan akan diberitahukan melalui email atau notifikasi di dashboard. Tanggal "Terakhir diperbarui" di bagian atas halaman ini akan diubah sesuai.</p>

        <h2>9. Kontak</h2>
        <p>Untuk pertanyaan terkait privasi data Anda, hubungi kami di:</p>
        <ul>
            <li>Email: <a href="mailto:support@aimurah.my.id">support@aimurah.my.id</a></li>
            <li>Telegram: <a href="https://t.me/aimurah" target="_blank">t.me/aimurah</a></li>
        </ul>
    </div>

    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-links">
                <a href="/">Beranda</a>
                <a href="/terms">Ketentuan Layanan</a>
                <a href="/privacy">Kebijakan Privasi</a>
                <a href="/donation-policy">Ketentuan Donasi</a>
            </div>
            <p class="footer-copy">&copy; {{ date('Y') }} AIMurah. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ketentuan Layanan - AIMurah</title>
    <meta name="description" content="Ketentuan Layanan (Terms of Service) platform AIMurah - AI API proxy termurah di Indonesia.">
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
        code {
            background: var(--color-surface-elevated); padding: 2px 6px;
            border-radius: 4px; font-size: 13px; color: var(--color-accent);
        }
        .highlight-box {
            background: var(--color-surface); border: 1px solid var(--color-border);
            border-radius: 8px; padding: 16px 20px; margin: 16px 0;
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
        <h1>Ketentuan Layanan</h1>
        <p class="subtitle">Terakhir diperbarui: 4 Mei 2026</p>

        <h2>1. Penerimaan Ketentuan</h2>
        <p>Dengan mengakses atau menggunakan layanan AIMurah (aimurah.my.id), Anda menyetujui untuk terikat oleh Ketentuan Layanan ini. Jika Anda tidak menyetujui ketentuan ini, mohon untuk tidak menggunakan layanan kami.</p>
        <p>AIMurah adalah platform AI API proxy yang menyediakan akses ke berbagai model AI melalui satu endpoint API yang kompatibel dengan standar OpenAI.</p>

        <h2>2. Pendaftaran Akun</h2>
        <p>Untuk menggunakan layanan AIMurah, Anda harus:</p>
        <ul>
            <li>Mendaftar menggunakan akun Google (OAuth) atau melalui formulir pendaftaran manual</li>
            <li>Memberikan informasi yang akurat dan lengkap</li>
            <li>Menjaga keamanan API key Anda dan tidak membagikannya kepada pihak lain</li>
            <li>Bertanggung jawab penuh atas semua aktivitas yang terjadi di akun Anda</li>
        </ul>
        <p>Anda harus berusia minimal 17 tahun atau memiliki izin dari orang tua/wali untuk menggunakan layanan ini.</p>

        <h2>3. Penggunaan API</h2>
        <p>Layanan AIMurah menyediakan akses API ke berbagai model AI. Penggunaan API harus mematuhi ketentuan berikut:</p>
        <ul>
            <li>API key bersifat pribadi dan tidak boleh dibagikan atau dijual kembali</li>
            <li>Dilarang menggunakan layanan untuk aktivitas ilegal, berbahaya, atau melanggar hukum</li>
            <li>Dilarang melakukan reverse engineering atau mencoba mengakses sistem internal</li>
            <li>Dilarang menggunakan bot atau script otomatis yang bertujuan menyalahgunakan layanan</li>
        </ul>

        <h2>4. Fair Use Policy</h2>
        <p>AIMurah menerapkan kebijakan penggunaan wajar untuk memastikan kualitas layanan bagi semua pengguna:</p>
        <ul>
            <li><strong>Dilarang menjual kembali</strong> akses API AIMurah kepada pihak ketiga</li>
            <li><strong>Dilarang melakukan abuse</strong> seperti flooding, spamming, atau penggunaan berlebihan yang mengganggu layanan</li>
            <li>Penggunaan harus sesuai dengan tujuan yang wajar (coding, analisis, produktivitas)</li>
            <li>AIMurah berhak membatasi atau menangguhkan akun yang terdeteksi melakukan penyalahgunaan</li>
        </ul>

        <h2>5. Rate Limits</h2>
        <p>Setiap plan memiliki batasan rate limit yang diberlakukan secara ketat:</p>
        <div class="highlight-box">
            <ul style="margin-bottom: 0;">
                <li><strong>FREE:</strong> 6 request per menit (rpm)</li>
                <li><strong>PRO:</strong> 30 request per menit (rpm)</li>
                <li><strong>PREMIUM:</strong> 90 request per menit (rpm)</li>
                <li><strong>Harian:</strong> 60 request per menit (rpm)</li>
                <li><strong>Harian Kenyang:</strong> 90 request per menit (rpm)</li>
            </ul>
        </div>
        <p>Melebihi rate limit akan menghasilkan response error <code>429 Too Many Requests</code>. Batasan harian dan token juga berlaku sesuai plan yang dipilih.</p>

        <h2>6. Layanan "As-Is"</h2>
        <p>Layanan AIMurah disediakan <strong>"sebagaimana adanya" (as-is)</strong> tanpa jaminan apapun, baik tersurat maupun tersirat. Secara khusus:</p>
        <ul>
            <li>Kami <strong>tidak memberikan SLA (Service Level Agreement)</strong> atau jaminan uptime</li>
            <li>Layanan dapat mengalami downtime untuk maintenance atau karena faktor di luar kendali kami</li>
            <li>Ketersediaan model AI tertentu bergantung pada provider upstream</li>
            <li>Kami tidak menjamin akurasi atau kualitas output dari model AI</li>
        </ul>

        <h2>7. Penangguhan dan Penghentian Akun</h2>
        <p>AIMurah berhak untuk menangguhkan atau menghentikan akun Anda tanpa pemberitahuan sebelumnya jika:</p>
        <ul>
            <li>Anda melanggar Ketentuan Layanan ini</li>
            <li>Terdeteksi aktivitas penyalahgunaan atau fraud</li>
            <li>Penggunaan Anda membahayakan infrastruktur atau pengguna lain</li>
            <li>Anda menjual kembali akses API tanpa izin</li>
            <li>Diwajibkan oleh hukum atau regulasi yang berlaku</li>
        </ul>
        <p>Dalam hal penangguhan, saldo yang tersisa di akun Anda <strong>tidak dapat di-refund</strong>.</p>

        <h2>8. Batasan Tanggung Jawab</h2>
        <p>Sejauh diizinkan oleh hukum yang berlaku:</p>
        <ul>
            <li>AIMurah tidak bertanggung jawab atas kerugian langsung, tidak langsung, insidental, atau konsekuensial yang timbul dari penggunaan layanan</li>
            <li>Total tanggung jawab AIMurah tidak akan melebihi jumlah yang Anda bayarkan dalam 30 hari terakhir</li>
            <li>AIMurah tidak bertanggung jawab atas konten yang dihasilkan oleh model AI</li>
            <li>Anda bertanggung jawab penuh atas penggunaan output AI dalam proyek atau produk Anda</li>
        </ul>

        <h2>9. Perubahan Ketentuan</h2>
        <p>AIMurah dapat mengubah Ketentuan Layanan ini sewaktu-waktu. Perubahan akan diberitahukan melalui:</p>
        <ul>
            <li>Email ke alamat yang terdaftar di akun Anda</li>
            <li>Notifikasi di dashboard AIMurah</li>
        </ul>
        <p>Penggunaan layanan setelah perubahan berlaku dianggap sebagai persetujuan Anda terhadap ketentuan yang baru.</p>

        <h2>10. Hukum yang Berlaku</h2>
        <p>Ketentuan Layanan ini diatur oleh dan ditafsirkan sesuai dengan hukum Republik Indonesia. Segala sengketa yang timbul akan diselesaikan melalui musyawarah terlebih dahulu, dan jika tidak tercapai kesepakatan, akan diselesaikan melalui pengadilan yang berwenang di Indonesia.</p>

        <h2>11. Kontak</h2>
        <p>Jika Anda memiliki pertanyaan mengenai Ketentuan Layanan ini, silakan hubungi kami melalui:</p>
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

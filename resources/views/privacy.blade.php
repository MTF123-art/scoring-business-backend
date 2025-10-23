<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscore | Kebijakan Privasi</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fb; color: #111827; margin: 0; padding: 0; }
        .container { max-width: 860px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.06); padding: 32px; }
        h1 { font-size: 28px; margin: 0 0 8px 0; }
        .logo { margin-bottom: 12px; }
        .logo img { height: 44px; display: block; }
        .muted { color: #6b7280; margin: 0 0 16px 0; font-size: 14px; }
        h2 { font-size: 18px; margin-top: 24px; }
        p, li { font-size: 14px; line-height: 1.7; }
        ul { margin-left: 1.25rem; }
        code { background:#f3f4f6; padding:2px 6px; border-radius:4px; font-size: 13px; }
        .section { margin-top: 20px; }
        .card { background:#f9fafb; border:1px solid #e5e7eb; padding:12px 14px; border-radius:10px; }
        .brand { color: #2d6cdf; font-weight: bold; }
        .small { font-size: 12px; color: #6b7280; }
    </style>
    <meta name="robots" content="noindex,follow">
</head>
<body>
<div class="container">
    <div class="logo">
        <img src="{{ asset('Logo Inscore.png') }}" alt="Logo Inscore">
    </div>
    <h1>Kebijakan Privasi <span class="brand">Inscore</span></h1>
    <p class="muted">Terakhir diperbarui: {{ now()->toDateString() }}</p>

    <p>Kebijakan Privasi ini menjelaskan bagaimana <strong>Inscore</strong> mengumpulkan, menggunakan, dan melindungi data pribadi Anda.</p>

    <div class="section">
        <h2>1) Data yang Kami Kumpulkan</h2>
        <ul>
            <li><strong>Data Akun Inscore</strong>: nama, alamat email, kata sandi (disimpan dalam bentuk terenkripsi), serta foto profil/avatar yang Anda unggah.</li>
            <li><strong>Data Akun Meta (Facebook/Instagram)</strong> setelah Anda memberikan izin melalui proses OAuth:
                <ul>
                    <li>ID akun/page/profil, nama akun/page.</li>
                    <li>Token akses (termasuk token jangka panjang) dan tanggal kedaluwarsanya, yang digunakan untuk mengambil data dari API Meta.</li>
                    <li>Metrik konten/akun yang disediakan Meta, seperti: jumlah pengikut (followers), jumlah posting/media, likes, comments, shares, reach, serta metrik turunan (engagement rate, reach ratio, engagement per post) yang kami hitung.</li>
                </ul>
            </li>
            <li><strong>Data Aplikasi</strong>:
                <ul>
                    <li>Skor harian/mingguan dan perhitungan leaderboard berdasarkan metrik yang diambil.</li>
                    <li>Log aplikasi dasar untuk keperluan operasional dan keamanan.</li>
                </ul>
            </li>
        </ul>
        <p class="small">Catatan: Kami <strong>tidak</strong> menyimpan kredensial login Facebook/Instagram Anda. Akses dilakukan menggunakan token yang Anda setujui melalui Meta OAuth.</p>
    </div>

    <div class="section">
        <h2>2) Bagaimana Data Digunakan</h2>
        <ul>
            <li>Menyediakan fitur utama aplikasi: pengambilan metrik sosial (Facebook/Instagram), perhitungan skor harian/mingguan, tampilan dashboard/home, dan leaderboard.</li>
            <li>Autentikasi dan keamanan akun, termasuk pengiriman email transaksional seperti reset password.</li>
            <li>Pemeliharaan, analitik terbatas, dan peningkatan kualitas layanan.</li>
        </ul>
        <p>Kami <strong>tidak</strong> menggunakan data Anda untuk penjualan data atau periklanan bertarget pihak ketiga.</p>
    </div>

    <div class="section">
        <h2>3) Dengan Siapa Data Dibagikan</h2>
        <ul>
            <li><strong>Tidak dijual</strong> ke pihak ketiga mana pun.</li>
            <li>Dapat dibagikan secara terbatas kepada penyedia layanan yang kami gunakan untuk mengoperasikan aplikasi (misal layanan email/SMTP dan infrastruktur hosting) dengan kewajiban kerahasiaan yang setara.</li>
            <li>Platform Meta (Facebook/Instagram) hanya menerima permintaan API untuk <em>mengambil</em> data atas izin Anda; kami tidak mengirimkan kembali data pribadi Anda ke Meta selain yang diperlukan untuk proses otorisasi standar.</li>
            <li>Data agregat/anonymized (tanpa identitas pribadi) dapat digunakan untuk analitik internal.</li>
        </ul>
    </div>

    <div class="section">
        <h2>4) Cara Menghapus/Mencabut Data</h2>
        <div class="card">
            <p><strong>Mencabut Koneksi Akun Meta:</strong></p>
            <ul>
                <li>Putuskan akun Instagram: <code>GET /api/instagram/disconnect</code></li>
                <li>Putuskan akun Facebook: <code>GET /api/facebook/disconnect</code></li>
            </ul>
            <p>Tindakan ini akan menghentikan pengambilan data baru dari platform terkait dan menghapus kredensial/token akses yang kami simpan untuk akun tersebut.</p>
        </div>
        <div class="card" style="margin-top:12px;">
            <p><strong>Meminta Penghapusan Akun Inscore & Data Terkait:</strong></p>
            <ul>
                <li>Kirimkan permintaan ke <a href="mailto:team.inscore@gmail.com">team.inscore@gmail.com</a> dengan subjek: <em>Permintaan Hapus Akun</em>.</li>
                <li>Sertakan alamat email akun Inscore Anda dan bukti kepemilikan jika diminta.</li>
            </ul>
            <p>Setelah verifikasi, kami akan menghapus akun Inscore Anda beserta data terkait (social account, metrik, skor, avatar) paling lambat 30 hari kerja, kecuali jika ada kewajiban hukum untuk menyimpan sebagian data tertentu.</p>
        </div>
        <p class="small">Anda juga dapat mencabut izin aplikasi langsung dari pengaturan Facebook/Instagram (Meta) Anda.</p>
    </div>

    <div class="section">
        <h2>5) Keamanan & Penyimpanan</h2>
        <ul>
            <li>Kata sandi akun Inscore disimpan dalam bentuk terenkripsi (hashed).</li>
            <li>Token akses Meta disimpan secara aman dan hanya digunakan untuk mengambil metrik sesuai izin yang Anda berikan.</li>
            <li>Avatar/foto profil yang Anda unggah disimpan di penyimpanan aplikasi yang tidak dapat diakses publik secara langsung.</li>
        </ul>
    </div>

    <div class="section">
        <h2>6) Perubahan Kebijakan</h2>
        <p>Kebijakan ini dapat diperbarui sewaktu-waktu. Perubahan material akan kami informasikan melalui aplikasi atau kanal resmi kami.</p>
    </div>

    <div class="section">
        <h2>7) Kontak</h2>
        <p>Jika ada pertanyaan atau permintaan terkait privasi, silakan hubungi: <a href="mailto:team.inscore@gmail.com">team.inscore@gmail.com</a>.</p>
    </div>
</div>
</body>
</html>

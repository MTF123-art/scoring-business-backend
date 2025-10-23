<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Inscore | Selamat Datang</title>
	<style>
		body { font-family: Arial, sans-serif; background: #f5f7fb; color: #111827; margin: 0; padding: 0; }
		.container { max-width: 860px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.06); padding: 32px; }
		h1 { font-size: 28px; margin: 0 0 8px 0; }
		.logo { margin-bottom: 12px; }
		.logo img { height: 44px; display: block; }
		.muted { color: #6b7280; margin: 0 0 16px 0; font-size: 14px; }
		p, li { font-size: 14px; line-height: 1.7; }
		ul { margin: 8px 0 0 1.25rem; }
		.card { background:#f9fafb; border:1px solid #e5e7eb; padding:12px 14px; border-radius:10px; }
		.brand { color: #2d6cdf; font-weight: 700; }
		a { color: #2563eb; text-decoration: none; }
		a:hover { text-decoration: underline; }
		.links { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 16px; }
		.link-btn { display:inline-block; padding:8px 12px; border:1px solid #e5e7eb; border-radius:8px; background:#fff; font-size:14px; }
		.primary-btn { display:inline-block; padding:10px 14px; border-radius:8px; background:#2563eb; color:#fff; border:1px solid #1d4ed8; font-size:14px; }
		.primary-btn:hover { background:#1d4ed8; text-decoration: none; }
		.btn-row { display:flex; gap:12px; flex-wrap:wrap; margin-top:10px; }
		.badge { display:inline-block; padding:2px 8px; font-size:12px; border:1px solid #e5e7eb; border-radius:999px; color:#6b7280; background:#fff; }
	</style>
</head>
<body>
<div class="container">
	<div class="logo">
		<img src="{{ asset('Logo Inscore.png') }}" alt="Logo Inscore">
	</div>
	<h1>Selamat Datang di <span class="brand">Inscore</span></h1>
	<p class="muted">Backend layanan sudah berjalan. Gunakan API yang tersedia atau hubungkan akun sosial Anda melalui aplikasi klien.</p>

	<div class="card" style="margin-top:12px;">
		<p><strong>Tentang Aplikasi Inscore</strong></p>
		<p>
			Inscore adalah aplikasi penilaian bisnis berbasis Flutter dengan backend Laravel untuk membantu
			UMKM — khususnya pelaku usaha kreatif — membuktikan kredibilitas dan potensi pertumbuhan mereka.
			Berbeda dari platform penilaian konvensional, Inscore mengevaluasi <em>aset non-fisik</em> dan metrik digital
			seperti performa media sosial, interaksi pelanggan, dan portofolio digital.
		</p>
		<ul>
			<li>Menjembatani kesenjangan akses pendanaan bagi UMKM yang minim agunan fisik.</li>
			<li>Mengukur metrik sosial (followers, engagement, reach) dan menurunkannya menjadi skor holistik.</li>
			<li>Menyediakan dashboard performa dan leaderboard untuk transparansi dan pembelajaran.</li>
			<li>Mendorong inklusi keuangan dengan bukti data yang dapat diverifikasi.</li>
		</ul>
	</div>

	<div class="card" style="margin-top:12px;">
		<p><strong>Download Aplikasi Inscore</strong></p>
		<div class="btn-row">
			<a class="primary-btn" href="{{ asset('downloads/inscore-latest.apk') }}" download>Download APK (Android)</a>
		</div>
	</div>

	<p class="muted" style="margin-top:16px;">
		Kontak: <a href="mailto:team.inscore@gmail.com">team.inscore@gmail.com</a>
		&nbsp;•&nbsp;
		<a href="/privacy">Kebijakan Privasi</a>
	</p>
</div>
</body>
</html>

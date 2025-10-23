<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscore | Status Koneksi</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fb; color: #111827; margin: 0; padding: 0; }
        .container { max-width: 700px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.06); padding: 28px 28px; }
        h1 { font-size: 22px; margin: 0 0 8px 0; }
        .logo { margin-bottom: 10px; }
        .logo img { height: 40px; display: block; }
        .muted { color: #6b7280; font-size: 14px; margin: 0 0 16px 0; }
        .card { background:#f9fafb; border:1px solid #e5e7eb; padding:12px 14px; border-radius:10px; margin: 14px 0; }
        .brand { color: #2d6cdf; font-weight: 700; }
    </style>
</head>
<body>
<div class="container">
    <div class="logo">
        <img src="{{ asset('Logo Inscore.png') }}" alt="Logo Inscore">
    </div>
    <h1>Status Koneksi <span class="brand">Inscore</span></h1>
    <p class="muted">Halaman ini menampilkan informasi singkat setelah proses koneksi akun sosial selesai.</p>

    <div class="card">
        {{ $message ?? 'Status tidak diketahui.' }}
    </div>

    <p class="muted">Silakan kembali ke aplikasi dan lakukan <strong>refresh</strong> pada halaman utama untuk melihat pembaruan.</p>
</div>
</body>
</html>

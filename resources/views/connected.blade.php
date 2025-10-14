<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Inscore | Status Koneksi</title>
	<style>
		body { font-family: system-ui, Arial, sans-serif; background: #f7fafc; color: #222; margin: 0; padding: 0; }
		.container { max-width: 420px; margin: 60px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px #0001; padding: 32px 22px; text-align: center; }
		.status { font-size: 1.2em; margin-bottom: 18px; }
		.msg { font-size: 1.05em; margin-bottom: 22px; color: #444; }
		.instruksi { color: #666; font-size: 0.98em; margin-bottom: 18px; }
		.refresh { display: inline-block; margin-top: 10px; padding: 10px 18px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-size: 1em; cursor: pointer; text-decoration: none; }
		.refresh:active { background: #1d4ed8; }
	</style>
</head>
<body>
	<div class="container">
		<div class="status"><b>Inscore</b></div>
		<div class="msg">{{ $message ?? 'Status tidak diketahui.' }}</div>
		<div class="instruksi">
			Silakan kembali ke aplikasi dan lakukan <b>refresh</b> pada halaman.<br>
			Jika halaman ini tidak menutup otomatis, Anda bisa menutup tab ini secara manual.
		</div>
		<button class="refresh" onclick="window.close()">Tutup Halaman</button>
	</div>
</body>
</html>

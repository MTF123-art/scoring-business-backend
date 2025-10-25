# inscore

Backend API untuk aplikasi penilaian (scoring) performa akun media sosial Inscore. Sistem ini mengumpulkan metrik dari platform (mis. Instagram, Facebook), melakukan normalisasi dan pembobotan, lalu menghasilkan skor yang konsisten untuk digunakan di aplikasi klien.

## Fitur Utama

-   Autentikasi & otorisasi (Laravel Sanctum)
-   Manajemen pengguna dan akun media sosial (Instagram/Facebook)
-   Pengambilan dan normalisasi metrik (ER, RR, Exposure per Post, dsb.)
-   Perhitungan skor berbobot melalui `ScoreService`
-   Home Screen API untuk ringkasan performa
-   Reset kata sandi via email (temporary password) dengan template HTML kustom
-   Halaman publik: Welcome, Connected, dan Kebijakan Privasi (sesuai pedoman Meta)
-   Tombol unduh APK di halaman welcome yang aktif hanya bila file tersedia
-   Antrian & penjadwalan (cron) siap dikonfigurasi untuk pekerjaan berkala
-   Logging terstruktur dan konfigurasi melalui environment (.env)

## Program BEKUP & Tim

-   Program: BEKUP Create
-   Kelompok: B25-PG007
-   Anggota Kelompok:
    -   BC25B017 — Ahmad Muqtafi
    -   BC25B018 — Bintoro
    -   BC25B069 — Muhammad Zulkifly Al Firdausy
    -   BC25B072 — Farras Abdulaziz El-Fahd

## Teknologi & Arsitektur Singkat

-   Framework: Laravel 12 (PHP 8.2+)
-   Autentikasi: Laravel Sanctum
-   Basis data: MySQL/PostgreSQL (sesuaikan `.env`)
-   Mailer: SMTP (konfigurasi di `.env`)
-   Eloquent Models: `User`, `SocialAccount`, `Metric`, `Score`
-   Services: `ScoreService`, `InstagramService`, `FacebookService`
-   Infrastruktur: Dockerfile tersedia, cron (laravel-cron), supervisord

## Cara Menjalankan (Windows PowerShell)

Prasyarat: PHP 8.2+, Composer, database (MySQL/PostgreSQL), dan ekstensi PHP standar.

```powershell
# 1) Instal dependensi
composer install

# 2) Salin dan atur environment
Copy-Item .env.example .env

# 3) Atur kredensial di .env (DB_*, MAIL_*, APP_URL)

# 4) Generate app key
php artisan key:generate

# 5) Migrasi database (tambahkan --seed bila ingin seeder berjalan)
php artisan migrate

# 6) Jalankan server pengembangan
php artisan serve
```

Opsional:

-   Jalankan queue: `php artisan queue:work`
-   Atur cron/scheduler pada host untuk menjalankan `php artisan schedule:run` per menit.

## Endpoint Utama

Daftar lengkap endpoint tersedia di `routes/api.php`. Beberapa area fungsional:

-   Autentikasi & Profil
-   Home/Scoring ringkasan
-   Manajemen akun sosial & metrik

Catatan: Kontrak respons mengikuti pola API yang konsisten (status code 2xx untuk sukses, 4xx untuk validasi/klien, dsb.).

## Download Aplikasi

Unduh APK Android terbaru melalui GitHub Releases:

-   https://github.com/AZulUye/Inscore-App/releases/download/v1.0.0/app-release.apk

Catatan: Tautan unduh juga tersedia di halaman Welcome aplikasi.

## Halaman Publik

-   Welcome: informasi singkat dan tautan unduh APK (GitHub Releases)
-   Connected: status setelah koneksi sosial berhasil/gagal
-   Kebijakan Privasi: tersedia di `/privacy`

## Lokasi File Penting

-   Model: `app/Models`
-   Layanan: `app/Services`
-   Controller: `app/Http/Controllers`
-   View publik: `resources/views` (welcome, privacy, connected)
-   Konfigurasi: `config/*.php`, `.env`
-   Migrasi & Seeder: `database/migrations`, `database/seeders`

## Kontribusi & Pengembangan Lanjutan

-   Normalisasi metrik dan bobot skor dapat disesuaikan di `ScoreService`
-   Batas (caps) metrik dapat dipindahkan ke file konfigurasi agar mudah di-tuning
-   Dokumentasi API lebih lanjut dapat ditambahkan ke folder `docs/`

---

Opsional, kirimkan jika tersedia agar README diperbarui:

-   Batch/angkatan program BEKUP Create
-   Peran dan kontak tiap anggota

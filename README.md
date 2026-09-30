# Absensi Keagamaan Siswa

Aplikasi absensi kegiatan keagamaan berbasis PHP Native + MySQL/MariaDB + Bootstrap 5 untuk sekolah/madrasah.

## Fitur utama
- Login multi-role: Admin, Guru, Siswa
- CRUD siswa, kelas, guru, kegiatan, jadwal, pengguna
- Absensi manual
- Absensi QR Code
- Dashboard statistik dengan Chart.js
- Rekap absensi per siswa, per kelas, per kegiatan
- Laporan harian/mingguan/bulanan
- Export CSV/Excel
- WhatsApp otomatis
- Security: session, CSRF, prepared statement, role check

## Struktur utama
- `config/`
- `admin/`
- `guru/`
- `siswa/`
- `auth/`
- `scan/`
- `assets/`
- `uploads/`

## Panduan install
1. Install XAMPP dan jalankan Apache serta MySQL
2. Buat database `db_absensi_keagamaan`
3. Import file `database.sql`
4. Konfigurasi `config/database.php` bila diperlukan
5. Buka browser ke `http://localhost/absensi-keagamaan`

## Default akun
- Admin: `admin` / `admin123`
- Guru: `guru` / `guru123`

## Catatan
Aplikasi dibuat untuk lingkungan XAMPP lokal dan dapat dikembangkan lebih lanjut sesuai kebutuhan.

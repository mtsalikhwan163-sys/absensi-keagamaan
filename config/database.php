CREATE DATABASE IF NOT EXISTS `db_absensi_keagamaan` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_absensi_keagamaan`;

CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `nama` VARCHAR(150) NOT NULL,
  `role` ENUM('admin','guru','siswa') NOT NULL DEFAULT 'admin',
  `status` ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `guru_id` INT UNSIGNED NULL,
  `siswa_id` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `kelas` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_kelas` VARCHAR(100) NOT NULL,
  `tingkat` VARCHAR(50) NOT NULL,
  `wali_kelas` VARCHAR(150) DEFAULT NULL,
  `tahun_pelajaran` VARCHAR(50) NOT NULL,
  `status` ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id`),
  KEY `idx_kelas_tingkat` (`tingkat`),
  KEY `idx_kelas_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `guru` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nip` VARCHAR(50) DEFAULT NULL,
  `nama` VARCHAR(150) NOT NULL,
  `no_hp` VARCHAR(30) DEFAULT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `status` ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_guru_nip` (`nip`),
  KEY `idx_guru_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `siswa` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nis` VARCHAR(50) NOT NULL,
  `nisn` VARCHAR(50) DEFAULT NULL,
  `nama` VARCHAR(150) NOT NULL,
  `jk` ENUM('L','P') DEFAULT NULL,
  `tempat_lahir` VARCHAR(100) DEFAULT NULL,
  `tanggal_lahir` DATE DEFAULT NULL,
  `kelas_id` INT UNSIGNED DEFAULT NULL,
  `no_hp` VARCHAR(30) DEFAULT NULL,
  `nama_orangtua` VARCHAR(150) DEFAULT NULL,
  `no_wa_orangtua` VARCHAR(30) DEFAULT NULL,
  `foto` VARCHAR(255) DEFAULT NULL,
  `qr_code` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_siswa_nis` (`nis`),
  UNIQUE KEY `uq_siswa_nisn` (`nisn`),
  KEY `idx_siswa_kelas` (`kelas_id`),
  KEY `idx_siswa_status` (`status`),
  CONSTRAINT `fk_siswa_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `kegiatan` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_kegiatan` VARCHAR(150) NOT NULL,
  `deskripsi` TEXT DEFAULT NULL,
  `lokasi` VARCHAR(150) DEFAULT NULL,
  `status` ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id`),
  KEY `idx_kegiatan_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `jadwal` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kegiatan_id` INT UNSIGNED NOT NULL,
  `kelas_id` INT UNSIGNED NOT NULL,
  `guru_id` INT UNSIGNED DEFAULT NULL,
  `tanggal` DATE NOT NULL,
  `jam_mulai` TIME NOT NULL,
  `jam_selesai` TIME NOT NULL,
  `keterangan` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_jadwal_tanggal` (`tanggal`),
  KEY `idx_jadwal_kegiatan` (`kegiatan_id`),
  KEY `idx_jadwal_kelas` (`kelas_id`),
  KEY `idx_jadwal_guru` (`guru_id`),
  CONSTRAINT `fk_jadwal_kegiatan` FOREIGN KEY (`kegiatan_id`) REFERENCES `kegiatan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_jadwal_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_jadwal_guru` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `absensi` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `siswa_id` INT UNSIGNED NOT NULL,
  `jadwal_id` INT UNSIGNED NOT NULL,
  `tanggal` DATE NOT NULL,
  `jam` TIME DEFAULT NULL,
  `status` ENUM('H','S','I','A','D') NOT NULL,
  `keterangan` VARCHAR(255) DEFAULT NULL,
  `metode_absen` ENUM('manual','qr','scan') NOT NULL DEFAULT 'manual',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_absensi_unique` (`siswa_id`,`jadwal_id`,`tanggal`),
  KEY `idx_absensi_siswa` (`siswa_id`),
  KEY `idx_absensi_jadwal` (`jadwal_id`),
  KEY `idx_absensi_tanggal` (`tanggal`),
  KEY `idx_absensi_status` (`status`),
  CONSTRAINT `fk_absensi_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_absensi_jadwal` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key_name` VARCHAR(100) NOT NULL,
  `key_value` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `activity_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `aksi` VARCHAR(255) NOT NULL,
  `detail` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_activity_log_user` (`user_id`),
  CONSTRAINT `fk_activity_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `kelas` (`id`, `nama_kelas`, `tingkat`, `wali_kelas`, `tahun_pelajaran`, `status`) VALUES
(1, 'X-A', 'X', 'Bapak Hadi', '2025/2026', 'aktif'),
(2, 'X-B', 'X', 'Ibu Siti', '2025/2026', 'aktif'),
(3, 'XI-A', 'XI', 'Bapak Fajar', '2025/2026', 'aktif'),
(4, 'XI-B', 'XI', 'Ibu Rahma', '2025/2026', 'aktif'),
(5, 'XII-A', 'XII', 'Bapak Arif', '2025/2026', 'aktif'),
(6, 'XII-B', 'XII', 'Ibu Nisa', '2025/2026', 'aktif');

INSERT INTO `guru` (`id`, `nip`, `nama`, `no_hp`, `email`, `status`) VALUES
(1, '19870001', 'Bapak Hasan', '081234567890', 'hasan@madrasah.sch.id', 'aktif'),
(2, '19870002', 'Ibu Salma', '081234567891', 'salma@madrasah.sch.id', 'aktif');

INSERT INTO `kegiatan` (`id`, `nama_kegiatan`, `deskripsi`, `lokasi`, `status`) VALUES
(1, 'Shalat Dhuha', 'Kegiatan shalat dhuha berjamaah', 'Masjid Sekolah', 'aktif'),
(2, 'Tadarus Al-Qur\'an', 'Tadarus bersama sebelum pembelajaran', 'Perpustakaan', 'aktif'),
(3, 'Tahfidz', 'Menghafal ayat pilihan', 'Ruang Tahfidz', 'aktif'),
(4, 'Kajian Keislaman', 'Kajian rutin keagamaan', 'Aula', 'aktif');

INSERT INTO `users` (`id`, `username`, `password`, `nama`, `role`, `status`, `guru_id`, `siswa_id`, `created_at`) VALUES
(1, 'admin', 'admin123', 'Administrator', 'admin', 'aktif', NULL, NULL, NOW()),
(2, 'guru', 'guru123', 'Guru Pembina', 'guru', 'aktif', 1, NULL, NOW());

INSERT INTO `settings` (`key_name`, `key_value`) VALUES
('tahun_pelajaran', '2025/2026'),
('semester', 'Ganjil');

INSERT INTO `jadwal` (`id`, `kegiatan_id`, `kelas_id`, `guru_id`, `tanggal`, `jam_mulai`, `jam_selesai`, `keterangan`) VALUES
(1, 1, 1, 1, CURDATE(), '06:45:00', '07:15:00', 'Shalat dhuha berjamaah kelas X-A'),
(2, 2, 2, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '07:00:00', '07:30:00', 'Tadarus kelas X-B');

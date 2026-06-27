-- Database creation if not exists
CREATE DATABASE IF NOT EXISTS `spmb_muh`;
USE `spmb_muh`;

-- Table users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'siswa') NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table tahun_ajaran
CREATE TABLE IF NOT EXISTS `tahun_ajaran` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tahun` VARCHAR(20) NOT NULL,
  `status` ENUM('aktif', 'tidak_aktif') DEFAULT 'tidak_aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table pendaftaran
CREATE TABLE IF NOT EXISTS `pendaftaran` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `no_pendaftaran` VARCHAR(20) NOT NULL UNIQUE,
  `nisn` VARCHAR(10) NOT NULL UNIQUE,
  `nik` VARCHAR(16) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `tempat_lahir` VARCHAR(50) NOT NULL,
  `tanggal_lahir` DATE NOT NULL,
  `jenis_kelamin` ENUM('L', 'P') NOT NULL,
  `agama` VARCHAR(20) NOT NULL,
  `alamat` TEXT NOT NULL,
  `asal_sekolah` VARCHAR(100) NOT NULL,
  `no_hp` VARCHAR(15) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `nama_ayah` VARCHAR(100) NOT NULL,
  `nama_ibu` VARCHAR(100) NOT NULL,
  `pekerjaan_ortu` VARCHAR(100) NOT NULL,
  `tahun_ajaran_id` INT NOT NULL,
  `tanggal_daftar` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table dokumen
CREATE TABLE IF NOT EXISTS `dokumen` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pendaftaran_id` INT NOT NULL,
  `jenis_dokumen` ENUM('foto', 'kk', 'akta', 'rapor', 'ijazah_skl', 'kip', 'piagam') NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `status_verifikasi` ENUM('Belum Dicek', 'Lengkap', 'Kurang Lengkap') DEFAULT 'Belum Dicek',
  `catatan` TEXT DEFAULT NULL,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table hasil_seleksi
CREATE TABLE IF NOT EXISTS `hasil_seleksi` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pendaftaran_id` INT NOT NULL UNIQUE,
  `status_seleksi` ENUM('Belum Diseleksi', 'LULUS', 'TIDAK LULUS') DEFAULT 'Belum Diseleksi',
  `keterangan` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table pengumuman
CREATE TABLE IF NOT EXISTS `pengumuman` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `judul` VARCHAR(255) NOT NULL,
  `isi` TEXT NOT NULL,
  `tanggal` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table jadwal
CREATE TABLE IF NOT EXISTS `jadwal` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `gelombang` VARCHAR(50) NOT NULL,
  `mulai` DATE NOT NULL,
  `selesai` DATE NOT NULL,
  `keterangan` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Data
-- Default Admin (username: admin, password: admin123)
INSERT INTO `users` (`username`, `password`, `role`, `nama_lengkap`) VALUES
('admin', '$2y$10$2wugyyp401tk9XhiX7NYhegm5SZSPeXH/q6iWmNZnbc9xkCDuUpmm', 'admin', 'Administrator SPMB');

-- Default Tahun Ajaran
INSERT INTO `tahun_ajaran` (`tahun`, `status`) VALUES
('2026/2027', 'aktif'),
('2027/2028', 'tidak_aktif');

-- Default Jadwal
INSERT INTO `jadwal` (`gelombang`, `mulai`, `selesai`, `keterangan`) VALUES
('Gelombang 1', '2026-03-01', '2026-05-31', 'Pendaftaran gelombang pertama jalur prestasi dan reguler'),
('Gelombang 2', '2026-06-01', '2026-07-15', 'Pendaftaran gelombang kedua jalur reguler');

-- Default Pengumuman
INSERT INTO `pengumuman` (`judul`, `isi`, `tanggal`) VALUES
('Informasi Pembukaan SPMB 2026/2027', 'Pendaftaran Peserta Didik Baru SMP Muhammadiyah 1 Pringsewu Tahun Pelajaran 2026/2027 resmi dibuka secara online. Silakan lakukan registrasi akun terlebih dahulu.', '2026-03-01'),
('Alur Verifikasi Berkas Fisik', 'Setelah mengupload berkas secara online, calon siswa diharapkan dapat mengumpulkan fotokopi berkas fisik ke panitia di ruang SPMB sekolah.', '2026-03-05');

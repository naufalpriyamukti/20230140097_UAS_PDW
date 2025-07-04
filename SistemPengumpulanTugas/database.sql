
-- Database: pengumpulantugas
CREATE DATABASE IF NOT EXISTS `pengumpulantugas` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `pengumpulantugas`;

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','asisten','admin') NOT NULL DEFAULT 'mahasiswa',
  `nim` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `nim` (`nim`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `praktikum`
CREATE TABLE `praktikum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_praktikum` varchar(100) NOT NULL,
  `deskripsi` text,
  `asisten_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_praktikum_asisten` (`asisten_id`),
  CONSTRAINT `fk_praktikum_asisten` FOREIGN KEY (`asisten_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `modul`
CREATE TABLE `modul` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `praktikum_id` int(11) NOT NULL,
  `judul_modul` varchar(100) NOT NULL,
  `deskripsi` text,
  `file_modul` varchar(255) DEFAULT NULL,
  `deadline` datetime NOT NULL,
  `status` enum('aktif','tidak_aktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_modul_praktikum` (`praktikum_id`),
  CONSTRAINT `fk_modul_praktikum` FOREIGN KEY (`praktikum_id`) REFERENCES `praktikum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `pendaftaran`
CREATE TABLE `pendaftaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` int(11) NOT NULL,
  `praktikum_id` int(11) NOT NULL,
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_pendaftaran` (`mahasiswa_id`,`praktikum_id`),
  KEY `fk_pendaftaran_praktikum` (`praktikum_id`),
  CONSTRAINT `fk_pendaftaran_mahasiswa` FOREIGN KEY (`mahasiswa_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pendaftaran_praktikum` FOREIGN KEY (`praktikum_id`) REFERENCES `praktikum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `laporan`
CREATE TABLE `laporan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` int(11) NOT NULL,
  `modul_id` int(11) NOT NULL,
  `file_laporan` varchar(255) NOT NULL,
  `tanggal_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  `nilai` decimal(5,2) DEFAULT NULL,
  `feedback` text,
  `status` enum('submitted','graded','late') NOT NULL DEFAULT 'submitted',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_laporan` (`mahasiswa_id`,`modul_id`),
  KEY `fk_laporan_modul` (`modul_id`),
  CONSTRAINT `fk_laporan_mahasiswa` FOREIGN KEY (`mahasiswa_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_laporan_modul` FOREIGN KEY (`modul_id`) REFERENCES `modul` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data with properly hashed passwords
-- Password for all demo accounts: password123
INSERT INTO `users` (`nama`, `email`, `password`, `role`, `nim`) VALUES
('Admin System', 'admin@system.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL),
('Dr. Asisten A', 'asisten1@univ.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'asisten', NULL),
('Dr. Asisten B', 'asisten2@univ.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'asisten', NULL),
('Budi Santoso', 'budi@student.univ.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', '2021001'),
('Sari Dewi', 'sari@student.univ.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', '2021002'),
('Ahmad Rahman', 'ahmad@student.univ.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', '2021003'),
('Maya Putri', 'maya@student.univ.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', '2021004');

INSERT INTO `praktikum` (`nama_praktikum`, `deskripsi`, `asisten_id`) VALUES
('Pengembangan Web', 'Praktikum tentang pengembangan web menggunakan PHP, HTML, CSS, dan JavaScript', 2),
('Jaringan Komputer', 'Praktikum tentang jaringan komputer dan konfigurasi server', 3),
('Basis Data', 'Praktikum tentang perancangan dan implementasi basis data', 2),
('Pemrograman Mobile', 'Praktikum pengembangan aplikasi mobile Android dan iOS', 3);

INSERT INTO `modul` (`praktikum_id`, `judul_modul`, `deskripsi`, `deadline`) VALUES
(1, 'HTML & CSS Dasar', 'Membuat halaman web statis dengan HTML dan CSS', '2024-03-15 23:59:59'),
(1, 'PHP Native', 'Membuat aplikasi web dinamis dengan PHP', '2024-03-30 23:59:59'),
(1, 'Framework CodeIgniter', 'Implementasi MVC dengan CodeIgniter', '2024-04-15 23:59:59'),
(2, 'Konfigurasi Router', 'Setup dan konfigurasi router jaringan', '2024-03-20 23:59:59'),
(2, 'Network Security', 'Implementasi keamanan jaringan', '2024-04-05 23:59:59'),
(3, 'ERD & Database Design', 'Perancangan basis data dengan ERD', '2024-03-25 23:59:59'),
(3, 'SQL Queries', 'Pembuatan query SQL tingkat lanjut', '2024-04-10 23:59:59'),
(4, 'Android Basic', 'Pengenalan pengembangan aplikasi Android', '2024-03-28 23:59:59');

-- Sample pendaftaran (mahasiswa mendaftar ke praktikum)
INSERT INTO `pendaftaran` (`mahasiswa_id`, `praktikum_id`, `status`) VALUES
(4, 1, 'approved'), -- Budi daftar ke Pengembangan Web
(4, 3, 'approved'), -- Budi daftar ke Basis Data
(5, 1, 'approved'), -- Sari daftar ke Pengembangan Web
(5, 2, 'approved'), -- Sari daftar ke Jaringan Komputer
(6, 2, 'approved'), -- Ahmad daftar ke Jaringan Komputer
(6, 4, 'approved'), -- Ahmad daftar ke Pemrograman Mobile
(7, 3, 'approved'), -- Maya daftar ke Basis Data
(7, 4, 'approved'); -- Maya daftar ke Pemrograman Mobile

-- Sample laporan
INSERT INTO `laporan` (`mahasiswa_id`, `modul_id`, `file_laporan`, `nilai`, `feedback`, `status`) VALUES
(4, 1, 'laporan_budi_html_css.pdf', 85.50, 'Bagus, tapi perlu perbaikan pada responsive design', 'graded'),
(4, 2, 'laporan_budi_php.pdf', 78.00, 'Logika sudah benar, tapi kurang error handling', 'graded'),
(5, 1, 'laporan_sari_html_css.pdf', 92.00, 'Sangat bagus! Design responsive sudah tepat', 'graded'),
(5, 4, 'laporan_sari_router.pdf', NULL, NULL, 'submitted'),
(6, 4, 'laporan_ahmad_router.pdf', 75.50, 'Konfigurasi basic sudah benar, perlu optimasi', 'graded');

-- ============================================================
--  PORTAL LOWONGAN KERJA KAMPUS
--  File   : portal_lowongan.sql
--  Dibuat : untuk tugas project pemrograman web
-- ============================================================

CREATE DATABASE IF NOT EXISTS portal_lowongan
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE portal_lowongan;

-- ------------------------------------------------------------
-- TABEL: users
-- Menyimpan semua pengguna (admin, mahasiswa, perusahaan)
-- ------------------------------------------------------------
CREATE TABLE users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nama       VARCHAR(100)  NOT NULL,
  email      VARCHAR(100)  NOT NULL UNIQUE,
  password   VARCHAR(255)  NOT NULL,
  role       ENUM('admin','mahasiswa','perusahaan') NOT NULL DEFAULT 'mahasiswa',
  no_hp      VARCHAR(20)   DEFAULT NULL,
  foto       VARCHAR(255)  DEFAULT 'default.png',
  alamat     TEXT          DEFAULT NULL,
  created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABEL: kategori
-- Kategori jenis pekerjaan
-- ------------------------------------------------------------
CREATE TABLE kategori (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  nama_kategori  VARCHAR(100) NOT NULL,
  deskripsi      TEXT         DEFAULT NULL,
  created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABEL: lowongan
-- Lowongan yang diposting oleh perusahaan
-- ------------------------------------------------------------
CREATE TABLE lowongan (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT          NOT NULL,
  kategori_id INT          NOT NULL,
  judul       VARCHAR(150) NOT NULL,
  deskripsi   TEXT         NOT NULL,
  kualifikasi TEXT         DEFAULT NULL,
  lokasi      VARCHAR(100) DEFAULT NULL,
  tipe_kerja  ENUM('remote','onsite','hybrid') DEFAULT 'onsite',
  gaji        VARCHAR(100) DEFAULT 'Negosiasi',
  deadline    DATE         NOT NULL,
  status      ENUM('aktif','ditutup') DEFAULT 'aktif',
  created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE CASCADE,
  FOREIGN KEY (kategori_id) REFERENCES kategori(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABEL: lamaran
-- Lamaran yang dikirim oleh mahasiswa
-- ------------------------------------------------------------
CREATE TABLE lamaran (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  user_id       INT  NOT NULL,
  lowongan_id   INT  NOT NULL,
  surat_lamaran TEXT NOT NULL,
  cv_file       VARCHAR(255) DEFAULT NULL,
  status        ENUM('pending','diterima','ditolak') DEFAULT 'pending',
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)     REFERENCES users(id)    ON DELETE CASCADE,
  FOREIGN KEY (lowongan_id) REFERENCES lowongan(id) ON DELETE CASCADE,
  UNIQUE KEY uq_lamaran (user_id, lowongan_id)
) ENGINE=InnoDB;

-- ============================================================
--  DATA AWAL (SEEDER)
-- ============================================================

-- ------------------------------------------------------------
-- Users: 1 admin, 2 perusahaan, 2 mahasiswa
-- Password semua akun: password123
-- Hash bcrypt dari password_hash('password123', PASSWORD_BCRYPT)
-- ------------------------------------------------------------
INSERT INTO users (nama, email, password, role, no_hp, alamat) VALUES
(
  'Admin Portal',
  'admin@portal.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin',
  '08100000001',
  'Manado, Sulawesi Utara'
),
(
  'CV Sulut Fresh',
  'hrd@sulutfresh.id',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'perusahaan',
  '08100000002',
  'Jl. Piere Tendean No. 45, Manado'
),
(
  'PT Maju Bersama',
  'hrd@majubersama.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'perusahaan',
  '08100000003',
  'Jl. Sam Ratulangi No. 12, Manado'
),
(
  'Andi Rezky',
  'andi@mahasiswa.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'mahasiswa',
  '08100000004',
  'Manado, Sulawesi Utara'
),
(
  'Sari Wulandari',
  'sari@mahasiswa.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'mahasiswa',
  '08100000005',
  'Bitung, Sulawesi Utara'
);

-- ------------------------------------------------------------
-- Kategori pekerjaan
-- ------------------------------------------------------------
INSERT INTO kategori (nama_kategori, deskripsi) VALUES
('Teknologi Informasi', 'Bidang IT, pemrograman, jaringan, dan sejenisnya'),
('Desain & Kreatif',    'UI/UX, grafis, fotografi, videografi'),
('Marketing',           'Pemasaran, sosial media, konten kreator'),
('Administrasi',        'Staf admin, data entry, sekretaris'),
('Keuangan',            'Akuntansi, keuangan, perpajakan'),
('Operasional',         'Gudang, logistik, produksi');

-- ------------------------------------------------------------
-- Lowongan (diposting oleh perusahaan id=2 dan id=3)
-- ------------------------------------------------------------
INSERT INTO lowongan (user_id, kategori_id, judul, deskripsi, kualifikasi, lokasi, tipe_kerja, gaji, deadline, status) VALUES
(
  2, 3,
  'Admin Pemasaran',
  'Kami mencari Admin Pemasaran yang aktif dan kreatif untuk bergabung bersama tim CV Sulut Fresh dalam mengembangkan strategi pemasaran produk lokal Sulawesi Utara.',
  'Minimal D3/S1 semua jurusan. Menguasai media sosial. Mampu membuat konten menarik. Komunikatif dan proaktif.',
  'Manado',
  'hybrid',
  'Rp 1.800.000 - Rp 2.500.000',
  '2026-06-20',
  'aktif'
),
(
  2, 6,
  'Staf Gudang & Logistik',
  'Bertanggung jawab atas pengelolaan stok barang, penerimaan, dan pengiriman produk CV Sulut Fresh ke mitra distribusi.',
  'Minimal SMA/SMK. Teliti dan bertanggung jawab. Mampu mengoperasikan komputer dasar. Diutamakan berpengalaman di bidang logistik.',
  'Manado',
  'onsite',
  'Rp 2.000.000',
  '2026-06-25',
  'aktif'
),
(
  2, 2,
  'Desainer Label Produk',
  'Membuat desain label kemasan produk olahan makanan CV Sulut Fresh yang menarik dan sesuai branding perusahaan.',
  'Menguasai Adobe Illustrator / CorelDraw. Portofolio desain menjadi nilai plus. Bisa bekerja remote.',
  'Remote',
  'remote',
  'Rp 1.500.000 / proyek',
  '2026-06-30',
  'aktif'
),
(
  3, 1,
  'Web Developer',
  'PT Maju Bersama membuka lowongan untuk Web Developer yang akan bertanggung jawab mengembangkan dan memelihara sistem informasi internal perusahaan.',
  'Minimal D3/S1 Teknik Informatika atau sejenis. Menguasai PHP, MySQL, HTML, CSS, JavaScript. Diutamakan berpengalaman.',
  'Manado',
  'onsite',
  'Rp 3.000.000 - Rp 4.500.000',
  '2026-06-28',
  'aktif'
),
(
  3, 4,
  'Staff Administrasi',
  'Mengelola dokumen perusahaan, korespondensi, dan mendukung operasional harian kantor PT Maju Bersama.',
  'Minimal D3 semua jurusan. Menguasai Microsoft Office. Teliti, rapi, dan mampu bekerja di bawah tekanan.',
  'Manado',
  'onsite',
  'Rp 2.200.000',
  '2026-06-15',
  'aktif'
);

-- ------------------------------------------------------------
-- Contoh lamaran dari mahasiswa
-- ------------------------------------------------------------
INSERT INTO lamaran (user_id, lowongan_id, surat_lamaran, status) VALUES
(
  4, 4,
  'Dengan hormat, saya Andi Rezky mahasiswa Teknik Informatika semester 6. Saya tertarik melamar posisi Web Developer di PT Maju Bersama. Saya memiliki kemampuan PHP, MySQL, dan JavaScript yang dipelajari selama kuliah maupun secara mandiri. Saya berharap dapat berkontribusi untuk perusahaan. Terima kasih.',
  'pending'
),
(
  5, 1,
  'Dengan hormat, saya Sari Wulandari dari jurusan Manajemen. Saya sangat tertarik dengan posisi Admin Pemasaran di CV Sulut Fresh. Saya aktif mengelola media sosial pribadi dan memiliki pengalaman membuat konten promosi. Saya siap memberikan kontribusi terbaik. Terima kasih.',
  'diterima'
);

-- ============================================================
-- SELESAI — semua tabel dan data awal berhasil dibuat
-- Akun login untuk testing:
--   Admin      : admin@portal.com      / password123
--   Perusahaan : hrd@sulutfresh.id     / password123
--   Perusahaan : hrd@majubersama.com   / password123
--   Mahasiswa  : andi@mahasiswa.com    / password123
--   Mahasiswa  : sari@mahasiswa.com    / password123
-- ============================================================

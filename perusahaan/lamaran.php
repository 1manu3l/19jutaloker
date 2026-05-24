<?php
// ============================================================
// perusahaan/lamaran.php
// Halaman daftar pelamar untuk perusahaan
// ============================================================

session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/sidebar_prs.php';

// Hanya perusahaan yang bisa akses
cekRole('perusahaan');

// ============================================================
// AMBIL DATA USER LOGIN
// ============================================================
$userId = $_SESSION['user_id'];

// ============================================================
// FILTER STATUS
// ============================================================
$filterStatus = $_GET['status'] ?? '';

$whereStatus = '';

if (!empty($filterStatus)) {
    $statusEscape = escape($conn, $filterStatus);
    $whereStatus = " AND lamaran.status = '{$statusEscape}' ";
}

// ============================================================
// UPDATE STATUS LAMARAN
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {

    $lamaranId = (int) ($_POST['lamaran_id'] ?? 0);
    $statusBaru = bersihkan($_POST['status'] ?? '');

    if (in_array($statusBaru, ['pending', 'diterima', 'ditolak'])) {

        // Pastikan lamaran milik lowongan perusahaan ini
        $cek = ambilSatu($conn, "
            SELECT lamaran.id
            FROM lamaran
            JOIN lowongan ON lowongan.id = lamaran.lowongan_id
            WHERE lamaran.id = {$lamaranId}
            AND lowongan.user_id = {$userId}
        ");

        if ($cek) {

            $statusEscape = escape($conn, $statusBaru);

            query($conn, "
                UPDATE lamaran
                SET status = '{$statusEscape}'
                WHERE id = {$lamaranId}
            ");

            setPesan('sukses', 'Status lamaran berhasil diperbarui.');
            redirect('lamaran.php');
        }
    }
}

// ============================================================
// AMBIL SEMUA DATA LAMARAN
// ============================================================
$semuaLamaran = ambilSemua($conn, "
    SELECT
        lamaran.*,
        users.nama AS nama_pelamar,
        users.email,
        users.no_hp,
        users.alamat,
        lowongan.judul AS judul_lowongan,
        lowongan.lokasi,
        lowongan.tipe_kerja
    FROM lamaran
    JOIN users ON users.id = lamaran.user_id
    JOIN lowongan ON lowongan.id = lamaran.lowongan_id
    WHERE lowongan.user_id = {$userId}
    {$whereStatus}
    ORDER BY lamaran.created_at DESC
");

// ============================================================
// HITUNG STATISTIK
// ============================================================
$totalLamaran = count($semuaLamaran);

$totalPending = hitungBaris($conn, "
    SELECT lamaran.id
    FROM lamaran
    JOIN lowongan ON lowongan.id = lamaran.lowongan_id
    WHERE lowongan.user_id = {$userId}
    AND lamaran.status = 'pending'
");

$totalDiterima = hitungBaris($conn, "
    SELECT lamaran.id
    FROM lamaran
    JOIN lowongan ON lowongan.id = lamaran.lowongan_id
    WHERE lowongan.user_id = {$userId}
    AND lamaran.status = 'diterima'
");

$totalDitolak = hitungBaris($conn, "
    SELECT lamaran.id
    FROM lamaran
    JOIN lowongan ON lowongan.id = lamaran.lowongan_id
    WHERE lowongan.user_id = {$userId}
    AND lamaran.status = 'ditolak'
");

$judulHalaman = 'Lamaran Masuk';
$halamanAktif = 'lamaran.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $judulHalaman ?> — <?= APP_NAME ?></title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">

    <style>

        .lamaran-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .lamaran-header h1 {
            font-size: 26px;
            font-weight: 800;
        }

        .lamaran-header p {
            color: var(--text-muted);
            margin-top: 6px;
        }

        .filter-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filter-form select {
            padding: 10px 14px;
            border-radius: var(--radius-md);
            border: 1.5px solid var(--border);
            background: #fff;
        }

        .btn-filter {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: var(--radius-md);
            font-weight: 700;
            cursor: pointer;
        }

        .lamaran-list {
            display: grid;
            gap: 18px;
        }

        .pelamar-card {
            background: #fff;
            border-radius: 18px;
            border: 1px solid var(--border);
            overflow: hidden;
            transition: 0.25s ease;
        }

        .pelamar-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .pelamar-top {
            padding: 24px;
            display: flex;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
            border-bottom: 1px solid var(--bg-section);
        }

        .pelamar-left {
            flex: 1;
            min-width: 260px;
        }

        .pelamar-nama {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .pelamar-job {
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .pelamar-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            font-size: 13px;
            color: var(--text-secondary);
        }

        .pelamar-status {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 12px;
        }

        .pelamar-date {
            font-size: 12px;
            color: var(--text-muted);
        }

        .pelamar-body {
            padding: 24px;
        }

        .pelamar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        .info-box {
            background: var(--bg-light);
            border-radius: 14px;
            padding: 18px;
        }

        .info-box-title {
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .info-box-content {
            font-size: 14px;
            line-height: 1.7;
            color: var(--text-secondary);
        }

        .cv-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            margin-top: 14px;
        }

        .status-form {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .status-form select {
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid var(--border);
        }

        .btn-status {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
        }

        .empty-state {
            background: white;
            border-radius: 20px;
            padding: 80px 20px;
            text-align: center;
            border: 1px dashed var(--border);
        }

        .empty-state h3 {
            margin-top: 18px;
            font-size: 24px;
        }

        .empty-state p {
            color: var(--text-muted);
            margin-top: 8px;
        }

    </style>
</head>
<body>

<div class="dash-layout">

    <?php require_once '../includes/sidebar_prs.php'; ?>

    <main class="dash-main">

        <!-- TOPBAR -->
        <header class="topbar">

            <div class="topbar-left">
                <button class="topbar-mobile-toggle" onclick="toggleSidebar()">☰</button>

                <div>
                    <div class="topbar-title">
                        Manajemen Lamaran
                    </div>
                </div>
            </div>

            <div class="topbar-right">

                <div class="topbar-user">
                    <div class="topbar-avatar">
                        <?= strtoupper(substr($_SESSION['nama'], 0, 2)) ?>
                    </div>

                    <div>
                        <?= bersihkan($_SESSION['nama']) ?>
                    </div>
                </div>

            </div>

        </header>

        <!-- CONTENT -->
        <div class="dash-content">

            <?php tampilPesan(); ?>

            <!-- HEADER -->
            <div class="lamaran-header">

                <div>
                    <h1>📨 Lamaran Masuk</h1>
                    <p>Kelola seluruh pelamar yang masuk ke lowongan perusahaan Anda.</p>
                </div>

                <!-- FILTER -->
                <form method="GET" class="filter-form">

                    <select name="status">

                        <option value="">Semua Status</option>

                        <option value="pending"
                            <?= $filterStatus === 'pending' ? 'selected' : '' ?>>
                            Pending
                        </option>

                        <option value="diterima"
                            <?= $filterStatus === 'diterima' ? 'selected' : '' ?>>
                            Diterima
                        </option>

                        <option value="ditolak"
                            <?= $filterStatus === 'ditolak' ? 'selected' : '' ?>>
                            Ditolak
                        </option>

                    </select>

                    <button type="submit" class="btn-filter">
                        Filter
                    </button>

                </form>

            </div>

            <!-- STATS -->
            <div class="stats-grid">

                <div class="stat-card">
                    <div class="stat-card-icon purple">📨</div>
                    <div class="stat-num"><?= $totalLamaran ?></div>
                    <div class="stat-label">Total Lamaran</div>
                    <div class="stat-card-bar purple"></div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon yellow">⏳</div>
                    <div class="stat-num"><?= $totalPending ?></div>
                    <div class="stat-label">Pending</div>
                    <div class="stat-card-bar yellow"></div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon green">✅</div>
                    <div class="stat-num"><?= $totalDiterima ?></div>
                    <div class="stat-label">Diterima</div>
                    <div class="stat-card-bar green"></div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon red">❌</div>
                    <div class="stat-num"><?= $totalDitolak ?></div>
                    <div class="stat-label">Ditolak</div>
                    <div class="stat-card-bar red"></div>
                </div>

            </div>

            <!-- LIST LAMARAN -->
            <?php if (count($semuaLamaran) > 0): ?>

                <div class="lamaran-list">

                    <?php foreach ($semuaLamaran as $lamaran): ?>

                        <div class="pelamar-card">

                            <!-- TOP -->
                            <div class="pelamar-top">

                                <div class="pelamar-left">

                                    <div class="pelamar-nama">
                                        <?= bersihkan($lamaran['nama_pelamar']) ?>
                                    </div>

                                    <div class="pelamar-job">
                                        <?= bersihkan($lamaran['judul_lowongan']) ?>
                                    </div>

                                    <div class="pelamar-meta">

                                        <span>📍 <?= bersihkan($lamaran['lokasi']) ?></span>

                                        <span>
                                            💼 <?= ucfirst($lamaran['tipe_kerja']) ?>
                                        </span>

                                        <span>
                                            📧 <?= bersihkan($lamaran['email']) ?>
                                        </span>

                                    </div>

                                </div>

                                <div class="pelamar-status">

                                    <?= badgeStatusLamaran($lamaran['status']) ?>

                                    <div class="pelamar-date">
                                        Masuk:
                                        <?= formatTanggal(date('Y-m-d', strtotime($lamaran['created_at']))) ?>
                                    </div>

                                </div>

                            </div>

                            <!-- BODY -->
                            <div class="pelamar-body">

                                <div class="pelamar-grid">

                                    <div class="info-box">

                                        <div class="info-box-title">
                                            Surat Lamaran
                                        </div>

                                        <div class="info-box-content">
                                            <?= nl2br(bersihkan($lamaran['surat_lamaran'])) ?>
                                        </div>

                                    </div>

                                    <div class="info-box">

                                        <div class="info-box-title">
                                            Informasi Pelamar
                                        </div>

                                        <div class="info-box-content">

                                            <p>
                                                <strong>Nomor HP:</strong><br>
                                                <?= bersihkan($lamaran['no_hp']) ?>
                                            </p>

                                            <br>

                                            <p>
                                                <strong>Alamat:</strong><br>
                                                <?= bersihkan($lamaran['alamat']) ?>
                                            </p>

                                        </div>

                                        <?php if (!empty($lamaran['cv_file'])): ?>

                                            <a
                                                href="../<?= UPLOAD_CV . $lamaran['cv_file'] ?>"
                                                target="_blank"
                                                class="cv-link"
                                            >
                                                📄 Lihat CV
                                            </a>

                                        <?php endif; ?>

                                    </div>

                                </div>

                                <!-- FORM UPDATE STATUS -->
                                <form method="POST" class="status-form">

                                    <input
                                        type="hidden"
                                        name="lamaran_id"
                                        value="<?= $lamaran['id'] ?>"
                                    >

                                    <select name="status">

                                        <option value="pending"
                                            <?= $lamaran['status'] === 'pending' ? 'selected' : '' ?>>
                                            Pending
                                        </option>

                                        <option value="diterima"
                                            <?= $lamaran['status'] === 'diterima' ? 'selected' : '' ?>>
                                            Diterima
                                        </option>

                                        <option value="ditolak"
                                            <?= $lamaran['status'] === 'ditolak' ? 'selected' : '' ?>>
                                            Ditolak
                                        </option>

                                    </select>

                                    <button
                                        type="submit"
                                        name="update_status"
                                        class="btn-status"
                                    >
                                        Simpan Status
                                    </button>

                                </form>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="empty-state">

                    <div style="font-size:72px;">📭</div>

                    <h3>Belum Ada Lamaran</h3>

                    <p>
                        Belum ada pelamar yang mengirim lamaran ke lowongan Anda.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </main>

</div>

<script>

function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
}

</script>

</body>
</html>
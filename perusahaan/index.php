<?php
session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/sidebar_prs.php';

cekRole('perusahaan');

$userId = $_SESSION['user_id'];

// ======================================================
// STATISTIK
// ======================================================

// Total lowongan
$totalLowongan = ambilSatu($conn,
    "SELECT COUNT(*) AS total
     FROM lowongan
     WHERE user_id = {$userId}"
)['total'] ?? 0;

// Lowongan aktif
$lowonganAktif = ambilSatu($conn,
    "SELECT COUNT(*) AS total
     FROM lowongan
     WHERE user_id = {$userId}
     AND status='aktif'"
)['total'] ?? 0;

// Total lamaran
$totalLamaran = ambilSatu($conn,
    "SELECT COUNT(*) AS total
     FROM lamaran l
     JOIN lowongan lw ON l.lowongan_id = lw.id
     WHERE lw.user_id = {$userId}"
)['total'] ?? 0;

// Lamaran pending
$pending = ambilSatu($conn,
    "SELECT COUNT(*) AS total
     FROM lamaran l
     JOIN lowongan lw ON l.lowongan_id = lw.id
     WHERE lw.user_id = {$userId}
     AND l.status='pending'"
)['total'] ?? 0;

// ======================================================
// LOWONGAN TERBARU
// ======================================================

$lowonganTerbaru = ambilSemua($conn,
    "SELECT *
     FROM lowongan
     WHERE user_id = {$userId}
     ORDER BY created_at DESC
     LIMIT 5"
);

// ======================================================
// LAMARAN TERBARU
// ======================================================

$lamaranTerbaru = ambilSemua($conn,
    "SELECT
        l.*,
        u.nama,
        lw.judul
     FROM lamaran l
     JOIN users u ON l.user_id = u.id
     JOIN lowongan lw ON l.lowongan_id = lw.id
     WHERE lw.user_id = {$userId}
     ORDER BY l.created_at DESC
     LIMIT 5"
);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Perusahaan — <?= APP_NAME ?></title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<?php require_once '../includes/sidebar_prs.php'; ?>

<main class="dash-main">

<!-- TOPBAR -->
<header class="topbar">

    <div class="topbar-left">

        <button class="topbar-mobile-toggle"
                onclick="toggleSidebar()">
            ☰
        </button>

        <h1 class="topbar-title">
            Dashboard Perusahaan
        </h1>

    </div>

    <div class="topbar-right">

        <div class="topbar-user">

            <div class="topbar-avatar">
                <?= strtoupper(substr($_SESSION['nama'],0,2)) ?>
            </div>

            <span>
                <?= bersihkan($_SESSION['nama']) ?>
            </span>

        </div>

    </div>

</header>

<!-- CONTENT -->
<div class="dash-content">

    <!-- HEADER -->
    <div class="page-header">

        <div>
            <h1>Halo, <?= bersihkan($_SESSION['nama']) ?> 👋</h1>

            <p style="margin-top:6px;color:var(--text-secondary)">
                Kelola lowongan kerja dan pantau pelamar perusahaan Anda.
            </p>
        </div>

        <div class="page-header-actions">

            <a href="lowongan.php" class="btn-tambah">
                + Tambah Lowongan
            </a>

        </div>

    </div>

    <!-- STATS -->
    <div class="stats-grid">

        <div class="stat-card">

            <div class="stat-card-icon purple">
                💼
            </div>

            <div class="stat-num">
                <?= $totalLowongan ?>
            </div>

            <div class="stat-label">
                Total Lowongan
            </div>

            <div class="stat-card-bar purple"></div>

        </div>

        <div class="stat-card">

            <div class="stat-card-icon green">
                ✅
            </div>

            <div class="stat-num">
                <?= $lowonganAktif ?>
            </div>

            <div class="stat-label">
                Lowongan Aktif
            </div>

            <div class="stat-card-bar green"></div>

        </div>

        <div class="stat-card">

            <div class="stat-card-icon blue">
                📨
            </div>

            <div class="stat-num">
                <?= $totalLamaran ?>
            </div>

            <div class="stat-label">
                Total Lamaran
            </div>

            <div class="stat-card-bar blue"></div>

        </div>

        <div class="stat-card">

            <div class="stat-card-icon yellow">
                ⏳
            </div>

            <div class="stat-num">
                <?= $pending ?>
            </div>

            <div class="stat-label">
                Menunggu Review
            </div>

            <div class="stat-card-bar yellow"></div>

        </div>

    </div>

    <!-- GRID -->
    <div class="layout-crud">

        <!-- LOWONGAN -->
        <div class="card">

            <div class="card-header">
                <div class="card-title">
                    Lowongan Terbaru
                </div>
            </div>

            <div class="card-body">

                <?php if (empty($lowonganTerbaru)): ?>

                    <div class="alert alert-info">
                        Anda belum membuat lowongan.
                    </div>

                <?php else: ?>

                    <div class="mini-list">

                        <?php foreach ($lowonganTerbaru as $l): ?>

                            <div class="mini-list-item">

                                <div>

                                    <div class="mini-list-title">
                                        <?= bersihkan($l['judul']) ?>
                                    </div>

                                    <div class="mini-list-sub">
                                        Deadline:
                                        <?= formatTanggal($l['deadline']) ?>
                                    </div>

                                </div>

                                <div>
                                    <?= $l['status'] === 'aktif'
                                        ? '<span class="badge badge-aktif">Aktif</span>'
                                        : '<span class="badge badge-tutup">Ditutup</span>'
                                    ?>
                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

        </div>

        <!-- LAMARAN -->
        <div class="card">

            <div class="card-header">
                <div class="card-title">
                    Pelamar Terbaru
                </div>
            </div>

            <div class="card-body">

                <?php if (empty($lamaranTerbaru)): ?>

                    <div class="alert alert-info">
                        Belum ada pelamar masuk.
                    </div>

                <?php else: ?>

                    <div class="mini-list">

                        <?php foreach ($lamaranTerbaru as $l): ?>

                            <div class="mini-list-item">

                                <div>

                                    <div class="mini-list-title">
                                        <?= bersihkan($l['nama']) ?>
                                    </div>

                                    <div class="mini-list-sub">
                                        Melamar:
                                        <?= bersihkan($l['judul']) ?>
                                    </div>

                                </div>

                                <div>

                                    <?=
                                        $l['status'] === 'pending'
                                        ? '<span class="badge badge-pending">Pending</span>'
                                        : (
                                            $l['status'] === 'diterima'
                                            ? '<span class="badge badge-diterima">Diterima</span>'
                                            : '<span class="badge badge-ditolak">Ditolak</span>'
                                        )
                                    ?>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>

</main>
</div>

<script>

function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
}

</script>
</main>
</div>

</body>
</html>
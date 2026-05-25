<?php
// ============================================================
// perusahaan/profil.php
// Profil Perusahaan
// ============================================================

session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../functions.php';

// ============================================================
// CEK LOGIN & ROLE
// ============================================================

if (!sudahLogin() || $_SESSION['role'] !== 'perusahaan') {
    redirect('../login.php');
}

$userId = $_SESSION['user_id'];

// ============================================================
// AMBIL DATA USER
// ============================================================

$queryUser = mysqli_query($conn, "
    SELECT *
    FROM users
    WHERE id = '$userId'
");

$perusahaan = mysqli_fetch_assoc($queryUser);

// ============================================================
// STATISTIK
// ============================================================

$totalLowongan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM lowongan
    WHERE user_id = '$userId'
"))['total'];

$totalLamaran = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(lamaran.id) as total
    FROM lamaran
    JOIN lowongan ON lamaran.lowongan_id = lowongan.id
    WHERE lowongan.user_id = '$userId'
"))['total'];

$totalDiterima = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(lamaran.id) as total
    FROM lamaran
    JOIN lowongan ON lamaran.lowongan_id = lowongan.id
    WHERE lowongan.user_id = '$userId'
    AND lamaran.status = 'diterima'
"))['total'];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Perusahaan | <?= APP_NAME ?></title>

    <!-- CSS DASHBOARD -->
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<div class="dash-layout">

<?php require_once __DIR__ . '/../includes/sidebar_prs.php'; ?>

<!-- ========================================================= -->
<!-- MAIN CONTENT -->
<!-- ========================================================= -->

<main class="dash-main">

    <!-- TOPBAR -->
    <div class="topbar">

        <div class="topbar-left">

            <button class="topbar-mobile-toggle" onclick="toggleSidebar()">
                ☰
            </button>

            <h1 class="topbar-title">
                Profil Perusahaan
            </h1>

        </div>

        <div class="topbar-right">

            <div class="topbar-user">

                <div class="topbar-avatar">
                    <?= strtoupper(substr($_SESSION['nama'], 0, 2)) ?>
                </div>

                <span>
                    <?= htmlspecialchars($_SESSION['nama']) ?>
                </span>

            </div>

        </div>

    </div>

    <!-- CONTENT -->
    <div class="dash-content">

        <!-- PAGE HEADER -->
        <div class="page-header">

            <div>

                <h1>🏢 Profil Perusahaan</h1>

                <p style="
                    margin-top:8px;
                    color:var(--text-secondary);
                    font-size:14px;
                    max-width:750px;
                    line-height:1.8;
                ">
                    Kelola identitas perusahaan Anda untuk meningkatkan
                    kredibilitas dan menarik kandidat berkualitas.
                    Profil yang lengkap membantu pelamar memahami
                    budaya kerja dan visi perusahaan Anda.
                </p>

            </div>

            <div class="page-header-actions">

                <a href="edit_profil.php" class="btn-submit">
    ✏ Edit Profil
</a>

            </div>

        </div>

        <!-- HERO CARD -->
        <div class="card" style="overflow:hidden;">

            <!-- BANNER -->
            <div style="
                height:180px;
                background:
                linear-gradient(
                    135deg,
                    #4f46e5,
                    #7c3aed,
                    #2563eb
                );
            "></div>

            <!-- BODY -->
            <div style="
                padding:0 28px 28px;
                position:relative;
            ">

                <!-- AVATAR -->
                <div style="
                    width:120px;
                    height:120px;
                    border-radius:24px;
                    background:white;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    font-size:42px;
                    font-weight:800;
                    color:var(--primary);
                    margin-top:-60px;
                    border:6px solid white;
                    box-shadow:0 10px 30px rgba(0,0,0,0.12);
                ">
                    <?= strtoupper(substr($perusahaan['nama'], 0, 2)) ?>
                </div>

                <!-- INFO -->
                <div style="margin-top:20px;">

                    <div style="
                        display:flex;
                        align-items:center;
                        gap:12px;
                        flex-wrap:wrap;
                        margin-bottom:12px;
                    ">

                        <h2 style="
                            font-size:32px;
                            font-weight:800;
                            letter-spacing:-1px;
                        ">
                            <?= htmlspecialchars($perusahaan['nama']) ?>
                        </h2>

                        <span class="badge badge-aktif">
                            Perusahaan Aktif
                        </span>

                    </div>

                    <p style="
                        color:var(--text-secondary);
                        line-height:1.8;
                        max-width:900px;
                    ">
                        Perusahaan ini aktif membuka peluang kerja
                        dan magang untuk mahasiswa maupun fresh graduate.
                        Bangun karier profesional bersama perusahaan
                        yang terus berkembang dan inovatif.
                    </p>

                </div>

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

                <div class="stat-card-icon green">
                    ✅
                </div>

                <div class="stat-num">
                    <?= $totalDiterima ?>
                </div>

                <div class="stat-label">
                    Kandidat Diterima
                </div>

                <div class="stat-card-bar green"></div>

            </div>

        </div>

        <!-- INFORMASI -->
        <div class="card">

            <div class="card-header">
                <h3 class="card-title">
                    📌 Informasi Perusahaan
                </h3>
            </div>

            <div class="card-body">

                <div class="form-grid-2">

                    <div class="form-group">

                        <label>Email</label>

                        <input
                            type="text"
                            value="<?= htmlspecialchars($perusahaan['email']) ?>"
                            readonly
                        >

                    </div>

                    <div class="form-group">

                        <label>Nomor HP</label>

                        <input
                            type="text"
                            value="<?= htmlspecialchars($perusahaan['no_hp'] ?: '-') ?>"
                            readonly
                        >

                    </div>

                    <div class="form-group">

                        <label>Alamat</label>

                        <input
                            type="text"
                            value="<?= htmlspecialchars($perusahaan['alamat'] ?: '-') ?>"
                            readonly
                        >

                    </div>

                    <div class="form-group">

                        <label>Bergabung Sejak</label>

                        <input
                            type="text"
                            value="<?= date('d F Y', strtotime($perusahaan['created_at'])) ?>"
                            readonly
                        >

                    </div>

                </div>

            </div>

        </div>

        <!-- TENTANG -->
        <div class="card">

            <div class="card-header">
                <h3 class="card-title">
                    🚀 Tentang Perusahaan
                </h3>
            </div>

            <div class="card-body">

                <p style="
                    color:var(--text-secondary);
                    line-height:1.9;
                    font-size:14px;
                ">
                    <?= htmlspecialchars($perusahaan['nama']) ?>
                    berkomitmen membangun lingkungan kerja yang inovatif,
                    kolaboratif, dan profesional. Kami percaya bahwa
                    mahasiswa dan fresh graduate memiliki potensi besar
                    untuk berkembang menjadi talenta masa depan yang unggul.
                </p>

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

</body>
</html>
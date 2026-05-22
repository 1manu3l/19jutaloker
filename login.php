<?php
// ============================================================
//  login.php
//  Halaman login untuk semua role (admin, mahasiswa, perusahaan)
//  Letakkan file ini di ROOT folder portal-lowongan/
// ============================================================

// Mulai session sebelum apapun
session_start();

// Hubungkan ke file konfigurasi dan helper
require_once 'config.php';
require_once 'auth_check.php';
require_once 'functions.php';

// Jika sudah login, langsung arahkan ke dashboard sesuai role
if (sudahLogin()) {
    switch ($_SESSION['role']) {
        case 'admin':      redirect('admin/index.php');      break;
        case 'mahasiswa':  redirect('mahasiswa/index.php');  break;
        case 'perusahaan': redirect('perusahaan/index.php'); break;
    }
}

// ============================================================
//  PROSES FORM LOGIN
// ============================================================
$error = '';
$emailInput = ''; // simpan email agar tidak hilang saat error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil input dari form
    $emailInput = trim($_POST['email']    ?? '');
    $password   = trim($_POST['password'] ?? '');

    // ----------------------------------------------------------
    //  VALIDASI — cek field tidak kosong
    // ----------------------------------------------------------
    if (empty($emailInput) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    }
    // Cek format email
    elseif (!filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    }
    else {
        // ----------------------------------------------------------
        //  CARI USER DI DATABASE BERDASARKAN EMAIL
        // ----------------------------------------------------------
        $emailEscape = escape($conn, $emailInput);
        $user = ambilSatu($conn, "SELECT * FROM users WHERE email = '{$emailEscape}'");

        // ----------------------------------------------------------
        //  VERIFIKASI PASSWORD
        //  password_verify() membandingkan password polos dengan
        //  hash yang tersimpan di database
        // ----------------------------------------------------------
        if ($user && password_verify($password, $user['password'])) {

            // LOGIN BERHASIL — simpan data penting ke session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['foto']    = $user['foto'];

            // Arahkan ke dashboard sesuai role
            switch ($user['role']) {
                case 'admin':
                    redirect('admin/index.php');
                    break;
                case 'mahasiswa':
                    redirect('mahasiswa/index.php');
                    break;
                case 'perusahaan':
                    redirect('perusahaan/index.php');
                    break;
                default:
                    // Jika role tidak dikenal, paksa logout
                    session_destroy();
                    $error = 'Role akun tidak valid. Hubungi administrator.';
                    break;
            }

        } else {
            // LOGIN GAGAL — jangan beritahu mana yang salah (email atau password)
            // karena alasan keamanan
            $error = 'Email atau password salah. Silakan coba lagi.';
        }
    }
}

// Ambil pesan dari redirect (misal: dari auth_check.php)
$pesanRedirect = '';
if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] === 'silakan_login') {
        $pesanRedirect = 'Silakan login terlebih dahulu untuk mengakses halaman tersebut.';
    }
    if ($_GET['pesan'] === 'logout') {
        $pesanRedirect = 'Anda berhasil logout. Sampai jumpa!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>

<div class="auth-wrapper">

    <!-- Logo & judul -->
    <div class="auth-header">
        <div class="auth-logo">💼</div>
        <h1 class="auth-title"><?= APP_NAME ?></h1>
        <p class="auth-subtitle">Portal Lowongan Kerja Kampus</p>
    </div>

    <!-- Kotak form -->
    <div class="auth-box">
        <h2 class="auth-box-title">Masuk ke Akun</h2>

        <!-- Pesan dari redirect (login dulu / logout) -->
        <?php if ($pesanRedirect): ?>
            <div class="alert alert-info"><?= bersihkan($pesanRedirect) ?></div>
        <?php endif; ?>

        <!-- Pesan error login -->
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <!-- Form login -->
        <form method="POST" action="login.php">

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email <span class="wajib">*</span></label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Masukkan email terdaftar"
                    value="<?= bersihkan($emailInput) ?>"
                    required
                    autofocus
                >
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password <span class="wajib">*</span></label>
                <div class="input-password">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        required
                    >
                    <button type="button" class="toggle-pw" onclick="togglePassword()">👁</button>
                </div>
            </div>

            <!-- Tombol login -->
            <button type="submit" class="btn-auth">Masuk</button>

        </form>

        <!-- Akun demo untuk testing (hapus saat production) -->
        <div class="demo-accounts">
            <p class="demo-title">Akun demo untuk testing:</p>
            <div class="demo-grid">
                <div class="demo-item" onclick="isiLogin('admin@portal.com', 'password123')">
                    <span class="demo-role admin">Admin</span>
                    <span class="demo-email">admin@portal.com</span>
                </div>
                <div class="demo-item" onclick="isiLogin('hrd@sulutfresh.id', 'password123')">
                    <span class="demo-role perusahaan">Perusahaan</span>
                    <span class="demo-email">hrd@sulutfresh.id</span>
                </div>
                <div class="demo-item" onclick="isiLogin('andi@mahasiswa.com', 'password123')">
                    <span class="demo-role mahasiswa">Mahasiswa</span>
                    <span class="demo-email">andi@mahasiswa.com</span>
                </div>
            </div>
            <p class="demo-pass">Semua password: <code>password123</code> · klik untuk isi otomatis</p>
        </div>

        <!-- Link ke register -->
        <p class="auth-link">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </p>
        <p class="auth-link" style="margin-top:6px;">
            <a href="index.php">← Kembali ke Beranda</a>
        </p>

    </div>
</div>

<script>
// Tampilkan / sembunyikan password
function togglePassword() {
    const input = document.getElementById('password');
    const btn   = document.querySelector('.toggle-pw');
    if (input.type === 'password') {
        input.type   = 'text';
        btn.textContent = '🙈';
    } else {
        input.type   = 'password';
        btn.textContent = '👁';
    }
}

// Isi form login otomatis saat klik akun demo
function isiLogin(email, password) {
    document.getElementById('email').value    = email;
    document.getElementById('password').value = password;
}
</script>

</body>
</html>
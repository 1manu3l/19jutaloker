<?php
// ============================================================
//  register.php
//  Halaman pendaftaran akun baru (mahasiswa / perusahaan)
//  Letakkan file ini di ROOT folder portal-lowongan/
// ============================================================

// Mulai session sebelum apapun
session_start();

// Hubungkan ke file konfigurasi dan helper
require_once 'config.php';
require_once 'auth_check.php';
require_once 'functions.php';

// Jika sudah login, langsung arahkan ke dashboard
if (sudahLogin()) {
    switch ($_SESSION['role']) {
        case 'admin':      redirect('admin/index.php');      break;
        case 'mahasiswa':  redirect('mahasiswa/index.php');  break;
        case 'perusahaan': redirect('perusahaan/index.php'); break;
    }
}

// ============================================================
//  PROSES FORM REGISTER (saat tombol submit ditekan)
// ============================================================
$error  = '';   // pesan error
$sukses = '';   // pesan sukses
$input  = [];   // simpan nilai input agar tidak hilang saat error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil dan bersihkan semua input dari form
    $input['nama']     = bersihkan($_POST['nama']     ?? '');
    $input['email']    = bersihkan($_POST['email']    ?? '');
    $input['role']     = bersihkan($_POST['role']     ?? '');
    $input['no_hp']    = bersihkan($_POST['no_hp']    ?? '');
    $input['alamat']   = bersihkan($_POST['alamat']   ?? '');
    $password          = $_POST['password']            ?? '';
    $konfirmPassword   = $_POST['konfirm_password']    ?? '';

    // ----------------------------------------------------------
    //  VALIDASI INPUT
    // ----------------------------------------------------------

    // 1. Cek semua field wajib tidak kosong
    if (empty($input['nama']) || empty($input['email']) || empty($password) || empty($input['role'])) {
        $error = 'Nama, email, password, dan role wajib diisi.';
    }
    // 2. Cek format email valid
    elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    }
    // 3. Cek panjang password minimal 6 karakter
    elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    }
    // 4. Cek konfirmasi password cocok
    elseif ($password !== $konfirmPassword) {
        $error = 'Password dan konfirmasi password tidak cocok.';
    }
    // 5. Cek role valid (hanya mahasiswa atau perusahaan yang boleh daftar sendiri)
    elseif (!in_array($input['role'], ['mahasiswa', 'perusahaan'])) {
        $error = 'Role tidak valid.';
    }
    else {
        // ----------------------------------------------------------
        //  CEK EMAIL SUDAH TERDAFTAR
        // ----------------------------------------------------------
        $emailEscape = escape($conn, $_POST['email']);
        $cekEmail    = ambilSatu($conn, "SELECT id FROM users WHERE email = '{$emailEscape}'");

        if ($cekEmail) {
            $error = 'Email sudah terdaftar. Silakan gunakan email lain atau langsung login.';
        } else {
            // ----------------------------------------------------------
            //  SIMPAN USER BARU KE DATABASE
            // ----------------------------------------------------------

            // Hash password — TIDAK boleh simpan password polos ke DB
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            $namaEscape   = escape($conn, $input['nama']);
            $emailEscape  = escape($conn, $_POST['email']);
            $roleEscape   = escape($conn, $input['role']);
            $hpEscape     = escape($conn, $input['no_hp']);
            $alamatEscape = escape($conn, $input['alamat']);

            $sql = "INSERT INTO users (nama, email, password, role, no_hp, alamat)
                    VALUES ('{$namaEscape}', '{$emailEscape}', '{$passwordHash}',
                            '{$roleEscape}', '{$hpEscape}', '{$alamatEscape}')";

            if (query($conn, $sql)) {
                // Berhasil — kosongkan input dan tampilkan pesan sukses
                $sukses = 'Akun berhasil dibuat! Silakan <a href="login.php" 
                           style="color:#085041;font-weight:500;">login di sini</a>.';
                $input  = []; // reset input form
            } else {
                $error = 'Terjadi kesalahan saat menyimpan data. Coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>

<div class="auth-wrapper">

    <!-- Logo & judul -->
    <div class="auth-header">
        <div class="auth-logo">💼</div>
        <h1 class="auth-title"><?= APP_NAME ?></h1>
        <p class="auth-subtitle">Buat akun baru untuk mulai mencari lowongan</p>
    </div>

    <!-- Kotak form -->
    <div class="auth-box">
        <h2 class="auth-box-title">Daftar Akun</h2>

        <!-- Tampilkan pesan error jika ada -->
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <!-- Tampilkan pesan sukses jika ada -->
        <?php if ($sukses): ?>
            <div class="alert alert-sukses"><?= $sukses ?></div>
        <?php endif; ?>

        <!-- Form register — method POST agar data tidak muncul di URL -->
        <form method="POST" action="register.php" id="formRegister">

            <!-- Nama lengkap -->
            <div class="form-group">
                <label for="nama">Nama Lengkap <span class="wajib">*</span></label>
                <input
                    type="text"
                    id="nama"
                    name="nama"
                    placeholder="Contoh: Andi Rezky"
                    value="<?= $input['nama'] ?? '' ?>"
                    required
                >
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email <span class="wajib">*</span></label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Contoh: andi@email.com"
                    value="<?= $input['email'] ?? '' ?>"
                    required
                >
            </div>

            <!-- Pilih role -->
            <div class="form-group">
                <label for="role">Daftar sebagai <span class="wajib">*</span></label>
                <select id="role" name="role" required>
                    <option value="">-- Pilih role --</option>
                    <option value="mahasiswa"  <?= (($input['role'] ?? '') === 'mahasiswa')  ? 'selected' : '' ?>>
                        Mahasiswa (pencari kerja)
                    </option>
                    <option value="perusahaan" <?= (($input['role'] ?? '') === 'perusahaan') ? 'selected' : '' ?>>
                        Perusahaan / UMKM (pemberi kerja)
                    </option>
                </select>
            </div>

            <!-- Nomor HP -->
            <div class="form-group">
                <label for="no_hp">Nomor HP</label>
                <input
                    type="text"
                    id="no_hp"
                    name="no_hp"
                    placeholder="Contoh: 08123456789"
                    value="<?= $input['no_hp'] ?? '' ?>"
                >
            </div>

            <!-- Alamat -->
            <div class="form-group">
                <label for="alamat">Alamat</label>
                <input
                    type="text"
                    id="alamat"
                    name="alamat"
                    placeholder="Contoh: Manado, Sulawesi Utara"
                    value="<?= $input['alamat'] ?? '' ?>"
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
                        placeholder="Minimal 6 karakter"
                        required
                    >
                    <button type="button" class="toggle-pw" onclick="togglePassword('password', this)">
                        👁
                    </button>
                </div>
            </div>

            <!-- Konfirmasi password -->
            <div class="form-group">
                <label for="konfirm_password">Konfirmasi Password <span class="wajib">*</span></label>
                <div class="input-password">
                    <input
                        type="password"
                        id="konfirm_password"
                        name="konfirm_password"
                        placeholder="Ulangi password"
                        required
                    >
                    <button type="button" class="toggle-pw" onclick="togglePassword('konfirm_password', this)">
                        👁
                    </button>
                </div>
                <!-- Indikator cocok/tidak cocok realtime -->
                <small id="cekPassword" style="font-size:12px; margin-top:4px; display:block;"></small>
            </div>

            <!-- Tombol submit -->
            <button type="submit" class="btn-auth">Buat Akun</button>

        </form>

        <!-- Link ke login -->
        <p class="auth-link">
            Sudah punya akun? <a href="login.php">Masuk di sini</a>
        </p>
    </div>

</div>

<script>
// Tampilkan / sembunyikan password
function togglePassword(id, btn) {
    const input = document.getElementById(id);
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
    } else {
        input.type = 'password';
        btn.textContent = '👁';
    }
}

// Cek realtime apakah password dan konfirmasi cocok
document.getElementById('konfirm_password').addEventListener('input', function () {
    const pw      = document.getElementById('password').value;
    const konfirm = this.value;
    const info    = document.getElementById('cekPassword');

    if (konfirm === '') {
        info.textContent = '';
    } else if (pw === konfirm) {
        info.style.color   = '#3B6D11';
        info.textContent   = '✓ Password cocok';
    } else {
        info.style.color   = '#A32D2D';
        info.textContent   = '✗ Password tidak cocok';
    }
});
</script>

</body>
</html>
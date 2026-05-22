<?php

// ============================================================
//  functions.php
//  Kumpulan fungsi pembantu (helper) yang dipakai di seluruh
//  halaman aplikasi. Di-include setelah config.php.
// ============================================================

// ============================================================
//  KEAMANAN & SANITASI
// ============================================================

// ------------------------------------------------------------
// Fungsi: bersihkan($data)
// Membersihkan input dari karakter berbahaya
// SELALU gunakan ini sebelum menampilkan data dari user
// ------------------------------------------------------------
function bersihkan($data) {
    $data = trim($data);           // hapus spasi di awal/akhir
    $data = stripslashes($data);   // hapus backslash
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); // encode karakter HTML
    return $data;
}

// ------------------------------------------------------------
// Fungsi: sanitasiInt($data)
// Pastikan nilai adalah integer (untuk id, dll)
// ------------------------------------------------------------
function sanitasiInt($data) {
    return (int) $data;
}

// ============================================================
//  DATABASE — QUERY HELPERS
// ============================================================

// ------------------------------------------------------------
// Fungsi: query($conn, $sql)
// Jalankan query dan kembalikan hasilnya
// Langsung hentikan program jika query error
// ------------------------------------------------------------
function query($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die("<b>Query Error:</b> " . mysqli_error($conn) . "<br><b>SQL:</b> " . $sql);
    }
    return $result;
}

// ------------------------------------------------------------
// Fungsi: ambilSemua($conn, $sql)
// Kembalikan semua baris hasil query sebagai array
// ------------------------------------------------------------
function ambilSemua($conn, $sql) {
    $result = query($conn, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// ------------------------------------------------------------
// Fungsi: ambilSatu($conn, $sql)
// Kembalikan satu baris hasil query sebagai array
// ------------------------------------------------------------
function ambilSatu($conn, $sql) {
    $result = query($conn, $sql);
    return mysqli_fetch_assoc($result);
}

// ------------------------------------------------------------
// Fungsi: hitungBaris($conn, $sql)
// Kembalikan jumlah baris hasil query
// ------------------------------------------------------------
function hitungBaris($conn, $sql) {
    $result = query($conn, $sql);
    return mysqli_num_rows($result);
}

// ------------------------------------------------------------
// Fungsi: escape($conn, $data)
// Escape string untuk mencegah SQL Injection
// SELALU gunakan ini untuk data yang masuk ke dalam query SQL
// ------------------------------------------------------------
function escape($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}

// ============================================================
//  REDIRECT & PESAN
// ============================================================

// ------------------------------------------------------------
// Fungsi: redirect($url)
// Arahkan user ke URL lain
// ------------------------------------------------------------
function redirect($url) {
    header("Location: " . $url);
    exit;
}

// ------------------------------------------------------------
// Fungsi: setPesan($tipe, $teks)
// Simpan pesan notifikasi ke session
// Tipe: 'sukses', 'error', 'info'
// ------------------------------------------------------------
function setPesan($tipe, $teks) {
    $_SESSION['pesan']      = $teks;
    $_SESSION['pesan_tipe'] = $tipe;
}

// ------------------------------------------------------------
// Fungsi: tampilPesan()
// Tampilkan pesan notifikasi dari session lalu hapus
// Taruh fungsi ini di bagian atas konten halaman
// ------------------------------------------------------------
function tampilPesan() {
    if (!empty($_SESSION['pesan'])) {
        $teks = bersihkan($_SESSION['pesan']);
        $tipe = $_SESSION['pesan_tipe'] ?? 'info';

        $warna = [
            'sukses' => '#EAF3DE',
            'error'  => '#FCEBEB',
            'info'   => '#E6F1FB',
        ];
        $warnaText = [
            'sukses' => '#3B6D11',
            'error'  => '#A32D2D',
            'info'   => '#0C447C',
        ];

        $bg  = $warna[$tipe]     ?? $warna['info'];
        $txt = $warnaText[$tipe] ?? $warnaText['info'];

        echo "
        <div style='background:{$bg}; color:{$txt}; border-radius:8px;
                    padding:12px 16px; margin-bottom:16px; font-size:14px;'>
            {$teks}
        </div>";

        // Hapus pesan setelah ditampilkan
        unset($_SESSION['pesan'], $_SESSION['pesan_tipe']);
    }
}

// ============================================================
//  FORMAT & TAMPILAN
// ============================================================

// ------------------------------------------------------------
// Fungsi: formatTanggal($tanggal)
// Ubah format tanggal dari Y-m-d ke d F Y (Indonesia)
// Contoh: 2026-06-15 → 15 Juni 2026
// ------------------------------------------------------------
function formatTanggal($tanggal) {
    $bulan = [
        '01'=>'Januari','02'=>'Februari','03'=>'Maret',
        '04'=>'April',  '05'=>'Mei',     '06'=>'Juni',
        '07'=>'Juli',   '08'=>'Agustus', '09'=>'September',
        '10'=>'Oktober','11'=>'November','12'=>'Desember'
    ];
    $parts = explode('-', $tanggal);
    return $parts[2] . ' ' . ($bulan[$parts[1]] ?? $parts[1]) . ' ' . $parts[0];
}

// ------------------------------------------------------------
// Fungsi: sisaHari($deadline)
// Hitung sisa hari hingga deadline
// ------------------------------------------------------------
function sisaHari($deadline) {
    $sekarang   = new DateTime();
    $tglDeadline = new DateTime($deadline);
    $selisih    = $sekarang->diff($tglDeadline);

    if ($tglDeadline < $sekarang) {
        return '<span style="color:#A32D2D">Sudah tutup</span>';
    } elseif ($selisih->days == 0) {
        return '<span style="color:#854F0B">Hari ini terakhir</span>';
    } elseif ($selisih->days <= 3) {
        return '<span style="color:#854F0B">Sisa ' . $selisih->days . ' hari</span>';
    } else {
        return 'Sisa ' . $selisih->days . ' hari';
    }
}

// ------------------------------------------------------------
// Fungsi: potongTeks($teks, $maks)
// Potong teks panjang dan tambahkan "..."
// ------------------------------------------------------------
function potongTeks($teks, $maks = 100) {
    if (strlen($teks) <= $maks) return $teks;
    return substr($teks, 0, $maks) . '...';
}

// ------------------------------------------------------------
// Fungsi: badgeTipeKerja($tipe)
// Tampilkan badge berwarna untuk tipe kerja
// ------------------------------------------------------------
function badgeTipeKerja($tipe) {
    $config = [
        'remote'  => ['bg'=>'#E1F5EE', 'txt'=>'#085041', 'label'=>'Remote'],
        'onsite'  => ['bg'=>'#E6F1FB', 'txt'=>'#0C447C', 'label'=>'On-site'],
        'hybrid'  => ['bg'=>'#FAEEDA', 'txt'=>'#633806', 'label'=>'Hybrid'],
    ];
    $c = $config[$tipe] ?? ['bg'=>'#F1EFE8','txt'=>'#5F5E5A','label'=>ucfirst($tipe)];
    return "<span style='background:{$c['bg']};color:{$c['txt']};
                padding:2px 10px;border-radius:20px;font-size:12px;
                font-weight:500;'>{$c['label']}</span>";
}

// ------------------------------------------------------------
// Fungsi: badgeStatusLamaran($status)
// Tampilkan badge berwarna untuk status lamaran
// ------------------------------------------------------------
function badgeStatusLamaran($status) {
    $config = [
        'pending'  => ['bg'=>'#FAEEDA', 'txt'=>'#633806', 'label'=>'Menunggu'],
        'diterima' => ['bg'=>'#EAF3DE', 'txt'=>'#3B6D11', 'label'=>'Diterima'],
        'ditolak'  => ['bg'=>'#FCEBEB', 'txt'=>'#A32D2D', 'label'=>'Ditolak'],
    ];
    $c = $config[$status] ?? ['bg'=>'#F1EFE8','txt'=>'#5F5E5A','label'=>ucfirst($status)];
    return "<span style='background:{$c['bg']};color:{$c['txt']};
                padding:2px 10px;border-radius:20px;font-size:12px;
                font-weight:500;'>{$c['label']}</span>";
}

// ============================================================
//  UPLOAD FILE
// ============================================================

// ------------------------------------------------------------
// Fungsi: uploadFile($file, $folder, $ekstensiDiizinkan, $maks)
// Upload file dan kembalikan nama file baru, atau false jika gagal
//
// Parameter:
//   $file              = $_FILES['nama_input']
//   $folder            = UPLOAD_CV atau UPLOAD_FOTO
//   $ekstensiDiizinkan = ['pdf', 'doc', 'docx'] atau ['jpg','png']
//   $maks              = ukuran maksimal dalam bytes (default 2MB)
// ------------------------------------------------------------
function uploadFile($file, $folder, $ekstensiDiizinkan, $maks = 2097152) {
    // Cek apakah ada file yang diupload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Cek ukuran file
    if ($file['size'] > $maks) {
        setPesan('error', 'Ukuran file terlalu besar. Maksimal ' . ($maks / 1024 / 1024) . 'MB.');
        return false;
    }

    // Ambil ekstensi file
    $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Cek ekstensi yang diizinkan
    if (!in_array($ekstensi, $ekstensiDiizinkan)) {
        setPesan('error', 'Format file tidak diizinkan. Format yang diterima: ' . implode(', ', $ekstensiDiizinkan));
        return false;
    }

    // Buat nama file unik agar tidak bentrok
    $namaFile = uniqid('file_', true) . '.' . $ekstensi;
    $tujuan   = $folder . $namaFile;

    // Pindahkan file dari temporary ke folder tujuan
    if (move_uploaded_file($file['tmp_name'], $tujuan)) {
        return $namaFile;
    }

    return false;
}
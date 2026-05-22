<!-- ============================================================
     includes/footer.php
     Footer global untuk halaman publik
     ============================================================ -->
<footer class="footer">
    <div class="container footer-inner">
        <div class="footer-brand">
            <span class="footer-logo">💼 <?= APP_NAME ?></span>
            <p>Platform lowongan kerja & magang untuk mahasiswa kampus.</p>
        </div>
        <div class="footer-links">
            <p><strong>Navigasi</strong></p>
            <a href="<?= isset($baseCSS) ? $baseCSS : '' ?>index.php">Beranda</a>
            <a href="<?= isset($baseCSS) ? $baseCSS : '' ?>lowongan.php">Lowongan</a>
            <a href="<?= isset($baseCSS) ? $baseCSS : '' ?>about.php">Tentang Tim</a>
        </div>
        <div class="footer-links">
            <p><strong>Akun</strong></p>
            <a href="<?= isset($baseCSS) ? $baseCSS : '' ?>login.php">Login</a>
            <a href="<?= isset($baseCSS) ? $baseCSS : '' ?>register.php">Daftar</a>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> <?= APP_NAME ?> — Tugas Project Pemrograman Web</p>
    </div>
</footer>

</body>
</html>
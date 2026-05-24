/* ================================================================
   main.js — KerjaCampus | Halaman Publik
   ================================================================ */

document.addEventListener('DOMContentLoaded', function () {

  // ----------------------------------------------------------------
  // 1. NAVBAR — shadow saat scroll + hamburger mobile
  // ----------------------------------------------------------------
  const navbar = document.querySelector('.navbar');
  const hamburger = document.querySelector('.nav-hamburger');
  const navMenu = document.getElementById('navMenu');

  if (navbar) {
    window.addEventListener('scroll', () => {
      navbar.classList.toggle('scrolled', window.scrollY > 20);
    });
  }

  if (hamburger && navMenu) {
    hamburger.addEventListener('click', () => {
      navMenu.classList.toggle('open');
      hamburger.textContent = navMenu.classList.contains('open') ? '✕' : '☰';
    });
    // Tutup menu saat klik di luar
    document.addEventListener('click', (e) => {
      if (!navbar.contains(e.target)) {
        navMenu.classList.remove('open');
        hamburger.textContent = '☰';
      }
    });
  }

  // ----------------------------------------------------------------
  // 2. HERO SEARCH — animasi placeholder cycling
  // ----------------------------------------------------------------
  const heroInput = document.querySelector('.hero-search input');
  if (heroInput) {
    const placeholders = [
      'Cari posisi UI/UX Designer...',
      'Cari Web Developer di Manado...',
      'Cari magang bidang Marketing...',
      'Cari Admin Pemasaran...',
      'Cari lowongan remote...',
    ];
    let idx = 0;
    let charIdx = 0;
    let deleting = false;
    let pausing = false;

    function typePlaceholder() {
      if (pausing) return;
      const current = placeholders[idx];
      if (!deleting) {
        charIdx++;
        heroInput.placeholder = current.slice(0, charIdx);
        if (charIdx === current.length) {
          pausing = true;
          setTimeout(() => { pausing = false; deleting = true; }, 2000);
        }
      } else {
        charIdx--;
        heroInput.placeholder = current.slice(0, charIdx);
        if (charIdx === 0) {
          deleting = false;
          idx = (idx + 1) % placeholders.length;
        }
      }
    }
    setInterval(typePlaceholder, deleting ? 40 : 80);
  }

  // ----------------------------------------------------------------
  // 3. FILTER FORM — submit otomatis saat pilih dropdown
  // ----------------------------------------------------------------
  const filterForm = document.getElementById('formFilter');
  if (filterForm) {
    filterForm.querySelectorAll('select').forEach(sel => {
      sel.addEventListener('change', () => filterForm.submit());
    });
    filterForm.querySelectorAll('input[type="radio"]').forEach(radio => {
      radio.addEventListener('change', () => filterForm.submit());
    });
  }

  // ----------------------------------------------------------------
  // 4. SCROLL REVEAL — animasi masuk saat scroll
  // ----------------------------------------------------------------
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('revealed');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

  document.querySelectorAll('.job-card, .job-list-card, .profil-job-row, .team-card').forEach(el => {
    el.classList.add('reveal-item');
    observer.observe(el);
  });

  // ----------------------------------------------------------------
  // 5. ALERT — auto dismiss setelah 5 detik
  // ----------------------------------------------------------------
  document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.5s, transform 0.5s';
      alert.style.opacity = '0';
      alert.style.transform = 'translateY(-8px)';
      setTimeout(() => alert.remove(), 500);
    }, 5000);
  });

  // ----------------------------------------------------------------
  // 6. SMOOTH SCROLL untuk anchor link
  // ----------------------------------------------------------------
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const target = document.querySelector(a.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // ----------------------------------------------------------------
  // 7. COUNTER ANIMASI — angka statistik di hero
  // ----------------------------------------------------------------
  function animateCounter(el, target, duration = 1500) {
    let start = 0;
    const step = target / (duration / 16);
    const timer = setInterval(() => {
      start += step;
      if (start >= target) { start = target; clearInterval(timer); }
      el.textContent = Math.floor(start);
    }, 16);
  }

  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const num = parseInt(entry.target.getAttribute('data-target'));
        if (!isNaN(num)) animateCounter(entry.target, num);
        counterObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('[data-target]').forEach(el => counterObserver.observe(el));

  // ----------------------------------------------------------------
  // 8. KONFIRMASI HAPUS — semua form dengan class confirm-delete
  // ----------------------------------------------------------------
  document.querySelectorAll('.confirm-delete').forEach(form => {
    form.addEventListener('submit', e => {
      const msg = form.getAttribute('data-msg') || 'Yakin ingin menghapus data ini?';
      if (!confirm(msg)) e.preventDefault();
    });
  });

  // ----------------------------------------------------------------
  // 9. TOOLTIP sederhana
  // ----------------------------------------------------------------
  document.querySelectorAll('[data-tooltip]').forEach(el => {
    el.style.position = 'relative';
    el.style.cursor = 'help';
    el.addEventListener('mouseenter', () => {
      const tip = document.createElement('div');
      tip.className = 'tooltip-bubble';
      tip.textContent = el.getAttribute('data-tooltip');
      tip.style.cssText = `
        position:absolute; bottom:calc(100% + 6px); left:50%; transform:translateX(-50%);
        background:#1e293b; color:#fff; font-size:11px; padding:5px 10px;
        border-radius:6px; white-space:nowrap; z-index:9999; pointer-events:none;
        box-shadow:0 4px 12px rgba(0,0,0,0.2);
      `;
      el.appendChild(tip);
    });
    el.addEventListener('mouseleave', () => {
      el.querySelector('.tooltip-bubble')?.remove();
    });
  });

});

/* ----------------------------------------------------------------
   CSS tambahan via JS untuk reveal animation
   ---------------------------------------------------------------- */
const revealStyle = document.createElement('style');
revealStyle.textContent = `
  .reveal-item {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.5s ease, transform 0.5s ease;
  }
  .reveal-item.revealed {
    opacity: 1;
    transform: translateY(0);
  }
`;
document.head.appendChild(revealStyle);
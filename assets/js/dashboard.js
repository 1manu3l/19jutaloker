/* ================================================================
   dashboard.js — KerjaCampus | Panel Dashboard
   Admin, Mahasiswa, Perusahaan
   ================================================================ */

document.addEventListener("DOMContentLoaded", function () {
  // ----------------------------------------------------------------
  // 1. SIDEBAR MOBILE — toggle buka/tutup
  // ----------------------------------------------------------------
  const mobileToggle = document.querySelector(".topbar-mobile-toggle");
  const sidebar = document.querySelector(".sidebar");
  const overlay = document.createElement("div");

  overlay.style.cssText = `
    display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5);
    z-index:199; backdrop-filter:blur(2px);
  `;
  document.body.appendChild(overlay);

  if (mobileToggle && sidebar) {
    mobileToggle.addEventListener("click", () => {
      sidebar.classList.toggle("open");
      overlay.style.display = sidebar.classList.contains("open")
        ? "block"
        : "none";
      mobileToggle.textContent = sidebar.classList.contains("open")
        ? "✕"
        : "☰";
    });
    overlay.addEventListener("click", () => {
      sidebar.classList.remove("open");
      overlay.style.display = "none";
      mobileToggle.textContent = "☰";
    });
  }

  // ----------------------------------------------------------------
  // 2. TOGGLE FORM TAMBAH/EDIT
  // ----------------------------------------------------------------
  window.toggleForm = function (panelId = "formPanel") {
    const panel = document.getElementById(panelId);
    if (!panel) return;
    const isHidden =
      panel.style.display === "none" || panel.style.display === "";
    panel.style.display = isHidden ? "block" : "none";
    if (isHidden) {
      panel.scrollIntoView({ behavior: "smooth", block: "nearest" });
      panel.style.opacity = "0";
      panel.style.transform = "translateY(-10px)";
      requestAnimationFrame(() => {
        panel.style.transition = "opacity 0.3s, transform 0.3s";
        panel.style.opacity = "1";
        panel.style.transform = "translateY(0)";
      });
    }
  };

  // ----------------------------------------------------------------
  // 3. KONFIRMASI HAPUS
  // ----------------------------------------------------------------
  document.querySelectorAll(".confirm-delete, [data-confirm]").forEach((el) => {
    el.addEventListener("submit", function (e) {
      const msg =
        this.getAttribute("data-msg") ||
        "Yakin ingin menghapus data ini? Tindakan ini tidak bisa dibatalkan.";
      if (!confirm(msg)) e.preventDefault();
    });
    // Untuk button langsung (bukan form)
    if (el.tagName === "BUTTON" || el.tagName === "A") {
      el.addEventListener("click", function (e) {
        const msg = this.getAttribute("data-confirm");
        if (msg && !confirm(msg)) e.preventDefault();
      });
    }
  });

  // ----------------------------------------------------------------
  // 4. AUTO-SUBMIT form filter saat select berubah
  // ----------------------------------------------------------------
  document
    .querySelectorAll('.auto-submit select, .auto-submit input[type="radio"]')
    .forEach((el) => {
      el.addEventListener("change", () => el.closest("form").submit());
    });

  // ----------------------------------------------------------------
  // 5. ALERT — auto dismiss
  // ----------------------------------------------------------------
  document.querySelectorAll(".alert").forEach((alert) => {
    // Tambah tombol close
    const closeBtn = document.createElement("button");
    closeBtn.innerHTML = "✕";
    closeBtn.style.cssText = `
      margin-left:auto; background:none; border:none; cursor:pointer;
      color:inherit; opacity:0.6; font-size:14px; padding:0 0 0 10px;
    `;
    closeBtn.addEventListener("click", () => dismissAlert(alert));
    alert.style.display = "flex";
    alert.appendChild(closeBtn);

    // Auto dismiss setelah 6 detik
    setTimeout(() => dismissAlert(alert), 6000);
  });

  function dismissAlert(el) {
    if (!el.parentNode) return;
    el.style.transition = "opacity 0.4s, transform 0.4s, max-height 0.4s";
    el.style.opacity = "0";
    el.style.transform = "translateY(-6px)";
    el.style.maxHeight = "0";
    el.style.marginBottom = "0";
    el.style.padding = "0";
    el.style.overflow = "hidden";
    setTimeout(() => el.remove(), 400);
  }

  // ----------------------------------------------------------------
  // 6. UBAH STATUS LAMARAN — submit form otomatis saat select berubah
  // ----------------------------------------------------------------
  document.querySelectorAll(".status-select").forEach((sel) => {
    sel.addEventListener("change", function () {
      const form = this.closest("form");
      if (form) {
        const badge = form.previousElementSibling;
        form.submit();
      }
    });
  });

  // ----------------------------------------------------------------
  // 7. PREVIEW GAMBAR upload foto profil
  // ----------------------------------------------------------------
  const fotoInput = document.getElementById("foto");
  const fotoPreview = document.getElementById("fotoPreview");
  if (fotoInput && fotoPreview) {
    fotoInput.addEventListener("change", function () {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
          fotoPreview.style.backgroundImage = `url(${e.target.result})`;
          fotoPreview.textContent = "";
        };
        reader.readAsDataURL(this.files[0]);
      }
    });
  }

  // ----------------------------------------------------------------
  // 8. TOPBAR — highlight halaman aktif dari URL
  // ----------------------------------------------------------------
  const currentPath = window.location.pathname.split("/").pop();
  document.querySelectorAll(".sidebar-nav a").forEach((link) => {
    const href = link.getAttribute("href");
    if (href && href.includes(currentPath) && currentPath !== "") {
      link.classList.add("aktif");
    }
  });

  // ----------------------------------------------------------------
  // 9. TABLE SEARCH — cari data di tabel secara client-side
  // ----------------------------------------------------------------
  const tableSearch = document.getElementById("tableSearch");
  if (tableSearch) {
    tableSearch.addEventListener("input", function () {
      const query = this.value.toLowerCase().trim();
      const rows = document.querySelectorAll(
        ".data-table tbody tr:not(.empty-row)",
      );
      let visible = 0;
      rows.forEach((row) => {
        const text = row.textContent.toLowerCase();
        const match = text.includes(query);
        row.style.display = match ? "" : "none";
        if (match) visible++;
      });
      const counter = document.getElementById("tableCount");
      if (counter) counter.textContent = visible;
    });
  }

  // ----------------------------------------------------------------
  // 10. CHAR COUNTER untuk textarea
  // ----------------------------------------------------------------
  document.querySelectorAll("textarea[maxlength]").forEach((ta) => {
    const max = parseInt(ta.getAttribute("maxlength"));
    const counter = document.createElement("small");
    counter.style.cssText =
      "display:block; text-align:right; font-size:11px; color:#9ca3af; margin-top:3px;";
    counter.textContent = `0 / ${max}`;
    ta.parentNode.appendChild(counter);
    ta.addEventListener("input", () => {
      const len = ta.value.length;
      counter.textContent = `${len} / ${max}`;
      counter.style.color = len > max * 0.9 ? "#ef4444" : "#9ca3af";
    });
  });

  // ----------------------------------------------------------------
  // 11. FORM VALIDATION — highlight field kosong saat submit
  // ----------------------------------------------------------------
  document.querySelectorAll("form").forEach((form) => {
    form.addEventListener("submit", function (e) {
      let valid = true;
      this.querySelectorAll("[required]").forEach((field) => {
        field.style.borderColor = "";
        if (!field.value.trim()) {
          field.style.borderColor = "#ef4444";
          field.style.boxShadow = "0 0 0 3px rgba(239,68,68,0.1)";
          valid = false;
          field.addEventListener(
            "input",
            () => {
              field.style.borderColor = "";
              field.style.boxShadow = "";
            },
            { once: true },
          );
        }
      });
      if (!valid) {
        e.preventDefault();
        const firstError = this.querySelector('[required][style*="ef4444"]');
        if (firstError)
          firstError.scrollIntoView({ behavior: "smooth", block: "center" });
      }
    });
  });

  // ----------------------------------------------------------------
  // 12. TOPBAR TITLE — update dari h1 halaman
  // ----------------------------------------------------------------
  const pageH1 = document.querySelector(".page-header h1");
  const topbarTitle = document.querySelector(".topbar-title");
  if (pageH1 && topbarTitle) {
    topbarTitle.textContent = pageH1.textContent.replace(/^[^\w\s]*\s/, ""); // hapus emoji di depan
  }
});

/* ----------------------------------------------------------------
   Fungsi global — bisa dipanggil dari inline onclick
   ---------------------------------------------------------------- */
function konfirmHapus(msg) {
  return confirm(
    msg || "Yakin ingin menghapus? Tindakan ini tidak bisa dibatalkan.",
  );
}

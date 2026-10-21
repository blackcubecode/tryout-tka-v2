// ============================================================
// ujian.js — Timer + autosave + navigasi soal
// Tryout TKA v2
// ============================================================
(function () {
  'use strict';

  const body = document.body;
  if (!body.classList.contains('halaman-ujian')) return;

  const SESI_ID   = parseInt(body.dataset.sesi, 10);
  const MAPEL     = body.dataset.mapel;
  const SISA_AWAL = parseInt(body.dataset.sisa, 10);

  const timerEl    = document.getElementById('timer');
  const soalEls    = Array.from(document.querySelectorAll('.soal'));
  const nomorEls   = Array.from(document.querySelectorAll('.grid-nomor .nomor'));
  const indikator  = document.getElementById('indikator');
  const btnPrev    = document.getElementById('btn-prev');
  const btnNext    = document.getElementById('btn-next');
  const btnSelesai = document.getElementById('btn-selesai');
  const overlay    = document.getElementById('overlay-selesai');
  const jmlTerj    = document.getElementById('jumlah-terjawab');
  const btnBatal   = document.getElementById('btn-batal');
  const btnYa      = document.getElementById('btn-ya-selesai');

  let aktif = 1;
  let sisa = SISA_AWAL;
  let sudahSubmit = false;

  function renderTimer() {
    const m = Math.floor(sisa / 60);
    const s = sisa % 60;
    timerEl.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    timerEl.classList.remove('warning', 'danger');
    if (sisa <= 60)       timerEl.classList.add('danger');
    else if (sisa <= 300) timerEl.classList.add('warning');
  }

  function tick() {
    if (sisa <= 0) { submitOtomatis(); return; }
    sisa--;
    renderTimer();
  }
  renderTimer();
  const ivTimer = setInterval(tick, 1000);

  setInterval(async () => {
    try {
      const r = await fetch('api/sisa_waktu.php?sesi_id=' + SESI_ID, { cache: 'no-store' });
      const j = await r.json();
      if (j.ok) {
        sisa = j.sisa;
        renderTimer();
        if (j.status === 'selesai') {
          window.location.href = 'selesai.php?mapel=' + encodeURIComponent(MAPEL);
        }
      }
    } catch (e) {}
  }, 30000);
  function tampilkanSoal(nomor) {
    aktif = Math.max(1, Math.min(soalEls.length, nomor));
    soalEls.forEach(el => {
      el.style.display = (parseInt(el.dataset.nomor, 10) === aktif) ? '' : 'none';
    });
    nomorEls.forEach(el => {
      el.classList.toggle('aktif', parseInt(el.dataset.nomor, 10) === aktif);
    });
    indikator.textContent = aktif + ' / ' + soalEls.length;
    btnPrev.disabled = aktif === 1;
    btnNext.disabled = aktif === soalEls.length;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  btnPrev.addEventListener('click', () => tampilkanSoal(aktif - 1));
  btnNext.addEventListener('click', () => tampilkanSoal(aktif + 1));
  nomorEls.forEach(el => {
    el.addEventListener('click', () => tampilkanSoal(parseInt(el.dataset.nomor, 10)));
  });
  tampilkanSoal(1);

  function hitungTerjawab() {
    return document.querySelectorAll('.pilihan.dipilih').length;
  }

  async function simpan(soalId, huruf, labelEl, nomorBtn) {
    labelEl.classList.add('dipilih');
    labelEl.parentElement.querySelectorAll('.pilihan').forEach(el => {
      if (el !== labelEl) el.classList.remove('dipilih');
    });
    if (nomorBtn) nomorBtn.classList.add('terjawab');
    try {
      const r = await fetch('api/simpan.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ sesi_id: SESI_ID, soal_id: soalId, pilihan: huruf })
      });
      const j = await r.json();
      if (!j.ok) {
        console.warn('Simpan gagal:', j.error);
        if (j.error === 'waktu habis') submitOtomatis();
      }
    } catch (e) {
      console.warn('Network error saat simpan:', e);
    }
  }

  document.querySelectorAll('.pilihan input[type=radio]').forEach(inp => {
    inp.addEventListener('change', () => {
      const soalId = parseInt(inp.dataset.soalId, 10);
      const huruf  = inp.dataset.huruf;
      const label  = inp.closest('.pilihan');
      const nomorBtn = document.querySelector(
        '.grid-nomor .nomor[data-soal-id="' + soalId + '"]'
      );
      simpan(soalId, huruf, label, nomorBtn);
    });
  });
  btnSelesai.addEventListener('click', () => {
    jmlTerj.textContent = hitungTerjawab();
    overlay.style.display = 'flex';
  });
  btnBatal.addEventListener('click', () => { overlay.style.display = 'none'; });

  async function submitOtomatis() {
    if (sudahSubmit) return;
    sudahSubmit = true;
    clearInterval(ivTimer);
    timerEl.textContent = '00:00';
    try {
      await fetch('api/submit.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ sesi_id: SESI_ID })
      });
    } catch (e) {}
    window.location.href = 'selesai.php?mapel=' + encodeURIComponent(MAPEL);
  }

  btnYa.addEventListener('click', async () => {
    btnYa.disabled = true;
    btnYa.textContent = 'Menyimpan...';
    sudahSubmit = true;
    clearInterval(ivTimer);
    try {
      const r = await fetch('api/submit.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ sesi_id: SESI_ID })
      });
      const j = await r.json();
      if (j.ok) {
        window.location.href = 'selesai.php?mapel=' + encodeURIComponent(MAPEL);
      } else {
        alert('Gagal menyimpan: ' + (j.error || 'unknown'));
        btnYa.disabled = false;
        btnYa.textContent = 'Ya, Selesai';
        sudahSubmit = false;
      }
    } catch (e) {
      alert('Koneksi terputus. Coba lagi.');
      btnYa.disabled = false;
      btnYa.textContent = 'Ya, Selesai';
      sudahSubmit = false;
    }
  });

  window.addEventListener('beforeunload', (e) => {
    if (!sudahSubmit) {
      e.preventDefault();
      e.returnValue = '';
    }
  });

  document.addEventListener('contextmenu', e => e.preventDefault());
  document.addEventListener('keydown', (e) => {
    if (e.key === 'F5' ||
        (e.ctrlKey && ['r','R','p','P'].includes(e.key)) ||
        e.key === 'F12') {
      e.preventDefault();
    }
  });
})();

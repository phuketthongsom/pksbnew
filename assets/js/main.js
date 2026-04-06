// ── Timetable tabs ───────────────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const target = btn.dataset.tab;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    const pane = document.getElementById(target);
    if (pane) pane.classList.add('active');
  });
});

// ── Highlight current time in timetable ─────────────────────
(function () {
  if (!document.querySelector('.tt-table')) return;
  const now  = new Date();
  const hhmm = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
  document.querySelectorAll('.tt-table td:not(:first-child)').forEach(td => {
    if (td.textContent.trim() === hhmm) td.classList.add('now-cell');
  });
})();

// ── Alert auto-dismiss ───────────────────────────────────────
document.querySelectorAll('.alert').forEach(el => {
  setTimeout(() => {
    el.style.transition = 'opacity .4s';
    el.style.opacity    = '0';
    setTimeout(() => el.remove(), 400);
  }, 5000);
});

// ── PWA install prompt ───────────────────────────────────────
let _deferredInstall = null;
window.addEventListener('beforeinstallprompt', e => {
  e.preventDefault();
  _deferredInstall = e;
  const btn = document.getElementById('pwa-install-btn');
  if (btn) btn.style.display = 'flex';
});
function pwaInstall() {
  if (!_deferredInstall) return;
  _deferredInstall.prompt();
  _deferredInstall.userChoice.then(() => { _deferredInstall = null; });
  const btn = document.getElementById('pwa-install-btn');
  if (btn) btn.style.display = 'none';
}
window.addEventListener('appinstalled', () => {
  const btn = document.getElementById('pwa-install-btn');
  if (btn) btn.style.display = 'none';
});

// ── Smooth scroll for anchor links ──────────────────────────
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) {
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
});

// ── Toast notification helper ────────────────────────────────
function showToast(msg, type = 'info', duration = 3500) {
  const t = document.createElement('div');
  t.className = 'toast toast-' + type;
  t.textContent = msg;
  document.body.appendChild(t);
  requestAnimationFrame(() => t.classList.add('toast-show'));
  setTimeout(() => {
    t.classList.remove('toast-show');
    setTimeout(() => t.remove(), 350);
  }, duration);
}

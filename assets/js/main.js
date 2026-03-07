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

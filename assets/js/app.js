// assets/js/app.js — IMS client-side helpers

// ── AUTO-DISMISS ALERTS ──────────────────────────────────
document.querySelectorAll('.alert').forEach(el => {
  setTimeout(() => {
    el.style.transition = 'opacity .4s, transform .4s';
    el.style.opacity = '0';
    el.style.transform = 'translateY(-4px)';
    setTimeout(() => el.remove(), 420);
  }, 3800);
});

// ── ACTIVE NAV HIGHLIGHT ─────────────────────────────────
(function () {
  const path = window.location.pathname;
  document.querySelectorAll('.nav-item').forEach(item => {
    const href = item.getAttribute('href') || '';
    if (href && path.endsWith(href.replace(/^\//, ''))) {
      item.classList.add('active');
    }
  });
})();

// ── CONFIRM DELETE via data-attribute ───────────────────
// Usage: <a href="delete.php?id=1" data-confirm="Delete this item?">Delete</a>
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => {
    if (!confirm(el.dataset.confirm)) e.preventDefault();
  });
});

// ── SKU AUTO-UPPERCASE ───────────────────────────────────
document.querySelectorAll('input[name="sku"]').forEach(input => {
  input.addEventListener('input', () => {
    const pos = input.selectionStart;
    input.value = input.value.toUpperCase();
    input.setSelectionRange(pos, pos);
  });
});

// ── STOCK QTY DISPLAY (for stock/index.php) ──────────────
// Called inline from the select onchange; also available globally
window.showQty = function (sel, displayId) {
  const opt    = sel.options[sel.selectedIndex];
  const qty    = parseInt(opt.dataset.qty    ?? -1);
  const thresh = parseInt(opt.dataset.thresh ?? 0);
  const box    = document.getElementById(displayId);
  const valEl  = document.getElementById(displayId + '-val');
  if (!sel.value || qty < 0) {
    box && box.classList.remove('visible');
    return;
  }
  box && box.classList.add('visible');
  if (valEl) {
    valEl.textContent = qty;
    valEl.className   = 'qty-val ' + (qty === 0 ? 'zero' : qty <= thresh ? 'warn' : 'ok');
  }
};

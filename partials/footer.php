  </div><!-- /content -->
</main>

<script src="/ims-starter/assets/js/app.js"></script>
<script src="/ims-starter/assets/js/spotlight-effects.js"></script>
<script>
// ── SIDEBAR TOGGLE ──────────────────────────────────────────
function toggleSidebar() {
  const sidebar  = document.getElementById('sidebar');
  const overlay  = document.getElementById('sidebar-overlay');
  const burger   = document.getElementById('hamburger');
  const isOpen   = sidebar.classList.contains('open');
  if (isOpen) {
    sidebar.classList.remove('open');
    overlay.classList.remove('open');
    burger.classList.remove('open');
    document.body.style.overflow = '';
  } else {
    sidebar.classList.add('open');
    overlay.classList.add('open');
    burger.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebar-overlay').classList.remove('open');
  const burger = document.getElementById('hamburger');
  if (burger) burger.classList.remove('open');
  document.body.style.overflow = '';
}
// Close on resize to desktop
window.addEventListener('resize', () => {
  if (window.innerWidth > 768) closeSidebar();
});
// Close on Escape key
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeSidebar();
});
</script>
</body>
</html>

// assets/js/shop.js — CGShop storefront JS

// ── CART ENGINE ──────────────────────────────────────────────
const CART_KEY = 'cgshop_cart';

function getCart() {
  try { return JSON.parse(localStorage.getItem(CART_KEY)) || []; }
  catch { return []; }
}
function saveCart(cart) {
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
  renderCart();
  updateCartCount();
}
function clearCart() {
  localStorage.removeItem(CART_KEY);
  renderCart();
  updateCartCount();
}

function addToCart(id, name, price, img, maxQty) {
  const cart = getCart();
  const idx  = cart.findIndex(i => i.id === id);
  if (idx > -1) {
    if (cart[idx].qty >= maxQty) {
      showToast('Maximum stock reached for ' + name, 'error'); return;
    }
    cart[idx].qty++;
  } else {
    cart.push({ id, name, price: parseFloat(price), img: img || '', qty: 1, maxQty });
  }
  saveCart(cart);
  showToast(name + ' added to cart');
  openCart();
}

function removeFromCart(id) {
  saveCart(getCart().filter(i => i.id !== id));
}

function changeQty(id, delta) {
  const cart = getCart();
  const idx  = cart.findIndex(i => i.id === id);
  if (idx === -1) return;
  cart[idx].qty += delta;
  if (cart[idx].qty <= 0)            cart.splice(idx, 1);
  else if (cart[idx].qty > cart[idx].maxQty) { showToast('Max stock reached', 'error'); return; }
  saveCart(cart);
}

function updateCartCount() {
  const total = getCart().reduce((a, i) => a + i.qty, 0);
  document.querySelectorAll('#cart-count').forEach(el => el.textContent = total);
}

function fmtMoney(n) {
  return '₱' + parseFloat(n).toLocaleString('en-PH', { minimumFractionDigits: 2 });
}

function renderCart() {
  const cart    = getCart();
  const listEl  = document.getElementById('cart-items-list');
  const footEl  = document.getElementById('cart-foot');
  const totalEl = document.getElementById('cart-total');
  const headCnt = document.getElementById('cart-head-count');
  if (!listEl) return;

  if (!cart.length) {
    listEl.innerHTML = `
      <div class="cart-empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        <p>Your cart is empty</p>
        <button class="btn btn-outline btn-sm" onclick="toggleCart()">Start shopping</button>
      </div>`;
    if (footEl) footEl.style.display = 'none';
    if (headCnt) headCnt.textContent = '';
    return;
  }

  let total = 0;
  listEl.innerHTML = cart.map(item => {
    const sub = item.price * item.qty;
    total += sub;
    return `
    <div class="cart-item">
      <div class="cart-item-img">
        ${item.img ? `<img src="${item.img}" alt="${item.name}" style="width:100%;height:100%;object-fit:cover"/>` :
          `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="opacity:.3"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>`}
      </div>
      <div class="cart-item-info">
        <div class="cart-item-name">${item.name}</div>
        <div class="cart-item-price">${fmtMoney(item.price)} each</div>
        <div class="cart-qty">
          <button class="qty-btn" onclick="changeQty(${item.id},-1)">−</button>
          <span class="qty-num">${item.qty}</span>
          <button class="qty-btn" onclick="changeQty(${item.id},1)">+</button>
          <span style="font-family:var(--font-mono);font-size:12px;color:var(--text2);margin-left:4px">${fmtMoney(sub)}</span>
        </div>
      </div>
      <button class="cart-item-remove" onclick="removeFromCart(${item.id})" title="Remove">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
      </button>
    </div>`;
  }).join('');

  if (footEl) {
    footEl.style.display = 'block';
    if (totalEl) totalEl.textContent = fmtMoney(total);
  }
  if (headCnt) headCnt.textContent = `(${cart.length} item${cart.length !== 1 ? 's' : ''})`;
}

// ── CART PANEL TOGGLE ────────────────────────────────────────
function toggleCart() {
  const overlay = document.getElementById('cart-overlay');
  const panel   = document.getElementById('cart-panel');
  if (!overlay || !panel) return;
  overlay.classList.toggle('open');
  panel.classList.toggle('open');
}
function openCart() {
  const overlay = document.getElementById('cart-overlay');
  const panel   = document.getElementById('cart-panel');
  if (overlay) overlay.classList.add('open');
  if (panel)   panel.classList.add('open');
}

// ── TOAST ────────────────────────────────────────────────────
function showToast(msg, type = 'success') {
  const container = document.getElementById('shop-toast');
  if (!container) return;
  const el = document.createElement('div');
  el.className = `toast-item ${type}`;
  const icons = {
    success: '<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
    error:   '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
  };
  el.innerHTML = `
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${icons[type] || icons.success}</svg>
    <span class="toast-msg">${msg}</span>`;
  container.appendChild(el);
  setTimeout(() => { el.style.transition='.3s'; el.style.opacity='0'; el.style.transform='translateY(4px)'; }, 2800);
  setTimeout(() => el.remove(), 3200);
}

// ══════════════════════════════════════════════════════════════
// PRODUCT FEATURE HOTSPOTS + QUICK VIEW
// Additive only — nothing above this line was changed.
//
// Each .product-card carries a data-product attribute (JSON, written
// by catalog.php / shop/index.php) with the product's info and its
// `features` array ({label, description, pos_x, pos_y}). The grid
// markup already renders a small hotspot layer over the thumbnail
// for the hover preview; this script wires up the interaction and
// builds the larger Quick View modal on demand.
// ══════════════════════════════════════════════════════════════

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str == null ? '' : String(str);
  return div.innerHTML;
}

function readProductData(card) {
  try { return JSON.parse(card.dataset.product || '{}'); }
  catch { return {}; }
}

function buildHotspotMarkup(features, idPrefix) {
  return features.map((f, i) => `
    <div class="hotspot-dot" data-idx="${i}" style="left:${f.pos_x}%;top:${f.pos_y}%">
      ${i + 1}
      <div class="hotspot-tip" id="${idPrefix}-tip-${i}">
        <strong>${escapeHtml(f.label)}</strong>${escapeHtml(f.description || '')}
      </div>
    </div>`).join('');
}

// ── QUICK VIEW MODAL ──────────────────────────────────────────
function openQuickView(card) {
  const data     = readProductData(card);
  const overlay  = document.getElementById('quickview-overlay');
  if (!overlay) return;

  const features = Array.isArray(data.features) ? data.features : [];

  document.getElementById('qv-img').src = data.img || '';
  document.getElementById('qv-img').alt = data.name || '';
  document.getElementById('qv-cat').textContent  = data.category || 'General';
  document.getElementById('qv-name').textContent = data.name || '';
  document.getElementById('qv-desc').textContent = data.desc || 'Quality product from our catalogue.';
  document.getElementById('qv-price').textContent = fmtMoney(data.price || 0);
  document.getElementById('qv-stock').textContent = (data.stock ?? 0) + ' in stock';

  const hotspotsEl = document.getElementById('qv-hotspots');
  const listWrap   = document.getElementById('qv-features-wrap');
  const listEl     = document.getElementById('qv-feature-list');

  if (features.length) {
    hotspotsEl.innerHTML = buildHotspotMarkup(features, 'qv');
    listEl.innerHTML = features.map((f, i) => `
      <div class="quickview-feature-item" data-idx="${i}">
        <div class="quickview-feature-num">${i + 1}</div>
        <div>
          <div class="quickview-feature-name">${escapeHtml(f.label)}</div>
          ${f.description ? `<div class="quickview-feature-desc">${escapeHtml(f.description)}</div>` : ''}
        </div>
      </div>`).join('');
    listWrap.style.display = 'block';
  } else {
    hotspotsEl.innerHTML = '';
    listWrap.style.display = 'none';
  }

  overlay.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeQuickView() {
  const overlay = document.getElementById('quickview-overlay');
  if (!overlay) return;
  overlay.classList.remove('open');
  document.body.style.overflow = '';
}

function setActiveQvFeature(idx) {
  document.querySelectorAll('.quickview-feature-item').forEach(el =>
    el.classList.toggle('active', el.dataset.idx === String(idx)));
  document.querySelectorAll('#qv-hotspots .hotspot-dot').forEach(el =>
    el.classList.toggle('active', el.dataset.idx === String(idx)));
}

// ── EVENT WIRING (delegated so it works for every product card,
//    including ones rendered after a filter/search reload) ──────
document.addEventListener('DOMContentLoaded', () => {
  renderCart();
  updateCartCount();

  // Clicking a product image (but not a hotspot dot) opens Quick View
  document.addEventListener('click', e => {
    const dot = e.target.closest('.hotspot-dot');
    const img = e.target.closest('.product-img');
    const card = e.target.closest('.product-card');

    if (dot) {
      // Touch devices: tap toggles the tooltip instead of relying on hover
      e.stopPropagation();
      const tip = dot.querySelector('.hotspot-tip');
      document.querySelectorAll('.hotspot-tip.show').forEach(t => { if (t !== tip) t.classList.remove('show'); });
      tip?.classList.toggle('show');
      return;
    }
    if (img && card && !card.closest('.quickview-modal')) {
      openQuickView(card);
      return;
    }
    if (e.target.id === 'quickview-overlay') closeQuickView();
    if (e.target.closest('.quickview-close')) closeQuickView();

    // Hovering/clicking a Quick View feature row highlights its dot
    const qvItem = e.target.closest('.quickview-feature-item');
    if (qvItem) setActiveQvFeature(qvItem.dataset.idx);
  });

  // Desktop hover: show the tooltip for whichever dot the mouse is over
  document.addEventListener('mouseover', e => {
    const dot = e.target.closest('.hotspot-dot');
    if (dot) dot.querySelector('.hotspot-tip')?.classList.add('show');

    const qvItem = e.target.closest('.quickview-feature-item');
    if (qvItem) setActiveQvFeature(qvItem.dataset.idx);
  });
  document.addEventListener('mouseout', e => {
    const dot = e.target.closest('.hotspot-dot');
    if (dot && !dot.matches(':hover')) dot.querySelector('.hotspot-tip')?.classList.remove('show');

    const qvItem = e.target.closest('.quickview-feature-item');
    if (qvItem && !document.querySelector('.quickview-feature-item:hover')) {
      document.querySelectorAll('.quickview-feature-item, #qv-hotspots .hotspot-dot').forEach(el => el.classList.remove('active'));
    }
  });

  // Esc closes Quick View
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeQuickView();
  });
});

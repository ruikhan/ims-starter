<?php
// shop/checkout.php — Checkout page (cart is JS, order is POST)
require_once '../config/db.php';
require_once '../includes/helpers.php';
$shopName = 'CGShop';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Checkout — <?= $shopName ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/shop.css"/>
</head>
<body>
<nav class="shop-nav">
  <a href="<?= BASE_URL ?>/shop/index.php" class="nav-brand">
    <div class="nav-brand-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
    </div>
    <?= $shopName ?>
  </a>
  <div class="nav-actions">
    <a href="<?= BASE_URL ?>/shop/catalog.php" class="btn btn-ghost btn-sm">← Continue shopping</a>
  </div>
</nav>

<div class="checkout-wrap" style="padding-top:48px">
  <div style="margin-bottom:32px">
    <div style="font-size:12px;font-family:var(--font-mono);color:var(--accent);letter-spacing:1.2px;text-transform:uppercase;margin-bottom:8px">Checkout</div>
    <h1 style="font-family:var(--font-head);font-size:32px;font-weight:700">Complete your order</h1>
  </div>

  <div id="empty-cart-msg" style="display:none;text-align:center;padding:80px 20px;color:var(--text3)">
    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="margin:0 auto 16px;display:block;opacity:.3"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
    <p style="font-size:16px;margin-bottom:16px">Your cart is empty</p>
    <a href="<?= BASE_URL ?>/shop/catalog.php" class="btn btn-primary">Start shopping</a>
  </div>

  <div class="checkout-grid" id="checkout-content">
    <!-- Customer form -->
    <div class="checkout-card">
      <div class="checkout-title">Delivery information</div>
      <form id="order-form">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Full name *</label>
            <input type="text" class="form-control" id="cust-name" placeholder="Maria Santos" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Phone number *</label>
            <input type="tel" class="form-control" id="cust-phone" placeholder="09XX XXX XXXX" required/>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Email address *</label>
          <input type="email" class="form-control" id="cust-email" placeholder="maria@email.com" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Delivery address *</label>
          <textarea class="form-control" id="cust-address" placeholder="House No., Street, Barangay, City, Province" required></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Order notes (optional)</label>
          <textarea class="form-control" id="cust-notes" placeholder="Any special instructions…" style="min-height:60px"></textarea>
        </div>
        <div style="background:var(--surface2);border:1px solid var(--border);border-radius:var(--r-sm);padding:14px 16px;margin-bottom:20px">
          <div style="font-size:12px;font-weight:500;color:var(--text2);margin-bottom:6px">Payment method</div>
          <div style="display:flex;align-items:center;gap:10px;font-size:14px">
            <div style="width:18px;height:18px;border-radius:50%;background:var(--accent);flex-shrink:0;display:flex;align-items:center;justify-content:center">
              <div style="width:7px;height:7px;border-radius:50%;background:#08090d"></div>
            </div>
            Cash on delivery
          </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px" id="place-order-btn">
          <svg class="ico" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          Place order
        </button>
      </form>
    </div>

    <!-- Order summary -->
    <div>
      <div class="checkout-card" style="position:sticky;top:80px">
        <div class="checkout-title">Order summary</div>
        <div id="order-items-list"></div>
        <div style="margin-top:16px;padding-top:12px;border-top:1px solid var(--border)">
          <div class="order-total-row"><span>Subtotal</span><span id="summary-subtotal">₱0.00</span></div>
          <div class="order-total-row"><span>Shipping</span><span style="color:var(--accent)">Free</span></div>
          <div class="order-total-row grand"><span>Total</span><span id="summary-total">₱0.00</span></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="shop-toast"></div>
<script src="<?= BASE_URL ?>/assets/js/shop.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const cart = getCart();
  if (!cart.length) {
    document.getElementById('checkout-content').style.display = 'none';
    document.getElementById('empty-cart-msg').style.display = 'block';
    return;
  }

  // Render order summary
  const listEl = document.getElementById('order-items-list');
  let total = 0;
  listEl.innerHTML = cart.map(item => {
    const sub = item.price * item.qty;
    total += sub;
    return `<div class="order-summary-item">
      <div class="order-summary-img">
        ${item.img ? `<img src="${item.img}" alt="${item.name}"/>` : `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="opacity:.3"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>`}
      </div>
      <div style="flex:1;min-width:0">
        <div class="order-summary-name">${item.name}</div>
        <div class="order-summary-qty">Qty: ${item.qty}</div>
      </div>
      <div class="order-summary-price">${fmtMoney(sub)}</div>
    </div>`;
  }).join('');

  document.getElementById('summary-subtotal').textContent = fmtMoney(total);
  document.getElementById('summary-total').textContent    = fmtMoney(total);

  // Submit order
  document.getElementById('order-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('place-order-btn');
    btn.disabled = true;
    btn.textContent = 'Placing order…';

    const payload = {
      name:    document.getElementById('cust-name').value,
      email:   document.getElementById('cust-email').value,
      phone:   document.getElementById('cust-phone').value,
      address: document.getElementById('cust-address').value,
      notes:   document.getElementById('cust-notes').value,
      cart:    getCart()
    };

    try {
      const res  = await fetch('<?= BASE_URL ?>/shop/place-order.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success) {
        clearCart();
        window.location.href = '<?= BASE_URL ?>/shop/success.php?code=' + data.order_code;
      } else {
        showToast(data.error || 'Order failed. Please try again.', 'error');
        btn.disabled = false;
        btn.textContent = 'Place order';
      }
    } catch {
      showToast('Network error. Please try again.', 'error');
      btn.disabled = false;
      btn.textContent = 'Place order';
    }
  });
});
</script>
</body>
</html>
<!-- cart-panel.php — Slide-in cart sidebar -->
<div class="cart-overlay" id="cart-overlay" onclick="toggleCart()"></div>
<div class="cart-panel" id="cart-panel">
  <div class="cart-head">
    <h3>Your cart <span id="cart-head-count" style="color:var(--text3);font-size:13px;font-family:var(--font-mono)"></span></h3>
    <button class="close-btn" onclick="toggleCart()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>
  <div class="cart-items" id="cart-items-list">
    <div class="cart-empty">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      <p>Your cart is empty</p>
      <button class="btn btn-outline btn-sm" onclick="toggleCart()">Start shopping</button>
    </div>
  </div>
  <div class="cart-foot" id="cart-foot" style="display:none">
    <div class="cart-total">
      <span class="cart-total-label">Total</span>
      <span class="cart-total-val" id="cart-total">₱0.00</span>
    </div>
    <a href="<?= BASE_URL ?>/shop/checkout.php" class="btn btn-primary" style="width:100%;justify-content:center" id="checkout-btn">
      Proceed to checkout →
    </a>
  </div>
</div>
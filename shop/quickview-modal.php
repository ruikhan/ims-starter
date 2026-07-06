<!-- quickview-modal.php — Product Quick View with feature hotspots
     Included once per storefront page. shop.js fills it in from the
     data-product JSON on whichever .product-card was clicked. -->
<div class="quickview-overlay" id="quickview-overlay">
  <div class="quickview-modal">
    <button type="button" class="quickview-close" aria-label="Close quick view">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>

    <div class="quickview-img-wrap" id="qv-img-wrap">
      <img id="qv-img" src="" alt=""/>
      <div class="hotspot-layer" id="qv-hotspots"></div>
    </div>

    <div class="quickview-info">
      <div class="product-cat" id="qv-cat"></div>
      <h2 class="quickview-name" id="qv-name"></h2>
      <p class="quickview-desc" id="qv-desc"></p>
      <div class="quickview-price-row">
        <div class="quickview-price" id="qv-price"></div>
        <div class="quickview-stock" id="qv-stock"></div>
      </div>

      <div id="qv-features-wrap" style="display:none">
        <div class="quickview-features-label">Product breakdown</div>
        <div class="quickview-feature-list" id="qv-feature-list"></div>
      </div>
    </div>
  </div>
</div>

<?php
// products/edit.php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { redirectWith('/products/index.php', 'danger', 'Product not found.'); }

$pageTitle  = 'Edit Product';
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$errors     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']               ?? '');
    $sku         = strtoupper(trim($_POST['sku']      ?? ''));
    $catId       = (int)($_POST['category_id']        ?? 0);
    $price       = (float)($_POST['price']             ?? 0);
    $threshold   = (int)($_POST['low_stock_threshold'] ?? 10);
    $description = trim($_POST['description']          ?? '');

    if (!$name)         $errors[] = 'Product name is required.';
    if (!$sku)          $errors[] = 'SKU is required.';
    if ($threshold < 1) $errors[] = 'Threshold must be at least 1.';

    if (!$errors) {
        $dup = $pdo->prepare("SELECT id FROM products WHERE sku = ? AND id != ?");
        $dup->execute([$sku, $id]);
        if ($dup->fetch()) $errors[] = "SKU \"$sku\" is already used by another product.";
    }

    if (!$errors) {
        $pdo->prepare("
            UPDATE products
            SET name=?, sku=?, category_id=?, price=?, low_stock_threshold=?, description=?
            WHERE id=?
        ")->execute([$name, $sku, $catId ?: null, $price, $threshold, $description ?: null, $id]);
        updateStockStatus($pdo, $id);
        redirectWith('/products/index.php', 'success', "Product \"$name\" updated.");
    }
    // Re-populate for display on error
    $product = array_merge($product, [
        'name'                => $name,
        'sku'                 => $sku,
        'category_id'         => $catId,
        'price'               => $price,
        'low_stock_threshold' => $threshold,
        'description'         => $description,
    ]);
}

// Feature hotspots for the "click the image to add a callout" panel below.
$features = getProductFeatures($pdo, $id);

require_once '../partials/header.php';
?>
<div class="page-header">
  <div><h2>Edit Product</h2><p>Modify product details &amp; image</p></div>
  <a href="<?= BASE_URL ?>/products/index.php" class="btn btn-ghost">← Back</a>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;max-width:960px">

  <!-- ── LEFT: product fields ── -->
  <div class="card">
    <div class="card-header"><span class="card-title">Product details</span></div>
    <div class="card-body" style="padding:24px">
      <?php foreach ($errors as $e): ?>
      <div class="alert alert-danger" style="margin-bottom:12px"><?= e($e) ?></div>
      <?php endforeach; ?>

      <form method="POST">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Product name *</label>
            <input type="text" name="name" class="form-control"
                   value="<?= e($product['name']) ?>" required/>
          </div>
          <div class="form-group">
            <label class="form-label">SKU *</label>
            <input type="text" name="sku" class="form-control"
                   value="<?= e($product['sku']) ?>" required style="text-transform:uppercase"/>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-control">
              <option value="">No category</option>
              <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $product['category_id'] == $c['id'] ? 'selected' : '' ?>>
                <?= e($c['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Price (₱)</label>
            <input type="number" name="price" class="form-control"
                   value="<?= e($product['price']) ?>" min="0" step="0.01"/>
          </div>
        </div>

        <div class="form-group" style="max-width:220px">
          <label class="form-label">Low stock threshold</label>
          <input type="number" name="low_stock_threshold" class="form-control"
                 value="<?= e($product['low_stock_threshold']) ?>" min="1"/>
          <div class="form-hint">
            Current qty: <strong style="color:var(--text)"><?= number_format($product['quantity']) ?></strong>
            &nbsp;·&nbsp; Status: <strong style="color:var(--text)"><?= statusLabel($product['status']) ?></strong>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Description <span style="color:var(--text3)">(shown in shop)</span></label>
          <textarea name="description" class="form-control" rows="3"
                    placeholder="Brief description shown on the storefront…"><?= e($product['description'] ?? '') ?></textarea>
        </div>

        <div style="display:flex;gap:10px;margin-top:8px">
          <button type="submit" class="btn btn-primary">Update Product</button>
          <a href="<?= BASE_URL ?>/products/index.php" class="btn btn-ghost">Cancel</a>
        </div>
      </form>
    </div>
  </div>

  <!-- ── RIGHT: image uploader ── -->
  <div>
    <div class="card">
      <div class="card-header"><span class="card-title">Product image</span></div>
      <div class="card-body" style="padding:20px">

        <?php
        $imgUrl = !empty($product['image'])
            ? BASE_URL . '/uploads/products/' . e($product['image'])
            : null;
        ?>

        <!-- Current image preview -->
        <div id="img-current"
             style="height:200px;border-radius:8px;overflow:hidden;background:var(--surface2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;margin-bottom:14px;position:relative">
          <?php if ($imgUrl): ?>
            <img id="img-preview" src="<?= $imgUrl ?>" alt="<?= e($product['name']) ?>"
                 style="width:100%;height:100%;object-fit:cover"/>
          <?php else: ?>
            <div id="img-placeholder" style="text-align:center;color:var(--text3)">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"
                   style="margin:0 auto 10px;display:block;opacity:.4">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
              </svg>
              <div style="font-size:12px">No image yet</div>
            </div>
            <img id="img-preview" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:none"/>
          <?php endif; ?>
        </div>

        <!-- Upload zone -->
        <div id="img-upload-zone"
             onclick="document.getElementById('img-file').click()"
             style="border:2px dashed var(--border2);border-radius:8px;padding:18px;text-align:center;cursor:pointer;transition:border-color .2s">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
               stroke-linecap="round" stroke-linejoin="round"
               style="margin:0 auto 8px;display:block;color:var(--text3)">
            <polyline points="16 16 12 12 8 16"/>
            <line x1="12" y1="12" x2="12" y2="21"/>
            <path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/>
          </svg>
          <div style="font-size:13px;color:var(--text2);margin-bottom:3px">Click or drag image here</div>
          <div style="font-size:11px;color:var(--text3);font-family:var(--font-mono)">JPG · PNG · WebP · max 5 MB</div>
          <input type="file" id="img-file" accept="image/jpeg,image/png,image/webp,image/gif"
                 style="display:none" onchange="handleImageUpload(this)"/>
        </div>

        <!-- Status line -->
        <div id="img-status" style="font-size:11px;font-family:var(--font-mono);margin-top:8px;min-height:16px;color:var(--text3)">
          <?= $imgUrl ? e($product['image']) : 'No image uploaded' ?>
        </div>

        <?php if ($imgUrl): ?>
        <!-- Remove image button -->
        <form method="POST" action="<?= BASE_URL ?>/products/remove-image.php" style="margin-top:10px"
              onsubmit="return confirm('Remove this product image?')">
          <input type="hidden" name="id" value="<?= $product['id'] ?>"/>
          <button type="submit" class="btn btn-danger btn-sm" style="width:100%;justify-content:center">
            <svg class="ico" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
            Remove image
          </button>
        </form>
        <?php endif; ?>

      </div>
    </div>

    <!-- ── FEATURE HOTSPOTS ── -->
    <div class="card">
      <div class="card-header"><span class="card-title">Feature hotspots</span></div>
      <div class="card-body" style="padding:20px">
        <p style="font-size:12px;color:var(--text2);line-height:1.6;margin-bottom:14px">
          Click anywhere on the image to drop a numbered callout — shoppers
          hover it on the storefront (or open Quick View) to see that feature.
        </p>

        <?php if ($imgUrl): ?>
        <div id="hotspot-editor" style="position:relative;border-radius:8px;overflow:hidden;background:var(--surface2);border:1px solid var(--border);cursor:crosshair;user-select:none">
          <img src="<?= $imgUrl ?>" style="width:100%;display:block;pointer-events:none"/>
          <div id="hotspot-editor-pins" style="position:absolute;inset:0"></div>

          <div id="hotspot-popover" style="display:none;position:absolute;z-index:50;width:210px;background:var(--surface);border:1px solid var(--border2);border-radius:10px;padding:12px;box-shadow:0 12px 32px rgba(0,0,0,.5)">
            <input type="text" id="hotspot-label-input" class="form-control" placeholder="Feature name" style="margin-bottom:8px;font-size:12px;padding:7px 9px"/>
            <input type="text" id="hotspot-desc-input" class="form-control" placeholder="Short description (optional)" style="margin-bottom:10px;font-size:12px;padding:7px 9px"/>
            <div style="display:flex;gap:6px">
              <button type="button" class="btn btn-primary btn-sm" style="flex:1;justify-content:center" onclick="confirmHotspot()">Add</button>
              <button type="button" class="btn btn-ghost btn-sm" onclick="cancelHotspot()">Cancel</button>
            </div>
          </div>
        </div>
        <?php else: ?>
        <div style="font-size:12px;color:var(--text3);background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:14px">
          Upload a product image above first — hotspots are positioned on it.
        </div>
        <?php endif; ?>

        <div id="hotspot-list" style="margin-top:14px;display:flex;flex-direction:column;gap:8px"></div>

        <input type="hidden" id="hotspot-csrf" value="<?= e(csrf_token()) ?>"/>
      </div>
    </div>

    <!-- Shop preview hint -->
    <div style="background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:14px 16px;margin-top:12px">
      <div style="font-size:11px;font-family:var(--font-mono);color:var(--text3);margin-bottom:6px">SHOP PREVIEW</div>
      <div style="font-size:12px;color:var(--text2);line-height:1.6">
        Images appear on the storefront catalog and product cards.
        For best results use a <strong style="color:var(--text)">square image</strong> (at least 600 × 600 px).
      </div>
      <a href="<?= BASE_URL ?>/shop/catalog.php" target="_blank" class="btn btn-ghost btn-sm" style="margin-top:10px;width:100%;justify-content:center">
        <svg class="ico" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        View storefront →
      </a>
    </div>
  </div>

</div><!-- /grid -->

<script>
const uploadZone = document.getElementById('img-upload-zone');

uploadZone.addEventListener('dragover', e => {
  e.preventDefault();
  uploadZone.style.borderColor = 'var(--accent)';
});
uploadZone.addEventListener('dragleave', () => {
  uploadZone.style.borderColor = 'var(--border2)';
});
uploadZone.addEventListener('drop', e => {
  e.preventDefault();
  uploadZone.style.borderColor = 'var(--border2)';
  if (e.dataTransfer.files[0]) handleImageUpload({ files: e.dataTransfer.files });
});

async function handleImageUpload(input) {
  const file = input.files[0];
  if (!file) return;

  const status      = document.getElementById('img-status');
  const preview     = document.getElementById('img-preview');
  const placeholder = document.getElementById('img-placeholder');

  // Instant local preview
  const reader = new FileReader();
  reader.onload = ev => {
    preview.src = ev.target.result;
    preview.style.display = 'block';
    if (placeholder) placeholder.style.display = 'none';
  };
  reader.readAsDataURL(file);

  status.textContent = 'Uploading…';
  status.style.color = 'var(--accent)';
  uploadZone.style.pointerEvents = 'none';
  uploadZone.style.opacity = '.6';

  const fd = new FormData();
  fd.append('product_id', '<?= $product['id'] ?>');
  fd.append('image', file);

  try {
    const res  = await fetch('<?= BASE_URL ?>/admin/upload-image.php', { method: 'POST', body: fd });
    const data = await res.json();

    if (data.success) {
      status.textContent = '✓ ' + data.filename;
      status.style.color = 'var(--accent)';
      preview.src        = data.image_url + '?t=' + Date.now(); // bust cache
      // Show remove button if it wasn't there
      if (!document.getElementById('remove-img-form')) {
        const form = document.createElement('form');
        form.id = 'remove-img-form';
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>/products/remove-image.php';
        form.style.marginTop = '10px';
        form.onsubmit = () => confirm('Remove this product image?');
        form.innerHTML = `
          <input type="hidden" name="id" value="<?= $product['id'] ?>"/>
          <button type="submit" class="btn btn-danger btn-sm" style="width:100%;justify-content:center">
            <svg class="ico" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
            Remove image
          </button>`;
        uploadZone.parentElement.appendChild(form);
      }
      // A newly-uploaded image means the hotspot editor should reflect it too.
      const editorImg = document.querySelector('#hotspot-editor img');
      if (editorImg) editorImg.src = data.image_url + '?t=' + Date.now();
    } else {
      status.textContent = '✗ ' + (data.error || 'Upload failed');
      status.style.color = 'var(--red)';
    }
  } catch {
    status.textContent = '✗ Network error — upload failed';
    status.style.color = 'var(--red)';
  }

  uploadZone.style.pointerEvents = '';
  uploadZone.style.opacity = '';
}
</script>

<script>
// ── FEATURE HOTSPOT EDITOR ───────────────────────────────────
let hotspots   = <?= json_encode(array_map(fn($f) => [
    'id'          => (int)$f['id'],
    'label'       => $f['label'],
    'description' => $f['description'],
    'pos_x'       => (float)$f['pos_x'],
    'pos_y'       => (float)$f['pos_y'],
], $features)) ?>;
let pendingPos = null;

function renderHotspotPins() {
  const wrap = document.getElementById('hotspot-editor-pins');
  if (!wrap) return;
  wrap.innerHTML = hotspots.map((h, i) => `
    <div style="position:absolute;left:${h.pos_x}%;top:${h.pos_y}%;transform:translate(-50%,-50%);
                width:22px;height:22px;border-radius:50%;background:var(--accent);color:#0f1117;
                font-family:var(--font-mono);font-size:11px;font-weight:700;
                display:flex;align-items:center;justify-content:center;box-shadow:0 0 0 3px rgba(34,211,160,.25)">
      ${i + 1}
    </div>`).join('');
}

function renderHotspotList() {
  const list = document.getElementById('hotspot-list');
  if (!list) return;
  if (!hotspots.length) {
    list.innerHTML = '<div style="font-size:12px;color:var(--text3)">No hotspots yet — click the image above to add one.</div>';
    return;
  }
  list.innerHTML = hotspots.map((h, i) => `
    <div style="display:flex;gap:10px;align-items:flex-start;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:10px 12px">
      <div style="width:20px;height:20px;border-radius:50%;background:var(--accent);color:#0f1117;font-family:var(--font-mono);font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px">${i + 1}</div>
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;font-weight:500">${escapeHtml(h.label)}</div>
        ${h.description ? `<div style="font-size:12px;color:var(--text2);margin-top:2px">${escapeHtml(h.description)}</div>` : ''}
      </div>
      <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="deleteHotspot(${h.id})" title="Delete">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
      </button>
    </div>`).join('');
}

function escapeHtml(str) {
  const d = document.createElement('div');
  d.textContent = str == null ? '' : String(str);
  return d.innerHTML;
}

const hotspotEditor = document.getElementById('hotspot-editor');
if (hotspotEditor) {
  hotspotEditor.addEventListener('click', e => {
    if (e.target.closest('#hotspot-popover')) return;
    const rect = hotspotEditor.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width  * 100).toFixed(2);
    const y = ((e.clientY - rect.top)  / rect.height * 100).toFixed(2);
    pendingPos = { x, y };

    const popover = document.getElementById('hotspot-popover');
    const maxLeft = rect.width - 220;
    popover.style.left = Math.min(e.clientX - rect.left, maxLeft) + 'px';
    popover.style.top  = Math.min(e.clientY - rect.top, rect.height - 140) + 'px';
    popover.style.display = 'block';
    document.getElementById('hotspot-label-input').value = '';
    document.getElementById('hotspot-desc-input').value  = '';
    document.getElementById('hotspot-label-input').focus();
  });
}

function cancelHotspot() {
  document.getElementById('hotspot-popover').style.display = 'none';
  pendingPos = null;
}

async function confirmHotspot() {
  if (!pendingPos) return;
  const label = document.getElementById('hotspot-label-input').value.trim();
  if (!label) { document.getElementById('hotspot-label-input').focus(); return; }
  const description = document.getElementById('hotspot-desc-input').value.trim();

  const fd = new FormData();
  fd.append('product_id', '<?= $product['id'] ?>');
  fd.append('pos_x', pendingPos.x);
  fd.append('pos_y', pendingPos.y);
  fd.append('label', label);
  fd.append('description', description);
  fd.append('csrf_token', document.getElementById('hotspot-csrf').value);

  try {
    const res  = await fetch('<?= BASE_URL ?>/admin/save-feature.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      hotspots.push({ id: data.id, label, description, pos_x: parseFloat(pendingPos.x), pos_y: parseFloat(pendingPos.y) });
      renderHotspotPins();
      renderHotspotList();
      cancelHotspot();
    } else {
      alert(data.error || 'Could not save that hotspot.');
    }
  } catch {
    alert('Network error — could not save that hotspot.');
  }
}

async function deleteHotspot(id) {
  if (!confirm('Remove this hotspot?')) return;
  const fd = new FormData();
  fd.append('id', id);
  fd.append('csrf_token', document.getElementById('hotspot-csrf').value);
  try {
    const res  = await fetch('<?= BASE_URL ?>/admin/delete-feature.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      hotspots = hotspots.filter(h => h.id !== id);
      renderHotspotPins();
      renderHotspotList();
    }
  } catch { /* leave the list as-is on network failure */ }
}

renderHotspotPins();
renderHotspotList();
</script>

<?php require_once '../partials/footer.php'; ?>

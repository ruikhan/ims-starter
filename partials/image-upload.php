<?php
// partials/image-upload.php — Drag & drop image uploader for products
// Requires: $product['id'], $product['image']
$imgUrl = !empty($product['image'])
    ? '/ims-starter/uploads/products/' . e($product['image'])
    : null;
?>
<div class="form-group" style="margin-bottom:20px">
  <label class="form-label">Product image</label>
  <div id="img-upload-zone" onclick="document.getElementById('img-file').click()"
    style="border:2px dashed var(--border2);border-radius:var(--r);padding:24px;text-align:center;cursor:pointer;transition:border-color var(--trans);position:relative;min-height:160px;display:flex;align-items:center;justify-content:center;background:var(--bg)">
    <div id="img-preview-wrap">
      <?php if ($imgUrl): ?>
      <img id="img-preview" src="<?= $imgUrl ?>" style="max-height:140px;border-radius:8px;object-fit:contain"/>
      <?php else: ?>
      <div id="img-placeholder" style="color:var(--text3)">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="margin:0 auto 10px;display:block"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        <div style="font-size:13px;margin-bottom:4px">Click or drag image here</div>
        <div style="font-size:11px;font-family:var(--font-mono)">JPG · PNG · WebP · max 5MB</div>
      </div>
      <img id="img-preview" src="" style="max-height:140px;border-radius:8px;object-fit:contain;display:none"/>
      <?php endif; ?>
    </div>
    <input type="file" id="img-file" accept="image/*" style="display:none" onchange="handleImageUpload(this)"/>
  </div>
  <div id="img-status" style="font-size:12px;font-family:var(--font-mono);margin-top:6px;color:var(--text3)">
    <?php if ($imgUrl): ?><?= e($product['image']) ?><?php endif; ?>
  </div>
</div>
<script>
const uploadZone = document.getElementById('img-upload-zone');
uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.style.borderColor='var(--accent)'; });
uploadZone.addEventListener('dragleave', () => { uploadZone.style.borderColor='var(--border2)'; });
uploadZone.addEventListener('drop', e => {
  e.preventDefault(); uploadZone.style.borderColor='var(--border2)';
  if (e.dataTransfer.files[0]) handleImageUpload({ files: e.dataTransfer.files });
});

async function handleImageUpload(input) {
  const file = input.files[0];
  if (!file) return;
  const status = document.getElementById('img-status');
  const preview = document.getElementById('img-preview');
  const placeholder = document.getElementById('img-placeholder');

  // Preview immediately
  const reader = new FileReader();
  reader.onload = e => {
    preview.src = e.target.result;
    preview.style.display = 'block';
    if (placeholder) placeholder.style.display = 'none';
  };
  reader.readAsDataURL(file);

  status.textContent = 'Uploading…';
  status.style.color = 'var(--accent)';

  const fd = new FormData();
  fd.append('product_id', '<?= $product['id'] ?>');
  fd.append('image', file);

  try {
    const res  = await fetch('/ims-starter/admin/upload-image.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.success) {
      status.textContent = '✓ ' + data.filename;
      status.style.color = 'var(--accent)';
      preview.src = data.image_url;
    } else {
      status.textContent = '✗ ' + data.error;
      status.style.color = 'var(--red)';
    }
  } catch {
    status.textContent = '✗ Upload failed';
    status.style.color = 'var(--red)';
  }
}
</script>

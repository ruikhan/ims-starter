<?php
// products/add.php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();
$pageTitle  = 'Add Product';
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$errors     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']               ?? '');
    $sku         = strtoupper(trim($_POST['sku']      ?? ''));
    $catId       = (int)($_POST['category_id']        ?? 0);
    $price       = (float)($_POST['price']             ?? 0);
    $qty         = (int)($_POST['quantity']             ?? 0);
    $threshold   = (int)($_POST['low_stock_threshold'] ?? 10);
    $description = trim($_POST['description']          ?? '');

    if (!$name)          $errors[] = 'Product name is required.';
    if (!$sku)           $errors[] = 'SKU is required.';
    if ($qty < 0)        $errors[] = 'Quantity cannot be negative.';
    if ($threshold < 1)  $errors[] = 'Low stock threshold must be at least 1.';

    if (!$errors) {
        $dup = $pdo->prepare("SELECT id FROM products WHERE sku = ?");
        $dup->execute([$sku]);
        if ($dup->fetch()) $errors[] = "SKU \"$sku\" already exists.";
    }

    if (!$errors) {
        $status = $qty <= 0 ? 'out_of_stock' : ($qty <= $threshold ? 'low_stock' : 'in_stock');
        $stmt = $pdo->prepare("
            INSERT INTO products (name, sku, category_id, price, quantity, low_stock_threshold, status, description)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $name,
            $sku,
            $catId ?: null,
            $price,
            $qty,
            $threshold,
            $status,
            $description ?: null,
        ]);
        $newId = (int)$pdo->lastInsertId();
        // Redirect to edit so the user can upload an image immediately
        redirectWith('/products/edit.php?id=' . $newId, 'success', "Product \"$name\" added. You can now upload an image.");
    }
}
require_once '../partials/header.php';
?>
<div class="page-header">
  <div><h2>Add Product</h2><p>Create a new inventory item</p></div>
  <a href="<?= BASE_URL ?>/products/index.php" class="btn btn-ghost">← Back</a>
</div>

<div class="card" style="max-width:640px">
  <div class="card-body" style="padding:24px">
    <?php foreach ($errors as $e): ?>
    <div class="alert alert-danger" style="margin-bottom:12px"><?= e($e) ?></div>
    <?php endforeach; ?>

    <form method="POST">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Product name *</label>
          <input type="text" name="name" class="form-control"
                 value="<?= e($_POST['name'] ?? '') ?>" required autofocus/>
        </div>
        <div class="form-group">
          <label class="form-label">SKU *</label>
          <input type="text" name="sku" class="form-control"
                 value="<?= e($_POST['sku'] ?? '') ?>" required style="text-transform:uppercase"/>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Category</label>
          <select name="category_id" class="form-control">
            <option value="">No category</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>" <?= ($_POST['category_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>>
              <?= e($c['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Price (₱)</label>
          <input type="number" name="price" class="form-control"
                 value="<?= e($_POST['price'] ?? '0') ?>" min="0" step="0.01"/>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Initial quantity</label>
          <input type="number" name="quantity" class="form-control"
                 value="<?= e($_POST['quantity'] ?? '0') ?>" min="0"/>
        </div>
        <div class="form-group">
          <label class="form-label">Low stock threshold</label>
          <input type="number" name="low_stock_threshold" class="form-control"
                 value="<?= e($_POST['low_stock_threshold'] ?? '10') ?>" min="1"/>
          <div class="form-hint">Alert when qty ≤ this number</div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Description <span style="color:var(--text3)">(shown in shop)</span></label>
        <textarea name="description" class="form-control" rows="3"
                  placeholder="Brief description shown on the storefront…"><?= e($_POST['description'] ?? '') ?></textarea>
      </div>

      <div class="alert" style="background:var(--accent-glow);border:1px solid rgba(34,211,160,.2);color:var(--accent);font-size:12px;margin-bottom:16px">
        <svg class="ico" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        After saving, you'll be taken to the edit page where you can upload a product image.
      </div>

      <div style="display:flex;gap:10px;margin-top:8px">
        <button type="submit" class="btn btn-primary">Save &amp; Upload Image →</button>
        <a href="<?= BASE_URL ?>/products/index.php" class="btn btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once '../partials/footer.php'; ?>
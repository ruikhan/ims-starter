<?php
// stock/index.php — Stock-in and Stock-out
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
$pageTitle = 'Stock Movement';

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $action    = $_POST['action']     ?? '';
    $productId = (int)($_POST['product_id'] ?? 0);
    $qty       = (int)($_POST['quantity']   ?? 0);
    $notes     = trim($_POST['notes']       ?? '');

    if (!$productId) $errors[] = 'Please select a product.';
    if ($qty <= 0)   $errors[] = 'Quantity must be greater than 0.';

    if (!$errors) {
        $stmt = $pdo->prepare("SELECT name, quantity FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            $errors[] = 'Product not found.';
        } elseif ($action === 'out' && $product['quantity'] < $qty) {
            $errors[] = "Insufficient stock. Only {$product['quantity']} unit(s) available for \"{$product['name']}\".";
        } else {
            $pdo->beginTransaction();
            try {
                $qtyChange = $action === 'in' ? $qty : -$qty;
                $pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?")->execute([$qtyChange, $productId]);
                $pdo->prepare("INSERT INTO transactions (product_id, type, quantity, notes, user_id) VALUES (?,?,?,?,?)")
                    ->execute([$productId, $action, $qty, $notes ?: null, currentUserId()]);
                $pdo->commit();
                updateStockStatus($pdo, $productId);
                $label = $action === 'in' ? "added to" : "removed from";
                redirectWith('/stock/history.php', 'success', "+{$qty} unit(s) {$label} \"{$product['name']}\" successfully.");
            } catch (Exception $ex) {
                $pdo->rollBack();
                $errors[] = 'Transaction failed. Please try again.';
            }
        }
    }
}

$products = $pdo->query("SELECT id, name, sku, quantity, low_stock_threshold, status FROM products ORDER BY name")->fetchAll();
require_once '../partials/header.php';
?>

<div class="page-header">
  <div><h2>Stock Movement</h2><p>Record stock-in and stock-out transactions</p></div>
</div>

<?php foreach ($errors as $err): ?>
<div class="alert alert-danger"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="stock-form-grid">

  <!-- ── STOCK IN ── -->
  <div class="stock-form-card in-card">
    <div class="stock-form-title" style="color:var(--accent)">
      <svg class="ico-lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><line x1="12" y1="17" x2="12" y2="23"/><line x1="9" y1="20" x2="15" y2="20"/></svg>
      Stock In
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="in"/>
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label">Product</label>
        <select name="product_id" class="form-control" onchange="showQty(this,'in-qty')" required>
          <option value="">Select product…</option>
          <?php foreach ($products as $p): ?>
          <option value="<?= $p['id'] ?>" data-qty="<?= $p['quantity'] ?>" data-thresh="<?= $p['low_stock_threshold'] ?>">
            <?= e($p['name']) ?> (<?= e($p['sku']) ?>)
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="qty-display" id="in-qty">
        <div><div class="qty-label">Current stock</div><div class="qty-val ok" id="in-qty-val">—</div></div>
      </div>
      <div class="form-group">
        <label class="form-label">Quantity to add</label>
        <input type="number" name="quantity" class="form-control" min="1" placeholder="0" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Notes</label>
        <input type="text" name="notes" class="form-control" placeholder="e.g. Purchase order #1234"/>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
        Confirm Stock In
      </button>
    </form>
  </div>

  <!-- ── STOCK OUT ── -->
  <div class="stock-form-card out-card">
    <div class="stock-form-title" style="color:var(--red)">
      <svg class="ico-lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/><line x1="12" y1="7" x2="12" y2="1"/><line x1="9" y1="4" x2="15" y2="4"/></svg>
      Stock Out
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="out"/>
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label">Product</label>
        <select name="product_id" class="form-control" onchange="showQty(this,'out-qty')" required>
          <option value="">Select product…</option>
          <?php foreach ($products as $p): ?>
          <option value="<?= $p['id'] ?>" data-qty="<?= $p['quantity'] ?>" data-thresh="<?= $p['low_stock_threshold'] ?>" <?= $p['quantity'] == 0 ? 'disabled' : '' ?>>
            <?= e($p['name']) ?> (<?= e($p['sku']) ?>)<?= $p['quantity'] == 0 ? ' — OUT' : '' ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="qty-display" id="out-qty">
        <div><div class="qty-label">Available stock</div><div class="qty-val ok" id="out-qty-val">—</div></div>
      </div>
      <div class="form-group">
        <label class="form-label">Quantity to remove</label>
        <input type="number" name="quantity" class="form-control" min="1" placeholder="0" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Notes</label>
        <input type="text" name="notes" class="form-control" placeholder="e.g. Sales order #5678"/>
      </div>
      <button type="submit" class="btn" style="width:100%;justify-content:center;background:var(--red-dim);border-color:var(--red);color:var(--red);font-weight:600">
        Confirm Stock Out
      </button>
    </form>
  </div>

</div>

<script>
function showQty(sel, displayId) {
  const opt   = sel.options[sel.selectedIndex];
  const qty   = parseInt(opt.dataset.qty   ?? -1);
  const thresh = parseInt(opt.dataset.thresh ?? 0);
  const box   = document.getElementById(displayId);
  const valEl = document.getElementById(displayId + '-val');
  if (!sel.value || qty < 0) { box.classList.remove('visible'); return; }
  box.classList.add('visible');
  valEl.textContent = qty;
  valEl.className   = 'qty-val ' + (qty === 0 ? 'zero' : qty <= thresh ? 'warn' : 'ok');
}
</script>
<?php require_once '../partials/footer.php'; ?>
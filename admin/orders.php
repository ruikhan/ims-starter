<?php
// admin/orders.php — Admin view of customer orders
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
require_once '../includes/workflow.php';
requireLogin();
$pageTitle = 'Customer Orders';

// Update status — now routed through the workflow engine instead of a raw UPDATE.
// transitionOrder() validates the status jump, writes an audit row to
// order_status_log, and queues a notification the Python service will pick up.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    csrf_verify();
    $allowed   = ['pending','confirmed','processing','shipped','delivered','cancelled'];
    $newStatus = $_POST['status'];
    $orderId   = (int)$_POST['order_id'];

    if (in_array($newStatus, $allowed, true)) {
        $result = transitionOrder($pdo, $orderId, $newStatus, currentUserId());
        if ($result['success']) {
            redirectWith('/admin/orders.php', 'success', 'Order status updated.');
        } else {
            redirectWith('/admin/orders.php', 'danger', $result['error']);
        }
    }
}

$statusFilter = $_GET['status'] ?? '';
$where  = $statusFilter ? 'WHERE o.status = ?' : '';
$params = $statusFilter ? [$statusFilter] : [];

$orders = $pdo->prepare("
    SELECT o.*, COUNT(oi.id) AS item_count
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.id
    $where
    GROUP BY o.id ORDER BY o.created_at DESC
");
$orders->execute($params);
$orders = $orders->fetchAll();

require_once '../partials/header.php';
?>
<div class="page-header">
  <div>
    <h2>Customer Orders</h2>
    <p>Live orders from the storefront — auto-linked to inventory</p>
  </div>
  <a href="<?= BASE_URL ?>/shop/index.php" target="_blank" class="btn btn-primary">
    <svg class="ico" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
    View storefront
  </a>
</div>

<div class="filter-bar" style="margin-bottom:20px">
  <?php
  $statuses = ['','pending','confirmed','processing','shipped','delivered','cancelled'];
  $labels   = ['All','Pending','Confirmed','Processing','Shipped','Delivered','Cancelled'];
  foreach ($statuses as $i => $s):
  ?>
  <a href="<?= BASE_URL ?>/admin/orders.php<?= $s ? '?status='.$s : '' ?>"
     class="btn btn-sm <?= $statusFilter===$s ? 'btn-primary' : 'btn-ghost' ?>">
    <?= $labels[$i] ?>
  </a>
  <?php endforeach; ?>
</div>

<div class="card">
  <div class="card-body">
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Order</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if (empty($orders)): ?>
        <tr><td colspan="7"><div class="empty-state"><p>No orders found</p></div></td></tr>
        <?php else: foreach ($orders as $o): ?>
        <tr>
          <td><span class="tbl-mono" style="color:var(--accent)"><?= e($o['order_code']) ?></span></td>
          <td>
            <div class="tbl-name"><?= e($o['customer_name']) ?></div>
            <div class="tbl-mono"><?= e($o['customer_email']) ?></div>
          </td>
          <td><span class="tbl-mono"><?= $o['item_count'] ?> item<?= $o['item_count']!=1?'s':'' ?></span></td>
          <td><span class="tbl-mono" style="color:var(--accent)"><?= formatMoney($o['total_amount']) ?></span></td>
          <td>
            <span class="orders-status status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span>
          </td>
          <td><span class="tbl-mono"><?= e($o['created_at']) ?></span></td>
          <td>
            <div style="display:flex;gap:6px;align-items:center">
              <button class="btn btn-ghost btn-sm" onclick="showOrder(<?= $o['id'] ?>,'<?= e($o['order_code']) ?>','<?= e(addslashes($o['customer_name'])) ?>','<?= e(addslashes($o['customer_address'])) ?>','<?= $o['status'] ?>')">
                <svg class="ico" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
              <form method="POST" style="display:inline">
                <input type="hidden" name="order_id" value="<?= $o['id'] ?>"/>
                <?= csrf_field() ?>
                <select name="status" class="form-control" style="width:auto;padding:5px 28px 5px 10px;font-size:12px" onchange="this.form.submit()">
                  <?php
                  // Current status is always shown; other options are limited to what
                  // the workflow engine actually allows next, so staff can't pick an
                  // invalid jump from the dropdown in the first place.
                  $nextOptions = array_unique(array_merge([$o['status']], nextAllowedStatuses($o['status'])));
                  foreach ($nextOptions as $s): ?>
                  <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Order detail modal -->
<div class="overlay" id="order-modal">
  <div class="modal" style="width:520px">
    <div class="modal-header">
      <span class="modal-title" id="modal-order-code"></span>
      <button class="modal-close" onclick="document.getElementById('order-modal').classList.remove('open')">
        <svg class="ico" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <div class="form-label">Customer</div>
        <div id="modal-customer" style="font-size:14px;font-weight:500"></div>
      </div>
      <div class="form-group">
        <div class="form-label">Delivery address</div>
        <div id="modal-address" style="font-size:14px;color:var(--text2)"></div>
      </div>
      <div class="form-group">
        <div class="form-label">Status</div>
        <div id="modal-status"></div>
      </div>
    </div>
  </div>
</div>

<script>
function showOrder(id, code, name, address, status) {
  document.getElementById('modal-order-code').textContent = code;
  document.getElementById('modal-customer').textContent   = name;
  document.getElementById('modal-address').textContent    = address;
  document.getElementById('modal-status').innerHTML = `<span class="orders-status status-${status}">${status.charAt(0).toUpperCase()+status.slice(1)}</span>`;
  document.getElementById('order-modal').classList.add('open');
}
document.getElementById('order-modal').addEventListener('click', e => {
  if(e.target===e.currentTarget) e.currentTarget.classList.remove('open');
});
</script>
<?php require_once '../partials/footer.php'; ?>
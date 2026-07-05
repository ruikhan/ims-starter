<?php
// categories/index.php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
$pageTitle = 'Categories';
require_once '../partials/header.php';

$categories = $pdo->query("
    SELECT c.*, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id
    GROUP BY c.id ORDER BY c.name
")->fetchAll();
?>
<div class="page-header">
  <div><h2>Categories</h2><p>Organise products into groups</p></div>
  <?php if (isAdmin()): ?>
  <a href="<?= BASE_URL ?>/categories/add.php" class="btn btn-primary">
    <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Add Category
  </a>
  <?php endif; ?>
</div>
<div class="card">
  <div class="card-body">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Category</th><th>Description</th><th>Products</th><?php if(isAdmin()):?><th>Actions</th><?php endif;?></tr></thead>
        <tbody>
        <?php if (empty($categories)): ?>
        <tr><td colspan="4"><div class="empty-state"><p>No categories yet</p></div></td></tr>
        <?php else: foreach ($categories as $c): ?>
        <tr>
          <td><span class="tbl-name"><?= e($c['name']) ?></span></td>
          <td style="color:var(--text2)"><?= e($c['description'] ?? '—') ?></td>
          <td><span class="tbl-mono"><?= $c['product_count'] ?></span></td>
          <?php if (isAdmin()): ?>
          <td><div class="tbl-actions">
            <a href="<?= BASE_URL ?>/categories/edit.php?id=<?= $c['id'] ?>" class="btn btn-ghost btn-sm btn-icon" title="Edit">
              <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </a>
            <form method="POST" action="<?= BASE_URL ?>/categories/delete.php" style="display:inline"
                  onsubmit="return confirm('Delete category \'<?= e(addslashes($c['name'])) ?>\'? Products will be uncategorised.')">
              <input type="hidden" name="id" value="<?= $c['id'] ?>"/>
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Delete">
                <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
              </button>
            </form>
          </div></td>
          <?php endif; ?>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require_once '../partials/footer.php'; ?>
<?php
// categories/edit.php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$cat = $stmt->fetch();
if (!$cat) redirectWith('/categories/index.php', 'danger', 'Category not found.');

$pageTitle = 'Edit Category';
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if (!$name) $errors[] = 'Category name is required.';
    if (!$errors) {
        $pdo->prepare("UPDATE categories SET name=?, description=? WHERE id=?")->execute([$name, $desc, $id]);
        redirectWith('/categories/index.php', 'success', "Category \"$name\" updated.");
    }
    $cat['name']        = $name;
    $cat['description'] = $desc;
}
require_once '../partials/header.php';
?>
<div class="page-header">
  <div><h2>Edit Category</h2></div>
  <a href="<?= BASE_URL ?>/categories/index.php" class="btn btn-ghost">← Back</a>
</div>
<div class="card" style="max-width:480px">
  <div class="card-body" style="padding:24px">
    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= e($e) ?></div><?php endforeach; ?>
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Category name *</label>
        <input type="text" name="name" class="form-control" value="<?= e($cat['name']) ?>" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <input type="text" name="description" class="form-control" value="<?= e($cat['description']) ?>"/>
      </div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary">Update Category</button>
        <a href="<?= BASE_URL ?>/categories/index.php" class="btn btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>
<?php require_once '../partials/footer.php'; ?>

<?php
// categories/add.php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();
$pageTitle = 'Add Category';
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if (!$name) $errors[] = 'Category name is required.';
    if (!$errors) {
        $pdo->prepare("INSERT INTO categories (name, description) VALUES (?,?)")->execute([$name, $desc]);
        redirectWith('/categories/index.php', 'success', "Category \"$name\" added.");
    }
}
require_once '../partials/header.php';
?>
<div class="page-header">
  <div><h2>Add Category</h2></div>
  <a href="<?= BASE_URL ?>/categories/index.php" class="btn btn-ghost">← Back</a>
</div>
<div class="card" style="max-width:480px">
  <div class="card-body" style="padding:24px">
    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= e($e) ?></div><?php endforeach; ?>
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Category name *</label>
        <input type="text" name="name" class="form-control" value="<?= e($_POST['name'] ?? '') ?>" required autofocus/>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <input type="text" name="description" class="form-control" value="<?= e($_POST['description'] ?? '') ?>" placeholder="Optional"/>
      </div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary">Save Category</button>
        <a href="<?= BASE_URL ?>/categories/index.php" class="btn btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>
<?php require_once '../partials/footer.php'; ?>

<?php
// users/add.php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();
$pageTitle = 'Add User';
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $role     = $_POST['role']          ?? 'staff';
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['confirm']       ?? '';

    if (!$name)                                      $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))  $errors[] = 'Valid email is required.';
    if (strlen($password) < 6)                       $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)                      $errors[] = 'Passwords do not match.';
    if (!in_array($role, ['admin','staff']))          $errors[] = 'Invalid role.';

    if (!$errors) {
        $dup = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $dup->execute([$email]);
        if ($dup->fetch()) $errors[] = "Email \"$email\" is already registered.";
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)")
            ->execute([$name, $email, $hash, $role]);
        redirectWith('/users/index.php', 'success', "User \"$name\" added successfully.");
    }
}
require_once '../partials/header.php';
?>
<div class="page-header">
  <div><h2>Add User</h2><p>Create a new system account</p></div>
  <a href="<?= BASE_URL ?>/users/index.php" class="btn btn-ghost">← Back</a>
</div>
<div class="card" style="max-width:500px">
  <div class="card-body" style="padding:24px">
    <?php foreach ($errors as $e): ?>
    <div class="alert alert-danger" style="margin-bottom:12px"><?= e($e) ?></div>
    <?php endforeach; ?>
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Full name *</label>
        <input type="text" name="name" class="form-control" value="<?= e($_POST['name'] ?? '') ?>" required autofocus/>
      </div>
      <div class="form-group">
        <label class="form-label">Email address *</label>
        <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Role</label>
        <select name="role" class="form-control">
          <option value="staff" <?= ($_POST['role'] ?? 'staff') === 'staff' ? 'selected' : '' ?>>Staff — limited access</option>
          <option value="admin" <?= ($_POST['role'] ?? '') === 'admin'  ? 'selected' : '' ?>>Admin — full control</option>
        </select>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Password *</label>
          <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm password *</label>
          <input type="password" name="confirm" class="form-control" placeholder="Repeat password" required/>
        </div>
      </div>
      <div style="display:flex;gap:10px;margin-top:8px">
        <button type="submit" class="btn btn-primary">Create User</button>
        <a href="<?= BASE_URL ?>/users/index.php" class="btn btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>
<?php require_once '../partials/footer.php'; ?>
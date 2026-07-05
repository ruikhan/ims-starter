<?php
// users/edit.php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) redirectWith('/users/index.php', 'danger', 'User not found.');

$pageTitle = 'Edit User';
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']  ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = $_POST['role']       ?? 'staff';
    $password = $_POST['password']   ?? '';
    $confirm  = $_POST['confirm']    ?? '';

    if (!$name) $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if ($password && strlen($password) < 6) $errors[] = 'New password must be at least 6 characters.';
    if ($password && $password !== $confirm) $errors[] = 'Passwords do not match.';

    // Protect root admin from role demotion
    if ($id === 1 && $role !== 'admin') {
        $errors[] = 'The root admin account cannot be changed to staff.';
        $role = 'admin';
    }

    if (!$errors) {
        $dup = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $dup->execute([$email, $id]);
        if ($dup->fetch()) $errors[] = "Email \"$email\" is already used by another account.";
    }

    if (!$errors) {
        if ($password) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE users SET name=?, email=?, role=?, password=? WHERE id=?")
                ->execute([$name, $email, $role, $hash, $id]);
        } else {
            $pdo->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?")
                ->execute([$name, $email, $role, $id]);
        }
        // Update session if editing self
        if ($id === currentUserId()) {
            $_SESSION['user_name'] = $name;
            $_SESSION['role']      = $role;
        }
        redirectWith('/users/index.php', 'success', "User \"$name\" updated.");
    }
    $user = array_merge($user, compact('name', 'email', 'role'));
}
require_once '../partials/header.php';
?>
<div class="page-header">
  <div><h2>Edit User</h2><p>Modify account details</p></div>
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
        <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Email address *</label>
        <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Role</label>
        <select name="role" class="form-control" <?= $id === 1 ? 'disabled' : '' ?>>
          <option value="staff" <?= $user['role'] === 'staff' ? 'selected' : '' ?>>Staff — limited access</option>
          <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin — full control</option>
        </select>
        <?php if ($id === 1): ?>
        <input type="hidden" name="role" value="admin"/>
        <div class="form-hint">Root admin role cannot be changed</div>
        <?php endif; ?>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">New password</label>
          <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current"/>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm new password</label>
          <input type="password" name="confirm" class="form-control" placeholder="Repeat if changing"/>
        </div>
      </div>
      <div style="display:flex;gap:10px;margin-top:8px">
        <button type="submit" class="btn btn-primary">Update User</button>
        <a href="<?= BASE_URL ?>/users/index.php" class="btn btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>
<?php require_once '../partials/footer.php'; ?>
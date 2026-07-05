<?php
// users/index.php — User management (admin only)
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();
$pageTitle = 'User Management';
require_once '../partials/header.php';

$users = $pdo->query("SELECT * FROM users ORDER BY created_at ASC")->fetchAll();
?>
<div class="page-header">
  <div><h2>User Management</h2><p>Admin-only · manage system access</p></div>
  <a href="<?= BASE_URL ?>/users/add.php" class="btn btn-primary">
    <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Add User
  </a>
</div>
<div class="card">
  <div class="card-body">
    <div class="table-wrap">
      <table>
        <thead><tr><th>User</th><th>Email</th><th>Role</th><th>Created</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u):
          $initials = strtoupper(implode('', array_map(fn($w)=>$w[0], explode(' ', $u['name']))));
          $initials = substr($initials, 0, 2);
          $avatarColor = $u['role'] === 'admin' ? 'var(--purple-dim,#2d1f5e)' : 'var(--blue-dim,#1e3a5e)';
          $textColor   = $u['role'] === 'admin' ? 'var(--purple,#a78bfa)'      : 'var(--blue,#60a5fa)';
        ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:32px;height:32px;border-radius:50%;background:<?= $avatarColor ?>;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:<?= $textColor ?>;flex-shrink:0">
                <?= e($initials) ?>
              </div>
              <span class="tbl-name"><?= e($u['name']) ?></span>
              <?php if ($u['id'] === (int)$_SESSION['user_id']): ?>
              <span class="badge in-stock" style="font-size:10px">You</span>
              <?php endif; ?>
            </div>
          </td>
          <td><span class="tbl-mono"><?= e($u['email']) ?></span></td>
          <td><span class="badge <?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
          <td><span class="tbl-mono"><?= e($u['created_at']) ?></span></td>
          <td><div class="tbl-actions">
            <a href="<?= BASE_URL ?>/users/edit.php?id=<?= $u['id'] ?>" class="btn btn-ghost btn-sm btn-icon" title="Edit">
              <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </a>
            <?php if ($u['id'] !== 1 && $u['id'] !== (int)$_SESSION['user_id']): ?>
            <form method="POST" action="<?= BASE_URL ?>/users/delete.php" style="display:inline"
                  onsubmit="return confirm('Remove user \'<?= e(addslashes($u['name'])) ?>\'?')">
              <input type="hidden" name="id" value="<?= $u['id'] ?>"/>
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Delete">
                <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
              </button>
            </form>
            <?php endif; ?>
          </div></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require_once '../partials/footer.php'; ?>
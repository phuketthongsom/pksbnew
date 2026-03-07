<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/helpers.php';

$admin_page_title = 'Manage Users';
$admin_active_nav = 'users';

$users = load_json('users.json');
$msg = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_user') {
        $uname = trim($_POST['username'] ?? '');
        $name  = trim($_POST['name'] ?? '');
        $pass  = trim($_POST['password'] ?? '');
        // Check duplicate
        foreach ($users as $u) {
            if ($u['username'] === $uname) { $err = 'Username already exists.'; goto done; }
        }
        if (strlen($pass) < 6) { $err = 'Password must be at least 6 characters.'; goto done; }
        $users[] = [
            'id'       => (max(array_column($users, 'id')) ?: 0) + 1,
            'username' => $uname,
            'password' => password_hash($pass, PASSWORD_DEFAULT),
            'name'     => $name ?: $uname,
            'role'     => 'admin',
        ];
        save_json('users.json', $users);
        $msg = 'User added.';

    } elseif ($action === 'change_password') {
        $id   = (int)($_POST['user_id'] ?? 0);
        $pass = trim($_POST['new_password'] ?? '');
        if (strlen($pass) < 6) { $err = 'Password must be at least 6 characters.'; goto done; }
        foreach ($users as &$u) {
            if ($u['id'] === $id) { $u['password'] = password_hash($pass, PASSWORD_DEFAULT); break; }
        }
        unset($u);
        save_json('users.json', $users);
        $msg = 'Password changed.';

    } elseif ($action === 'delete_user') {
        $id = (int)($_POST['user_id'] ?? 0);
        if ($id === 1) { $err = 'Cannot delete default admin.'; goto done; }
        $users = array_values(array_filter($users, fn($u) => $u['id'] !== $id));
        save_json('users.json', $users);
        $msg = 'User deleted.';
    }
    done:
}

require_once __DIR__ . '/inc/admin_header.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><?= esc($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= esc($err) ?></div><?php endif; ?>

<div class="admin-card">
  <h2>Admin Users</h2>
  <table class="admin-table">
    <thead><tr><th>ID</th><th>Username</th><th>Name</th><th>Role</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td><?= esc($u['id']) ?></td>
        <td><strong><?= esc($u['username']) ?></strong></td>
        <td><?= esc($u['name']) ?></td>
        <td><span style="background:#01aaa8;color:#fff;padding:2px 10px;border-radius:12px;font-size:.78rem"><?= esc($u['role']) ?></span></td>
        <td style="display:flex;gap:8px">
          <form method="post" style="display:inline-flex;gap:6px;align-items:center">
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" name="user_id" value="<?= esc($u['id']) ?>">
            <input type="password" name="new_password" placeholder="New password" style="padding:4px 8px;border:1px solid #ddd;border-radius:4px;font-size:.82rem;width:130px">
            <button type="submit" class="btn btn-sm" style="background:#f0f0f0;color:#333">🔑</button>
          </form>
          <?php if ($u['id'] != 1): ?>
          <form method="post" style="display:inline" onsubmit="return confirm('Delete user <?= esc($u['username']) ?>?')">
            <input type="hidden" name="action" value="delete_user">
            <input type="hidden" name="user_id" value="<?= esc($u['id']) ?>">
            <button type="submit" class="btn btn-sm" style="background:#f0f0f0;color:#e74c3c">🗑</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="admin-card">
  <h2>Add New User</h2>
  <form method="post">
    <input type="hidden" name="action" value="add_user">
    <div class="form-row">
      <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
      <div class="form-group"><label>Display Name</label><input type="text" name="name"></div>
    </div>
    <div class="form-group"><label>Password (min 6 chars)</label><input type="password" name="password" required minlength="6"></div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">➕ Add User</button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/inc/admin_footer.php'; ?>

<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/auth.php';

if (auth_enabled()) {
  $u = current_user();
  if ($u) { header('Location: dashboard.php'); exit; }
}

$msg = null;
$type = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'] ?? '';
  $pass = $_POST['password'] ?? '';

  $res = try_login($email, $pass);
  if ($res['ok']) {
    header('Location: dashboard.php');
    exit;
  }
  $msg = $res['msg'];
  $type = 'error';
}

page_header("Login");
?>
<div class="card">
  <h2>Login</h2>

  <?php if ($msg): ?>
    <div class="flash <?= h($type) ?>"><?= h($msg) ?></div>
  <?php endif; ?>

  <form method="post">
    <label>Email</label>
    <input name="email" type="email" required autocomplete="email">

    <label>Password</label>
    <input name="password" type="password" required autocomplete="current-password">

    <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
      <button class="btn" type="submit">Sign in</button>
      <a class="btn" href="register.php">Create account</a>
    </div>
  </form>
</div>
<?php page_footer(); ?>

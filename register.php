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
  $name = $_POST['display_name'] ?? '';
  $pass = $_POST['password'] ?? '';

  $res = try_register($email, $name, $pass);
if ($res['ok']) {
  header('Location: dashboard.php');
  exit;
}
$msg = $res['msg'];
$type = 'error';

}

page_header("Register");
?>
<div class="card">
  <h2>Create account</h2>
  <p>Make up a password you will remember. Revolutionary concept.</p>

  <?php if ($msg): ?>
    <div class="flash <?= h($type) ?>"><?= h($msg) ?></div>
  <?php endif; ?>

  <form method="post">
    <label>Name</label>
    <input name="display_name" required maxlength="80" autocomplete="name">

    <label>Email</label>
    <input name="email" type="email" required maxlength="190" autocomplete="email">

    <label>Password</label>
    <input name="password" type="password" required minlength="6" autocomplete="new-password">

    <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
      <button class="btn" type="submit">Register</button>
      <a class="btn" href="login.php">I already have an account</a>
    </div>
  </form>
</div>
<?php page_footer(); ?>

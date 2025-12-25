<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

function page_header(string $title): void {
  ensure_session();
  $enabled = auth_enabled();
  $u = current_user(); // null если не залогинен

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title) ?></title>

  <link rel="stylesheet" href="assets/app.css">

  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

  <!-- JS libs -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
<header class="topbar">
  <div class="container topbar__row">
    <div class="brand">
      <span class="brand__logo">🚗</span>
      <span class="brand__name">Driving Experience</span>
    </div>

    <nav class="nav">
      <a href="dashboard.php">Dashboard</a>
      <a href="experience_add.php">Add</a>
      <a href="experiences.php">Experiences</a>
      <a href="variables.php">Variables</a>

      <?php if ($enabled): ?>
        <?php if ($u): ?>
          <span class="nav__muted" style="padding:8px 10px;">
            <?= h($u['display_name']) ?>
          </span>
          <a class="nav__muted" href="logout.php">Logout</a>
        <?php else: ?>
          <a class="nav__muted" href="register.php">Register</a>
          <a class="nav__muted" href="login.php">Login</a>
        <?php endif; ?>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="container">
<?php
}

function page_footer(): void {
?>
</main>

<footer class="footer">
  <div class="container footer__row">
    <small>PHP + MySQL, built by a human under academic pressure.</small>
  </div>
</footer>

<script src="assets/app.js"></script>
</body>
</html>
<?php
}

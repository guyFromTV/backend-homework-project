<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

require_login();
$pdo = db();

$tables = [
  'weather' => ['pk'=>'id_weather','label'=>'label','title'=>'Weather'],
  'road_conditions' => ['pk'=>'id_road','label'=>'label','title'=>'Road conditions'],
  'traffic_levels' => ['pk'=>'id_traffic','label'=>'label','title'=>'Traffic levels'],
  'maneuvers' => ['pk'=>'id_maneuver','label'=>'label','title'=>'Maneuvers (many-to-many)'],
];

ensure_session();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $table = $_POST['table'] ?? '';
  $label = trim($_POST['label'] ?? '');
  if (!isset($tables[$table]) || $label === '') {
    $_SESSION['flash'] = ['type'=>'error','msg'=>'Invalid input.'];
    header('Location: variables.php');
    exit;
  }
  $meta = $tables[$table];
  $stmt = $pdo->prepare("INSERT INTO {$table} ({$meta['label']}, is_active) VALUES (:label, 1)");
  try {
    $stmt->execute([':label'=>$label]);
    $_SESSION['flash'] = ['type'=>'ok','msg'=>'Added.'];
  } catch (Throwable $e) {
    $_SESSION['flash'] = ['type'=>'error','msg'=>'Add failed: '.$e->getMessage()];
  }
  header('Location: variables.php');
  exit;
}

$data = [];
foreach ($tables as $t => $meta) {
  $data[$t] = $pdo->query("SELECT {$meta['pk']} AS id, {$meta['label']} AS label, is_active FROM {$t} ORDER BY label")->fetchAll();
}

page_header("Variables");
?>
<h1>Variables</h1>

<?php if ($flash): ?>
  <div class="flash <?= h($flash['type']) ?>"><?= h($flash['msg']) ?></div>
<?php endif; ?>

<div class="grid cols-2">
<?php foreach ($tables as $t => $meta): ?>
  <div class="card">
    <h3><?= h($meta['title']) ?></h3>

    <form method="post" style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
      <input type="hidden" name="table" value="<?= h($t) ?>">
      <div style="flex:1; min-width:220px;">
        <label>Add new</label>
        <input name="label" placeholder="New value..." required>
      </div>
      <button class="btn" type="submit">Add</button>
    </form>

    <div style="margin-top:12px; overflow:auto;">
      <table style="width:100%; border-collapse: collapse;">
        <thead>
          <tr><th align="left">Value</th><th align="left">Active</th><th align="left">Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($data[$t] as $row): ?>
          <tr>
            <td style="padding:8px 0; border-bottom:1px solid var(--line);"><?= h($row['label']) ?></td>
            <td style="border-bottom:1px solid var(--line); color:<?= $row['is_active'] ? 'var(--accent)' : 'var(--muted)' ?>;">
              <?= $row['is_active'] ? 'yes' : 'no' ?>
            </td>
            <td style="border-bottom:1px solid var(--line);">
              <a class="btn <?= $row['is_active'] ? 'danger' : '' ?>"
                 href="variable_toggle.php?table=<?= h($t) ?>&id=<?= (int)$row['id'] ?>">
                 <?= $row['is_active'] ? 'Deactivate' : 'Activate' ?>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </div>
<?php endforeach; ?>
</div>

<?php page_footer(); ?>

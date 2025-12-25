<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

require_login();
$pdo = db();

$weather = $pdo->query("SELECT id_weather, label FROM weather WHERE is_active=1 ORDER BY label")->fetchAll();
$roads   = $pdo->query("SELECT id_road, label FROM road_conditions WHERE is_active=1 ORDER BY label")->fetchAll();
$traffic = $pdo->query("SELECT id_traffic, label FROM traffic_levels WHERE is_active=1 ORDER BY label")->fetchAll();
$mans    = $pdo->query("SELECT id_maneuver, label FROM maneuvers WHERE is_active=1 ORDER BY label")->fetchAll();

ensure_session();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$now = (new DateTime())->format('Y-m-d\TH:i'); // input datetime-local
page_header("Add experience");
?>
<h1>Add driving experience</h1>

<?php if ($flash): ?>
  <div class="flash <?= h($flash['type']) ?>"><?= h($flash['msg']) ?></div>
<?php endif; ?>

<div class="card">
  <form method="post" action="experience_save.php">
    <div class="row cols-2">
      <div>
        <label>Date & time</label>
        <input type="datetime-local" name="started_at" required value="<?= h($now) ?>">
      </div>
      <div>
        <label>Kilometers</label>
        <input type="number" name="km" required inputmode="decimal" step="0.01" min="0" placeholder="e.g. 12.50">
      </div>
    </div>

    <div class="row cols-3">
      <div>
        <label>Weather</label>
        <select name="id_weather" required>
          <?php foreach ($weather as $w): ?>
            <option value="<?= (int)$w['id_weather'] ?>"><?= h($w['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>Road condition</label>
        <select name="id_road" required>
          <?php foreach ($roads as $r): ?>
            <option value="<?= (int)$r['id_road'] ?>"><?= h($r['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>Traffic</label>
        <select name="id_traffic" required>
          <?php foreach ($traffic as $t): ?>
            <option value="<?= (int)$t['id_traffic'] ?>"><?= h($t['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div>
      <label>Maneuvers (many-to-many)</label>
      <select name="maneuvers[]" multiple size="6">
        <?php foreach ($mans as $m): ?>
          <option value="<?= (int)$m['id_maneuver'] ?>"><?= h($m['label']) ?></option>
        <?php endforeach; ?>
      </select>
      <p style="margin:6px 0 0">Hold Ctrl / Cmd to select multiple.</p>
    </div>

    <div>
      <label>Comment (optional)</label>
      <textarea name="comment" placeholder="Anything about the session..."></textarea>
    </div>

    <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
      <button class="btn" type="submit">Save</button>
      <a class="btn" href="experiences.php">Go to summary</a>
    </div>
  </form>
</div>

<?php page_footer(); ?>

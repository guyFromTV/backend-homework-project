<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

require_login();
$pdo = db();

$rows = $pdo->query("
  SELECT de.id_experience, de.started_at, de.km, de.comment,
         w.label AS weather, r.label AS road, t.label AS traffic,
         GROUP_CONCAT(m.label ORDER BY m.label SEPARATOR ', ') AS maneuvers
  FROM driving_experiences de
  JOIN weather w ON w.id_weather=de.id_weather
  JOIN road_conditions r ON r.id_road=de.id_road
  JOIN traffic_levels t ON t.id_traffic=de.id_traffic
  LEFT JOIN experience_maneuvers em ON em.id_experience=de.id_experience
  LEFT JOIN maneuvers m ON m.id_maneuver=em.id_maneuver
  GROUP BY de.id_experience
  ORDER BY de.started_at DESC
")->fetchAll();

$totalKm = (float)$pdo->query("SELECT COALESCE(SUM(km),0) AS s FROM driving_experiences")->fetch()['s'];

page_header("Experiences summary");
?>
<h1>Experiences summary</h1>

<div class="card">
  <p>Total kilometers recorded: <b style="color:var(--accent)"><?= h(number_format($totalKm, 2)) ?></b></p>

  <div style="overflow:auto">
    <table id="experiencesTable" class="display" style="width:100%">
      <thead>
        <tr>
          <th>Date</th>
          <th>Km</th>
          <th>Weather</th>
          <th>Road</th>
          <th>Traffic</th>
          <th>Maneuvers</th>
          <th>Comment</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $x): ?>
        <tr>
          <td><?= h($x['started_at']) ?></td>
          <td><?= h((string)$x['km']) ?></td>
          <td><?= h($x['weather']) ?></td>
          <td><?= h($x['road']) ?></td>
          <td><?= h($x['traffic']) ?></td>
          <td><?= h($x['maneuvers'] ?? '') ?></td>
          <td><?= h(mb_strimwidth((string)($x['comment'] ?? ''), 0, 80, '…')) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php page_footer(); ?>

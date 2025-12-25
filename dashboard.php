<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

require_login();
$pdo = db();

// totals
$totalKm = (float)$pdo->query("SELECT COALESCE(SUM(km),0) AS s FROM driving_experiences")->fetch()['s'];
$totalTrips = (int)$pdo->query("SELECT COUNT(*) AS c FROM driving_experiences")->fetch()['c'];

// last trips
$last = $pdo->query("
  SELECT de.id_experience, de.started_at, de.km,
         w.label AS weather, r.label AS road, t.label AS traffic
  FROM driving_experiences de
  JOIN weather w ON w.id_weather=de.id_weather
  JOIN road_conditions r ON r.id_road=de.id_road
  JOIN traffic_levels t ON t.id_traffic=de.id_traffic
  ORDER BY de.started_at DESC
  LIMIT 10
")->fetchAll();

// stats for charts
$byWeather = $pdo->query("
  SELECT w.label AS label, COALESCE(SUM(de.km),0) AS km
  FROM weather w
  LEFT JOIN driving_experiences de ON de.id_weather = w.id_weather
  WHERE w.is_active=1
  GROUP BY w.id_weather
  ORDER BY km DESC
")->fetchAll();

$byRoad = $pdo->query("
  SELECT r.label AS label, COALESCE(SUM(de.km),0) AS km
  FROM road_conditions r
  LEFT JOIN driving_experiences de ON de.id_road = r.id_road
  WHERE r.is_active=1
  GROUP BY r.id_road
  ORDER BY km DESC
")->fetchAll();

$byTraffic = $pdo->query("
  SELECT t.label AS label, COALESCE(SUM(de.km),0) AS km
  FROM traffic_levels t
  LEFT JOIN driving_experiences de ON de.id_traffic = t.id_traffic
  WHERE t.is_active=1
  GROUP BY t.id_traffic
  ORDER BY km DESC
")->fetchAll();

$topManeuvers = $pdo->query("
  SELECT m.label AS label, COUNT(*) AS cnt
  FROM experience_maneuvers em
  JOIN maneuvers m ON m.id_maneuver = em.id_maneuver
  GROUP BY m.id_maneuver
  ORDER BY cnt DESC
  LIMIT 8
")->fetchAll();

page_header("Dashboard");
?>
<h1>Hello Eric</h1>

<div class="grid cols-3">
  <div class="card">
    <h3>Total km</h3>
    <p style="font-size:32px; margin:0; color:var(--accent); font-weight:800;"><?= h(number_format($totalKm, 2)) ?></p>
  </div>
  <div class="card">
    <h3>Total experiences</h3>
    <p style="font-size:32px; margin:0; font-weight:800;"><?= h((string)$totalTrips) ?></p>
  </div>
  <div class="card">
    <h3>Quick action</h3>
    <p><a class="btn" href="experience_add.php">➕ Add experience</a></p>
  </div>
</div>

<div class="grid cols-2" style="margin-top:14px;">
  <div class="card">
    <h3>Kilometers by Weather</h3>
    <canvas id="cWeather"></canvas>
  </div>
  <div class="card">
    <h3>Kilometers by Road</h3>
    <canvas id="cRoad"></canvas>
  </div>
  <div class="card">
    <h3>Kilometers by Traffic</h3>
    <canvas id="cTraffic"></canvas>
  </div>
  <div class="card">
    <h3>Top maneuvers (many-to-many)</h3>
    <canvas id="cManeuvers"></canvas>
  </div>
</div>

<div class="card" style="margin-top:14px;">
  <h3>Last 10 experiences</h3>
  <div style="overflow:auto">
    <table class="display" style="width:100%">
      <thead>
        <tr>
          <th>Date</th><th>Km</th><th>Weather</th><th>Road</th><th>Traffic</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($last as $x): ?>
          <tr>
            <td><?= h($x['started_at']) ?></td>
            <td><?= h((string)$x['km']) ?></td>
            <td><?= h($x['weather']) ?></td>
            <td><?= h($x['road']) ?></td>
            <td><?= h($x['traffic']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
const byWeather = <?= json_encode($byWeather) ?>;
const byRoad = <?= json_encode($byRoad) ?>;
const byTraffic = <?= json_encode($byTraffic) ?>;
const topManeuvers = <?= json_encode($topManeuvers) ?>;

function mkBar(canvasId, rows, valueKey='km') {
  const ctx = document.getElementById(canvasId);
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: rows.map(r => r.label),
      datasets: [{ label: valueKey, data: rows.map(r => Number(r[valueKey])) }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });
}

mkBar('cWeather', byWeather, 'km');
mkBar('cRoad', byRoad, 'km');
mkBar('cTraffic', byTraffic, 'km');

new Chart(document.getElementById('cManeuvers'), {
  type: 'bar',
  data: {
    labels: topManeuvers.map(r => r.label),
    datasets: [{ label: 'count', data: topManeuvers.map(r => Number(r.cnt)) }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});
</script>

<?php page_footer(); ?>

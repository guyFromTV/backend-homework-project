<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$pdo = db();
ensure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: experience_add.php');
  exit;
}

function flash(string $type, string $msg): void {
  $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

$startedRaw = trim($_POST['started_at'] ?? '');
$kmRaw = trim($_POST['km'] ?? '');
$idWeather = (int)($_POST['id_weather'] ?? 0);
$idRoad = (int)($_POST['id_road'] ?? 0);
$idTraffic = (int)($_POST['id_traffic'] ?? 0);
$comment = trim($_POST['comment'] ?? '');
$maneuvers = $_POST['maneuvers'] ?? [];

if ($startedRaw === '' || $kmRaw === '' || $idWeather<=0 || $idRoad<=0 || $idTraffic<=0) {
  flash('error', 'Missing required fields.');
  header('Location: experience_add.php');
  exit;
}

$km = filter_var($kmRaw, FILTER_VALIDATE_FLOAT);
if ($km === false || $km < 0) {
  flash('error', 'Invalid kilometers.');
  header('Location: experience_add.php');
  exit;
}

$dt = DateTime::createFromFormat('Y-m-d\TH:i', $startedRaw);
if (!$dt) {
  flash('error', 'Invalid date/time format.');
  header('Location: experience_add.php');
  exit;
}

try {
  $pdo->beginTransaction();

  $stmt = $pdo->prepare("
    INSERT INTO driving_experiences (started_at, km, comment, id_weather, id_road, id_traffic)
    VALUES (:started_at, :km, :comment, :id_weather, :id_road, :id_traffic)
  ");
  $stmt->execute([
    ':started_at' => $dt->format('Y-m-d H:i:s'),
    ':km' => $km,
    ':comment' => ($comment === '' ? null : $comment),
    ':id_weather' => $idWeather,
    ':id_road' => $idRoad,
    ':id_traffic' => $idTraffic,
  ]);

  $idExp = (int)$pdo->lastInsertId();

  // many-to-many inserts
  if (is_array($maneuvers) && count($maneuvers) > 0) {
    $ins = $pdo->prepare("INSERT IGNORE INTO experience_maneuvers (id_experience, id_maneuver) VALUES (?, ?)");
    foreach ($maneuvers as $mid) {
      $mid = (int)$mid;
      if ($mid > 0) $ins->execute([$idExp, $mid]);
    }
  }

  $pdo->commit();
  flash('ok', 'Saved successfully.');
  header('Location: experience_add.php');
  exit;

} catch (Throwable $e) {
  $pdo->rollBack();
  flash('error', 'Save failed: ' . $e->getMessage());
  header('Location: experience_add.php');
  exit;
}

<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$pdo = db();
ensure_session();

$tables = [
  'weather' => 'id_weather',
  'road_conditions' => 'id_road',
  'traffic_levels' => 'id_traffic',
  'maneuvers' => 'id_maneuver',
];

$table = $_GET['table'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if (!isset($tables[$table]) || $id <= 0) {
  $_SESSION['flash'] = ['type'=>'error','msg'=>'Invalid request.'];
  header('Location: variables.php');
  exit;
}

$pk = $tables[$table];

$stmt = $pdo->prepare("UPDATE {$table} SET is_active = 1 - is_active WHERE {$pk} = :id");
try {
  $stmt->execute([':id'=>$id]);
  $_SESSION['flash'] = ['type'=>'ok','msg'=>'Updated.'];
} catch (Throwable $e) {
  $_SESSION['flash'] = ['type'=>'error','msg'=>'Update failed: '.$e->getMessage()];
}

header('Location: variables.php');
exit;

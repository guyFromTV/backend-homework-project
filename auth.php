<?php
require_once __DIR__ . '/db.php';

function auth_enabled(): bool {
  $cfg = require __DIR__ . '/config.php';
  return (bool)($cfg['auth']['enabled'] ?? true);
}

function ensure_session(): void {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
}

function require_login(): void {
  if (!auth_enabled()) return;
  ensure_session();
  if (!($_SESSION['user_id'] ?? null)) {
    header('Location: login.php');
    exit;
  }
}

function current_user(): ?array {
  if (!auth_enabled()) return ['id_user'=>0,'email'=>'','display_name'=>'Guest'];
  ensure_session();
  if (!($_SESSION['user_id'] ?? null)) return null;
  return [
    'id_user' => (int)$_SESSION['user_id'],
    'email' => (string)$_SESSION['user_email'],
    'display_name' => (string)$_SESSION['user_name'],
  ];
}

function logout(): void {
  ensure_session();
  $_SESSION = [];
  session_destroy();
}

function try_register(string $email, string $displayName, string $password): array {
    $email = trim(mb_strtolower($email));
    $displayName = trim($displayName);
  
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return ['ok'=>false,'msg'=>'Invalid email.'];
    }
    if (mb_strlen($displayName) < 2) {
      return ['ok'=>false,'msg'=>'Name is too short.'];
    }
    if (mb_strlen($password) < 6) {
      return ['ok'=>false,'msg'=>'Password must be at least 6 characters.'];
    }
  
    $pdo = db();
    $hash = password_hash($password, PASSWORD_DEFAULT);
  
    try {
      $stmt = $pdo->prepare(
        "INSERT INTO users (email, password_hash, display_name) VALUES (:e,:p,:n)"
      );
      $stmt->execute([':e'=>$email, ':p'=>$hash, ':n'=>$displayName]);
  
      $id = (int)$pdo->lastInsertId();
  
      // ✅ auto-login right after registration
      ensure_session();
      $_SESSION['user_id'] = $id;
      $_SESSION['user_email'] = $email;
      $_SESSION['user_name'] = $displayName;
  
      return ['ok'=>true,'msg'=>'Registered and logged in.'];
    } catch (Throwable $e) {
      return ['ok'=>false,'msg'=>'Registration failed. Email may already exist.'];
    }
  }
  

function try_login(string $email, string $password): array {
  $email = trim(mb_strtolower($email));
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    return ['ok'=>false,'msg'=>'Invalid email.'];
  }

  $pdo = db();
  $stmt = $pdo->prepare("SELECT id_user, email, password_hash, display_name FROM users WHERE email=:e LIMIT 1");
  $stmt->execute([':e'=>$email]);
  $u = $stmt->fetch();

  if (!$u || !password_verify($password, $u['password_hash'])) {
    return ['ok'=>false,'msg'=>'Wrong email or password.'];
  }

  ensure_session();
  $_SESSION['user_id'] = (int)$u['id_user'];
  $_SESSION['user_email'] = (string)$u['email'];
  $_SESSION['user_name'] = (string)$u['display_name'];

  return ['ok'=>true,'msg'=>'Logged in.'];
}

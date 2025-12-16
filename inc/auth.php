<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function login_patient(PDO $pdo, string $patient_id, string $pin): bool {
  $st = $pdo->prepare("SELECT patient_id,pin_hash FROM patients WHERE patient_id=?");
  $st->execute([$patient_id]);
  $p = $st->fetch();
  if (!$p) return false;
  if (!password_verify($pin, $p['pin_hash'])) return false;

  $_SESSION['patient_id'] = $p['patient_id'];
  unset($_SESSION['employee_id'], $_SESSION['employee_role']);
  return true;
}

function login_employee(PDO $pdo, string $username, string $pin): bool {
  $st = $pdo->prepare("SELECT id,role,pin_hash,display_name FROM employees WHERE username=?");
  $st->execute([$username]);
  $u = $st->fetch();
  if (!$u) return false;
  if (!password_verify($pin, $u['pin_hash'])) return false;

  $_SESSION['employee_id'] = (int)$u['id'];
  $_SESSION['employee_role'] = $u['role'];
  $_SESSION['employee_name'] = $u['display_name'];
  unset($_SESSION['patient_id']);
  return true;
}

function require_patient(): string {
  if (empty($_SESSION['patient_id'])) { header("Location: patientenportal.php"); exit; }
  return (string)$_SESSION['patient_id'];
}

function require_employee(array $roles = []): void {
  if (empty($_SESSION['employee_id']) || empty($_SESSION['employee_role'])) {
    header("Location: mitarbeiterportal.php"); exit;
  }
  if ($roles && !in_array($_SESSION['employee_role'], $roles, true)) {
    http_response_code(403); exit("Keine Berechtigung.");
  }
}

function employee_role(): string {
  return (string)($_SESSION['employee_role'] ?? '');
}

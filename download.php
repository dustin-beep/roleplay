<?php
declare(strict_types=1);
require __DIR__ . "/inc/db.php";
require __DIR__ . "/inc/auth.php";

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(400); exit("Bad request."); }

$isPatient = !empty($_SESSION['patient_id']);
$isEmployee = !empty($_SESSION['employee_id']);

$st = $pdo->prepare("SELECT * FROM documents WHERE id=?");
$st->execute([$id]);
$doc = $st->fetch();
if (!$doc) { http_response_code(404); exit("Nicht gefunden."); }

if ($isPatient) {
  $pid = require_patient();
  if ($doc['patient_id'] !== $pid) { http_response_code(403); exit("Forbidden."); }
} elseif ($isEmployee) {
  require_employee(['systemadministrator','arzt','pflegekraft','auszubildender']);
  // Mitarbeiter dürfen grundsätzlich einsehen (kannst du feiner machen)
} else {
  http_response_code(403); exit("Forbidden.");
}

$path = $doc['file_path'];
if (!is_file($path)) { http_response_code(404); exit("Datei fehlt."); }

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="'.basename($doc['file_name']).'"');
header('Content-Length: ' . filesize($path));
readfile($path);

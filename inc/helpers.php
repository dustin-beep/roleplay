<?php
declare(strict_types=1);

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function ensure_dir(string $dir): void {
  if (!is_dir($dir)) { mkdir($dir, 0775, true); }
}

function generate_patient_id(PDO $pdo): string {
  // BST_ + 5 Ziffern
  for ($i=0; $i<20; $i++) {
    $num = str_pad((string)random_int(0, 99999), 5, '0', STR_PAD_LEFT);
    $id  = "BST_$num";
    $st = $pdo->prepare("SELECT 1 FROM patients WHERE patient_id=?");
    $st->execute([$id]);
    if (!$st->fetchColumn()) return $id;
  }
  throw new RuntimeException("Konnte keine eindeutige Patient-ID erzeugen.");
}

function generate_pin4(): string {
  return str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
}

function compute_age(?string $dob): ?int {
  if (!$dob) return null;
  try {
    $d = new DateTime($dob);
    $now = new DateTime('today');
    return $d->diff($now)->y;
  } catch (Throwable $e) { return null; }
}

function next_patient_id(PDO $pdo): string {
  // fortlaufend BST_00001, BST_00002 ...
  $pdo->beginTransaction();
  try {
    $st = $pdo->prepare("SELECT value FROM counters WHERE name='patients' FOR UPDATE");
    $st->execute();
    $val = (int)$st->fetchColumn();
    $val++;

    $up = $pdo->prepare("UPDATE counters SET value=? WHERE name='patients'");
    $up->execute([$val]);

    $pdo->commit();

    return 'BST_' . str_pad((string)$val, 5, '0', STR_PAD_LEFT);
  } catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
  }
}

function next_doc_seq(PDO $pdo, string $patientId, string $docType): int {
  $st = $pdo->prepare("SELECT COALESCE(MAX(doc_seq),0) FROM documents WHERE patient_id=? AND doc_type=?");
  $st->execute([$patientId, $docType]);
  return ((int)$st->fetchColumn()) + 1;
}

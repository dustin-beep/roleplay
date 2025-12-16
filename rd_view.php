<?php
declare(strict_types=1);

require __DIR__ . "/inc/db.php";
require __DIR__ . "/inc/auth.php";

require_employee(['systemadministrator','arzt','pflegekraft','auszubildender']);

$config = require __DIR__ . "/inc/config.php";
$rddir = rtrim($config['storage_dir'], '/\\') . DIRECTORY_SEPARATOR . "rddocs";

$file = (string)($_GET['file'] ?? '');
$file = str_replace(['..', '/', '\\'], '', $file); // grobe Traversal-Abwehr

if ($file === '' || !preg_match('/\.pdf$/i', $file)) {
  http_response_code(400);
  exit("Ungültige Datei.");
}

$path = $rddir . DIRECTORY_SEPARATOR . $file;

// realpath-Schutz: muss wirklich innerhalb rddocs liegen
$realBase = realpath($rddir);
$realPath = realpath($path);

if (!$realBase || !$realPath || strpos($realPath, $realBase) !== 0 || !is_file($realPath)) {
  http_response_code(404);
  exit("Nicht gefunden.");
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="'.basename($file).'"');
header('Content-Length: ' . filesize($realPath));
header('X-Content-Type-Options: nosniff');

readfile($realPath);

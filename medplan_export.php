<?php
declare(strict_types=1);

require __DIR__ . "/inc/db.php";
require __DIR__ . "/inc/auth.php";
require __DIR__ . "/inc/helpers.php";

require_employee(['systemadministrator','arzt']); // Export+Speichern nur Arzt/Systemadmin

$config = require __DIR__ . "/inc/config.php";

$pid = strtoupper(trim((string)($_GET['patient'] ?? '')));
if ($pid === '') { http_response_code(400); exit("Patient fehlt."); }

// Patient
$pst = $pdo->prepare("SELECT * FROM patients WHERE patient_id=?");
$pst->execute([$pid]);
$patient = $pst->fetch();
if (!$patient) { http_response_code(404); exit("Patient nicht gefunden."); }

// Letzter Plan
$planSt = $pdo->prepare("SELECT * FROM medication_plans WHERE patient_id=? ORDER BY updated_at DESC LIMIT 1");
$planSt->execute([$pid]);
$plan = $planSt->fetch();
if (!$plan) { http_response_code(404); exit("Kein Medikamentenplan vorhanden."); }

$itemsSt = $pdo->prepare("SELECT * FROM medication_items WHERE plan_id=? ORDER BY id DESC");
$itemsSt->execute([(int)$plan['id']]);
$items = $itemsSt->fetchAll();

// dompdf
$autoload = __DIR__ . "/vendor/autoload.php";
if (!is_file($autoload)) {
  http_response_code(500);
  exit("dompdf fehlt. Bitte composer install ausführen.");
}
require $autoload;

use Dompdf\Dompdf;
use Dompdf\Options;

// Logo als Data-URI
$logoPath = $config['logo_path'];
$logoDataUri = '';
if (is_file($logoPath)) {
  $logoDataUri = 'data:image/png;base64,' . base64_encode((string)file_get_contents($logoPath));
}

// HTML Template inline
function schedule_label(string $s): string {
  return match ($s) {
    '1-3_taeglich' => '1–3× täglich',
    '1-5_woechentlich' => '1–5× wöchentlich',
    'bedarfsweise' => 'Bedarfsweise',
    default => $s
  };
}

$doctorName = (string)($_SESSION['employee_name'] ?? 'Unbekannt');

$age = compute_age($patient['dob'] ?? null);
$gender = (string)($patient['gender'] ?? '');
$genderLabel = match($gender) {
  'm' => 'männlich',
  'w' => 'weiblich',
  'd' => 'divers',
  'ka' => 'keine Angabe',
  default => ''
};

ob_start();
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<style>
  body{ font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color:#111; }
  .head{ display:flex; justify-content:space-between; align-items:flex-start; }
  .logo{ width:90px; }
  .h1{ margin-top:10px; font-size:18px; font-weight:800; }
  .meta{ margin-top:8px; line-height:1.6; }
  table{ width:100%; border-collapse:collapse; margin-top:12px; }
  th,td{ border:1px solid #d7dce6; padding:8px; text-align:left; }
  th{ background:#f3f6fb; font-weight:800; }
  .pill{ display:inline-block; padding:2px 8px; border-radius:999px; font-weight:800; font-size:10px; }
  .yes{ background:#0a7a2f; color:#fff; }
  .no{ background:#b00020; color:#fff; }
  .footer{ position: fixed; bottom: 18px; left: 0; right: 0; font-size:10px; color:#333; }
</style>
</head>
<body>

<div class="head">
  <div>
    <div style="font-weight:800;">Bundeswehrkrankenhaus<br>Blaustetten</div>
    <div class="h1">Medikamentenplan</div>

    <div class="meta">
      <b>Patient:</b> <?= htmlspecialchars($patient['first_name'].' '.$patient['last_name']) ?><br>
      <b>ID:</b> <?= htmlspecialchars($pid) ?><br>
      <b>Geburtsdatum:</b> <?= htmlspecialchars((string)($patient['dob'] ?? '')) ?>
      <?php if ($age !== null): ?> (<?= (int)$age ?> Jahre)<?php endif; ?><br>
      <b>Geschlecht:</b> <?= htmlspecialchars($genderLabel) ?><br>
      <b>Blutgruppe:</b> <?= htmlspecialchars((string)($patient['blood_group'] ?? '')) ?><br>
      <b>Versicherung:</b> <?= htmlspecialchars((string)($patient['insurance'] ?? '')) ?><br>
      <b>Notfallkontakt:</b> <?= htmlspecialchars((string)($patient['emergency_contact'] ?? '')) ?><br>
      <b>Stand:</b> <?= htmlspecialchars((string)$plan['updated_at']) ?>
    </div>
  </div>

  <div>
    <?php if ($logoDataUri): ?>
      <img class="logo" src="<?= $logoDataUri ?>" alt="BWK Logo">
    <?php endif; ?>
  </div>
</div>

<table>
  <thead>
    <tr>
      <th>Medikament</th>
      <th>Dosis</th>
      <th>Einnahmezeit</th>
      <th>Aktiv</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($items as $it): ?>
      <tr>
        <td><b><?= htmlspecialchars($it['medication']) ?></b></td>
        <td><?= htmlspecialchars($it['dose']) ?></td>
        <td><?= htmlspecialchars(schedule_label($it['schedule'])) ?></td>
        <td>
          <?php if ((int)$it['is_active'] === 1): ?>
            <span class="pill yes">Ja</span>
          <?php else: ?>
            <span class="pill no">Nein</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$items): ?>
      <tr><td colspan="4">Keine Einträge.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<div style="margin-top:22px;">
  Mit freundlichen Grüßen<br>
  <b><?= htmlspecialchars($doctorName) ?></b>
</div>

<div class="footer">
  Dieses Dokument wurde elektronisch erstellt und ist ohne Signatur gültig.
</div>

</body>
</html>
<?php
$html = (string)ob_get_clean();

$options = new Options();
$options->set('isRemoteEnabled', false);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$pdfBytes = $dompdf->output();

// Speichern + documents-Eintrag
$seq = next_doc_seq($pdo, $pid, 'medplan');
$dir = $config['docs_dir'] . '/' . $pid;
ensure_dir($dir);

$fileName = strtolower($pid) . "_" . $seq . "_medplan.pdf";
$filePath = $dir . "/" . $fileName;

file_put_contents($filePath, $pdfBytes);

$ins = $pdo->prepare("
  INSERT INTO documents (patient_id, doc_type, title, file_name, file_path, doc_seq, created_by)
  VALUES (?,?,?,?,?,?,?)
");
$ins->execute([
  $pid,
  'medplan',
  'Medikamentenplan',
  $fileName,
  $filePath,
  $seq,
  (int)$_SESSION['employee_id']
]);

// Ausgabe im Browser
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="'.$fileName.'"');
header('Content-Length: ' . strlen($pdfBytes));
echo $pdfBytes;

<?php
declare(strict_types=1);

require __DIR__ . "/inc/db.php";
require __DIR__ . "/inc/auth.php";
require __DIR__ . "/inc/helpers.php";

require_employee(['systemadministrator','arzt']);

$config = require __DIR__ . "/inc/config.php";

$autoload = __DIR__ . "/vendor/autoload.php";
if (!is_file($autoload)) {
  http_response_code(500);
  exit("dompdf fehlt. Bitte composer install ausführen.");
}
require $autoload;

use Dompdf\Dompdf;
use Dompdf\Options;

$pageTitle = "BWK - Blaustetten | Arztbrief erstellen";

$pid = strtoupper(trim((string)($_GET['patient'] ?? $_POST['patient_id'] ?? '')));
if ($pid === '') { http_response_code(400); exit("Patient fehlt."); }

// Patient laden
$pst = $pdo->prepare("SELECT * FROM patients WHERE patient_id=?");
$pst->execute([$pid]);
$patient = $pst->fetch();
if (!$patient) { http_response_code(404); exit("Patient nicht gefunden."); }

// Logo Data-URI
$logoDataUri = '';
if (is_file($config['logo_path'])) {
  $logoDataUri = 'data:image/png;base64,' . base64_encode((string)file_get_contents($config['logo_path']));
}

$doctorName = (string)($_SESSION['employee_name'] ?? 'Unbekannt');
$success = null;
$error = null;

$diagnose = (string)($_POST['diagnose'] ?? '');
$anamnese = (string)($_POST['anamnese'] ?? '');
$therapie = (string)($_POST['therapie'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_arztbrief') {
  $diagnose = trim($diagnose);
  $anamnese = trim($anamnese);
  $therapie = trim($therapie);

  if ($diagnose === '' || $anamnese === '' || $therapie === '') {
    $error = "Bitte Diagnose, Anamnese und Therapie ausfüllen.";
  } else {
    // HTML aus Template
    ob_start();
    include __DIR__ . "/templates/arztbrief.html.php"; // nutzt $logoDataUri, $patient, $doctorName, $diagnose, $anamnese, $therapie
    $html = (string)ob_get_clean();

    $options = new Options();
    $options->set('isRemoteEnabled', false);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $pdfBytes = $dompdf->output();

    // Speichern (fortlaufend pro Patient)
    $seq = next_doc_seq($pdo, $pid, 'arztbrief');

    $dir = $config['docs_dir'] . '/' . $pid;
    ensure_dir($dir);

    $fileName = strtolower($pid) . "_" . $seq . "_arztbrief.pdf";
    $filePath = $dir . "/" . $fileName;

    file_put_contents($filePath, $pdfBytes);

    // DB Eintrag
    $ins = $pdo->prepare("
      INSERT INTO documents (patient_id, doc_type, title, file_name, file_path, doc_seq, created_by)
      VALUES (?,?,?,?,?,?,?)
    ");
    $ins->execute([
      $pid,
      'arztbrief',
      "Arztbrief #".$seq,
      $fileName,
      $filePath,
      $seq,
      (int)$_SESSION['employee_id']
    ]);

    $success = "Arztbrief erstellt: ".$fileName;
  }
}

include __DIR__ . "/header.php";
?>

<section class="dg" style="padding:24px 0;">
  <div class="dg__headline"><span>ARZTBRIEF ERSTELLEN</span></div>

  <div class="dg-card" style="margin-bottom:14px;">
    <div class="dg-card__name">Patient</div>
    <div style="margin-top:10px;line-height:1.6;">
      <b><?php echo h($patient['first_name'].' '.$patient['last_name']); ?></b><br>
      <?php echo h($pid); ?>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="dg-card" style="border-color:#ffd6d6;margin-bottom:14px;">
      <b style="color:#b00020;"><?php echo h($error); ?></b>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="dg-card" style="border-color:#cfead6;margin-bottom:14px;">
      <b><?php echo h($success); ?></b><br>
      <a class="dg__chip is-active" href="mitarbeiterportal.php?view=patient&id=<?php echo h($pid); ?>">Zurück zum Patienten</a>
    </div>
  <?php endif; ?>

  <div class="dg-card">
    <div class="dg-card__name">Inhalt</div>

    <form method="post" style="margin-top:12px;display:grid;gap:10px;">
      <input type="hidden" name="action" value="create_arztbrief">
      <input type="hidden" name="patient_id" value="<?php echo h($pid); ?>">

      <div>
        <div class="dg__label">Diagnose</div>
        <textarea class="dg__input" name="diagnose" rows="4" style="width:100%;height:auto;" required><?php echo h($diagnose); ?></textarea>
      </div>

      <div>
        <div class="dg__label">Aktuelle Anamnese</div>
        <textarea class="dg__input" name="anamnese" rows="6" style="width:100%;height:auto;" required><?php echo h($anamnese); ?></textarea>
      </div>

      <div>
        <div class="dg__label">Verlauf bzw. Aktuelle Therapie</div>
        <textarea class="dg__input" name="therapie" rows="7" style="width:100%;height:auto;" required><?php echo h($therapie); ?></textarea>
      </div>

      <button class="dg__chip is-active" type="submit">Arztbrief als PDF erstellen</button>
    </form>
  </div>
</section>

<?php include __DIR__ . "/footer.php"; ?>

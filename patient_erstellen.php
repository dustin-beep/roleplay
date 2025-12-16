<?php
declare(strict_types=1);
require __DIR__ . "/inc/db.php";
require __DIR__ . "/inc/auth.php";
require __DIR__ . "/inc/helpers.php";

require_employee(['systemadministrator','arzt','pflegekraft']);

$pageTitle = "BWK - Blaustetten | Patient anlegen";
$created = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $first = trim((string)($_POST['first_name'] ?? ''));
  $last  = trim((string)($_POST['last_name'] ?? ''));

  if ($first === '' || $last === '') {
    $error = "Vorname und Nachname sind Pflicht.";
  } else {
    $patientId = next_patient_id($pdo);
    $pinPlain = generate_pin4();
    $pinHash = password_hash($pinPlain, PASSWORD_DEFAULT);

    $st = $pdo->prepare("
      INSERT INTO patients (patient_id, pin_hash, first_name, last_name, dob, emergency_contact, blood_group, insurance, gender)
      VALUES (?,?,?,?,?,?,?,?,?)
    ");
    $st->execute([
      $patientId,
      $pinHash,
      $first,
      $last,
      $_POST['dob'] ?: null,
      trim((string)($_POST['emergency_contact'] ?? '')) ?: null,
      trim((string)($_POST['blood_group'] ?? '')) ?: null,
      trim((string)($_POST['insurance'] ?? '')) ?: null,
      $_POST['gender'] ?: null,
    ]);

    $created = ['id' => $patientId, 'pin' => $pinPlain, 'name' => "$first $last"];
  }
}

include __DIR__ . "/header.php";
?>

<section class="dg" style="padding:24px 0;">
  <div class="dg__headline"><span>PATIENT ANLEGEN</span></div>

  <?php if ($error): ?>
    <div class="dg-card" style="border-color:#ffd6d6;">
      <b style="color:#b00020;"><?php echo h($error); ?></b>
    </div>
  <?php endif; ?>

  <?php if ($created): ?>
    <div class="dg-card" style="border-color:#cfead6;">
      <div class="dg-card__name">Patient erstellt</div>
      <div style="margin-top:10px;line-height:1.7;">
        <div><b>Name:</b> <?php echo h($created['name']); ?></div>
        <div><b>ID:</b> <span style="background:var(--blue);color:#fff;padding:4px 8px;border-radius:8px;font-weight:900;"><?php echo h($created['id']); ?></span></div>
        <div><b>PIN:</b> <span style="background:#111;color:#fff;padding:4px 8px;border-radius:8px;font-weight:900;"><?php echo h($created['pin']); ?></span></div>
        <div style="opacity:.8;margin-top:8px;">PIN wird nur einmal angezeigt. Bitte notieren/ausgeben.</div>
      </div>
    </div>
  <?php endif; ?>

  <div class="dg-card" style="margin-top:14px;">
    <div class="dg-card__name">Stammdaten</div>

    <form method="post" style="margin-top:12px;display:grid;gap:10px;grid-template-columns:repeat(2,minmax(220px,1fr));">
      <input class="dg__input" name="first_name" placeholder="Vorname *" required>
      <input class="dg__input" name="last_name" placeholder="Nachname *" required>

      <input class="dg__input" type="date" name="dob" placeholder="Geburtsdatum">
      <select class="dg__input" name="gender">
        <option value="">Geschlecht…</option>
        <option value="m">männlich</option>
        <option value="w">weiblich</option>
        <option value="d">divers</option>
        <option value="ka">keine Angabe</option>
      </select>

      <input class="dg__input" name="blood_group" placeholder="Blutgruppe (z.B. 0+, A-)">
      <input class="dg__input" name="insurance" placeholder="Versicherung">

      <input class="dg__input" style="grid-column:1/-1;" name="emergency_contact" placeholder="Notfallkontakt (Name + Telefon)">

      <button class="dg__chip is-active" type="submit">Patient erstellen</button>
    </form>
  </div>
</section>

<?php include __DIR__ . "/footer.php"; ?>

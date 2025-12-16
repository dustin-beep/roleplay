<?php
declare(strict_types=1);
require __DIR__ . "/inc/db.php";
require __DIR__ . "/inc/auth.php";
require __DIR__ . "/inc/helpers.php";

$pageTitle = "BWK - Blaustetten | Mitarbeiterportal";
$err = null;

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'emp_login') {
  $u = trim((string)($_POST['username'] ?? ''));
  $p = trim((string)($_POST['pin'] ?? ''));
  if (!login_employee($pdo, $u, $p)) $err = "Login fehlgeschlagen.";
}

// Aktionen (nur wenn eingeloggt)
if (!empty($_SESSION['employee_id'])) {
  $role = employee_role();

  // Patient Stammdaten ändern (nur Arzt + Systemadmin)
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'patient_update') {
    require_employee(['arzt','systemadministrator']);
    $pid = strtoupper(trim((string)$_POST['patient_id']));
    $st = $pdo->prepare("UPDATE patients SET first_name=?, last_name=?, dob=?, street=?, city=?, phone=?, email=?, emergency_contact=?, blood_group=?, insurance=?, gender=? WHERE patient_id=?");
    $st->execute([
      trim((string)$_POST['first_name']),
      trim((string)$_POST['last_name']),
      $_POST['dob'] ?: null,
      trim((string)$_POST['emergency_contact']),
      trim((string)$_POST['blood_group']),
      trim((string)$_POST['insurance']),
      trim((string)$_POST['gender']),
      trim((string)$_POST['street']),
      trim((string)$_POST['city']),
      trim((string)$_POST['phone']),
      trim((string)$_POST['email']),
      $pid
    ]);
  }

  // Vitals eintragen (Arzt + Pflegekraft + Systemadmin)
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'vitals_add') {
    require_employee(['arzt','pflegekraft','systemadministrator']);
    $pid = strtoupper(trim((string)$_POST['patient_id']));
    $st = $pdo->prepare("INSERT INTO vitals (patient_id,measured_at,spo2,rr_sys,rr_dia,pulse,temp_c,bz,notes,recorded_by) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $st->execute([
      $pid,
      $_POST['measured_at'],
      $_POST['spo2'] !== '' ? (int)$_POST['spo2'] : null,
      $_POST['rr_sys'] !== '' ? (int)$_POST['rr_sys'] : null,
      $_POST['rr_dia'] !== '' ? (int)$_POST['rr_dia'] : null,
      $_POST['pulse'] !== '' ? (int)$_POST['pulse'] : null,
      $_POST['temp_c'] !== '' ? (float)$_POST['temp_c'] : null,
      $_POST['bz'] !== '' ? (int)$_POST['bz'] : null,
      trim((string)($_POST['notes'] ?? '')),
      (int)$_SESSION['employee_id']
    ]);
  }

  // Bettzuweisung (Arzt + Pflegekraft + Systemadmin)
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'bed_assign') {
    require_employee(['arzt','pflegekraft','systemadministrator']);
    $pid = strtoupper(trim((string)$_POST['patient_id']));
    $bedId = (int)$_POST['bed_id'];

    // alte Zuweisung schließen
    $pdo->prepare("UPDATE bed_assignments SET released_at=NOW() WHERE patient_id=? AND released_at IS NULL")
        ->execute([$pid]);

    // neue Zuweisung
    $pdo->prepare("INSERT INTO bed_assignments (patient_id,bed_id,assigned_by) VALUES (?,?,?)")
        ->execute([$pid, $bedId, (int)$_SESSION['employee_id']]);
  }

  // Fachabteilungen & Betten (nur Systemadmin)
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'dept_add') {
    require_employee(['systemadministrator']);
    $pdo->prepare("INSERT INTO departments (name,created_by) VALUES (?,?)")
        ->execute([trim((string)$_POST['dept_name']), (int)$_SESSION['employee_id']]);
  }
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'bed_add') {
    require_employee(['systemadministrator']);
    $pdo->prepare("INSERT INTO beds (department_id,bed_code) VALUES (?,?)")
        ->execute([(int)$_POST['dept_id'], trim((string)$_POST['bed_code'])]);
  }
}

include __DIR__ . "/header.php";

if (empty($_SESSION['employee_id'])):
?>
<section class="dg" style="padding:24px 0;">
  <div class="dg__headline"><span>MITARBEITERPORTAL</span></div>

  <div style="max-width:420px;margin:0 auto;border:1px solid #e6eaf2;border-radius:12px;padding:16px;">
    <?php if ($err): ?><p style="color:#b00020;font-weight:800;"><?php echo h($err); ?></p><?php endif; ?>

    <form method="post">
      <input type="hidden" name="action" value="emp_login">
      <label class="dg__label">Benutzername</label>
      <input class="dg__input" name="username" required>

      <label class="dg__label" style="margin-top:10px;display:block;">PIN (4-stellig)</label>
      <input class="dg__input" name="pin" inputmode="numeric" pattern="\d{4}" maxlength="4" required>

      <button class="dg__chip is-active" style="margin-top:12px;" type="submit">Einloggen</button>
    </form>
  </div>
</section>
<?php
include __DIR__ . "/footer.php";
exit;
endif;

require_employee(['systemadministrator','arzt','pflegekraft','auszubildender']);

$role = employee_role();
$view = $_GET['view'] ?? 'patients';
$q = trim((string)($_GET['q'] ?? ''));

// Daten für Settings (Depts/Beds)
$depts = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
$beds  = $pdo->query("SELECT b.id,b.bed_code,d.name AS dept FROM beds b JOIN departments d ON d.id=b.department_id WHERE b.is_active=1 ORDER BY d.name,b.bed_code")->fetchAll();

// Patientensuche
$patients = [];
if ($view === 'patients') {
  if ($q !== '') {
    $st = $pdo->prepare("SELECT patient_id,first_name,last_name FROM patients WHERE patient_id LIKE ? OR last_name LIKE ? OR first_name LIKE ? ORDER BY last_name LIMIT 50");
    $like = "%$q%";
    $st->execute([$like,$like,$like]);
    $patients = $st->fetchAll();
  }
}

// Patientendetail
$patient = null; $pid = null; $docs=[]; $vitals=[]; $bed=null;
if ($view === 'patient') {
  $pid = strtoupper(trim((string)($_GET['id'] ?? '')));
  $st = $pdo->prepare("SELECT * FROM patients WHERE patient_id=?");
  $st->execute([$pid]);
  $patient = $st->fetch();

  $docsSt = $pdo->prepare("SELECT id,doc_type,title,created_at FROM documents WHERE patient_id=? ORDER BY created_at DESC");
  $docsSt->execute([$pid]);
  $docs = $docsSt->fetchAll();

  $vitSt = $pdo->prepare("SELECT * FROM vitals WHERE patient_id=? ORDER BY measured_at DESC LIMIT 40");
  $vitSt->execute([$pid]);
  $vitals = $vitSt->fetchAll();

  $bedSt = $pdo->prepare("
    SELECT d.name AS dept, b.bed_code
    FROM bed_assignments ba
    JOIN beds b ON b.id=ba.bed_id
    JOIN departments d ON d.id=b.department_id
    WHERE ba.patient_id=? AND ba.released_at IS NULL
    ORDER BY ba.assigned_at DESC LIMIT 1
  ");
  $bedSt->execute([$pid]);
  $bed = $bedSt->fetch();
}
?>

<section class="dg" style="padding:24px 0;">
  <div class="dg__headline"><span>MITARBEITERPORTAL</span></div>

  <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;justify-content:space-between;margin-bottom:12px;">
    <div>
      <b><?php echo h($_SESSION['employee_name'] ?? ''); ?></b>
      · Rolle: <b><?php echo h($role); ?></b>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a class="dg__chip <?php echo $view==='patients'?'is-active':''; ?>" href="mitarbeiterportal.php?view=patients">Patienten</a>
      <a class="dg__chip" href="bettenuebersicht.php">BKN</a>
      <a class="dg__chip" href="patient_erstellen.php">+</a>
      <?php if ($role === 'systemadministrator'): ?>
        <a class="dg__chip <?php echo $view==='settings'?'is-active':''; ?>" href="mitarbeiterportal.php?view=settings">System</a>
        <a class="dg__chip" href="mitarbeiter_anlegen.php">Mitarbeiter +</a>
        <a class="dg__chip" href="rdprotokolle.php">Protokolle</a>
        <?php endif; ?>
      <a class="dg__chip" href="logout.php">Logout</a>
    </div>
  </div>

  <?php if ($view === 'patients'): ?>
    <div class="dg-card">
      <div class="dg-card__name">Patientensuche</div>
      <form style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap;" method="get">
        <input type="hidden" name="view" value="patients">
        <input class="dg__input" name="q" value="<?php echo h($q); ?>" placeholder="BST_11111 oder Name">
        <button class="dg__chip is-active" type="submit">Suchen</button>
      </form>

      <div style="margin-top:12px;display:grid;gap:10px;">
        <?php foreach ($patients as $p): ?>
          <a class="dg-card" style="display:block;"
             href="mitarbeiterportal.php?view=patient&id=<?php echo h($p['patient_id']); ?>">
            <div class="dg-card__name"><?php echo h($p['last_name'].', '.$p['first_name']); ?></div>
            <div class="dg-card__group"><?php echo h($p['patient_id']); ?></div>
          </a>
        <?php endforeach; ?>
        <?php if ($q !== '' && !$patients): ?><div style="opacity:.7;">Keine Treffer.</div><?php endif; ?>
      </div>
    </div>

  <?php elseif ($view === 'patient' && $patient): ?>
    <div class="dg__grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));">
      <div class="dg-card">
        <div class="dg-card__name">Patient</div>
        <div style="margin-top:10px;">
          <b><?php echo h($patient['first_name'].' '.$patient['last_name']); ?></b><br>
          <?php echo h($patient['patient_id']); ?><br>
          <div style="margin-top:10px;">
            <?php if ($bed): ?>
              <span style="display:inline-block;background:var(--blue);color:#fff;padding:8px 10px;border-radius:10px;font-weight:900;">
                <?php echo h($bed['dept']); ?> · <?php echo h($bed['bed_code']); ?>
              </span>
            <?php else: ?>
              <span style="opacity:.7;">Kein Bett</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="dg-card">
        <div class="dg-card__name">Bett zuweisen</div>
        <?php if (in_array($role, ['arzt','pflegekraft','systemadministrator'], true)): ?>
          <form method="post" style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="action" value="bed_assign">
            <input type="hidden" name="patient_id" value="<?php echo h($pid); ?>">
            <select class="dg__input" name="bed_id" required>
              <option value="">Bett wählen…</option>
              <?php foreach ($beds as $b): ?>
                <option value="<?php echo (int)$b['id']; ?>"><?php echo h($b['dept'].' · '.$b['bed_code']); ?></option>
              <?php endforeach; ?>
            </select>
            <button class="dg__chip is-active" type="submit">Zuweisen</button>
          </form>
        <?php else: ?>
          <div style="margin-top:10px;opacity:.7;">Nur Arzt/Pflege/Systemadmin.</div>
        <?php endif; ?>
      </div>

      <div class="dg-card" style="grid-column:1/-1;">
        <div class="dg-card__name">Dokumente</div>
        <div style="margin-top:10px;display:grid;gap:10px;">
          <?php foreach ($docs as $d): ?>
            <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;border:1px solid #edf1f8;border-radius:10px;padding:10px;">
              <div>
                <div style="font-weight:900;color:var(--blue);text-transform:uppercase;"><?php echo h($d['title']); ?></div>
                <div style="font-size:12px;opacity:.8;"><?php echo h($d['doc_type']); ?> · <?php echo h($d['created_at']); ?></div>
              </div>
              <a class="dg__chip is-active" href="download.php?id=<?php echo (int)$d['id']; ?>">Öffnen</a>
            </div>
          <?php endforeach; ?>
          <?php if (!$docs): ?><div style="opacity:.7;">Keine Dokumente.</div><?php endif; ?>
        </div>

        <?php if (in_array($role, ['arzt','systemadministrator'], true)): ?>
          <div style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;">
            <a class="dg__chip is-active" href="arztbrief_erstellen.php?patient=<?php echo h($pid); ?>">Arztbrief erstellen (PDF)</a>
            <a class="dg__chip" href="medplan.php?patient=<?php echo h($pid); ?>">Medikamentenplan bearbeiten</a>
          </div>
        <?php endif; ?>
      </div>

      <div class="dg-card" style="grid-column:1/-1;">
        <div class="dg-card__name">Vitalzeichen</div>

        <?php if (in_array($role, ['arzt','pflegekraft','systemadministrator'], true)): ?>
          <form method="post" style="margin-top:10px;display:grid;gap:10px;grid-template-columns:repeat(6,minmax(120px,1fr));">
            <input type="hidden" name="action" value="vitals_add">
            <input type="hidden" name="patient_id" value="<?php echo h($pid); ?>">

            <input class="dg__input" type="datetime-local" name="measured_at" required>
            <input class="dg__input" name="spo2" placeholder="SpO2 %">
            <input class="dg__input" name="rr_sys" placeholder="RR sys">
            <input class="dg__input" name="rr_dia" placeholder="RR dia">
            <input class="dg__input" name="pulse" placeholder="Puls">
            <input class="dg__input" name="temp_c" placeholder="Temp °C">
            <input class="dg__input" name="bz" placeholder="BZ">
            <input class="dg__input" style="grid-column:span 5;" name="notes" placeholder="Notiz / Behandlung (Pflege) …">
            <button class="dg__chip is-active" style="grid-column:span 1;" type="submit">Speichern</button>
          </form>
        <?php endif; ?>

        <div style="margin-top:12px;overflow:auto;">
          <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
              <tr style="text-align:left;border-bottom:2px solid #e6eaf2;">
                <th style="padding:8px;">Zeit</th><th>SpO2</th><th>RR</th><th>Puls</th><th>Temp</th><th>BZ</th><th>Notiz</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($vitals as $v): ?>
                <tr style="border-bottom:1px solid #edf1f8;">
                  <td style="padding:8px;"><?php echo h($v['measured_at']); ?></td>
                  <td><?php echo h((string)$v['spo2']); ?></td>
                  <td><?php echo h((string)$v['rr_sys'].'/'.(string)$v['rr_dia']); ?></td>
                  <td><?php echo h((string)$v['pulse']); ?></td>
                  <td><?php echo h((string)$v['temp_c']); ?></td>
                  <td><?php echo h((string)$v['bz']); ?></td>
                  <td><?php echo h((string)$v['notes']); ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$vitals): ?><tr><td style="padding:8px;opacity:.7;" colspan="7">Keine Einträge.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <?php if (in_array($role, ['arzt','systemadministrator'], true)): ?>
      <div class="dg-card" style="grid-column:1/-1;">
        <div class="dg-card__name">Stammdaten bearbeiten</div>
        <form method="post" style="margin-top:10px;display:grid;gap:10px;grid-template-columns:repeat(2,minmax(220px,1fr));">
          <input type="hidden" name="action" value="patient_update">
          <input type="hidden" name="patient_id" value="<?php echo h($pid); ?>">

          <input class="dg__input" name="first_name" value="<?php echo h($patient['first_name']); ?>" placeholder="Vorname">
          <input class="dg__input" name="last_name" value="<?php echo h($patient['last_name']); ?>" placeholder="Nachname">
          <input class="dg__input" type="date" name="dob" value="<?php echo h((string)$patient['dob']); ?>">
          <input class="dg__input" name="phone" value="<?php echo h((string)$patient['phone']); ?>" placeholder="Telefon">
          <input class="dg__input" name="street" value="<?php echo h((string)$patient['street']); ?>" placeholder="Straße + Hausnr.">
          <input class="dg__input" name="city" value="<?php echo h((string)$patient['city']); ?>" placeholder="Ort">
          <input class="dg__input" style="grid-column:1/-1;" name="email" value="<?php echo h((string)$patient['email']); ?>" placeholder="E-Mail">
          <button class="dg__chip is-active" type="submit">Speichern</button>
        </form>
      </div>
      <?php endif; ?>

    </div>

  <?php elseif ($view === 'settings' && $role === 'systemadministrator'): ?>
    <div class="dg__grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));">
      <div class="dg-card">
        <div class="dg-card__name">Fachabteilung erstellen</div>
        <form method="post" style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap;">
          <input type="hidden" name="action" value="dept_add">
          <input class="dg__input" name="dept_name" placeholder="z.B. Innere" required>
          <button class="dg__chip is-active" type="submit">Anlegen</button>
        </form>
        <div style="margin-top:12px;opacity:.85;">
          <?php foreach ($depts as $d): ?>• <?php echo h($d['name']); ?><br><?php endforeach; ?>
        </div>
      </div>

      <div class="dg-card">
        <div class="dg-card__name">Bett hinzufügen</div>
        <form method="post" style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap;">
          <input type="hidden" name="action" value="bed_add">
          <select class="dg__input" name="dept_id" required>
            <option value="">Abteilung…</option>
            <?php foreach ($depts as $d): ?>
              <option value="<?php echo (int)$d['id']; ?>"><?php echo h($d['name']); ?></option>
            <?php endforeach; ?>
          </select>
          <input class="dg__input" name="bed_code" placeholder="z.B. INN-01" required>
          <button class="dg__chip is-active" type="submit">Hinzufügen</button>
        </form>
      </div>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . "/footer.php"; ?>

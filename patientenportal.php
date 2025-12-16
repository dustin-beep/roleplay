<?php
declare(strict_types=1);
require __DIR__ . "/inc/db.php";
require __DIR__ . "/inc/auth.php";
require __DIR__ . "/inc/helpers.php";

$pageTitle = "BWK - Blaustetten | Patientenportal";
$err = null;

$age = compute_age($patient['dob'] ?? null);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'patient_login') {
  $pid = strtoupper(trim((string)($_POST['patient_id'] ?? '')));
  $pin = trim((string)($_POST['pin'] ?? ''));
  if (!login_patient($pdo, $pid, $pin)) $err = "Login fehlgeschlagen.";
}

include __DIR__ . "/header.php";

if (empty($_SESSION['patient_id'])):
?>
<section class="dg" style="padding:24px 0;">
  <div class="dg__headline"><span>PATIENTENPORTAL</span></div>

  <div style="max-width:420px;margin:0 auto;border:1px solid #e6eaf2;border-radius:12px;padding:16px;">
    <?php if ($err): ?><p style="color:#b00020;font-weight:800;"><?php echo h($err); ?></p><?php endif; ?>

    <form method="post">
      <input type="hidden" name="action" value="patient_login">
      <label class="dg__label">Patienten-ID</label>
      <input class="dg__input" name="patient_id" placeholder="BST_12345" required>

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

$pid = require_patient();

// Stammdaten
$st = $pdo->prepare("SELECT * FROM patients WHERE patient_id=?");
$st->execute([$pid]);
$patient = $st->fetch();

// Dokumente
$docs = $pdo->prepare("SELECT id,doc_type,title,created_at FROM documents WHERE patient_id=? ORDER BY created_at DESC");
$docs->execute([$pid]);
$docs = $docs->fetchAll();

// Bett (aktuelle Zuweisung)
$bed = $pdo->prepare("
  SELECT d.name AS dept, b.bed_code
  FROM bed_assignments ba
  JOIN beds b ON b.id = ba.bed_id
  JOIN departments d ON d.id = b.department_id
  WHERE ba.patient_id=? AND ba.released_at IS NULL
  ORDER BY ba.assigned_at DESC LIMIT 1
");
$bed->execute([$pid]);
$bed = $bed->fetch();
?>

<section class="dg" style="padding:24px 0;">
  <div class="dg__headline"><span>PATIENTENPORTAL</span></div>

  <div style="display:flex;gap:14px;flex-wrap:wrap;align-items:center;justify-content:space-between;margin-bottom:10px;">
    <div><strong><?php echo h($patient['first_name'].' '.$patient['last_name']); ?></strong> · <?php echo h($pid); ?></div>
    <a class="dg__chip" href="logout.php">Logout</a>
  </div>

  <div class="dg__grid" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
    <div class="dg-card">
      <div class="dg-card__name">Stammdaten</div>
      <div style="margin-top:10px;font-size:13px;line-height:1.6;">
        <div><b>Geburtsdatum:</b> <?php echo h((string)$patient['dob']); ?> <?php echo $age!==null ? '('.h((string)$age).' Jahre)' : ''; ?></div>
        <div><b>Notfallkontakt:</b> <?php echo h((string)$patient['emergency_contact']); ?></div>
        <div><b>Blutgruppe:</b> <?php echo h((string)$patient['blood_group']); ?></div>
        <div><b>Versicherung:</b> <?php echo h((string)$patient['insurance']); ?></div>
        <div><b>Geschlecht:</b> <?php echo h((string)$patient['gender']); ?></div>
        <div><b>Adresse:</b> <?php echo h((string)$patient['street']); ?>, <?php echo h((string)$patient['city']); ?></div>
        <div><b>Telefon:</b> <?php echo h((string)$patient['phone']); ?></div>
        <div><b>E-Mail:</b> <?php echo h((string)$patient['email']); ?></div>
        <div style="margin-top:8px;opacity:.8;">
          Änderungen nur durch <b>Arzt</b> oder <b>Systemadministrator</b>.
        </div>
      </div>
    </div>

    <div class="dg-card">
      <div class="dg-card__name">Aktuelles Bett</div>
      <div style="margin-top:10px;">
        <?php if ($bed): ?>
          <div style="display:inline-block;background:var(--blue);color:#fff;padding:10px 12px;border-radius:10px;font-weight:900;">
            <?php echo h($bed['dept']); ?> · <?php echo h($bed['bed_code']); ?>
          </div>
        <?php else: ?>
          <div style="opacity:.7;">Keine Bettzuweisung vorhanden.</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="dg-card" style="grid-column:1/-1;">
      <div class="dg-card__name">Dokumente</div>
      <div style="margin-top:10px;display:grid;gap:10px;">
        <?php if (!$docs): ?>
          <div style="opacity:.7;">Noch keine Dokumente vorhanden.</div>
        <?php else: foreach ($docs as $d): ?>
          <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;border:1px solid #edf1f8;border-radius:10px;padding:10px;">
            <div>
              <div style="font-weight:900;color:var(--blue);text-transform:uppercase;"><?php echo h($d['title']); ?></div>
              <div style="font-size:12px;opacity:.8;"><?php echo h($d['doc_type']); ?> · <?php echo h($d['created_at']); ?></div>
            </div>
            <a class="dg__chip is-active" href="download.php?id=<?php echo (int)$d['id']; ?>">Öffnen</a>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . "/footer.php"; ?>

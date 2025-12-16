<?php
declare(strict_types=1);
require __DIR__ . "/inc/db.php";
require __DIR__ . "/inc/auth.php";
require __DIR__ . "/inc/helpers.php";

require_employee(['systemadministrator','arzt','pflegekraft','auszubildender']);

$role = employee_role();
$canEdit = in_array($role, ['systemadministrator','arzt'], true);

$pid = strtoupper(trim((string)($_GET['patient'] ?? '')));
if ($pid === '') { http_response_code(400); exit("Patient fehlt."); }

$pst = $pdo->prepare("SELECT * FROM patients WHERE patient_id=?");
$pst->execute([$pid]);
$patient = $pst->fetch();
if (!$patient) { http_response_code(404); exit("Patient nicht gefunden."); }

// Plan holen/erstellen
$planSt = $pdo->prepare("SELECT * FROM medication_plans WHERE patient_id=? ORDER BY updated_at DESC LIMIT 1");
$planSt->execute([$pid]);
$plan = $planSt->fetch();

if (!$plan && $canEdit) {
  $pdo->prepare("INSERT INTO medication_plans (patient_id,created_by,is_active) VALUES (?,?,1)")
      ->execute([$pid, (int)$_SESSION['employee_id']]);
  $planSt->execute([$pid]);
  $plan = $planSt->fetch();
}

$planId = $plan ? (int)$plan['id'] : 0;

// add item / toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!$canEdit) { http_response_code(403); exit("Keine Berechtigung."); }

  if (($_POST['action'] ?? '') === 'add_item') {
    $pdo->prepare("INSERT INTO medication_items (plan_id,medication,dose,schedule,is_active) VALUES (?,?,?,?,?)")
        ->execute([
          $planId,
          trim((string)$_POST['medication']),
          trim((string)$_POST['dose']),
          $_POST['schedule'],
          isset($_POST['is_active']) ? 1 : 0
        ]);
    $pdo->prepare("UPDATE medication_plans SET updated_at=NOW() WHERE id=?")->execute([$planId]);
  }

  if (($_POST['action'] ?? '') === 'toggle_item') {
    $id = (int)$_POST['item_id'];
    $pdo->prepare("UPDATE medication_items SET is_active=1-is_active WHERE id=? AND plan_id=?")
        ->execute([$id, $planId]);
    $pdo->prepare("UPDATE medication_plans SET updated_at=NOW() WHERE id=?")->execute([$planId]);
  }
}

$items = [];
if ($planId) {
  $it = $pdo->prepare("SELECT * FROM medication_items WHERE plan_id=? ORDER BY id DESC");
  $it->execute([$planId]);
  $items = $it->fetchAll();
}

$pageTitle = "BWK - Blaustetten | Medikamentenplan";

include __DIR__ . "/header.php";
?>

<section class="dg" style="padding:24px 0;">
  <div class="dg__headline"><span>MEDIKAMENTENPLAN</span></div>

  <div class="dg-card">
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;justify-content:space-between;">
      <div>
        <b><?php echo h($patient['first_name'].' '.$patient['last_name']); ?></b>
        · <?php echo h($pid); ?>
      </div>

      <?php if ($planId): ?>
        <a class="dg__chip is-active" href="medplan_export.php?patient=<?php echo h($pid); ?>">Export PDF</a>
      <?php endif; ?>
    </div>

    <?php if ($canEdit && $planId): ?>
      <form method="post" style="margin-top:14px;display:grid;gap:10px;grid-template-columns:repeat(4,minmax(200px,1fr));align-items:end;">
        <input type="hidden" name="action" value="add_item">

        <div>
          <div class="dg__label">Medikament</div>
          <input class="dg__input" name="medication" required>
        </div>
        <div>
          <div class="dg__label">Dosis</div>
          <input class="dg__input" name="dose" required>
        </div>
        <div>
          <div class="dg__label">Einnahmezeit</div>
          <select class="dg__input" name="schedule" required>
            <option value="1-3_taeglich">1–3× täglich</option>
            <option value="1-5_woechentlich">1–5× wöchentlich</option>
            <option value="bedarfsweise">Bedarfsweise</option>
          </select>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
          <label style="font-weight:800;color:var(--blue);text-transform:uppercase;font-size:12px;">
            <input type="checkbox" name="is_active" checked> Aktiv
          </label>
          <button class="dg__chip is-active" type="submit">Hinzufügen</button>
        </div>
      </form>
    <?php endif; ?>

    <div style="margin-top:14px;overflow:auto;">
      <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
          <tr style="text-align:left;border-bottom:2px solid #e6eaf2;">
            <th style="padding:8px;">Medikament</th>
            <th>Dosis</th>
            <th>Einnahmezeit</th>
            <th>Aktiv</th>
            <?php if ($canEdit): ?><th></th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <tr style="border-bottom:1px solid #edf1f8;">
              <td style="padding:8px;"><b><?php echo h($it['medication']); ?></b></td>
              <td><?php echo h($it['dose']); ?></td>
              <td><?php echo h($it['schedule']); ?></td>
              <td>
                <?php if ((int)$it['is_active'] === 1): ?>
                  <span style="background:#0a7a2f;color:#fff;padding:3px 8px;border-radius:999px;font-weight:900;">Ja</span>
                <?php else: ?>
                  <span style="background:#b00020;color:#fff;padding:3px 8px;border-radius:999px;font-weight:900;">Nein</span>
                <?php endif; ?>
              </td>

              <?php if ($canEdit): ?>
                <td style="text-align:right;padding:8px;">
                  <form method="post" style="margin:0;">
                    <input type="hidden" name="action" value="toggle_item">
                    <input type="hidden" name="item_id" value="<?php echo (int)$it['id']; ?>">
                    <button class="dg__chip" type="submit">Toggle</button>
                  </form>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
          <?php if (!$items): ?>
            <tr><td style="padding:8px;opacity:.7;" colspan="<?php echo $canEdit?5:4; ?>">Keine Einträge.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php include __DIR__ . "/footer.php"; ?>

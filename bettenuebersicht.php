<?php
declare(strict_types=1);
require __DIR__ . "/inc/db.php";
require __DIR__ . "/inc/auth.php";
require __DIR__ . "/inc/helpers.php";

require_employee(['systemadministrator','arzt','pflegekraft','auszubildender']);

$role = employee_role();
$canManage = in_array($role, ['systemadministrator','arzt','pflegekraft'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'release_bed') {
  if (!$canManage) { http_response_code(403); exit("Keine Berechtigung."); }
  $aid = (int)($_POST['assignment_id'] ?? 0);
  if ($aid > 0) {
    $pdo->prepare("UPDATE bed_assignments SET released_at=NOW() WHERE id=? AND released_at IS NULL")->execute([$aid]);
  }
}

$pageTitle = "BWK - Blaustetten | Bettenübersicht";

$rows = $pdo->query("
  SELECT
    d.id AS dept_id, d.name AS dept_name,
    b.id AS bed_id, b.bed_code,
    a.id AS assignment_id, a.patient_id,
    p.first_name, p.last_name
  FROM departments d
  JOIN beds b ON b.department_id=d.id AND b.is_active=1
  LEFT JOIN bed_assignments a ON a.bed_id=b.id AND a.released_at IS NULL
  LEFT JOIN patients p ON p.patient_id=a.patient_id
  ORDER BY d.name, b.bed_code
")->fetchAll();

$byDept = [];
foreach ($rows as $r) { $byDept[$r['dept_name']][] = $r; }

include __DIR__ . "/header.php";
?>

<section class="dg" style="padding:24px 0;">
  <div class="dg__headline"><span>BETTENÜBERSICHT</span></div>

  <div class="dg__grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));">
    <?php foreach ($byDept as $deptName => $beds): ?>
      <div class="dg-card">
        <div class="dg-card__name"><?php echo h($deptName); ?></div>

        <div style="margin-top:12px;display:grid;gap:10px;">
          <?php foreach ($beds as $b): $occupied = !empty($b['patient_id']); ?>
            <div style="
              border:1px solid #edf1f8;border-radius:12px;padding:10px;
              display:flex;justify-content:space-between;gap:10px;align-items:center;
              background: <?php echo $occupied ? '#ffe9e9' : '#e9fff0'; ?>;
            ">
              <div>
                <div style="font-weight:1000;color:var(--blue);"><?php echo h($b['bed_code']); ?></div>
                <?php if ($occupied): ?>
                  <div style="margin-top:6px;">
                    <span style="display:inline-block;background:#b00020;color:#fff;padding:4px 8px;border-radius:999px;font-weight:900;font-size:12px;">
                      BELEGT
                    </span>
                    <div style="margin-top:6px;font-size:13px;">
                      <b><?php echo h($b['last_name'].', '.$b['first_name']); ?></b><br>
                      <?php echo h($b['patient_id']); ?>
                    </div>
                  </div>
                <?php else: ?>
                  <div style="margin-top:6px;">
                    <span style="display:inline-block;background:#0a7a2f;color:#fff;padding:4px 8px;border-radius:999px;font-weight:900;font-size:12px;">
                      FREI
                    </span>
                  </div>
                <?php endif; ?>
              </div>

              <?php if ($occupied && $canManage): ?>
                <form method="post" style="margin:0;">
                  <input type="hidden" name="action" value="release_bed">
                  <input type="hidden" name="assignment_id" value="<?php echo (int)$b['assignment_id']; ?>">
                  <button class="dg__chip" type="submit">Entfernen</button>
                </form>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php include __DIR__ . "/footer.php"; ?>

<?php
declare(strict_types=1);

require __DIR__ . "/inc/db.php";
require __DIR__ . "/inc/auth.php";
require __DIR__ . "/inc/helpers.php";

require_employee(['systemadministrator','arzt','pflegekraft','auszubildender']);

$pageTitle = "BWK - Flensburg | RD-Protokolle";

$config = require __DIR__ . "/inc/config.php";
$rddir = rtrim($config['storage_dir'], '/\\') . DIRECTORY_SEPARATOR . "rddocs";

$files = [];
if (is_dir($rddir)) {
  foreach (glob($rddir . DIRECTORY_SEPARATOR . "*.pdf") as $p) {
    $files[] = [
      "name" => basename($p),
      "mtime" => filemtime($p) ?: 0,
      "size" => filesize($p) ?: 0,
    ];
  }
  usort($files, fn($a,$b) => $b["mtime"] <=> $a["mtime"]);
}

$active = (string)($_GET['file'] ?? '');
$active = str_replace(['..','/','\\'], '', $active);

include __DIR__ . "/header.php";
?>

<section class="rd" style="padding:24px 0;">
  <!--<div class="dg__headline"><span>RD-PROTOKOLLE</span></div>-->

  <div class="rd__layout">
    <!-- Liste links -->
    <aside class="rd__list dg-card">
      <div class="dg-card__name">Dokumente</div>

      <?php if (!is_dir($rddir)): ?>
        <div style="margin-top:10px;opacity:.8;">
          Ordner nicht gefunden: <b><?php echo h($rddir); ?></b><br>
          Bitte anlegen: <b>/storage/rddocs/</b> und Rechte setzen.
        </div>
      <?php elseif (!$files): ?>
        <div style="margin-top:10px;opacity:.7;">Keine RD-Protokolle vorhanden.</div>
      <?php else: ?>
        <div class="rd__items">
          <?php foreach ($files as $f): ?>
            <?php
              $isActive = ($active !== '' && strcasecmp($active, $f['name']) === 0);
              $url = "rdprotokolle.php?file=" . rawurlencode($f['name']);
              $date = $f['mtime'] ? date('d.m.Y H:i', $f['mtime']) : '';
              $kb = (int)round($f['size'] / 1024);
            ?>
            <a class="rd-item <?php echo $isActive ? 'is-active' : ''; ?>" href="<?php echo h($url); ?>">
              <div class="rd-item__title"><?php echo h($f['name']); ?></div>
              <div class="rd-item__meta"><?php echo h($date); ?> · <?php echo h((string)$kb); ?> KB</div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </aside>

    <!-- Viewer rechts -->
    <div class="rd__viewer dg-card">
      <div class="rd__viewerHead">
        <div class="dg-card__name">Vorschau</div>
        <?php if ($active): ?>
          <a class="dg__chip" href="rd_view.php?file=<?php echo rawurlencode($active); ?>" target="_blank" rel="noopener">
            In neuem Tab öffnen
          </a>
        <?php endif; ?>
      </div>

      <?php if (!$active): ?>
        <div style="margin-top:10px;opacity:.75;">
          Wähle links ein RD-Protokoll aus, um es hier eingebettet anzuzeigen.
        </div>
      <?php else: ?>
        <iframe
          class="rd__frame"
          src="rd_view.php?file=<?php echo rawurlencode($active); ?>"
          title="RD-Protokoll <?php echo h($active); ?>"
        ></iframe>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . "/footer.php"; ?>

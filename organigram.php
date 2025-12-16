<?php
$pageTitle = "BWK - Blaustetten | Organigramm";
include __DIR__ . "/header.php";

$arztDienst = [
  "Generalarzt",
  "Oberstarzt",
  "Oberfeldarzt",
  "Oberstabsarzt",
  "Stabsarzt",
  "Sanitätsoberleutnant",
  "Sanitätsleutnant",
];

$pflegeDienst = [
  "Sanitätsoberstabsfeldwebel",
  "Sanitätsstabsfeldwebel",
  "Sanitätshauptfeldwebel",
  "Sanitätsfeldwebel",
  "Sanitätsunteroffizier",
  "Sanitätsobergefreiter",
  "Sanitätssoldat",
];
?>

<section class="org-page">
  <div class="dg__headline"><span>ORGANIGRAMM</span></div>

  <div class="org-chart">
    <!-- Root -->
    <div class="org-node org-node--root">
      <div class="org-node__title">BWK – Blaustetten</div>
      <div class="org-node__sub">Sanitätsdienst</div>
    </div>

    <!-- Branches -->
    <div class="org-branches">
      <!-- Left branch -->
      <div class="org-branch">
        <div class="org-branch__title">Ärztlicher Dienst</div>

        <div class="org-stack">
          <?php foreach ($arztDienst as $rank): ?>
            <div class="org-node">
              <div class="org-node__title"><?php echo htmlspecialchars($rank, ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="org-node__sub">Dienstgrad</div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Right branch -->
      <div class="org-branch">
        <div class="org-branch__title">Sanitäts-/Pflegedienst</div>

        <div class="org-stack">
          <?php foreach ($pflegeDienst as $rank): ?>
            <div class="org-node">
              <div class="org-node__title"><?php echo htmlspecialchars($rank, ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="org-node__sub">Dienstgrad</div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="org-footnote">
      Hinweis: Darstellung als Rollen-/Dienstgrad-Struktur (hierarchisch, von oben nach unten).
    </div>
  </div>
</section>

<?php include __DIR__ . "/footer.php"; ?>

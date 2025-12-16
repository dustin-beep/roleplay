<?php
// Erwartet: $logoDataUri, $patient, $doctorName, $diagnose, $anamnese, $therapie
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<style>
  body{ font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color:#111; }
  .head{ display:flex; justify-content:space-between; align-items:flex-start; }
  .logo{ width:90px; }
  .title{ margin-top:18px; font-size:18px; font-weight:800; }
  .box{ margin-top:12px; }
  .label{ font-weight:800; margin-top:12px; }
  .footer{ position: fixed; bottom: 18px; left: 0; right: 0; font-size:10px; color:#333; }
  .sign{ margin-top:28px; }
</style>
</head>
<body>

<div class="head">
  <div>
    <div style="font-weight:800;">Bundeswehrkrankenhaus<br>Blaustetten</div>

    <div style="margin-top:10px;">
      Herr / Frau<br>
      <?= htmlspecialchars($patient['first_name'].' '.$patient['last_name']) ?><br>
      <?php if (!empty($patient['street'])): ?><?= htmlspecialchars((string)$patient['street']) ?><br><?php endif; ?>
      <?php if (!empty($patient['city'])): ?><?= htmlspecialchars((string)$patient['city']) ?><?php endif; ?>
    </div>

    <div class="title">Arztbrief</div>
  </div>

  <div>
    <?php if (!empty($logoDataUri)): ?>
      <img class="logo" src="<?= $logoDataUri ?>" alt="BWK Logo">
    <?php endif; ?>
  </div>
</div>

<div class="box">
  <div style="margin-top:12px;">
    Sehr geehrter Herr Kollege, sehr geehrte Frau Kollegin,<br>
    im Folgenden erhalten Sie Informationen über die durch uns festgestellten Diagnosen und Verläufe zu Ihrem Patienten.
  </div>

  <div class="label">Diagnose:</div>
  <div><?= nl2br(htmlspecialchars($diagnose)) ?></div>

  <div class="label">Aktuelle Anamnese:</div>
  <div><?= nl2br(htmlspecialchars($anamnese)) ?></div>

  <div class="label">Verlauf bzw. Aktuelle Therapie:</div>
  <div><?= nl2br(htmlspecialchars($therapie)) ?></div>

  <div class="sign">
    Mit freundlichen Grüßen<br>
    <b><?= htmlspecialchars($doctorName) ?></b>
  </div>
</div>

<div class="footer">
  Dieses Dokument wurde elektronisch erstellt und ist ohne Signatur gültig.
</div>

</body>
</html>

<?php
// Erwartet: $logoDataUri, $patient, $doctorName, $diagnose, $anamnese, $therapie
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />

  <style>
    /* =========================
       A4 / PRINT SETUP
       ========================= */
    @page { size: A4; margin: 0; }  /* wichtig: keine extra Margin hier */

html, body { margin: 0; padding: 0; }

body{
  padding: 15mm 15mm 12mm 25mm;  /* <- hier “nach rechts” (links 25mm) */
  font-family: Arial, sans-serif;
  font-size: 9.5pt;              /* etwas kleiner = weniger Seitenumbruch */
  line-height: 1.2;
  color: #000;
}

.page{
  width: 100%;                   /* NICHT 210mm */
  min-height: auto;              /* NICHT 297mm */
  box-sizing: border-box;
  padding: 0;
}

    /* Hintergrund bei PDF/Print erhalten */
    @media print {
      * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }

    /* =========================
       PAGE LAYOUT
       ========================= */
   


    .letterhead {
      display: table;
      width: 100%;
      table-layout: fixed;
      margin-bottom: 6mm;
    }
    .lh-left, .lh-right {
      display: table-cell;
      vertical-align: top;
    }
    .lh-left {
      padding-right: 8mm;
    }
    .lh-right {
      width: 35mm;
      text-align: center;
    }
    .logo {
      width: 30mm;
      height: 30mm;
      object-fit: contain;
      display: inline-block;
    }
    .clinic-name {
      font-weight: 700;
      font-size: 11pt;
    }
    .clinic-city {
      font-weight: 700;
      font-size: 13pt;
      margin-top: 2mm;
    }

    .recipient {
      margin: 0 0 6mm 0;
    }
    .recipient p {
      margin: 0;
    }

    .subject {
      font-weight: 700;
      margin: 0 0 3mm 0;
      font-size: 11pt; 
      
    }

    /* =========================
       MAIN 2-COLUMN AREA
       ========================= */
    .twocol, .twocol tr, .twocol td { page-break-inside: avoid; }
    .col-left { padding-right: 6mm; }
    .col-right{
  width: 45mm;          /* war 55mm -> schmäler */
  padding: 3mm;         /* war 4mm -> etwas kompakter */
  font-size: 8pt;       /* war 8.5pt */
  line-height: 1.15;
  background-color: #efefef;
}

    .block-title {
      font-weight: 700;
      margin: 3mm 0 1mm 0;
    }
    .text {
      margin: 0;
      white-space: pre-wrap;      /* Zeilenumbrüche respektieren */
      overflow-wrap: anywhere;    /* keine Überläufe */
    }

    .greeting {
      margin: 0 0 3mm 0;
      font-weight: 700;
    }

    footer {
      margin-top: 6mm;
    }
    .sig {
      margin-top: 6mm;
      font-weight: 700;
      font-size: 11pt;
    }
    .legal {
      margin-top: 10mm;
      font-size: 7.5pt;
    }

    /* Optional: etwas kompakter, falls du oft knapp > 1 Seite bist */
    .compact html, .compact body { font-size: 9.5pt; }
  </style>
</head>

<body>
  <div class="page">
    <!-- BRIEFKOPF -->
    <header class="letterhead">
      <div class="lh-left">
        <div class="clinic-name">Bundeswehrkrankenhaus</div>
        <div class="clinic-city">Blaustetten</div>
      </div>

      <div class="lh-right">
        <?php if (!empty($logoDataUri)): ?>
          <img class="logo" src="<?= $logoDataUri ?>" alt="BWK Logo">
        <?php endif; ?>
      </div>
    </header>

    <!-- EMPFÄNGERBLOCK -->
    <section class="recipient">
      <p>Herr / Frau</p>
      <p><?= htmlspecialchars($patient['first_name'].' '.$patient['last_name']) ?></p>
      <?php if (!empty($patient['street'])): ?>
        <p><?= htmlspecialchars((string)$patient['street']) ?></p>
      <?php endif; ?>
      <?php if (!empty($patient['city'])): ?>
        <p><?= htmlspecialchars((string)$patient['city']) ?></p>
      <?php endif; ?>
      <p style="margin-top:2mm;">Blaustetten</p>
    </section>

    <!-- BETREFF -->
    <div class="subject">Arztbrief</div>

    <!-- HAUPTINHALT -->
    <table class="twocol" role="presentation">
      <tr>
        <td class="col-left">
          <p class="greeting">Sehr geehrter Herr Kollege, sehr geehrte Frau Kollegin,</p>
          <p style="margin:0 0 3mm 0;">
            Im Folgenden erhalten Sie Informationen über die durch uns festgestellten Diagnosen und Verläufe zu Ihrem Patienten.
          </p>

          <div class="block-title">Diagnose:</div>
          <p class="text"><?= nl2br(htmlspecialchars($diagnose)) ?></p>

          <div class="block-title">Aktuelle Anamnese:</div>
          <p class="text"><?= nl2br(htmlspecialchars($anamnese)) ?></p>

          <div class="block-title">Verlauf bzw. aktuelle Therapie:</div>
          <p class="text"><?= nl2br(htmlspecialchars($therapie)) ?></p>
        </td>

        <td class="col-right">
          <div style="font-weight:700; margin-bottom:2mm;">Ärztliche Direktion</div>
          <div>Generalarzt Dr. med. Denrasen</div>

          <div style="font-weight:700; margin:4mm 0 1mm 0;">Chefärzte</div>
          <div>Oberstarzt Dr. med. Gottlieb</div>
          <div>Oberstabsarzt Dr. med. Talhof</div>

          <div style="font-weight:700; margin:4mm 0 1mm 0;">Fachärzte</div>
          <div>Stabsarzt Prof. Dr. med. Univ. Wolf</div>
        </td>
      </tr>
    </table>

    <!-- FOOTER -->
    <footer>
      <div>Mit freundlichen Grüßen</div>
      <div class="sig"><?= htmlspecialchars($doctorName) ?></div>

      <div class="legal">Dieses Dokument wurde elektronisch erstellt und ist ohne Signatur gültig.</div>
    </footer>
  </div>
</body>
</html>

<?php
$pageTitle = "BWK - Flensburg | Fachrichtungen";
include __DIR__ . "/header.php";

/**
 * Bereiche + Fachkliniken (Cluster)
 * - highlights: kurze Punkte, die die Karten visuell “greifbar” machen
 */
$bereiche = [
  [
    "key" => "akut",
    "name" => "Akut & OP",
    "desc" => "Schnelle Versorgung im Notfall und sichere Betreuung rund um operative Eingriffe.",
    "items" => [
      [
        "name" => "Fachklinik für Notfallmedizin",
        "tag"  => "Triage • Schockraum",
        "text" => "Erstversorgung bei akuten Erkrankungen und Verletzungen – von der Einschätzung bis zur Stabilisierung.",
        "highlights" => ["Akutaufnahme", "Stabilisierung", "Weiterleitung/Koordination"],
        "icon" => "bolt"
      ],
      [
        "name" => "Fachklinik für Anästhesiologie",
        "tag"  => "Narkose • Schmerz",
        "text" => "Narkoseverfahren, perioperative Überwachung und Schmerztherapie – auch im intensivmedizinischen Umfeld.",
        "highlights" => ["Narkose & Überwachung", "Schmerztherapie", "Intensivmedizin"],
        "icon" => "shield"
      ],
    ],
  ],
  [
    "key" => "chirurgie",
    "name" => "Chirurgische Fachkliniken",
    "desc" => "Operative Versorgung, Rekonstruktion und spezialisierte Eingriffe.",
    "items" => [
      [
        "name" => "Fachklinik für Unfallchirurgie",
        "tag"  => "Trauma • OP",
        "text" => "Versorgung von Verletzungen des Bewegungsapparats – von der Erstversorgung bis zur Nachbehandlung.",
        "highlights" => ["Frakturen & Weichteile", "Operative Therapie", "Nachsorge"],
        "icon" => "bone"
      ],
      [
        "name" => "Fachklinik für Gesichtschirurgie",
        "tag"  => "Rekonstruktion",
        "text" => "Chirurgische Versorgung im Gesichtsbereich – funktionell und rekonstruktiv.",
        "highlights" => ["Rekonstruktion", "Trauma im Gesicht", "Nachsorge"],
        "icon" => "mask"
      ],
      [
        "name" => "Fachklinik für HNO",
        "tag"  => "Hals • Nase • Ohren",
        "text" => "Diagnostik und Therapie von Erkrankungen im HNO-Bereich – ambulant und stationär.",
        "highlights" => ["HNO-Diagnostik", "Eingriffe", "Nachsorge"],
        "icon" => "ear"
      ],
      [
        "name" => "Fachklinik für Augenheilkunde",
        "tag"  => "Sehfunktion",
        "text" => "Untersuchung und Behandlung von Augenerkrankungen und Sehbeeinträchtigungen.",
        "highlights" => ["Sehdiagnostik", "Therapieplanung", "Kontrollen"],
        "icon" => "eye"
      ],
      [
        "name" => "Fachklinik für Urologie",
        "tag"  => "Harnwege",
        "text" => "Behandlung von Erkrankungen der Harnwege – diagnostisch, konservativ und operativ.",
        "highlights" => ["Diagnostik", "Therapie", "Verlaufskontrolle"],
        "icon" => "drop"
      ],
    ],
  ],
  [
    "key" => "diagnostik",
    "name" => "Diagnostik & Neuro",
    "desc" => "Präzise Abklärung als Grundlage für Behandlung und Verlauf.",
    "items" => [
      [
        "name" => "Fachklinik für Radiologie",
        "tag"  => "CT • MRT • Röntgen",
        "text" => "Bildgebende Diagnostik zur schnellen und präzisen Abklärung – Befundung und Zusammenarbeit mit allen Bereichen.",
        "highlights" => ["Röntgen/CT/MRT", "Ultraschall", "Befundung"],
        "icon" => "scan"
      ],
      [
        "name" => "Fachklinik für Neurologie",
        "tag"  => "Nerven • Gehirn",
        "text" => "Abklärung und Therapie neurologischer Krankheitsbilder – Diagnostik, Verlauf und Therapieplanung.",
        "highlights" => ["Neurologische Diagnostik", "Therapieplanung", "Verlaufskontrolle"],
        "icon" => "brain"
      ],
    ],
  ],
  [
    "key" => "innere",
    "name" => "Innere Medizin",
    "desc" => "Ganzheitliche internistische Diagnostik und medikamentöse Therapie.",
    "items" => [
      [
        "name" => "Fachklinik für Innere Medizin",
        "tag"  => "Herz • Lunge • Stoffwechsel",
        "text" => "Behandlung internistischer Erkrankungen – Diagnostik, Therapie und engmaschige Verlaufsüberwachung.",
        "highlights" => ["Diagnostik", "Medikation", "Verlauf"],
        "icon" => "heart"
      ],
    ],
  ],
  [
    "key" => "leistung",
    "name" => "Therapie & Leistungsfähigkeit",
    "desc" => "Funktionsdiagnostik, Belastung und Unterstützung im Reha-/Leistungsbereich.",
    "items" => [
      [
        "name" => "Fachklinik für Physiologie",
        "tag"  => "Funktion • Belastung",
        "text" => "Beurteilung körperlicher Funktionen und Leistungsfähigkeit – Unterstützung bei Tests und Beratung.",
        "highlights" => ["Belastungstests", "Regeneration", "Leistungsdiagnostik"],
        "icon" => "activity"
      ],
    ],
  ],
  [
    "key" => "psyche",
    "name" => "Psychosoziale Versorgung",
    "desc" => "Stabilisierung, Beratung und therapeutische Begleitung.",
    "items" => [
      [
        "name" => "Fachklinik für Psychologie",
        "tag"  => "Krise • Begleitung",
        "text" => "Psychologische Betreuung, Krisenintervention und Begleitung im Krankheitsverlauf.",
        "highlights" => ["Gesprächsangebote", "Krisenintervention", "Stabilisierung"],
        "icon" => "chat"
      ],
    ],
  ],
];

function icon_svg(string $name): string {
  // Kleine Inline-Icons (passen zur Optik; ohne externe Lib)
  return match($name) {
    "bolt" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13 2L3 14h7l-1 8 12-14h-7l-1-6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>',
    "shield" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l8 3v7c0 5-3.2 9.5-8 10-4.8-.5-8-5-8-10V5l8-3z" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
    "bone" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 8a2.5 2.5 0 1 1 4-2l8 8a2.5 2.5 0 1 1-2 4l-8-8a2.5 2.5 0 0 1-2-2z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
    "mask" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7c2-2 6-3 8-3s6 1 8 3v5c0 5-4 8-8 8s-8-3-8-8V7z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M8 12h.01M16 12h.01M9 15c2 1.5 4 1.5 6 0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
    "ear" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20c3 0 5-2 5-5 0-3-2-4-4-5-2-1-3-2-3-4a4 4 0 0 1 8 0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M9 13c0 2 1 3 3 3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
    "eye" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
    "drop" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2s7 8 7 13a7 7 0 0 1-14 0c0-5 7-13 7-13z" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
    "scan" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7V4h3M20 7V4h-3M4 17v3h3M20 17v3h-3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M7 12h10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
    "brain" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 22a4 4 0 0 1-4-4v-1a3 3 0 0 1 0-6V9a4 4 0 0 1 7-2 4 4 0 0 1 7 2v2a3 3 0 0 1 0 6v1a4 4 0 0 1-4 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
    "heart" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-8-5-8-11a5 5 0 0 1 9-3 5 5 0 0 1 9 3c0 6-10 11-10 11z" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
    "activity" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12h4l2-6 4 12 2-6h6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    "chat" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>',
    default => ''
  };
}
?>

<section class="fach2">
  <div class="dg__headline"><span>FACHRICHTUNGEN</span></div>

  <div class="fach2__hero">
    <div class="fach2__heroLeft">
      <div class="fach2__kicker">Überblick</div>
      <h2 class="fach2__title">Unsere Fachkliniken – strukturiert nach Bereichen</h2>
      <p class="fach2__text">
        Wähle einen Bereich oder nutze die Suche, um schnell die passende Fachklinik zu finden.
      </p>
    </div>

    <div class="fach2__heroRight">
      <div class="fach2__stat">
        <div class="fach2__statNum"><?php echo count($bereiche); ?></div>
        <div class="fach2__statLbl">Bereiche</div>
      </div>
      <div class="fach2__stat">
        <?php
          $countAll = 0; foreach ($bereiche as $b) $countAll += count($b['items']);
        ?>
        <div class="fach2__statNum"><?php echo (int)$countAll; ?></div>
        <div class="fach2__statLbl">Fachkliniken</div>
      </div>
    </div>
  </div>

  <div class="fach2__toolbar">
    <div class="fach2__search">
      <label class="dg__label" for="fachSearch">Suchen</label>
      <input id="fachSearch" class="dg__input" type="text" placeholder="z.B. Radiologie, Notfall …">
    </div>

    <div class="fach2__filters" aria-label="Filter">
      <button class="dg__chip is-active" type="button" data-filter="all">Alle</button>
      <?php foreach ($bereiche as $b): ?>
        <button class="dg__chip" type="button" data-filter="<?php echo htmlspecialchars($b['key'], ENT_QUOTES, 'UTF-8'); ?>">
          <?php echo htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8'); ?>
        </button>
      <?php endforeach; ?>
    </div>
  </div>

  <?php foreach ($bereiche as $bereich): ?>
    <section class="fach2__section" data-section="<?php echo htmlspecialchars($bereich['key'], ENT_QUOTES, 'UTF-8'); ?>">
      <div class="fach2__sectionHead">
        <div class="fach2__sectionTitle"><?php echo htmlspecialchars($bereich['name'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="fach2__sectionDesc"><?php echo htmlspecialchars($bereich['desc'], ENT_QUOTES, 'UTF-8'); ?></div>
      </div>

      <div class="fach2__grid">
        <?php foreach ($bereich['items'] as $f): ?>
          <article class="fach2-card"
            data-name="<?php echo htmlspecialchars(mb_strtolower($f['name']), ENT_QUOTES, 'UTF-8'); ?>"
            data-cat="<?php echo htmlspecialchars($bereich['key'], ENT_QUOTES, 'UTF-8'); ?>"
          >
            <div class="fach2-card__top">
              <div class="fach2-card__icon" aria-hidden="true"><?php echo icon_svg($f['icon']); ?></div>
              <div class="fach2-card__topText">
                <div class="fach2-card__title"><?php echo htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="fach2-card__tag"><?php echo htmlspecialchars($f['tag'], ENT_QUOTES, 'UTF-8'); ?></div>
              </div>
            </div>

            <div class="fach2-card__text"><?php echo htmlspecialchars($f['text'], ENT_QUOTES, 'UTF-8'); ?></div>

            <div class="fach2-card__bullets">
              <?php foreach ($f['highlights'] as $h): ?>
                <span class="fach2-bullet"><?php echo htmlspecialchars($h, ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endforeach; ?>
            </div>

            <details class="fach2-card__details">
              <summary>Mehr anzeigen</summary>
              <div class="fach2-card__more">
                Typische Inhalte/Leistungen können hier später erweitert werden (z.B. Verfahren, Sprechstunden, Stationen, Ansprechpartner).
              </div>
            </details>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
</section>

<script>
(() => {
  const search = document.getElementById('fachSearch');
  const chips  = [...document.querySelectorAll('.fach2__filters .dg__chip')];
  const cards  = [...document.querySelectorAll('.fach2-card')];
  const sections = [...document.querySelectorAll('.fach2__section')];

  let active = 'all';

  const apply = () => {
    const q = (search.value || '').trim().toLowerCase();

    // cards filtern
    cards.forEach(card => {
      const name = (card.dataset.name || '');
      const cat  = (card.dataset.cat || '');
      const okCat = (active === 'all') || (cat === active);
      const okQ   = !q || name.includes(q);
      card.style.display = (okCat && okQ) ? '' : 'none';
    });

    // sections ausblenden wenn leer
    sections.forEach(sec => {
      const key = sec.dataset.section;
      const anyVisible = [...sec.querySelectorAll('.fach2-card')]
        .some(c => c.style.display !== 'none');
      sec.style.display = anyVisible ? '' : 'none';
    });
  };

  chips.forEach(chip => {
    chip.addEventListener('click', () => {
      chips.forEach(c => c.classList.remove('is-active'));
      chip.classList.add('is-active');
      active = chip.dataset.filter || 'all';
      apply();
    });
  });

  search.addEventListener('input', apply);
})();
</script>

<?php include __DIR__ . "/footer.php"; ?>

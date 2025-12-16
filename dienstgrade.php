<?php
$pageTitle = "BWK - Blaustetten | Dienstgrade";
include __DIR__ . "/header.php";

$dienstgrade = [
  ["name" => "Schütze",        "group" => "Mannschaften", "img" => "assets/img/dienstgrade/schuetze.png",        "order" => 1],
  ["name" => "Leutnant",       "group" => "Leutnante & Hauptleute",     "img" => "assets/img/dienstgrade/leutnant.png",       "order" => 2],
  ["name" => "Oberleutnant",   "group" => "Leutnante & Hauptleute",     "img" => "assets/img/dienstgrade/oberleutnant.png",   "order" => 3],

  ["name" => "Stabsarzt",      "group" => "Leutnante & Hauptleute",       "img" => "assets/img/dienstgrade/stabsarzt.png",      "order" => 4],
  ["name" => "Oberfeldarzt",   "group" => "Stabsoffiziere",       "img" => "assets/img/dienstgrade/oberfeldarzt.png",   "order" => 6],
  ["name" => "Oberstarzt",     "group" => "Stabsoffiziere",       "img" => "assets/img/dienstgrade/oberstarzt.png",     "order" => 7],
  ["name" => "Oberstabsarzt",  "group" => "Stabsoffiziere",       "img" => "assets/img/dienstgrade/oberstabsarzt.png",  "order" => 5],
  ["name" => "Generalarzt",    "group" => "Generale",       "img" => "assets/img/dienstgrade/generalarzt.png",    "order" => 8],
];

usort($dienstgrade, fn($a,$b) => $a["order"] <=> $b["order"]);
?>

<section class="dg">
  <div class="dg__headline">
    <span>DIENSTGRADE</span>
  </div>

  <div class="dg__toolbar">
    <div class="dg__search">
      <label class="dg__label" for="rankSearch">Suchen</label>
      <input id="rankSearch" class="dg__input" type="text" placeholder="z.B. Oberleutnant …">
    </div>

    <div class="dg__filters" aria-label="Filter">
      <button class="dg__chip is-active" type="button" data-filter="ALL">Alle</button>
      <button class="dg__chip" type="button" data-filter="Mannschaften">Mannschaften</button>
      <button class="dg__chip" type="button" data-filter="Leutnante & Hauptleute">Leutnante & Hauptleute</button>
      <button class="dg__chip" type="button" data-filter="Stabsoffiziere">Stabsoffiziere</button>
      <button class="dg__chip" type="button" data-filter="Generale">Generale</button>
    </div>
  </div>

  <div class="dg__grid" id="rankGrid">
    <?php foreach ($dienstgrade as $d): ?>
      <button
        type="button"
        class="dg-card"
        data-name="<?php echo htmlspecialchars($d["name"], ENT_QUOTES, 'UTF-8'); ?>"
        data-group="<?php echo htmlspecialchars($d["group"], ENT_QUOTES, 'UTF-8'); ?>"
        data-img="<?php echo htmlspecialchars($d["img"], ENT_QUOTES, 'UTF-8'); ?>"
        aria-label="<?php echo htmlspecialchars($d["name"], ENT_QUOTES, 'UTF-8'); ?> öffnen"
      >
        <div class="dg-card__img">
          <img src="<?php echo htmlspecialchars($d["img"], ENT_QUOTES, 'UTF-8'); ?>"
               alt="<?php echo htmlspecialchars($d["name"], ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div class="dg-card__meta">
          <div class="dg-card__name"><?php echo htmlspecialchars($d["name"], ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="dg-card__group"><?php echo htmlspecialchars($d["group"], ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
      </button>
    <?php endforeach; ?>
  </div>
</section>

<!-- Modal -->
<div id="rankModal" class="dg-modal">
  <div class="dg-modal__backdrop" data-close="1"></div>
  <div class="dg-modal__panel" role="dialog" aria-modal="true" aria-labelledby="rankModalTitle">
    <button class="dg-modal__close" type="button" data-close="1" aria-label="Schließen">×</button>
    <div class="dg-modal__content">
      <img id="rankModalImg" class="dg-modal__img" src="" alt="">
      <div id="rankModalTitle" class="dg-modal__title"></div>
    </div>
  </div>
</div>


<script>
(() => {
  const search = document.getElementById('rankSearch');
  const chips = [...document.querySelectorAll('.dg__chip')];
  const cards = [...document.querySelectorAll('.dg-card')];

  let activeFilter = 'ALL';

  const apply = () => {
    const q = (search.value || '').trim().toLowerCase();
    cards.forEach(card => {
      const name = (card.dataset.name || '').toLowerCase();
      const group = card.dataset.group || '';
      const okFilter = (activeFilter === 'ALL') || (group === activeFilter);
      const okSearch = !q || name.includes(q);
      card.style.display = (okFilter && okSearch) ? '' : 'none';
    });
  };

  chips.forEach(chip => {
    chip.addEventListener('click', () => {
      chips.forEach(c => c.classList.remove('is-active'));
      chip.classList.add('is-active');
      activeFilter = chip.dataset.filter || 'ALL';
      apply();
    });
  });

  search.addEventListener('input', apply);

  // Modal (Lightbox)
  const modal = document.getElementById('rankModal');
  const modalImg = document.getElementById('rankModalImg');
  const modalTitle = document.getElementById('rankModalTitle');

  const openModal = (imgSrc, title) => {
  modalImg.src = imgSrc;
  modalImg.alt = title;
  modalTitle.textContent = title;
  modal.classList.add('is-open');
  document.body.classList.add('modal-open');
};

const closeModal = () => {
  modal.classList.remove('is-open');
  document.body.classList.remove('modal-open');
  modalImg.src = '';
};

  cards.forEach(card => {
    card.addEventListener('click', () => openModal(card.dataset.img, card.dataset.name));
  });

  modal.addEventListener('click', (e) => {
    if (e.target && e.target.dataset && e.target.dataset.close) closeModal();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.hidden) closeModal();
  });
})();
</script>

<?php include __DIR__ . "/footer.php"; ?>

<?php
// footer.php
?>
    </main>

      <p class="special">WICHTIG - DIESE WEBSITE GEHÖRT ZU EINEM ROLEPLAYPROJEKT - SIE HAT KEINE RELEVANZ IM ECHTEN LEBEN</p>


    <!-- Footer-Top: Hashtags -->
    <section class="hashtags">
      <div class="hashtags__col">
        <div class="hashtags__tag">#sicherung</div>
        <div class="hashtags__hint">Hilfe in der Not? Wir sind da!</div>
      </div>
      <div class="hashtags__col">
        <div class="hashtags__tag">#gesundheit</div>
        <div class="hashtags__hint">Wir geben unser bestes</div>
      </div>
      <div class="hashtags__col hashtags__col--right">
        <div class="hashtags__tag">#medizin</div>
        <div class="hashtags__hint">Qualität vor Quantität</div>
      </div>
      <div class="hashtags__col hashtags__col--right">
        <div class="hashtags__tag">#freiheit</div>
        <div class="hashtags__hint">Medizin für jedermann!</div>
      </div>

    </section>

    <!-- Footer area -->
    <footer class="footer">
      <div class="footer__inner">
        <div class="footer__service">
          <div class="footer__headline">SERVICE</div>
          <ul class="footer__list">
            <li><a href="#">Kontakt</a></li>
            <li><a href="./dienstgrade.php">Dienstgrade</a></li>
            <li><a href="#">Fachbereiche</a></li>
            <li><a href="#">Newsletter</a></li>
            <li><a href="#">Broschüren</a></li>
          </ul>
        </div>

        <div class="footer__slogan">
          <span>WIR FÜR EUCH</span>
        </div>
      </div>
    </footer>

  </div> <!-- /.page-shell -->
</body>

<script>
(() => {
  const btn = document.getElementById('menuToggle');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('overlay');

  if (!btn || !sidebar || !overlay) return;

  const openMenu = () => {
    document.body.classList.add('sidebar-open');
    overlay.hidden = false;
    btn.setAttribute('aria-expanded', 'true');
    sidebar.setAttribute('aria-hidden', 'false');
  };

  const closeMenu = () => {
    document.body.classList.remove('sidebar-open');
    btn.setAttribute('aria-expanded', 'false');
    sidebar.setAttribute('aria-hidden', 'true');

    // Overlay nach Fade ausblenden
    window.setTimeout(() => {
      if (!document.body.classList.contains('sidebar-open')) {
        overlay.hidden = true;
      }
    }, 220);
  };

  const toggleMenu = () => {
    document.body.classList.contains('sidebar-open') ? closeMenu() : openMenu();
  };

  btn.addEventListener('click', toggleMenu);
  overlay.addEventListener('click', closeMenu);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
      closeMenu();
    }
  });
})();
</script>

</html>

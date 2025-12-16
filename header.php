<?php
// header.php
if (!isset($pageTitle)) { $pageTitle = "BWK - Blaustetten"; }
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
  <div class="page-shell">

    <!-- Topbar -->
    <header class="topbar">
     
        <!-- Hamburger -->
        <button id="menuToggle" class="icon-btn menu-toggle" aria-label="Menü öffnen" aria-expanded="false" type="button">
  <svg class="hamburger-icon" width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
    <path d="M4 6h16M4 12h16M4 18h16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
  </svg>
</button>
     

      <!-- Overlay (klickbar zum Schließen) -->
<div id="overlay" class="overlay" hidden></div>

<!-- Sidebar -->
<aside id="sidebar" class="sidebar" aria-hidden="true">
  <div class="sidebar__head">
    <div class="sidebar__crest" aria-hidden="true">
      <!-- optional: dein Wappen -->
      
    </div>
    <div class="sidebar__title">
      <div class="sidebar__bwk">BWK</div>
      <div class="sidebar__city">Blaustetten</div>
    </div>
  </div>

  <nav class="sidebar__nav" aria-label="Hauptmenü">
    <a class="sidebar__link" href="./index.php">STARTSEITE</a>
    <a class="sidebar__link" href="#">GEFAHRENABWEHR</a>
    <a class="sidebar__link" href="#">INFORMATIONEN</a>
    <a class="sidebar__link" href="#">VERWALTUNG</a>
    <a class="sidebar__link" href="#">PRESSEABTEILUNG</a>
    <a class="sidebar__link" href="./dienstgrade.php">DIENSTGRADE BEIM BWK</a>
    <a class="sidebar__link" href="./karriere.php">KARRIERE BEIM BWK</a>
    <a class="sidebar__link" href="#">KONTAKT</a>
  </nav>
</aside>


      <div class="brand">
        <span class="brand__crest" aria-hidden="true">
          <!-- kleines Wappen-Placeholder -->
        </span>
        <span class="brand__text">BWK - Blaustetten</span>
      </div>
    </header>

    <!-- Hero -->
    <section class="hero">
      <div class="hero__left">
        <h1 class="hero-title">
          <span>OPTIMALE BEHANDLUNG, IHR</span>
          <span>BUNDESWEHRKRANKENHAUS</span>
        </h1>
      </div>

    <div class="hero__right" aria-label="Logo">
        <img class="hero__crest" src="assets/img/crest.png" alt="Bundeswehrkrankenhaus Wappen">
    </div>
    </section>

    <div class="rule rule--thick"></div>

    <!-- Primary nav -->
  <p class="special">WICHTIG - DIESE WEBSITE GEHÖRT ZU EINEM ROLEPLAYPROJEKT - SIE HAT KEINE RELEVANZ IM ECHTEN LEBEN</p>

    <div class="rule rule--thick"></div>
    
    <nav class="nav">
      <a href="./index.php" class="nav__link">STARTSEITE</a>
      <a href="./patientenportal.php" class="nav__link">PATIENTENPORTAL</a>
      <a href="./fachrichtung.php" class="nav__link">FACHRICHTUNGEN</a>
      <a href="./karriere.php" class="nav__link">KARRIERE</a>
      <a href="./mitarbeiterportal.php" class="nav__link">MITARBEITERPORTAL</a>
    </nav>

    

    <!-- Subnav buttons -->
    <div class="subnav">
      <a class="pill" href="./organigram.php">ORGANIGRAMM</a>
      <a class="pill" href="./leitung.php">KRANKENHAUSLEITUNG</a>
      <a class="pill" href="./dienstgrade.php">DIENSTGRADE</a>
      <a class="pill" href="./ausbildung.php">AUS- UND FORTBILDUNG</a>
    </div>

    <main class="main">

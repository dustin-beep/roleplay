<?php
declare(strict_types=1);

require __DIR__ . "/inc/db.php";
require __DIR__ . "/inc/auth.php";
require __DIR__ . "/inc/helpers.php";

require_employee(['systemadministrator']);

$pageTitle = "BWK - Flensburg | Mitarbeiter anlegen";

$error = null;
$created = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim((string)($_POST['username'] ?? ''));
  $display  = trim((string)($_POST['display_name'] ?? ''));
  $role     = trim((string)($_POST['role'] ?? ''));

  $allowed = ['systemadministrator','arzt','pflegekraft','auszubildender'];

  if ($username === '' || $display === '' || $role === '') {
    $error = "Bitte Benutzername, Anzeigename und Rolle ausfüllen.";
  } elseif (!preg_match('/^[a-zA-Z0-9._-]{3,60}$/', $username)) {
    $error = "Benutzername ungültig (3–60 Zeichen, nur a-z, 0-9, . _ -).";
  } elseif (!in_array($role, $allowed, true)) {
    $error = "Ungültige Rolle.";
  } else {
    // Username schon vergeben?
    $st = $pdo->prepare("SELECT 1 FROM employees WHERE username=?");
    $st->execute([$username]);
    if ($st->fetchColumn()) {
      $error = "Benutzername ist bereits vergeben.";
    } else {
      $pinPlain = generate_pin4();
      $pinHash  = password_hash($pinPlain, PASSWORD_DEFAULT);

      $ins = $pdo->prepare("INSERT INTO employees (username, display_name, role, pin_hash) VALUES (?,?,?,?)");
      $ins->execute([$username, $display, $role, $pinHash]);

      $created = [
        'username' => $username,
        'display'  => $display,
        'role'     => $role,
        'pin'      => $pinPlain,
      ];
    }
  }
}

include __DIR__ . "/header.php";
?>

<section class="dg" style="padding:24px 0;">
  <div class="dg__headline"><span>MITARBEITER ANLEGEN</span></div>

  <?php if ($error): ?>
    <div class="dg-card" style="border-color:#ffd6d6;margin-bottom:14px;">
      <b style="color:#b00020;"><?php echo h($error); ?></b>
    </div>
  <?php endif; ?>

  <?php if ($created): ?>
    <div class="dg-card" style="border-color:#cfead6;margin-bottom:14px;">
      <div class="dg-card__name">Mitarbeiter erstellt</div>
      <div style="margin-top:10px;line-height:1.7;">
        <div><b>Anzeigename:</b> <?php echo h($created['display']); ?></div>
        <div><b>Benutzername:</b> <span style="background:var(--blue);color:#fff;padding:4px 8px;border-radius:8px;font-weight:900;"><?php echo h($created['username']); ?></span></div>
        <div><b>Rolle:</b> <?php echo h($created['role']); ?></div>
        <div style="margin-top:8px;">
          <b>PIN (nur einmal sichtbar):</b>
          <span style="background:#111;color:#fff;padding:4px 8px;border-radius:8px;font-weight:900;"><?php echo h($created['pin']); ?></span>
        </div>
        <div style="opacity:.8;margin-top:8px;">Bitte PIN notieren/ausgeben. Sie wird nicht erneut angezeigt.</div>
      </div>
    </div>
  <?php endif; ?>

  <div class="dg__grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));">
    <div class="dg-card">
      <div class="dg-card__name">Neuen Mitarbeiter anlegen</div>

      <form method="post" style="margin-top:12px;display:grid;gap:10px;">
        <div>
          <div class="dg__label">Benutzername</div>
          <input class="dg__input" name="username" placeholder="z.B. dr.mueller" required>
          <div style="font-size:12px;opacity:.75;margin-top:6px;">
            Erlaubt: a-z, 0-9, Punkt, Unterstrich, Bindestrich (3–60 Zeichen)
          </div>
        </div>

        <div>
          <div class="dg__label">Anzeigename</div>
          <input class="dg__input" name="display_name" placeholder="z.B. Dr. Max Müller" required>
        </div>

        <div>
          <div class="dg__label">Rolle</div>
          <select class="dg__input" name="role" required>
            <option value="">Bitte wählen…</option>
            <option value="arzt">Arzt</option>
            <option value="pflegekraft">Pflegekraft</option>
            <option value="auszubildender">Auszubildender</option>
            <option value="systemadministrator">Systemadministrator</option>
          </select>
        </div>

        <button class="dg__chip is-active" type="submit">Mitarbeiter erstellen</button>
      </form>
    </div>

    <div class="dg-card">
      <div class="dg-card__name">Hinweise</div>
      <div style="margin-top:10px;font-size:13px;line-height:1.7;opacity:.9;">
        <ul style="margin:0;padding-left:18px;">
          <li>Nur <b>Systemadministratoren</b> dürfen Mitarbeiter anlegen.</li>
          <li>PIN ist <b>4-stellig</b> und wird sicher gehasht gespeichert.</li>
          <li>PIN wird nach Erstellung <b>nur einmal</b> angezeigt.</li>
          <li>Rollen steuern automatisch Berechtigungen im Mitarbeiterportal.</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . "/footer.php"; ?>

<?php
$page = "gestion-reservaciones";
require __DIR__ . "/app/page.php";
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reservaciones (gestión) | Astir</title>
  <link rel="stylesheet" href="css/styles.css">
</head>

<body>
  <div id="app-nav"></div>
  <script>window.ASTIR = <?= scriptJSON($client) ?>;</script>
  <?php if ($pageError): ?>
    <p class="empty" role="alert"><?= htmlEncode($pageError) ?></p><?php endif; ?>

  <details class="switcher">
    <summary>Reservaciones<svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="M7.41 8.59 12 13.17l4.59-4.58L18 10l-6 6-6-6z" />
      </svg></summary>
    <nav class="switcher__menu" aria-label="Secciones de gestión">
      <a href="habitaciones.php">Habitaciones</a>
      <a href="usuarios.php">Usuarios</a>
    </nav>
  </details>

  <main class="page">
    <form class="toolbar" id="booking-search" action="gestion-reservaciones.php" method="post">
      <input type="hidden" name="csrf" value="<?= htmlEncode($_SESSION['csrf']) ?>">
      <label class="search">
        <input type="search" name="query" maxlength="255" value="<?= htmlEncode($criteria['query'] ?? '') ?>"
          placeholder="Buscar" aria-label="Buscar" data-filter="#lista">
        <svg viewBox="0 0 24 24" aria-hidden="true">
          <path
            d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
        </svg>
      </label>
      <button class="btn-mini" type="submit">Buscar</button>
    </form>
    <?php require __DIR__ . "/app/booking_history.php"; ?>

    <section class="list" id="lista">
      <?php require __DIR__ . "/app/list_rows.php"; ?>
    </section>
  </main>

  <div id="app-footer"></div>

  <script src="js/nav.js"></script>
  <script src="js/ui.js"></script>
  <script src="js/filtrar-lista.js"></script>
</body>

</html>
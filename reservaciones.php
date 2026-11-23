<?php
$page = "reservaciones";
require __DIR__ . "/app/page.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reservaciones | Astir</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div id="app-nav"></div>
  <script>window.ASTIR = <?= scriptJson($client) ?>;</script>
  <?php if ($pageError): ?><p class="empty" role="alert"><?= h($pageError) ?></p><?php endif; ?>

  <main class="page">
    <form class="toolbar" id="booking-search" action="reservaciones.php" method="post">
      <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
      <label class="search">
        <input type="search" name="query" maxlength="255" value="<?= h($criteria['query'] ?? '') ?>" placeholder="Buscar" aria-label="Buscar" data-filter="#lista">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
      </label>
      <a class="toolbar__filter" href="#filtros" aria-label="Filtros"><svg viewBox="0 0 24 24"><path d="M3 17v2h6v-2H3zM3 5v2h10V5H3zm10 16v-2h8v-2h-8v-2h-2v6h2zM7 9v2H3v2h4v2h2V9H7zm14 4v-2H11v2h10zm-6-4h2V7h4V5h-4V3h-2v6z"/></svg></a>
      <button class="btn-mini" type="submit">Buscar</button>
    </form>
    <?php require __DIR__ . "/app/booking_history.php"; ?>

    <section class="list" id="lista">
      <?php require __DIR__ . "/app/list_rows.php"; ?>
    </section>
  </main>

  <div class="filters-wrap filters-wrap--css" id="filtros">
    <aside class="filters" aria-label="Filtros">
      <div class="filters__head">
        <h2>Filtros</h2>
        <a class="filters__close" href="#" aria-label="Cerrar filtros"><svg viewBox="0 0 24 24"><path d="M19 6.41 17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></a>
      </div>
      <section><h3>Categorías</h3><div class="fchips">
        <?php foreach (ROOM_CATEGORIES as $i => $category): ?>
          <button type="button" class="cat cat--<?= $i ?>" data-category="<?= h($category) ?>" aria-pressed="<?= in_array($category, $criteria['categories'] ?? [], true) ? 'true' : 'false' ?>"><?= h(ucfirst($category)) ?></button>
        <?php endforeach; ?>
      </div></section>
      <section>
        <h3>Precio</h3>
        <label class="money">Desde <span class="money__box"><span>$</span><input form="booking-search" name="min" value="<?= h($criteria['min'] ?? '') ?>" id="min-price" type="number" min="0" max="999999999.99" step="0.01" placeholder="0.00"></span></label>
        <label class="money">Hasta <span class="money__box"><span>$</span><input form="booking-search" name="max" value="<?= h($criteria['max'] ?? '') ?>" id="max-price" type="number" min="0" max="999999999.99" step="0.01" placeholder="0.00"></span></label>
      </section>
    </aside>
    <a class="filters__cover" href="#" aria-label="Cerrar filtros"></a>
  </div>

  <div id="app-footer"></div>

  <script src="js/nav.js"></script>
  <script src="js/ui.js"></script>
  <script src="js/filtrar-lista.js"></script>
</body>
</html>

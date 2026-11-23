<?php
$page = "habitaciones";
require __DIR__ . "/app/page.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Habitaciones | Astir</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body data-mode="habitaciones">
  <div id="app-nav"></div>
  <script>window.ASTIR = <?= scriptJson($client) ?>;</script>
  <?php if ($pageError): ?><p class="empty" role="alert"><?= h($pageError) ?></p><?php endif; ?>

  <details class="switcher">
    <summary>Habitaciones<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.41 8.59 12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg></summary>
    <nav class="switcher__menu" aria-label="Secciones de gestión">
      <a href="gestion-reservaciones.php">Reservaciones</a>
      <a href="usuarios.php">Usuarios</a>
    </nav>
  </details>

  <main class="page">
    <div class="toolbar" id="toolbar">
      <a class="toolbar__add" id="add-room" href="habitacion.php?nuevo=1" aria-label="Agregar habitación" hidden>
        <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
      </a>
      <label class="search">
        <input type="search" id="search" placeholder="Buscar" aria-label="Buscar habitaciones">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
      </label>
      <button class="toolbar__filter" id="open-filters" type="button" aria-label="Filtros">
        <svg viewBox="0 0 24 24"><path d="M3 17v2h6v-2H3zM3 5v2h10V5H3zm10 16v-2h8v-2h-8v-2h-2v6h2zM7 9v2H3v2h4v2h2V9H7zm14 4v-2H11v2h10zm-6-4h2V7h4V5h-4V3h-2v6z"/></svg>
      </button>
    </div>

    <section class="grid" id="grid" aria-live="polite"></section>
  </main>

  <!-- Panel de filtros -->
  <div class="filters-wrap" id="filters" hidden>
    <aside class="filters" aria-label="Filtros">
      <div class="filters__head">
        <h2>Filtros</h2>
        <button class="filters__close" id="close-filters" type="button" aria-label="Cerrar filtros">
          <svg viewBox="0 0 24 24"><path d="M19 6.41 17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
        </button>
      </div>
      <section><h3>Categorías</h3><div class="fchips" id="f-cats"></div></section>
      <section><h3>Comodidades</h3><div class="fchips" id="f-amen"></div></section>
      <section>
        <h3>Precio</h3>
        <label class="money">Desde <span class="money__box"><span>$</span><input type="number" id="f-min" min="0" max="999999999.99" step="0.01" placeholder="0.00"></span></label>
        <label class="money">Hasta <span class="money__box"><span>$</span><input type="number" id="f-max" min="0" max="999999999.99" step="0.01" placeholder="0.00"></span></label>
      </section>
      <div class="stepper-row">
        <span>Disponibilidad</span>
        <div class="stepper">
          <button type="button" id="av-minus" aria-label="Menos">−</button>
          <input type="number" id="f-av" max="2147483647" value="0" min="0" aria-label="Disponibilidad mínima">
          <button type="button" id="av-plus" aria-label="Más">+</button>
        </div>
      </div>
    </aside>
    <button class="filters__cover" id="filters-cover" type="button" aria-label="Cerrar filtros"></button>
  </div>

  <script src="js/nav.js"></script>
  <script src="js/rooms.js"></script>
  <script src="js/ui.js"></script>
  <script src="cart.js"></script>
  <script src="js/habitaciones.js"></script>
</body>
</html>

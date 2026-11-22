<?php
$page = "index";
require __DIR__ . "/app/page.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Astir | Para juntas y negocios</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div id="app-nav"></div>
  <script>window.ASTIR = <?= scriptJson($client) ?>;</script>
  <?php if ($pageError): ?><p class="empty" role="alert"><?= h($pageError) ?></p><?php endif; ?>

  <main class="home">
    <section class="hero">
      <h1 class="hero__banner">
        <span>Para </span><em>juntas</em><span> y </span><em>negocios</em><br>
        <span>Confía en </span><strong>nosotros</strong>
      </h1>
      <div class="hero__carousel">
        <div class="carousel" id="carousel">
          <div class="carousel__img ph-img" role="img" aria-label="Imagen del carrusel"></div>
          <div class="carousel__bar">
            <button class="carousel__btn" id="prev" type="button" aria-label="Anterior">
              <svg viewBox="0 0 24 24"><path d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
            </button>
            <span id="carousel-text">TEXT</span>
            <button class="carousel__btn" id="next" type="button" aria-label="Siguiente">
              <svg viewBox="0 0 24 24"><path d="M8.59 16.59 13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
            </button>
          </div>
        </div>
      </div>
    </section>

    <div class="home__text">
      <p>Reuniones, contratos, y un descanso</p>
      <p>Tenemos todo para tus asuntos</p>
    </div>

    <h2 class="home__section">Alojamiento</h2>
    <div class="rooms-row" id="rooms-row"></div>
  </main>

  <div id="app-footer"></div>

  <script src="js/nav.js"></script>
  <script src="js/rooms.js"></script>
  <script src="js/home.js"></script>
</body>
</html>

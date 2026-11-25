<?php
$page = "habitacion";
require __DIR__ . "/app/page.php";
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Habitación | Astir</title>
  <link rel="stylesheet" href="css/styles.css">
</head>

<body>
  <div id="app-nav"></div>
  <script>window.ASTIR = <?= scriptJSON($client) ?>;</script>
  <?php if ($pageError): ?>
    <p class="empty" role="alert"><?= htmlEncode($pageError) ?></p><?php endif; ?>

  <main class="home">
    <section class="detail" id="detail"></section>
    <section class="more" id="more">
      <h2>Ver Más Habitaciones</h2>
      <div class="rooms-row" id="rooms-row"></div>
    </section>
  </main>

  <div id="app-footer"></div>

  <script src="js/nav.js"></script>
  <script src="js/rooms.js"></script>
  <script src="js/ui.js"></script>
  <script src="cart.js"></script>
  <script src="js/habitacion.js"></script>
</body>

</html>
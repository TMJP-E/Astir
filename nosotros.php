<?php
$page = "nosotros";
require __DIR__ . "/app/page.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nosotros | Astir</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div id="app-nav"></div>
  <script>window.ASTIR = <?= scriptJson($client) ?>;</script>
  <?php if ($pageError): ?><p class="empty" role="alert"><?= h($pageError) ?></p><?php endif; ?>

  <main class="about">
    <h1 class="about__title">Astir</h1>
    <div class="about__block">
      <p>ASTIR es una entidad dedicada al desarrollo de herramientas digitales para la gestión de reservaciones hoteleras. Su actividad se enfoca en facilitar el registro, la organización y la consulta de información relacionada con las reservaciones, las habitaciones y las estancias de los huéspedes.</p>
      <img class="about__img" src="room_images/dummy1.jpg" alt="" loading="lazy">
    </div>
    <div class="about__block about__block--rev">
      <p>Este tipo de sistemas permite mantener la información organizada en un solo lugar, facilitando las tareas relacionadas con la administración de alojamientos. También ayuda a llevar un mejor control de las reservaciones y a reducir el uso de registros manuales.</p>
      <img class="about__img" src="room_images/dummy3.jpg" alt="" loading="lazy">
    </div>
    <div class="about__block">
      <p>ASTIR se enfoca en atender las necesidades básicas de organización que existen dentro de la gestión hotelera, tomando en cuenta las actividades que se realizan al recibir, registrar y administrar reservaciones.</p>
      <img class="about__img" src="room_images/dummy5.jpg" alt="" loading="lazy">
    </div>
    <div class="about__block about__block--rev">
      <p>De esta manera, buscamos que la gestión de reservaciones sea un proceso más ordenado y sencillo, mediante el uso de herramientas digitales que permitan manejar la información de forma clara y facilitar las actividades administrativas relacionadas con el alojamiento.</p>
      <img class="about__img" src="room_images/dummy7.jpg" alt="" loading="lazy">
    </div>
  </main>

  <div id="app-footer"></div>

  <script src="js/nav.js"></script>
</body>
</html>

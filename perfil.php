<?php
$page = "perfil";
require __DIR__ . "/app/page.php";
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Perfil | Astir</title>
  <link rel="stylesheet" href="css/styles.css">
</head>

<body>
  <div id="app-nav"></div>
  <script>window.ASTIR = <?= scriptJSON($client) ?>;</script>
  <?php if ($pageError): ?>
    <p class="empty" role="alert"><?= htmlEncode($pageError) ?></p><?php endif; ?>

  <main class="profile">
    <div class="profile__avatar"><img src="img/avatar-dark.svg" alt=""></div>
    <h1 class="profile__name"><?= htmlEncode($account["name"]) ?></h1>
    <p class="profile__mail"><?= htmlEncode($account["email"]) ?></p>
    <p class="profile__role role--<?= $client["role"] ?>">
      <?= $account["role"] === "admin" ? "Administrador" : "Usuario" ?></p>
    <form method="post"><input type="hidden" name="csrf" value="<?= htmlEncode($_SESSION['csrf']) ?>">
      <button class="btn btn--red" type="submit">Cerrar sesión</button>
    </form>
  </main>

  <div id="app-footer"></div>

  <script src="js/nav.js"></script>
</body>

</html>
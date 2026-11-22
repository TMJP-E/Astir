<?php
$page = "login";
require __DIR__ . "/app/page.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar sesión | Astir</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div id="app-nav"></div>
  <script>window.ASTIR = <?= scriptJson($client) ?>;</script>
  <?php if ($pageError): ?><p class="empty" role="alert"><?= h($pageError) ?></p><?php endif; ?>

  <main class="main">
    <h1 class="title">Iniciar Sesión</h1>

    <form class="form" id="login-form" method="post" novalidate>
      <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
      <div class="form__fields">
        <div class="field">
          <label for="email">Correo electrónico:</label>
          <input type="email" id="email" name="email" autocomplete="email" required>
          <span class="field__error">El correo ingresado no es válido.</span>
        </div>
        <div class="field">
          <label for="password">Contraseña:</label>
          <input type="password" id="password" name="password" autocomplete="current-password" required>
          <span class="field__error">Ingresa tu contraseña.</span>
        </div>
      </div>
      <button class="btn" type="submit">Iniciar</button>
    </form>
    <p><a href="registro.php<?= ($_GET['destino'] ?? '') === 'carrito' ? '?destino=carrito' : '' ?>">Crear una cuenta</a></p>
  </main>

  <script src="js/nav.js"></script>
  <script src="js/login.js"></script>
</body>
</html>

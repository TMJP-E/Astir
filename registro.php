<?php
$page = "registro";
require __DIR__ . "/app/page.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrar usuario | Astir</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div id="app-nav"></div>
  <script>window.ASTIR = <?= scriptJson($client) ?>;</script>
  <?php if ($pageError): ?><p class="empty" role="alert"><?= h($pageError) ?></p><?php endif; ?>

  <main class="main">
    <h1 class="title">Registrar Usuario</h1>

    <form class="form" id="registro-form" method="post" novalidate>
      <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
      <div class="form__fields form__fields--tight">
        <div class="field">
          <label for="nombre">Nombre:</label>
          <input type="text" id="nombre" name="nombre" autocomplete="name" required>
          <span class="field__error">Ingresa tu nombre.</span>
        </div>
        <div class="field">
          <label for="email">Correo electrónico:</label>
          <input type="email" id="email" name="email" autocomplete="email" required>
          <span class="field__error">El correo ingresado no es valido.</span>
        </div>
        <div class="field">
          <label for="password">Contraseña:</label>
          <input type="password" id="password" name="password" autocomplete="new-password" required>
          <span class="field__error" id="password-error"></span>
        </div>
        <div class="field">
          <label for="confirm">Confirmar contraseña:</label>
          <input type="password" id="confirm" name="confirm" autocomplete="new-password" required>
          <span class="field__error">Las contraseñas no coinciden.</span>
        </div>
      </div>
      <button class="btn" type="submit">Registrar</button>
    </form>
    <p><a href="login.php<?= ($_GET['destino'] ?? '') === 'carrito' ? '?destino=carrito' : '' ?>">Iniciar sesión</a></p>
  </main>

  <script src="js/nav.js"></script>
  <script src="js/registro.js"></script>
</body>
</html>

<?php if (!isset($page)) {
  http_response_code(404);
  exit;
} ?>
<?php if ($page === 'usuarios'): ?>
  <?php foreach ($users ?? [] as $user): ?>
    <?php $isAdmin = $user->getRole() === 'admin'; ?>
    <article class="row row--user">
      <img class="row__avatar" src="img/avatar.svg" alt="">
      <div class="row__info">
        <h3 class="row__name"><?= htmlEncode($user->getName()) ?></h3>
        <span class="row__mail"><?= htmlEncode($user->getEmail()) ?></span>
        <span
          class="row__role role--<?= $isAdmin ? 'admin' : 'user' ?>"><?= $isAdmin ? 'Administrador' : 'Usuario' ?></span>
      </div>
      <form class="row__actions"
        data-confirm="<?= htmlEncode(($isAdmin ? '¿Revocar' : '¿Otorgar') . ' permisos de administración a ' . $user->getName() . '?') ?>"
        method="post" action="actions.php">
        <input type="hidden" name="action" value="role">
        <input type="hidden" name="uuid" value="<?= htmlEncode($user->getUuid()) ?>">
        <input type="hidden" name="role" value="<?= $isAdmin ? 'regular' : 'admin' ?>">
        <button class="btn-mini btn-mini--<?= $isAdmin ? 'hard' : 'soft' ?>"><?= $isAdmin ? 'Revocar' : 'Otorgar' ?>
          administrador</button>
      </form>
    </article>
  <?php endforeach; ?>
<?php else: ?>
  <?php foreach ($bookings ?? [] as $booking): ?>
    <article class="row" data-search="<?= htmlEncode(bookingSearchText($booking, $page === 'gestion-reservaciones')) ?>"
      data-category="<?= htmlEncode($booking['category']) ?>" data-price="<?= htmlEncode((string) $booking['price']) ?>">
      <?php if ($booking['image']): ?>
        <img class="row__img" src="<?= htmlEncode(ltrim($booking['image'], '/')) ?>" alt="">
      <?php else: ?>
        <div class="row__img ph-img" aria-hidden="true"></div><?php endif; ?>
      <div class="row__info">
        <h3 class="row__name"><?= htmlEncode($booking['name']) ?></h3>
        <?php if ($page === 'gestion-reservaciones'): ?>
          <span class="row__meta">Cliente: <?= htmlEncode($booking['customer']) ?></span>
        <?php endif; ?>
        <span class="row__meta">Cantidad: <strong><?= (int) $booking['quantity'] ?></strong></span>
        <span class="row__meta">Precio individual: <span
            class="price">$<?= number_format((float) $booking['price'], 2) ?></span></span>
      </div>
      <form class="row__actions" method="post" action="actions.php"
        data-confirm="<?= htmlEncode($booking['name'] . ' · ' . $booking['customer']) ?>">
        <input type="hidden" name="action" value="cancel">
        <input type="hidden" name="uuid" value="<?= htmlEncode($booking['room_uuid']) ?>">
        <input type="hidden" name="owner" value="<?= htmlEncode($booking['user_uuid']) ?>">
        <?php if ($booking['quantity'] > 1): ?><button class="btn-mini btn-mini--hard" name="units" value="all">Cancelar
            todas</button><?php endif; ?>
        <button class="btn-mini btn-mini--soft" name="units" value="one">Cancelar 1</button>
      </form>
    </article>
  <?php endforeach; ?>
<?php endif; ?>
<?php if (!$pageError): ?>
  <p class="empty" id="no-results" <?= ($users ?? $bookings ?? []) ? 'hidden' : '' ?>>No hay resultados.</p><?php endif; ?>
<?php if (!isset($bookingPage) || !$bookingPage) {
  http_response_code(404);
  exit;
} ?>
<details class="booking-history">
  <summary>Historial de búsquedas</summary>
  <?php $history = array_reverse($_SESSION['booking_history'][$page] ?? [], true); ?>
  <?php if (!$history): ?>
    <p>Aún no has confirmado una búsqueda en esta pantalla.</p><?php endif; ?>
  <?php foreach ($history as $entry): ?>
    <?php
    $filters = $entry['criteria'];
    $labels = array_filter([$filters['query'], implode(', ', $filters['categories'])], fn($s) => $s !== '');
    if ($filters['min'] !== '') {
      $labels[] = 'Desde $' . $filters['min'];
    }
    if ($filters['max'] !== '') {
      $labels[] = 'Hasta $' . $filters['max'];
    }
    $count = count($entry['results']);
    ?>
    <details>
      <summary><?= htmlEncode($labels ? implode(' · ', $labels) : 'Todas las reservaciones') ?> (<?= $count ?>)</summary>
      <?php if (!$count): ?>
        <p>No hubo resultados.</p><?php endif; ?>
      <?php foreach ($entry['results'] as $row): ?>
        <article class="row">
          <?php if ($row['image']): ?><img class="row__img" src="<?= htmlEncode(ltrim($row['image'], '/')) ?>"
              alt=""><?php endif; ?>
          <div class="row__info">
            <h3 class="row__name"><?= htmlEncode($row['name']) ?></h3>
            <?php if ($page === 'gestion-reservaciones'): ?><span>Cliente:
                <?= htmlEncode($row['customer']) ?></span><?php endif; ?>
            <span>Cantidad: <?= (int) $row['quantity'] ?></span>
            <span>Precio individual: $<?= number_format((float) $row['price'], 2) ?></span>
          </div>
        </article>
      <?php endforeach; ?>
    </details>
  <?php endforeach; ?>
</details>
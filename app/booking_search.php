<?php
declare(strict_types=1);

const BOOKING_HISTORY_LIMIT = 25;

function bookingSearchCriteria(bool $withFilters): array
{
    $text = trim(postText('query', 'búsqueda'));
    if (mb_strlen($text) > 255) {
        throw new DomainException('La búsqueda admite hasta 255 caracteres.');
    }
    $categories = $_POST['categories'] ?? [];
    Validator::validateStringArray('Categorías', $categories);
    if (!array_is_list($categories) || array_diff($categories, ROOM_CATEGORIES)
        || (!$withFilters && $categories)) {
        throw new DomainException('Selecciona categorías disponibles en esta pantalla.');
    }
    $criteria = ['query' => $text, 'categories' => array_values(array_unique($categories))];
    foreach (['min' => 'Precio mínimo', 'max' => 'Precio máximo'] as $key => $label) {
        $value = postText($key, $label);
        $criteria[$key] = '';
        if ($value === '') { continue; }
        if (!$withFilters) { throw new DomainException('Esta búsqueda solo admite texto.'); }
        Validator::validatePrice($label, filter_var($value, FILTER_VALIDATE_FLOAT));
        $criteria[$key] = $value;
    }
    if ($criteria['min'] !== '' && $criteria['max'] !== '' && (float)$criteria['min'] > (float)$criteria['max']) {
        throw new DomainException('El precio máximo debe ser igual o mayor que el mínimo.');
    }
    return $criteria;
}

function bookingSearchText(array $row, bool $management): string
{
    $customer = $management ? ' Cliente: ' . $row['customer'] : '';
    return mb_strtolower($row['name'] . $customer . ' Cantidad: ' . $row['quantity']
        . ' Precio individual: $' . number_format((float)$row['price'], 2));
}

function bookingMatches(array $row, array $criteria, bool $management): bool
{
    return str_contains(bookingSearchText($row, $management), mb_strtolower($criteria['query']))
        && (!$criteria['categories'] || in_array($row['category'], $criteria['categories'], true))
        && ($criteria['min'] === '' || $row['price'] >= (float)$criteria['min'])
        && ($criteria['max'] === '' || $row['price'] <= (float)$criteria['max']);
}

function saveBookingSearch(string $page, string $userUuid): never
{
    $management = $page === 'gestion-reservaciones';
    $criteria = bookingSearchCriteria(!$management);
    $rows = getReservationRows($management ? null : $userUuid);
    $matches = array_values(array_filter($rows, fn($row) => bookingMatches($row, $criteria, $management)));
    $id = bin2hex(random_bytes(12));
    $_SESSION['booking_history'][$page][$id] = ['criteria' => $criteria, 'results' => $matches];
    $_SESSION['booking_history'][$page] = array_slice($_SESSION['booking_history'][$page], -BOOKING_HISTORY_LIMIT, null, true);
    redirectTo($page . '.php?buscar=' . $id);
}

function loadBookingCriteria(string $page): array
{
    if (!isset($_GET['buscar'])) { return ['query' => '', 'categories' => [], 'min' => '', 'max' => '']; }
    $id = $_GET['buscar'];
    if (!is_string($id) || !preg_match('/^[a-f0-9]{24}$/D', $id)) {
        throw new DomainException('El identificador de búsqueda no es válido.');
    }
    return $_SESSION['booking_history'][$page][$id]['criteria']
        ?? throw new DomainException('La búsqueda ya no está en el historial de esta sesión.');
}

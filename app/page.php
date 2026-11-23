<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';
if (!isset($page)) { http_response_code(404); exit; }
if ($page === 'index' && ($_GET['action'] ?? '') === 'cart_rooms') {
    require __DIR__ . '/cart_rooms.php';
}

try {
    $account = currentAccount();
} catch (Throwable $error) {
    http_response_code(503);
    exit(h(publicError($error)));
}
$admin = ($account['role'] ?? '') === 'admin';
$adminPage = in_array($page, ['habitaciones', 'gestion-reservaciones', 'usuarios'], true)
    || ($page === 'habitacion' && (isset($_GET['nuevo']) || isset($_GET['editar'])));
if ($adminPage || in_array($page, ['reservaciones', 'perfil'], true)) {
    if (!$account) { redirectTo('login.php'); }
    if ($adminPage && !$admin) { http_response_code(403); exit('Se requiere una cuenta de administrador.'); }
}
if ($account && in_array($page, ['login', 'registro'], true)) { redirectTo('perfil.php'); }

$bookingPage = in_array($page, ['reservaciones', 'gestion-reservaciones'], true);
if ($bookingPage) { require_once __DIR__ . '/booking_search.php'; }
$pageError = null;
$client = [
    'role' => $admin ? 'admin' : ($account ? 'user' : 'anon'),
    'csrf' => $_SESSION['csrf']
];
if (isset($_SESSION['cart_id'])) {
    $client['legacyCartKey'] = 'astir_cart_' . $_SESSION['cart_id'];
}
try {
    if ($page === 'habitacion') {
        foreach (['nuevo', 'editar'] as $mode) {
            if (isset($_GET[$mode]) && $_GET[$mode] !== '1') {
                throw new DomainException('El modo de habitación solicitado no es válido.');
            }
        }
        if (isset($_GET['nuevo'])) {
            if (isset($_GET['id']) || isset($_GET['editar'])) {
                throw new DomainException('Selecciona una sola operación para la habitación.');
            }
        } else {
            Validator::validateUuid('Habitación', $_GET['id'] ?? null);
        }
    }
    if (in_array($page, ['registro', 'login'], true) && isset($_GET['destino']) && $_GET['destino'] !== 'carrito') {
        throw new DomainException('El destino solicitado no es válido.');
    }
    if ($page === 'index' && isset($_GET['action'])) {
        throw new DomainException('Operación desconocida.');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        checkCsrf();
        switch ($page) {
            case 'registro':
                $password = postText('password', 'contraseña');
                if ($password !== postText('confirm', 'confirmación')) {
                    throw new DomainException('Las contraseñas no coinciden.');
                }
                insertUser(trim(postText('nombre', 'nombre')), trim(postText('email', 'correo')), $password);
                redirectTo(($_GET['destino'] ?? '') === 'carrito' ? 'login.php?destino=carrito' : 'login.php');
            case 'login':
                $email = trim(postText('email', 'correo'));
                Validator::validateEmail('Correo', $email);
                $password = postText('password', 'contraseña');
                if ($password === '') { throw new DomainException('Ingresa tu contraseña.'); }
                $user = getUserWithEmailAndPassword($email, $password);
                session_regenerate_id(true);
                $_SESSION['csrf'] = bin2hex(random_bytes(32));
                $_SESSION['user_uuid'] = $user->getUuid();
                $_SESSION['role'] = $user->getRole();
                redirectTo(($_GET['destino'] ?? '') === 'carrito' ? 'carrito.php' : 'comprar.php');
            case 'reservaciones':
            case 'gestion-reservaciones':
                saveBookingSearch($page, $account['uuid']);
            case 'perfil':
                expireCartCookie();
                $_SESSION = [];
                session_regenerate_id(true);
                redirectTo('index.php');
            default:
                http_response_code(405);
                throw new DomainException('Método no permitido en esta página.');
        }
    }

    if (in_array($page, ['index', 'comprar', 'habitaciones', 'habitacion', 'carrito'], true)) {
        $client['rooms'] = array_map('roomView', getAllRooms());
        if ($page === 'habitacion' && !isset($_GET['nuevo']) && !in_array($_GET['id'] ?? '', array_column($client['rooms'], 'id'), true)) {
            http_response_code(404);
            throw new DomainException('La habitación solicitada no existe.');
        }
        if ($page === 'habitacion' && $admin) {
            $client['categories'] = ROOM_CATEGORIES;
            $client['maxImageBytes'] = InputValidator::MAX_IMAGE_BYTES;
            $client['amenities'] = array_map(fn($c) => $c->getType(), getAllCommodities());
        }
        if ($page === 'carrito') {
            $_SESSION['checkout'] ??= bin2hex(random_bytes(16));
            $client['checkout'] = $_SESSION['checkout'];
        }
    } elseif ($page === 'usuarios') {
        $users = getAllUsers();
    } elseif ($bookingPage) {
        $bookings = getReservationRows($page === 'reservaciones' ? $account['uuid'] : null);
        $criteria = loadBookingCriteria($page);
    }
} catch (Throwable $error) {
    if (http_response_code() < 400) {
        http_response_code($error instanceof DomainException || $error instanceof ValidationException || $_SERVER['REQUEST_METHOD'] === 'POST' ? 422 : 503);
    }
    $pageError = publicError($error);
    $client['loadError'] = true;
}


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
    || ($page === 'habitacion' && isset($_GET['nuevo']));
if ($adminPage || in_array($page, ['reservaciones', 'perfil'], true)) {
    if (!$account) { redirectTo('login.php'); }
    if ($adminPage && !$admin) { http_response_code(403); exit('Se requiere una cuenta de administrador.'); }
}
if ($account && in_array($page, ['login', 'registro'], true)) { redirectTo('perfil.php'); }

$pageError = null;
$client = [
    'role' => $admin ? 'admin' : ($account ? 'user' : 'anon'),
    'storageKey' => 'astir_cart_' . $_SESSION['cart_id'],
    'csrf' => $_SESSION['csrf']
];
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        checkCsrf();
        switch ($page) {
            case 'registro':
                if (($_POST['password'] ?? '') !== ($_POST['confirm'] ?? null)) {
                    throw new DomainException('Las contraseñas no coinciden.');
                }
                insertUser(trim($_POST['nombre'] ?? ''), trim($_POST['email'] ?? ''), $_POST['password'] ?? '');
                redirectTo(($_GET['destino'] ?? '') === 'carrito' ? 'login.php?destino=carrito' : 'login.php');
            case 'login':
                $user = getUserWithEmailAndPassword(trim($_POST['email'] ?? ''), $_POST['password'] ?? '');
                session_regenerate_id(true);
                $_SESSION['csrf'] = bin2hex(random_bytes(32));
                $_SESSION['user_uuid'] = $user->getUuid();
                $_SESSION['role'] = $user->getRole();
                redirectTo(($_GET['destino'] ?? '') === 'carrito' ? 'carrito.php' : 'comprar.php');
            case 'perfil':
                $_SESSION = [];
                session_regenerate_id(true);
                redirectTo('index.php');
            default:
                http_response_code(405);
                exit;
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
            $client['amenities'] = array_map(fn($c) => $c->getType(), getAllCommodities());
        }
        if ($page === 'carrito') {
            $_SESSION['checkout'] ??= bin2hex(random_bytes(16));
            $client['checkout'] = $_SESSION['checkout'];
        }
    } elseif ($page === 'usuarios') {
        $users = getAllUsers();
    } elseif ($page === 'reservaciones' || $page === 'gestion-reservaciones') {
        $bookings = getReservationRows($page === 'reservaciones' ? $account['uuid'] : null);
    }
} catch (Throwable $error) {
    if (http_response_code() < 400) {
        http_response_code($_SERVER['REQUEST_METHOD'] === 'POST' ? 422 : 503);
    }
    $pageError = publicError($error);
    $client['loadError'] = true;
}


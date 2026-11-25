<?php
declare(strict_types=1);
require_once __DIR__ . '/app/session.php';
header('Content-Type: application/json; charset=utf-8');

function handleAction(array $account): void
{
    $action = postText('action', 'operación');
    Validator::validateEnum('Operación', $action, ['reserve', 'cancel', 'role', 'delete-room', 'save-room']);
    $admin = $account['role'] === 'admin';
    if ($action === 'reserve') {
        $token = $_POST['checkout'] ?? '';
        if (!is_string($token) || !isset($_SESSION['checkout']) || !hash_equals($_SESSION['checkout'], $token)) {
            http_response_code(409);
            throw new DomainException('La confirmación ya se procesó o caducó. Consulta tus reservaciones.');
        }
        $rooms = json_decode(postText('rooms', 'carrito'), true);
        if (!is_array($rooms) || !$rooms || count($rooms) > 100) {
            throw new DomainException('El carrito no es válido.');
        }
        foreach ($rooms as $id => $quantity) {
            Validator::validateUuid('Habitación', $id);
            Validator::validateInteger('Cantidad', $quantity, 1);
        }
        ksort($rooms);
        insertUserRooms($account['uuid'], $rooms);
        unset($_SESSION['checkout']);
        return;
    }
    $uuid = postText('uuid', 'habitación o usuario');
    if ($action !== 'save-room' || $uuid !== '') {
        Validator::validateUuid('Identificador', $uuid);
    }
    if ($action === 'cancel') {
        $owner = $_POST['owner'] ?? $account['uuid'];
        Validator::validateUuid('Cliente', $owner);
        if (!$admin && $owner !== $account['uuid']) {
            http_response_code(403);
            throw new DomainException('Solo puedes cancelar tus propias reservaciones.');
        }
        if (($_POST['units'] ?? '') === 'all') {
            removeUserRoom($owner, $uuid);
        } elseif (($_POST['units'] ?? '') === 'one') {
            decreaseUserRoom($owner, $uuid);
        } else {
            throw new DomainException('Selecciona la cantidad que deseas cancelar.');
        }
        return;
    }
    if (!$admin) {
        http_response_code(403);
        throw new DomainException('Se requieren permisos de administración.');
    }
    switch ($action) {
        case 'role':
            $role = $_POST['role'] ?? '';
            Validator::validateEnum('Rol', $role, ['regular', 'admin']);
            toggleUserRole($uuid, $role);
            return;
        case 'delete-room':
            $room = getRoomByUuid($uuid);
            if (!$room) {
                throw new DomainException('La habitación ya no existe.');
            }
            deleteRoom($uuid);
            // Las fotos de muestra pueden estar compartidas. Solo retiramos archivos propios de la carga.
            $uploads = preg_grep('~^room_images/[a-f0-9]{32}\.(jpg|png|gif|webp)$~D', $room->getImages());
            deleteRoomImages($uploads, __DIR__);
            return;
        case 'save-room':
            saveRoomForm($uuid);
            return;
        default:
            throw new DomainException('Operación desconocida.');
    }
}

function saveRoomForm(string $uuid): void
{
    $name = trim(postText('name', 'nombre'));
    $description = trim(postText('description', 'descripción'));
    $category = postText('category', 'categoría');
    Validator::validateRequiredString('Nombre', $name, 255);
    Validator::validateRequiredString('Descripción', $description, 4095);
    $available = filter_var(postText('available', 'disponibilidad'), FILTER_VALIDATE_INT);
    $price = filter_var(postText('price', 'precio'), FILTER_VALIDATE_FLOAT);
    Validator::validateInteger('Disponibilidad', $available, 0);
    Validator::validatePrice('Precio', $price);
    $amenities = $_POST['amenities'] ?? [];
    Validator::validateStringArray('Comodidades', $amenities);
    $allowed = array_map(fn($c) => $c->getType(), getAllCommodities());
    if (!array_is_list($amenities) || count($amenities) !== count(array_unique($amenities)) || array_diff($amenities, $allowed)) {
        throw new DomainException('Selecciona comodidades del catálogo.');
    }
    $original = null;
    if ($uuid !== '' && isset($_POST['originalAvailable'])) {
        $original = filter_var(postText('originalAvailable', 'disponibilidad original'), FILTER_VALIDATE_INT);
        Validator::validateInteger('Disponibilidad original', $original, 0);
    }
    $paths = saveRoomImages($_FILES['images'] ?? [], __DIR__ . '/room_images');
    try {
        if ($uuid === '') {
            insertRoom($name, $description, $category, $price, $available, $amenities, $paths);
        } else {
            // Los archivos nuevos se agregan a la galería en la misma transacción.
            $newAvailable = $available === $original ? null : $available;
            updateRoom($uuid, $name, $description, $category, $price, $newAvailable, $amenities, null, $paths);
        }
    } catch (Throwable $error) {
        deleteRoomImages($paths, __DIR__);
        throw $error;
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        throw new DomainException('Método no permitido.');
    }
    if (!$_POST && !$_FILES && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        http_response_code(413);
        throw new DomainException('No se pudo leer el formulario. Si adjuntaste fotos, reduce el tamaño de la carga.');
    }
    $account = currentAccount();
    if (!$account) {
        http_response_code(401);
        throw new DomainException('Inicia sesión para continuar.');
    }
    checkCSRF();
    handleAction($account);
    echo '{"ok":true}';
} catch (Throwable $error) {
    if (http_response_code() < 400) {
        http_response_code(422);
    }
    $message = publicError($error);
    if (($_POST['action'] ?? '') === 'reserve' && $error instanceof RuntimeException && !$error->getPrevious()) {
        $message = 'No se pudo reservar la selección. Revisa la disponibilidad del carrito.';
    }
    echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
}

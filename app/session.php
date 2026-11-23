<?php
declare(strict_types=1);

require_once __DIR__ . '/../data/dataAccess.php';

session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
    'path' => '/'
]);
session_start();
header('Cache-Control: no-store');
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

const ROOM_CATEGORIES = ['express', 'regular', 'comfort', 'suite'];

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function scriptJson(mixed $value): string
{
    return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
}

function redirectTo(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}

function checkCsrf(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(403);
        throw new DomainException('La sesión cambió. Recarga la página para continuar.');
    }
}

function postText(string $key, string $label): string
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value) || !mb_check_encoding($value, 'UTF-8')) {
        throw new DomainException("El campo $label debe contener texto válido.");
    }
    return $value;
}

function expireCartCookie(): void
{
    // Sin Path explícito se usa el mismo directorio que al escribir desde JavaScript.
    setcookie('astir_cart', '', [
        'expires' => time() - 3600,
        'secure' => session_get_cookie_params()['secure'],
        'samesite' => 'Lax'
    ]);
}

function currentAccount(): ?array
{
    if (!isset($_SESSION['user_uuid'])) {
        return null;
    }
    $db = connectDatabase();
    try {
        $user = $db->execute_query('SELECT uuid, name, email, role FROM users WHERE uuid = ?', [$_SESSION['user_uuid']])->fetch_assoc();
        if (!$user) {
            $_SESSION = [];
            session_regenerate_id(true);
            expireCartCookie();
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            return null;
        }
        $_SESSION['role'] = $user['role'];
        return $user;
    } finally {
        $db->close();
    }
}

function publicError(Throwable $error): string
{
    if ($error instanceof DomainException || $error instanceof ValidationException) {
        return $error->getMessage();
    }
    $cause = $error->getPrevious();
    if ($cause instanceof ValidationException) {
        return $cause->getMessage();
    }
    if ($cause instanceof mysqli_sql_exception && $cause->getCode() === 1062) {
        return 'Ya existe un registro con esos datos.';
    }
    error_log($error->getMessage());
    return 'No fue posible completar la operación. Actualiza la página e inténtalo de nuevo.';
}

function roomView(Room $room): array
{
    $images = array_map(fn($path) => str_starts_with($path, '/room_images/') ? substr($path, 1) : $path, $room->getImages());
    return [
        'id' => $room->getUuid(), 'name' => $room->getName(), 'description' => $room->getDescription(),
        'price' => $room->getPrice(), 'available' => $room->getAvailable(), 'category' => $room->getCategory(),
        'attributes' => $room->getCommodities(), 'images' => $images, 'image' => $images[0] ?? null
    ];
}


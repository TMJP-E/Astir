<?php
function sendJson(array $payload, int $status = 200): never
{
    http_response_code($status);
    header("Content-Type: application/json; charset=utf-8");
    header("Cache-Control: no-store");
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function cartRoomToArray(Room $room): array
{
    return [
        "uuid" => $room->getUuid(),
        "name" => $room->getName(),
        "description" => $room->getDescription(),
        "category" => $room->getCategory(),
        "price" => $room->getPrice(),
        "available" => $room->getAvailable(),
        "commodities" => $room->getCommodities(),
        "images" => $room->getImages()
    ];
}

if (!isset($page)) { http_response_code(404); exit; }
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendJson(["error" => "Método no permitido."], 405);
}
$body = json_decode(file_get_contents("php://input"), true);
$uuids = is_array($body) ? ($body["uuids"] ?? null) : null;
if (!is_array($uuids) || count($uuids) > 100) {
    sendJson(["error" => "La selección del carrito no es válida."], 400);
}
foreach ($uuids as $uuid) {
    if (!is_string($uuid) || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid)) {
        sendJson(["error" => "Uno o más identificadores de habitación no son válidos."], 400);
    }
}
try {
    $rooms = getRoomsByUuids($uuids);
    sendJson(["rooms" => array_map("cartRoomToArray", $rooms)]);
} catch (Throwable $exception) {
    error_log("Error al consultar las habitaciones del carrito: " . $exception->getMessage());
    sendJson(["error" => "No fue posible actualizar los precios y la disponibilidad. Intenta de nuevo."], 503);
}

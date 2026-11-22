<?php
declare(strict_types=1);

session_set_cookie_params([
    "httponly" => true,
    "secure" => isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax",
    "path" => "/"
]);
session_start();

require_once __DIR__ . "/data/dataAccess.php";

if (!isset($_SESSION["cart_id"])) {
    $_SESSION["cart_id"] = bin2hex(random_bytes(16));
}

function sendJson(array $payload, int $status = 200): never
{
    http_response_code($status);
    header("Content-Type: application/json; charset=utf-8");
    header("Cache-Control: no-store");
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function roomToArray(Room $room): array
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

if (($_GET["action"] ?? "") === "cart_rooms") {
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
        sendJson(["rooms" => array_map("roomToArray", $rooms)]);
    } catch (Throwable $exception) {
        error_log("Error al consultar las habitaciones del carrito: " . $exception->getMessage());
        sendJson(["error" => "No fue posible actualizar los precios y la disponibilidad. Intenta de nuevo."], 503);
    }
}

try {
    $rooms = getAllRooms();
    $catalogError = null;
} catch (Throwable $exception) {
    error_log("Error al cargar el catálogo: " . $exception->getMessage());
    $rooms = [];
    $catalogError = "No fue posible cargar las habitaciones. Verifica que la base de datos esté disponible.";
}

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Astir | Habitaciones y carrito</title>
    <style>
        :root { color-scheme: light; --ink: #202522; --muted: #64716a; --accent: #245b49; --line: #dce4de; --paper: #f5f7f4; }
        * { box-sizing: border-box; }
        body { margin: 0; color: var(--ink); background: var(--paper); font: 16px/1.5 system-ui, sans-serif; }
        header { padding: 2rem max(1rem, calc((100% - 1180px)/2)); background: #173b31; color: white; }
        header h1 { margin: 0; font-size: clamp(1.8rem, 4vw, 2.6rem); }
        header p { margin: .25rem 0 0; color: #d8e6dc; }
        main { max-width: 1180px; margin: 2rem auto; padding: 0 1rem; display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 1.5rem; align-items: start; }
        h2 { margin-top: 0; }
        .catalog { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; }
        .card, .cart { background: white; border: 1px solid var(--line); border-radius: 14px; overflow: hidden; }
        .card img { display: block; width: 100%; height: 170px; object-fit: cover; background: #e8eee9; }
        .card-body { padding: 1rem; }
        .card h3 { margin: 0 0 .25rem; }
        .muted { color: var(--muted); }
        .meta { display: flex; justify-content: space-between; gap: .5rem; margin: .8rem 0; }
        button, input { font: inherit; }
        button { border: 0; border-radius: 8px; padding: .65rem .9rem; color: white; background: var(--accent); cursor: pointer; }
        button:disabled { background: #9aa69f; cursor: not-allowed; }
        .cart { padding: 1rem; position: sticky; top: 1rem; }
        .cart-item { padding: .9rem 0; border-bottom: 1px solid var(--line); }
        .cart-title { display: flex; justify-content: space-between; gap: .5rem; }
        .cart-controls { display: flex; align-items: center; gap: .5rem; margin-top: .6rem; }
        .cart-controls input { width: 4.5rem; padding: .35rem; border: 1px solid var(--line); border-radius: 6px; }
        .small-button { padding: .35rem .55rem; }
        .remove { color: #9d3329; background: transparent; padding: .2rem; text-decoration: underline; }
        .totals { display: flex; justify-content: space-between; margin-top: 1rem; font-weight: 700; }
        [role="status"] { min-height: 1.5rem; color: #8b332b; }
        .notice { padding: 1rem; background: #fff4db; border-radius: 8px; }
        @media (max-width: 820px) { main { grid-template-columns: 1fr; } .cart { position: static; grid-row: 1; } }
    </style>
</head>
<body>
<header>
    <h1>Astir</h1>
    <p>Encuentra una habitación y prepara tu reservación.</p>
</header>
<main>
    <section aria-labelledby="catalog-title">
        <h2 id="catalog-title">Habitaciones disponibles</h2>
        <?php if ($catalogError !== null): ?>
            <p class="notice"><?= escapeHtml($catalogError) ?></p>
        <?php elseif ($rooms === []): ?>
            <p class="muted">Por ahora no hay habitaciones registradas.</p>
        <?php else: ?>
            <div class="catalog">
                <?php foreach ($rooms as $room): ?>
                    <?php $image = $room->getImages()[0] ?? ""; ?>
                    <article class="card">
                        <?php if ($image !== ""): ?><img src="<?= escapeHtml($image) ?>" alt="Habitación <?= escapeHtml($room->getName()) ?>" loading="lazy"><?php endif; ?>
                        <div class="card-body">
                            <h3><?= escapeHtml($room->getName()) ?></h3>
                            <p class="muted"><?= escapeHtml($room->getDescription()) ?></p>
                            <div class="meta"><span><?= escapeHtml(ucfirst($room->getCategory())) ?></span><strong>$<?= number_format($room->getPrice(), 2) ?> / noche</strong></div>
                            <p class="muted">Disponibles: <?= $room->getAvailable() ?></p>
                            <button type="button" data-add-room="<?= escapeHtml((string) $room->getUuid()) ?>" data-available="<?= $room->getAvailable() ?>" <?= $room->getAvailable() < 1 ? "disabled" : "" ?>>Agregar al carrito</button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <aside class="cart" aria-labelledby="cart-title">
        <h2 id="cart-title">Tu carrito</h2>
        <p id="cart-status" role="status" aria-live="polite"></p>
        <div id="cart-items"><p class="muted">Cargando carrito…</p></div>
        <div class="totals"><span>Total</span><span id="cart-total">$0.00</span></div>
        <p class="muted">Los precios y la disponibilidad se consultan de nuevo al abrir el carrito.</p>
    </aside>
</main>
<script>
window.ASTIR_CART_CONFIG = <?= json_encode(["storageKey" => "astir_cart_" . $_SESSION["cart_id"]], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="cart.js" defer></script>
</body>
</html>

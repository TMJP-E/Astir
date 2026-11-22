(() => {
    "use strict";

    const storageKey = window.ASTIR_CART_CONFIG?.storageKey;
    const itemsElement = document.getElementById("cart-items");
    const totalElement = document.getElementById("cart-total");
    const statusElement = document.getElementById("cart-status");
    const currency = new Intl.NumberFormat("es-MX", { style: "currency", currency: "MXN" });
    let cart = readCart();
    let roomsByUuid = new Map();
    let refreshRequest = 0;

    function readCart() {
        if (!storageKey) return {};
        try {
            const stored = JSON.parse(localStorage.getItem(storageKey) || "{}");
            if (!stored || typeof stored !== "object" || Array.isArray(stored)) return {};
            return Object.fromEntries(Object.entries(stored).filter(([uuid, quantity]) =>
                /^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i.test(uuid) &&
                Number.isSafeInteger(quantity) && quantity > 0
            ));
        } catch {
            return {};
        }
    }

    function saveCart() {
        try {
            localStorage.setItem(storageKey, JSON.stringify(cart));
            return true;
        } catch {
            showStatus("No fue posible guardar el carrito en este navegador.");
            return false;
        }
    }

    function showStatus(message = "") {
        statusElement.textContent = message;
    }

    function makeElement(tag, className, text) {
        const element = document.createElement(tag);
        if (className) element.className = className;
        if (text !== undefined) element.textContent = text;
        return element;
    }

    function renderCart() {
        itemsElement.replaceChildren();
        let total = 0;
        const entries = Object.entries(cart);
        if (entries.length === 0) {
            itemsElement.append(makeElement("p", "muted", "Tu carrito está vacío."));
            totalElement.textContent = currency.format(0);
            return;
        }

        for (const [uuid, quantity] of entries) {
            const room = roomsByUuid.get(uuid);
            if (!room) continue;
            const item = makeElement("div", "cart-item");
            const title = makeElement("div", "cart-title");
            title.append(makeElement("strong", "", room.name));
            title.append(makeElement("span", "", currency.format(room.price * quantity)));
            item.append(title);

            const controls = makeElement("div", "cart-controls");
            controls.append(makeElement("label", "", "Cantidad"));
            const input = document.createElement("input");
            input.type = "number";
            input.min = "1";
            input.max = String(room.available);
            input.step = "1";
            input.value = String(quantity);
            input.setAttribute("aria-label", `Cantidad para ${room.name}`);
            input.addEventListener("change", () => {
                const next = Number(input.value);
                if (!Number.isSafeInteger(next) || next < 1 || next > room.available) {
                    input.value = String(cart[uuid]);
                    showStatus(`La cantidad debe estar entre 1 y ${room.available} para ${room.name}.`);
                    return;
                }
                cart[uuid] = next;
                saveCart();
                showStatus();
                renderCart();
            });
            controls.append(input);
            const remove = makeElement("button", "remove", "Quitar");
            remove.type = "button";
            remove.addEventListener("click", () => {
                delete cart[uuid];
                saveCart();
                showStatus();
                renderCart();
            });
            controls.append(remove);
            item.append(controls);
            item.append(makeElement("small", "muted", `${currency.format(room.price)} por noche · ${room.available} disponibles`));
            itemsElement.append(item);
            total += room.price * quantity;
        }
        totalElement.textContent = currency.format(total);
    }

    async function refreshCart() {
        const requestId = ++refreshRequest;
        const uuids = Object.keys(cart);
        if (uuids.length === 0) {
            roomsByUuid = new Map();
            renderCart();
            return;
        }
        try {
            const response = await fetch("?action=cart_rooms", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ uuids })
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.error || "No fue posible actualizar el carrito.");
            if (requestId !== refreshRequest) return;

            roomsByUuid = new Map(result.rooms.map(room => [room.uuid, room]));
            let changed = false;
            for (const uuid of uuids) {
                const room = roomsByUuid.get(uuid);
                if (!room || room.available < 1) {
                    delete cart[uuid];
                    changed = true;
                } else if (cart[uuid] > room.available) {
                    cart[uuid] = room.available;
                    changed = true;
                }
            }
            if (changed) saveCart();
            showStatus(changed ? "Se ajustó el carrito según la disponibilidad actual." : "");
            renderCart();
        } catch (error) {
            if (requestId !== refreshRequest) return;
            showStatus(error.message || "No fue posible actualizar el carrito.");
            itemsElement.replaceChildren(makeElement("p", "muted", "No se pudieron consultar los precios y la disponibilidad."));
        }
    }

    document.querySelectorAll("[data-add-room]").forEach(button => {
        button.addEventListener("click", () => {
            const uuid = button.dataset.addRoom;
            const available = Number(button.dataset.available);
            const current = cart[uuid] || 0;
            if (current >= available) {
                showStatus(`Ya agregaste todas las unidades disponibles (${available}).`);
                return;
            }
            cart[uuid] = current + 1;
            saveCart();
            showStatus("Habitación agregada al carrito.");
            refreshCart();
        });
    });

    renderCart();
    refreshCart();
})();

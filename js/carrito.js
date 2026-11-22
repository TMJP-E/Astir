// Carrito local de la sesión y confirmación de reservaciones en MariaDB.
(async function () {
const data = window.ASTIR;
if (data.loadError) return;
const esc = window.astirEsc;
const money = window.astirMoney;
const cont = document.getElementById("cart");

const TRASH = '<svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>';
const CHECK = '<svg viewBox="0 0 24 24"><path d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>';
const SAD = '<svg viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 3.5c-2.33 0-4.31 1.46-5.11 3.5h10.22c-.8-2.04-2.78-3.5-5.11-3.5z"/></svg>';

function items() {
  return window.astirCart.read()
    .map((c) => ({ room: data.rooms.find((r) => r.id === c.id), qty: c.qty }))
    .filter((x) => x.room);
}
function total(lista) { return lista.reduce((s, x) => s + x.room.price * x.qty, 0); }
function totalBox(n) { return '<span class="money__box"><span>$</span><span>' + Number(n).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + "</span></span>"; }

function filaItem(x, editable) {
  const r = x.room;
  return (
    '<article class="cart-item" data-id="' + esc(r.id) + '">' +
      (r.image ? '<img class="cart-item__img" src="' + esc(r.image) + '" alt="">' : '<div class="cart-item__img ph-img"></div>') +
      '<div class="cart-item__info">' +
        '<h3 class="cart-item__name">' + esc(r.name) + "</h3>" +
        '<div class="cart-item__row">Cantidad: ' +
          (editable
            ? '<div class="stepper"><button type="button" data-q="-1" aria-label="Menos">−</button><input type="number" value="' + esc(x.qty) + '" min="1" max="' + esc(r.available) + '" aria-label="Cantidad"><button type="button" data-q="1" aria-label="Más">+</button></div>'
            : "<span>" + esc(x.qty) + "</span>") +
        "</div>" +
        '<div class="cart-item__row">Precio Individual: <span class="price">' + money(r.price) + "</span></div>" +
      "</div>" +
      '<div class="cart-item__total">Total: <strong>' + money(r.price * x.qty) + "</strong>" +
        (editable ? '<button class="btn-trash" type="button" data-del aria-label="Quitar del carrito">' + TRASH + "</button>" : "") +
      "</div>" +
    "</article>"
  );
}

// ---------- Lista / vacio ----------
function vistaLista() {
  const lista = items();
  const vacio = lista.length === 0;
  cont.innerHTML =
    '<h1 class="cart__title">Carrito</h1>' +
    '<div class="cart__main" id="cart-main">' +
      (vacio ? '<p class="cart__empty">Tu carrito está vacío.</p>' : lista.map((x) => filaItem(x, true)).join("")) +
    "</div>" +
    '<div class="cart__summary">' +
      '<div class="cart__total">Total: ' + totalBox(total(lista)) + "</div>" +
      '<div class="cart__buttons">' +
        '<button class="btn btn--red" id="vaciar" type="button"' + (vacio ? " disabled" : "") + ">Vaciar Carrito</button>" +
        '<button class="btn btn--green" id="revisar" type="button"' + (vacio ? " disabled" : "") + ">Revisar reservación</button>" +
      "</div>" +
    "</div>";

  if (vacio) return;

  document.getElementById("cart-main").addEventListener("click", (e) => {
    const fila = e.target.closest(".cart-item");
    if (!fila) return;
    const id = fila.dataset.id;
    const q = e.target.closest("[data-q]");
    if (q) {
      const input = fila.querySelector("input");
      const max = Number(input.max) || 1;
      cambiarCantidad(id, Math.min(max, Math.max(1, Number(input.value) + Number(q.dataset.q))));
    } else if (e.target.closest("[data-del]")) {
      const antes = window.astirCart.read();
      if (!window.astirCart.write(antes.filter((c) => c.id !== id))) return;
      vistaLista();
      window.astirSnackbar("Se eliminó la habitación del carrito", { action: "Deshacer", onAction: () => { window.astirCart.write(antes); vistaLista(); } });
    }
  });
  document.getElementById("cart-main").addEventListener("change", (e) => {
    const fila = e.target.closest(".cart-item");
    if (fila && e.target.matches("input")) {
      const max = Number(e.target.max) || 1;
      const n = Number(e.target.value);
      if (!Number.isSafeInteger(n) || n < 1 || n > max) {
        astirSnackbar("Ingresa una cantidad entre 1 y " + max + ".");
        vistaLista();
        return;
      }
      cambiarCantidad(fila.dataset.id, n);
    }
  });
  document.getElementById("vaciar").addEventListener("click", () => {
    window.astirConfirm({
      html: "¿Desea <em>vaciar</em> el carrito?",
      sub: "Esta acción <em>no</em> se puede deshacer.",
      onConfirm: () => { window.astirCart.write([]); vistaLista(); }
    });
  });
  document.getElementById("revisar").addEventListener("click", vistaConfirmacion);
}
function cambiarCantidad(id, n) {
  if (!Number.isSafeInteger(n)) { astirSnackbar("Ingresa una cantidad entera."); vistaLista(); return; }
  window.astirCart.write(window.astirCart.read().map((c) => (c.id === id ? { id: c.id, qty: n } : c)));
  vistaLista();
}

function vistaConfirmacion() {
  if (data.role === "anon") { location.assign("login.php?destino=carrito"); return; }
  const lista = items();
  if (!lista.length) { vistaLista(); return; }
  cont.innerHTML =
    '<div class="cart__main"><h1 class="cart__title">Confirmar reservación</h1>' +
    lista.map(x => filaItem(x, false)).join("") + '</div>' +
    '<div class="cart__summary"><div class="cart__total">Total: ' + totalBox(total(lista)) + '</div>' +
    '<div class="cart__buttons"><button class="btn btn--teal" id="volver">Volver al carrito</button>' +
    '<button class="btn btn--green" id="confirmar">Confirmar reservación</button></div></div>';
  document.getElementById("volver").onclick = vistaLista;
  document.getElementById("confirmar").onclick = async event => {
    const reviewed = Object.fromEntries(lista.map(x => [x.room.id, x.qty]));
    const current = Object.fromEntries(astirCart.read().map(x => [x.id, x.qty]));
    if (JSON.stringify(reviewed) !== JSON.stringify(current)) {
      location.reload();
      return;
    }
    event.currentTarget.disabled = true;
    document.getElementById("volver").disabled = true;
    const body = new FormData();
    body.set("action", "reserve");
    body.set("checkout", data.checkout);
    body.set("rooms", JSON.stringify(reviewed));
    try {
      await astirSubmit(body);
      const remaining = astirCart.read()
        .map(item => ({id: item.id, qty: item.qty - (reviewed[item.id] || 0)}))
        .filter(item => item.qty > 0);
      astirCart.write(remaining);
      vistaEstado(true);
    } catch (error) {
      vistaEstado(false);
      astirSnackbar(error.message);
    }
  };
}

// ---------- Exito / error ----------
function vistaEstado(ok) {
  cont.innerHTML =
    '<div class="cart-state">' +
      "<p>" + (ok ? "Se han realizado sus reservaciones" : "Algo salió mal") + "</p>" +
      '<div class="cart-state__icon ' + (ok ? "cart-state__icon--ok" : "cart-state__icon--bad") + '" aria-hidden="true">' + (ok ? CHECK : SAD) + "</div>" +
      '<a class="btn btn--teal" href="' + (ok ? "reservaciones.php" : "carrito.php") + '">' + (ok ? "Mis reservaciones" : "Revisar carrito") + '</a>' +
    "</div>";
  document.getElementById("more").hidden = true;
}

// ---------- Inicio ----------
const saved = astirCart.read();
window.addEventListener("storage", event => {
  if (event.key === data.storageKey) location.reload();
});
if (saved.length) {
  try {
    const response = await fetch("index.php?action=cart_rooms", {
      method: "POST", headers: {"Content-Type": "application/json"},
      body: JSON.stringify({uuids: saved.map(item => item.id)})
    });
    const result = await response.json();
    if (!response.ok) throw new Error(result.error);
    const fresh = new Map(result.rooms.map(room => [room.uuid, room]));
    const adjusted = [];
    for (const item of saved) {
      const room = fresh.get(item.id);
      const display = data.rooms.find(r => r.id === item.id);
      if (!room || !display || room.available < 1) continue;
      display.price = room.price;
      display.available = room.available;
      adjusted.push({id: item.id, qty: Math.min(item.qty, room.available)});
    }
    if (JSON.stringify(saved) !== JSON.stringify(adjusted)) {
      if (!astirCart.write(adjusted)) return;
      astirSnackbar("El carrito se ajustó a las unidades disponibles.");
    }
  } catch (error) {
    cont.textContent = error.message || "No se pudo consultar el carrito.";
    return;
  }
}
vistaLista();
// Mas habitaciones (solo se ve en pantallas grandes)
document.getElementById("rooms-row").innerHTML = data.rooms.map((r) => window.astirRoomCard(r, "reserve")).join("");
})();

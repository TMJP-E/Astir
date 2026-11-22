// Detalle de la habitación solicitada por UUID.
(function () {
const data = window.ASTIR;
if (data.loadError) return;
const esc = window.astirEsc;
const params = new URLSearchParams(location.search);
const esNuevo = params.has("nuevo") && data.role === "admin";
const editar = data.role === "admin" && (esNuevo || params.has("editar"));
const room = esNuevo
  ? {id: "", name: "", category: "regular", price: 0, available: 1, description: "", attributes: []}
  : data.rooms.find(r => r.id === params.get("id"));

const ICON_L = '<svg viewBox="0 0 24 24"><path d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>';
const ICON_R = '<svg viewBox="0 0 24 24"><path d="M8.59 16.59 13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>';

// Galería de imágenes de la habitación.
const imagenes = room.images && room.images.length ? room.images : [null];
let img = 0;
function galeria() {
  return (
    '<div class="gallery">' +
      '<div class="gallery__img ph-img" id="g-img" role="img" aria-label="Foto de la habitación"></div>' +
      '<div class="room__price"><span>' + esc(room.available) + "</span></div>" +
      '<button class="gallery__btn gallery__btn--prev" id="g-prev" type="button" aria-label="Foto anterior">' + ICON_L + "</button>" +
      '<button class="gallery__btn gallery__btn--next" id="g-next" type="button" aria-label="Foto siguiente">' + ICON_R + "</button>" +
    "</div>"
  );
}
function pintarImagen() {
  const el = document.getElementById("g-img");
  el.style.backgroundImage = imagenes[img] ? "url('" + imagenes[img] + "')" : "";
}
function activarGaleria() {
  document.getElementById("g-prev").addEventListener("click", () => { img = (img - 1 + imagenes.length) % imagenes.length; pintarImagen(); });
  document.getElementById("g-next").addEventListener("click", () => { img = (img + 1) % imagenes.length; pintarImagen(); });
  pintarImagen();
}

function vistaUsuario() {
  const cont = document.getElementById("detail");
  cont.innerHTML =
    '<div class="detail__head">' +
      '<h1 class="detail__name">' + esc(room.name) + "</h1>" +
      '<span class="tag">' + esc(room.category) + "</span>" +
    "</div>" +
    galeria() +
    '<div class="detail__info">' +
      '<p class="detail__desc">' + esc(room.description) + "</p>" +
      '<div class="chips">' + window.astirChips(room.attributes) + "</div>" +
      '<p class="room__pricerow" style="padding:0">Precio: <span class="price">' + window.astirMoney(room.price) + "</span></p>" +
      '<div class="qty">Cantidad: <div class="stepper"><button type="button" id="q-minus" aria-label="Menos">−</button><input type="number" id="qty" value="1" min="1" max="' + esc(room.available) + '" aria-label="Cantidad"><button type="button" id="q-plus" aria-label="Más">+</button></div></div>' +
      '<button class="btn-cart" id="to-cart" type="button">Mover al carrito</button>' +
    "</div>";
  activarGaleria();

  const qty = document.getElementById("qty");
  function ajustar(n) { qty.value = Math.min(room.available, Math.max(1, n)); }
  document.getElementById("q-minus").addEventListener("click", () => ajustar(Number(qty.value) - 1));
  document.getElementById("q-plus").addEventListener("click", () => ajustar(Number(qty.value) + 1));
  qty.addEventListener("change", () => ajustar(Number(qty.value) || 1));

  const add = document.getElementById("to-cart");
  add.disabled = room.available === 0;
  add.addEventListener("click", () => astirCart.add(room.id, Number(qty.value)));
}

function vistaAdmin() {
  const cats = data.categories;
  const cont = document.getElementById("detail");
  cont.innerHTML =
    '<div class="detail__head"><h1 class="detail__name">' + (esNuevo ? "Nueva habitación" : "Editar habitación") + "</h1></div>" +
    galeria() +
    '<form class="admin-form" id="admin-form">' +
      '<div class="field"><label for="a-name">Nombre:</label><input name="name" maxlength="255" required id="a-name" type="text" value="' + esc(room.name) + '"><span class="field__error">Ingresa el nombre.</span></div>' +
      '<div class="field"><label for="a-cat">Categoría:</label><select name="category" id="a-cat">' + cats.map((c) => "<option" + (c === room.category ? " selected" : "") + ">" + esc(c) + "</option>").join("") + "</select></div>" +
      '<div class="field"><label for="a-desc">Descripción:</label><textarea name="description" required maxlength="4095" id="a-desc">' + esc(room.description) + "</textarea></div>" +
      '<fieldset><legend>Comodidades</legend>' + data.amenities.map(a =>
        '<label><input type="checkbox" name="amenities[]" value="' + esc(a) + '"' + (room.attributes.includes(a) ? ' checked' : '') + '> ' + esc(a) + '</label> '
      ).join('') + '</fieldset>' +
      '<div class="field"><label for="a-images">Agregar fotos</label><input type="file" id="a-images" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple></div>' +
      '<div class="field"><label for="a-price">Precio:</label><input name="price" required id="a-price" type="number" min="0" step="0.01" value="' + esc(room.price) + '"><span class="field__error">Ingresa un precio válido.</span></div>' +
      '<div class="field"><label for="a-av">Disponibilidad:</label><input name="available" required id="a-av" type="number" min="0" value="' + esc(room.available) + '"></div>' +
      '<div class="admin-form__actions">' +
        '<button class="btn" type="submit">Guardar</button>' +
        '<button class="btn btn--ghost" type="button" id="a-cancel">Cancelar</button>' +
        (esNuevo ? "" : '<button class="btn btn--danger" type="button" id="a-delete">Eliminar</button>') +
      "</div>" +
    "</form>";
  activarGaleria();

  const nombreActual = () => esc(document.getElementById("a-name").value.trim() || room.name || "sin nombre").toUpperCase();

  document.getElementById("admin-form").addEventListener("submit", (e) => {
    e.preventDefault();
    const nameOk = document.getElementById("a-name").value.trim() !== "";
    const priceOk = Number(document.getElementById("a-price").value) >= 0;
    document.getElementById("a-name").closest(".field").classList.toggle("field--invalid", !nameOk);
    document.getElementById("a-price").closest(".field").classList.toggle("field--invalid", !priceOk);
    if (!nameOk || !priceOk) return;
    window.astirConfirm({
      html: "¿Desea guardar los cambios de la habitación " + nombreActual() + "?",
      onConfirm: () => submitRoom("save-room")
    });
  });
  document.getElementById("a-cancel").addEventListener("click", () => {
    window.astirConfirm({
      html: "¿Desea cancelar los cambios de la habitación " + nombreActual() + "?",
      onConfirm: () => (window.location.href = "habitaciones.php")
    });
  });
  const del = document.getElementById("a-delete");
  if (del) del.addEventListener("click", () => {
    window.astirConfirm({
      html: "¿Desea <em>eliminar</em> la habitación " + nombreActual() + "?",
      sub: "Esta acción <em>no</em> se puede deshacer.",
      onConfirm: () => submitRoom("delete-room")
    });
  });
}

async function submitRoom(action) {
  const form = document.getElementById("admin-form");
  if (form.dataset.busy) return;
  form.dataset.busy = "1";
  const body = new FormData(form);
  body.set("action", action);
  body.set("uuid", room.id);
  body.set("originalAvailable", String(room.available));
  try {
    await astirSubmit(body);
    location.assign("habitaciones.php");
  } catch (error) {
    delete form.dataset.busy;
    astirSnackbar(error.message);
  }
}
editar ? vistaAdmin() : vistaUsuario();
document.title = (esNuevo ? "Nueva habitación" : room.name) + " | Astir";

// Mas habitaciones
const otras = data.rooms.filter((r) => r.id !== room.id);
document.getElementById("rooms-row").innerHTML = otras.map((r) => window.astirRoomCard(r, "reserve")).join("");
})();

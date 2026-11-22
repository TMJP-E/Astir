// Listado de habitaciones (habitaciones.php) y catalogo para comprar (comprar.php).
// El modo sale de <body data-mode="habitaciones|comprar">.
const rooms = window.ASTIR.rooms || [];
const modo = document.body.dataset.mode || "habitaciones";
const $ = (id) => document.getElementById(id);

// Solo el admin ve el boton "+"
const role = window.ASTIR.role;
if ($("add-room")) $("add-room").hidden = role !== "admin";

// Estado de filtros
const filtros = { texto: "", categorias: new Set(), comodidades: new Set(), min: null, max: null, disp: 0 };

const categorias = [...new Set(rooms.map((r) => r.category))];
const comodidades = [...new Set(rooms.flatMap((r) => r.attributes))];

$("f-cats").innerHTML = categorias
  .map((c, i) => '<button type="button" class="cat cat--' + (i % 4) + '" aria-pressed="false" data-v="' + window.astirEsc(c) + '">' + window.astirEsc(c) + "</button>")
  .join("");
$("f-amen").innerHTML = comodidades
  .map((a) => '<button type="button" class="amen" aria-pressed="false" data-v="' + window.astirEsc(a) + '">' + window.astirEsc(a) + "</button>")
  .join("");

function alternar(contenedor, set) {
  contenedor.addEventListener("click", (e) => {
    const b = e.target.closest("button");
    if (!b) return;
    const v = b.dataset.v;
    const activo = !set.has(v);
    activo ? set.add(v) : set.delete(v);
    b.setAttribute("aria-pressed", String(activo));
    pintar();
  });
}
alternar($("f-cats"), filtros.categorias);
alternar($("f-amen"), filtros.comodidades);

$("search").addEventListener("input", (e) => { filtros.texto = e.target.value.trim().toLowerCase(); pintar(); });
$("f-min").addEventListener("input", (e) => { filtros.min = e.target.value === "" ? null : Number(e.target.value); pintar(); });
$("f-max").addEventListener("input", (e) => { filtros.max = e.target.value === "" ? null : Number(e.target.value); pintar(); });
$("f-av").addEventListener("input", (e) => { filtros.disp = Math.max(0, Number(e.target.value) || 0); pintar(); });
$("av-minus").addEventListener("click", () => { $("f-av").value = Math.max(0, Number($("f-av").value) - 1); $("f-av").dispatchEvent(new Event("input")); });
$("av-plus").addEventListener("click", () => { $("f-av").value = Number($("f-av").value) + 1; $("f-av").dispatchEvent(new Event("input")); });

// Abrir/cerrar panel
function panel(abrir) {
  $("filters").hidden = !abrir;
  document.body.classList.toggle("no-scroll", abrir);
}
$("open-filters").addEventListener("click", () => panel(true));
$("close-filters").addEventListener("click", () => panel(false));
$("filters-cover").addEventListener("click", () => panel(false));
document.addEventListener("keydown", (e) => { if (e.key === "Escape") panel(false); });

function pasa(r) {
  if (filtros.texto && r.name.toLowerCase().indexOf(filtros.texto) < 0) return false;
  if (filtros.categorias.size && !filtros.categorias.has(r.category)) return false;
  for (const a of filtros.comodidades) if (r.attributes.indexOf(a) < 0) return false;
  if (filtros.min !== null && r.price < filtros.min) return false;
  if (filtros.max !== null && r.price > filtros.max) return false;
  if (r.available < filtros.disp) return false;
  return true;
}

function pintar() {
  if (window.ASTIR.loadError) return;
  const lista = rooms.filter(pasa);
  if (!lista.length) {
    $("grid").innerHTML = '<p class="empty">No hay habitaciones con esos filtros.</p>';
    return;
  }
  if (modo === "comprar") {
    $("grid").innerHTML = lista.map((r) => window.astirRoomCard(r, "buy").replace('<article class="room"', '<article class="room room--list"')).join("");
  } else {
    $("grid").innerHTML = lista
      .map((r) => '<a class="room-link" href="habitacion.php?editar=1&amp;id=' + encodeURIComponent(r.id) + '">' + window.astirRoomCard(r, "list").replace('<article class="room"', '<article class="room room--list"') + "</a>")
      .join("");
  }
}
pintar();

// Comprar: cantidad y "Mover al carrito"
if (modo === "comprar") {
  $("grid").addEventListener("click", (e) => {
    const q = e.target.closest("[data-q]");
    if (q) {
      const input = q.parentElement.querySelector("input");
      const max = Number(input.max) || 1;
      input.value = Math.min(max, Math.max(1, Number(input.value) + Number(q.dataset.q)));
      return;
    }
    const add = e.target.closest("[data-add]");
    if (add) {
      const n = Number(add.closest(".room").querySelector("input").value);
      window.astirCart.add(add.dataset.add, n);
    }
  });
}

// Pagina de inicio: carrusel principal y fila de habitaciones.
const rooms = window.ASTIR.rooms || [];
const slides = rooms.filter(room => room.image).slice(0, 3);
if (!slides.length) slides.push({name: "Alojamiento"});

// Carrusel (cambia el texto e imagen al pulsar las flechas)
let actual = 0;
const texto = document.getElementById("carousel-text");
const imagen = document.querySelector(".carousel__img");

function mostrar(i) {
  actual = (i + slides.length) % slides.length;
  const s = slides[actual];
  texto.textContent = s.name;
  imagen.style.backgroundImage = s.image ? "url('" + s.image + "')" : "";
}
document.getElementById("prev").addEventListener("click", () => mostrar(actual - 1));
document.getElementById("next").addEventListener("click", () => mostrar(actual + 1));
mostrar(0);

// Habitaciones
document.getElementById("rooms-row").innerHTML = rooms.map((r) => window.astirRoomCard(r, "reserve")).join("") || (window.ASTIR.loadError ? "" : "<p>No hay habitaciones registradas.</p>");

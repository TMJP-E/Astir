// Pinta tarjetas de habitacion con el mismo formato en todas las pantallas.
(function () {
  const ICON_ATTR = '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/></svg>';

  function esc(t) {
    return String(t).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function chips(list) {
    return list.map(function (a) { return '<span class="chip">' + ICON_ATTR + esc(a) + "</span>"; }).join("");
  }

  function money(n) {
    return "$" + Number(n).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // variant "reserve": boton Reservar Ahora (inicio). variant "list": linea de precio (habitaciones).
  window.astirRoomCard = function (r, variant) {
    const photo = r.image
      ? '<img class="room__img" src="' + esc(r.image) + '" alt="' + esc(r.name) + '" loading="lazy">'
      : '<div class="room__img ph-img" aria-hidden="true"></div>';
    return (
      '<article class="room">' + photo +
        '<div class="room__price"><span>' + esc(r.available) + "</span></div>" +
        '<div class="room__body">' +
          '<h3 class="room__name">' + (variant === "buy" ? '<a href="habitacion.php?id=' + encodeURIComponent(r.id) + '">' + esc(r.name) + '</a>' : esc(r.name)) + '</h3>' +
          '<span class="tag">' + esc(r.category) + "</span>" +
          '<p class="room__desc">' + esc(r.description) + "</p>" +
          '<div class="chips">' + chips(r.attributes) + "</div>" +
        "</div>" +
        (variant === "buy"
          ? '<div class="room__buy">' +
              '<p class="room__pricerow">Precio: <span class="price">' + money(r.price) + "</span></p>" +
              '<div class="qty">Cantidad: <div class="stepper"><button type="button" data-q="-1" aria-label="Menos">−</button><input type="number" value="1" min="1" max="' + esc(r.available) + '" aria-label="Cantidad"><button type="button" data-q="1" aria-label="Más">+</button></div></div>' +
              '<button class="btn-cart" type="button" data-add="' + esc(r.id) + '"' + (r.available < 1 ? ' disabled' : '') + '>Mover al carrito</button>' +
            "</div>"
          : variant === "list"
          ? '<p class="room__pricerow">Precio: <span class="price">' + money(r.price) + "</span></p>"
          : '<div class="room__actions"><a class="btn-reserve" href="habitacion.php?id=' + encodeURIComponent(r.id) + '">Reservar Ahora</a></div>') +
      "</article>"
    );
  };
  window.astirChips = chips;
  window.astirMoney = money;
  window.astirEsc = esc;
})();

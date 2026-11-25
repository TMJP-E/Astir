// Navegación compartida según la sesión de PHP.
(function () {
  const role = window.ASTIR.role;
  const links = {
    admin: [
      ["Habitaciones", "comprar.php"],
      ["Reservaciones", "reservaciones.php"],
      ["Gestión", "habitaciones.php"],
      ["Perfil", "perfil.php"],
      ["Nosotros", "nosotros.php"],
    ],
    user: [
      ["Habitaciones", "comprar.php"],
      ["Reservaciones", "reservaciones.php"],
      ["Perfil", "perfil.php"],
      ["Nosotros", "nosotros.php"],
    ],
    anon: [["Nosotros", "nosotros.php"]],
  };

  const host = document.getElementById("app-nav");
  if (!host) return;
  // Visitante: icono de persona (login). Usuario/admin: carrito.
  const accountHref = role === "anon" ? "login.php" : "carrito.php";
  const accountIcon = role === "anon" ? "img/person.svg" : "img/cart.svg";
  const accountLabel = role === "anon" ? "Iniciar sesión" : "Carrito";

  host.innerHTML =
    '<header class="nav">' +
    '<div class="nav__left">' +
    '<button class="nav__btn" id="menu-btn" type="button" aria-label="Abrir menú"><img src="img/menu.svg" alt=""></button>' +
    '<a class="nav__logo" href="index.php">Astir</a>' +
    "</div>" +
    '<div class="nav__right">' +
    '<div class="nav__links">' +
    links[role]
      .map(function (l) {
        return '<a href="' + l[1] + '">' + l[0] + "</a>";
      })
      .join("") +
    "</div>" +
    '<a class="nav__btn" href="' +
    accountHref +
    '" aria-label="' +
    accountLabel +
    '"><img src="' +
    accountIcon +
    '" alt=""></a>' +
    "</div>" +
    "</header>" +
    '<div class="drawer-wrap" id="drawer" hidden>' +
    '<nav class="drawer" aria-label="Menú principal">' +
    '<div class="drawer__head">' +
    '<a class="nav__logo" href="index.php">Astir</a>' +
    '<button class="nav__btn" id="drawer-close" type="button" aria-label="Cerrar menú"><img src="img/arrow-back.svg" alt=""></button>' +
    "</div>" +
    links[role]
      .map(function (l) {
        return '<a class="drawer__link" href="' + l[1] + '">' + l[0] + "</a>";
      })
      .join("") +
    "</nav>" +
    '<button class="drawer__cover" id="drawer-cover" type="button" aria-label="Cerrar menú"></button>' +
    "</div>";

  const drawer = document.getElementById("drawer");
  function toggle(open) {
    drawer.hidden = !open;
    document.body.classList.toggle("no-scroll", open);
  }
  document.getElementById("menu-btn").addEventListener("click", function () {
    toggle(true);
  });
  document
    .getElementById("drawer-close")
    .addEventListener("click", function () {
      toggle(false);
    });
  document
    .getElementById("drawer-cover")
    .addEventListener("click", function () {
      toggle(false);
    });
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") toggle(false);
  });
})();

// Footer compartido (se monta en <div id="app-footer"></div>)
(function () {
  const host = document.getElementById("app-footer");
  if (!host) return;
  host.innerHTML =
    '<footer class="footer"><div class="footer__box">' +
    '<p class="footer__text">Astir es una compañía ficticia, cuyo proposito es gestionar hoteles, la información y funcionalidad proporcionada tiene fines puramente academicos.</p>' +
    '<a href="https://github.com/TMJP-E/Astir" aria-label="GitHub" target="_blank" rel="noopener">' +
    '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 .5C5.65.5.5 5.65.5 12c0 5.08 3.29 9.39 7.86 10.91.58.1.79-.25.79-.56v-2c-3.2.7-3.88-1.54-3.88-1.54-.52-1.33-1.28-1.69-1.28-1.69-1.05-.72.08-.7.08-.7 1.15.08 1.76 1.19 1.76 1.19 1.03 1.76 2.69 1.25 3.35.96.1-.75.4-1.25.73-1.54-2.55-.29-5.24-1.28-5.24-5.69 0-1.26.45-2.28 1.19-3.09-.12-.29-.52-1.46.11-3.05 0 0 .97-.31 3.17 1.18a11 11 0 0 1 5.78 0c2.2-1.49 3.17-1.18 3.17-1.18.63 1.59.23 2.76.11 3.05.74.81 1.19 1.83 1.19 3.09 0 4.42-2.69 5.39-5.25 5.68.41.36.78 1.06.78 2.14v3.17c0 .31.21.67.8.56A11.51 11.51 0 0 0 23.5 12C23.5 5.65 18.35.5 12 .5z"/></svg>' +
    "</a>" +
    "</div></footer>";
})();

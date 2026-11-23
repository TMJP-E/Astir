// Piezas de interfaz compartidas: snackbar y dialogo de confirmacion.
(function () {
  let timer = null;
  // Región viva fija: los lectores de pantalla solo anuncian cambios dentro de una región que ya existía.
  const region = document.createElement("div");
  region.setAttribute("role", "status");
  document.body.appendChild(region);

  // astirSnackbar("Mensaje", { action: "Deshacer", onAction: fn })
  window.astirSnackbar = function (mensaje, opciones) {
    opciones = opciones || {};
    const viejo = document.getElementById("snackbar");
    if (viejo) viejo.remove();
    clearTimeout(timer);

    const bar = document.createElement("div");
    bar.id = "snackbar";
    bar.className = "snackbar";
    const txt = document.createElement("span");
    txt.textContent = mensaje;
    bar.appendChild(txt);
    if (opciones.action) {
      const b = document.createElement("button");
      b.type = "button";
      b.textContent = opciones.action;
      b.addEventListener("click", function () {
        bar.remove();
        if (opciones.onAction) opciones.onAction();
      });
      bar.appendChild(b);
    }
    region.appendChild(bar);
    timer = setTimeout(function () { bar.remove(); }, opciones.ms || 5000);
  };

  // astirConfirm({ html: "¿Desea <em>eliminar</em>...?", sub: "texto pequeño", onConfirm: fn })
  // "html" debe armarse con astirEsc() para cualquier dato del usuario.
  window.astirConfirm = function (o) {
    // Un doble clic no debe abrir dos diálogos ni enviar la acción dos veces.
    if (document.querySelector(".dialog-wrap")) return;
    const wrap = document.createElement("div");
    wrap.className = "dialog-wrap";
    wrap.innerHTML =
      '<div class="dialog" role="alertdialog" aria-modal="true">' +
        "<p>" + o.html + "</p>" +
        (o.sub ? '<p class="dialog__sub">' + o.sub + "</p>" : "") +
        '<div class="dialog__actions">' +
          '<button type="button" class="dialog__cancel">Cancelar</button>' +
          '<button type="button" class="dialog__ok">Confirmar</button>' +
        "</div>" +
      "</div>";
    function cerrar() { wrap.remove(); document.removeEventListener("keydown", tecla); }
    function tecla(e) { if (e.key === "Escape") cerrar(); }
    wrap.querySelector(".dialog__cancel").addEventListener("click", cerrar);
    wrap.querySelector(".dialog__ok").addEventListener("click", function () { cerrar(); if (o.onConfirm) o.onConfirm(); });
    wrap.addEventListener("click", function (e) { if (e.target === wrap) cerrar(); });
    document.addEventListener("keydown", tecla);
    document.body.appendChild(wrap);
    wrap.querySelector(".dialog__cancel").focus();
  };
})();

async function astirSubmit(body) {
  body.set("csrf", window.ASTIR.csrf);
  let response;
  try {
    response = await fetch("actions.php", {method: "POST", body});
  } catch {
    throw new Error("No hay conexión con el servidor. Inténtalo de nuevo.");
  }
  // Si PHP imprime un aviso en HTML, la respuesta no es JSON válido.
  const result = await response.json().catch(() => ({}));
  if (!response.ok || !result.ok) throw new Error(result.error || "No fue posible completar la operación.");
}

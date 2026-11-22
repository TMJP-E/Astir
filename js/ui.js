// Piezas de interfaz compartidas: snackbar y dialogo de confirmacion.
(function () {
  let timer = null;

  // astirSnackbar("Mensaje", { action: "Deshacer", onAction: fn })
  window.astirSnackbar = function (mensaje, opciones) {
    opciones = opciones || {};
    const viejo = document.getElementById("snackbar");
    if (viejo) viejo.remove();
    clearTimeout(timer);

    const bar = document.createElement("div");
    bar.id = "snackbar";
    bar.className = "snackbar";
    bar.setAttribute("role", "status");
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
    document.body.appendChild(bar);
    timer = setTimeout(function () { bar.remove(); }, opciones.ms || 5000);
  };

  // astirConfirm({ html: "¿Desea <em>eliminar</em>...?", sub: "texto pequeño", onConfirm: fn })
  // "html" debe armarse con astirEsc() para cualquier dato del usuario.
  window.astirConfirm = function (o) {
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
  const response = await fetch("actions.php", {method: "POST", body});
  const result = await response.json();
  if (!response.ok || !result.ok) throw new Error(result.error || "No fue posible completar la operación.");
}

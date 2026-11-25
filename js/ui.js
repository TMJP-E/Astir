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
    timer = setTimeout(function () {
      bar.remove();
    }, opciones.ms || 5000);
  };

  // astirConfirm({ html: "¿Desea <em>eliminar</em>...?", sub: "texto pequeño", onConfirm: fn })
  // "html" y "sub" deben armarse con astirEsc() para cualquier dato del usuario.
  window.astirConfirm = function (o) {
    // Un doble clic no debe abrir dos diálogos ni enviar la acción dos veces.
    if (document.querySelector("dialog.dialog")) return;
    // showModal() atrapa el foco, cierra con Escape y al cerrar devuelve el foco al botón que lo abrió.
    const dlg = document.createElement("dialog");
    dlg.className = "dialog";
    dlg.setAttribute("role", "alertdialog");
    dlg.setAttribute("aria-labelledby", "dialog-pregunta");
    dlg.innerHTML =
      '<p id="dialog-pregunta">' +
      o.html +
      "</p>" +
      (o.sub ? '<p class="dialog__sub">' + o.sub + "</p>" : "") +
      '<div class="dialog__actions">' +
      '<button type="button" class="dialog__cancel">Cancelar</button>' +
      '<button type="button" class="dialog__ok">Confirmar</button>' +
      "</div>";
    dlg.addEventListener("close", function () {
      dlg.remove();
    });
    dlg.querySelector(".dialog__cancel").addEventListener("click", function () {
      dlg.close();
    });
    dlg.querySelector(".dialog__ok").addEventListener("click", function () {
      dlg.close();
      if (o.onConfirm) o.onConfirm();
    });
    // Un clic en el fondo oscuro tiene como destino el propio <dialog>; se compara con su caja para no cerrar al tocar el relleno.
    dlg.addEventListener("click", function (e) {
      const r = dlg.getBoundingClientRect();
      if (
        e.target === dlg &&
        (e.clientX < r.left ||
          e.clientX > r.right ||
          e.clientY < r.top ||
          e.clientY > r.bottom)
      )
        dlg.close();
    });
    document.body.appendChild(dlg);
    dlg.showModal();
  };
})();

function astirStepQuantity(input, step) {
  const next = input.valueAsNumber + step;
  if (
    !Number.isSafeInteger(next) ||
    next < Number(input.min) ||
    (input.max !== "" && next > Number(input.max))
  ) {
    astirSnackbar(
      input.max === ""
        ? "Ingresa una disponibilidad entera igual o mayor que cero."
        : "Selecciona una cantidad entre " +
            input.min +
            " y " +
            input.max +
            ".",
    );
    return false;
  }
  input.value = String(next);
  return true;
}

function astirValidFilters(min, max, available) {
  if (!min || !max) return true;
  max.setCustomValidity("");
  const rangeInvalid =
    min.value !== "" &&
    max.value !== "" &&
    min.valueAsNumber > max.valueAsNumber;
  if (rangeInvalid)
    max.setCustomValidity(
      "El precio máximo debe ser igual o mayor que el mínimo.",
    );
  for (const field of [min, max, available]) {
    if (field && !field.reportValidity()) return false;
  }
  return true;
}

async function astirSubmit(body) {
  body.set("csrf", window.ASTIR.csrf);
  let response;
  try {
    response = await fetch("actions.php", { method: "POST", body });
  } catch {
    throw new Error("No hay conexión con el servidor. Inténtalo de nuevo.");
  }
  // Si PHP imprime un aviso en HTML, la respuesta no es JSON válido.
  const result = await response.json().catch(() => ({}));
  if (!response.ok || !result.ok)
    throw new Error(result.error || "No fue posible completar la operación.");
}

// Cookie de sesión limitada al directorio de la aplicación.
(() => {
  const cookieName = "astir_cart";
  const uuid = /^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;
  const maxQuantity = 2147483647;
  const channel = typeof BroadcastChannel === "function"
    ? new BroadcastChannel("astir_cart:" + new URL(".", location.href).pathname) : null;

  function cookieValue() {
    const prefix = cookieName + "=";
    return document.cookie.split("; ").find(entry => entry.startsWith(prefix))?.slice(prefix.length) || "";
  }

  // UUID sin guiones y cantidad en base 36: hasta 100 habitaciones caben en una cookie.
  function read() {
    const items = [];
    for (const entry of cookieValue().split(".")) {
      const match = /^([0-9a-f]{8})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{12}):([0-9a-z]{1,6})$/i.exec(entry);
      if (!match) continue;
      const id = match.slice(1, 6).join("-");
      const qty = parseInt(match[6], 36);
      if (uuid.test(id) && qty > 0 && qty <= maxQuantity && !items.some(item => item.id === id)) {
        items.push({id, qty});
      }
    }
    return items;
  }

  function write(items) {
    try {
      const valid = items.length <= 100 && items.every(({id, qty}) =>
        uuid.test(id) && Number.isInteger(qty) && qty > 0 && qty <= maxQuantity);
      if (!valid) throw new Error();
      const value = items.map(({id, qty}) => id.replaceAll("-", "") + ":" + qty.toString(36)).join(".");
      const secure = location.protocol === "https:" ? "; Secure" : "";
      document.cookie = cookieName + "=" + value + "; SameSite=Lax" + secure + (value ? "" : "; Max-Age=0");
      if (cookieValue() !== value) throw new Error();
      channel?.postMessage("changed");
      return true;
    } catch {
      astirSnackbar("No fue posible guardar el carrito en este navegador.");
      return false;
    }
  }

  // Solo se proporciona esta clave a sesiones que ya tenían un carrito antes de la actualización.
  function migrateLegacyCart(key) {
    if (!key) return;
    try {
      const stored = localStorage.getItem(key);
      if (!stored) return;
      if (!cookieValue()) {
        const items = Object.entries(JSON.parse(stored))
          .filter(([id, qty]) => uuid.test(id) && Number.isInteger(qty) && qty > 0 && qty <= maxQuantity)
          .map(([id, qty]) => ({id, qty}));
        if (items.length && !write(items)) return;
      }
      localStorage.removeItem(key);
    } catch {
      astirSnackbar("No fue posible recuperar el carrito anterior en este navegador.");
    }
  }
  migrateLegacyCart(window.ASTIR.legacyCartKey);

  function add(id, qty) {
    const room = window.ASTIR.rooms.find(room => room.id === id);
    const items = read();
    const selected = items.find(item => item.id === id);
    const next = (selected?.qty || 0) + qty;
    if (!room || !Number.isSafeInteger(qty) || qty < 1 || next > room.available) {
      astirSnackbar("Revisa la cantidad y las unidades disponibles.");
      return;
    }
    if (!selected && items.length >= 100) {
      astirSnackbar("El carrito admite hasta 100 habitaciones distintas.");
      return;
    }
    if (selected) selected.qty = next;
    else items.push({id, qty});
    if (write(items)) astirSnackbar("Habitación agregada al carrito.", {
      action: "Ver carrito", onAction: () => location.assign("carrito.php")
    });
  }
  window.astirCart = {read, write, add, channel};
})();

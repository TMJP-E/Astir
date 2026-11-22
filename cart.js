// El formato persistido sigue siendo { UUID: cantidad } bajo la clave de la sesión PHP.
(() => {
  const key = window.ASTIR.storageKey;
  const uuid = /^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

  function read() {
    try {
      const stored = JSON.parse(localStorage.getItem(key) || "{}");
      if (!stored || typeof stored !== "object" || Array.isArray(stored)) return [];
      return Object.entries(stored)
        .filter(([id, qty]) => uuid.test(id) && Number.isSafeInteger(qty) && qty > 0)
        .map(([id, qty]) => ({id, qty}));
    } catch {
      return [];
    }
  }

  function write(items) {
    try {
      localStorage.setItem(key, JSON.stringify(Object.fromEntries(items.map(item => [item.id, item.qty]))));
      return true;
    } catch {
      astirSnackbar("No fue posible guardar el carrito en este navegador.");
      return false;
    }
  }

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
  window.astirCart = {read, write, add};
})();

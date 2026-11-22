const input = document.querySelector("[data-filter]");
const categories = new Set();
const min = document.getElementById("min-price");
const max = document.getElementById("max-price");
function filterRows() {
  const query = input.value.trim().toLocaleLowerCase();
  let count = 0;
  for (const row of document.querySelectorAll("#lista .row")) {
    const price = Number(row.dataset.price);
    row.hidden = !row.querySelector(".row__info").textContent.toLocaleLowerCase().includes(query)
      || (categories.size > 0 && !categories.has(row.dataset.category))
      || (min?.value && price < Number(min.value)) || (max?.value && price > Number(max.value));
    if (!row.hidden) count++;
  }
  const empty = document.getElementById("no-results");
  if (empty) empty.hidden = count > 0;
}
for (const field of [input, min, max]) field?.addEventListener("input", filterRows);
document.querySelectorAll("button[data-category]").forEach(button => {
  button.onclick = () => {
    const value = button.dataset.category;
    if (categories.has(value)) categories.delete(value);
    else categories.add(value);
    button.setAttribute("aria-pressed", String(categories.has(value)));
    filterRows();
  };
});
document.querySelectorAll("form[data-confirm]").forEach(form => {
  form.onsubmit = event => {
    event.preventDefault();
    const button = event.submitter;
    const description = button.name ? button.textContent + " · " + form.dataset.confirm : form.dataset.confirm;
    const label = document.createElement("span");
    label.textContent = description;
    astirConfirm({html: label.innerHTML, onConfirm: async () => {
      button.disabled = true;
      const body = new FormData(form);
      if (button.name) body.set(button.name, button.value);
      try {
        await astirSubmit(body);
        location.reload();
      } catch (error) {
        astirSnackbar(error.message);
        button.disabled = false;
      }
    }});
  };
});


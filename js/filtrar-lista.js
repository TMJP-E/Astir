const input = document.querySelector("[data-filter]");
const categoryButtons = document.querySelectorAll("button[data-category]");
const categories = new Set(Array.from(categoryButtons).filter(b => b.getAttribute("aria-pressed") === "true").map(b => b.dataset.category));
const min = document.getElementById("min-price");
const max = document.getElementById("max-price");
function filterRows() {
  if (!astirValidFilters(min, max)) return;
  const query = input.value.trim().toLocaleLowerCase();
  let count = 0;
  for (const row of document.querySelectorAll("#lista .row")) {
    const price = Number(row.dataset.price);
    row.hidden = !(row.dataset.search ?? row.querySelector(".row__info").textContent.toLowerCase()).includes(query)
      || (categories.size > 0 && !categories.has(row.dataset.category))
      || (min?.value && price < Number(min.value)) || (max?.value && price > Number(max.value));
    if (!row.hidden) count++;
  }
  const empty = document.getElementById("no-results");
  if (empty) empty.hidden = count > 0;
}
for (const field of [input, min, max]) field?.addEventListener("input", filterRows);
categoryButtons.forEach(button => {
  button.onclick = () => {
    const value = button.dataset.category;
    if (categories.has(value)) categories.delete(value);
    else categories.add(value);
    button.setAttribute("aria-pressed", String(categories.has(value)));
    filterRows();
  };
});
const searchForm = document.getElementById("booking-search");
if (searchForm) {
  filterRows();
  searchForm.addEventListener("submit", event => {
    if (!astirValidFilters(min, max)) event.preventDefault();
  });
  searchForm.addEventListener("formdata", event => {
    for (const category of categories) event.formData.append("categories[]", category);
  });
}
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


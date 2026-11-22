// Validacion del registro. Los errores salen debajo de cada campo.
const form = document.getElementById("registro-form");
const nombre = document.getElementById("nombre");
const email = document.getElementById("email");
const password = document.getElementById("password");
const confirmar = document.getElementById("confirm");
const passwordError = document.getElementById("password-error");

function marcar(input, valido) {
  input.closest(".field").classList.toggle("field--invalid", !valido);
}

// Devuelve la lista de caracteristicas que le faltan a la contraseña.
function faltantes(valor) {
  const falta = [];
  if (valor.length < 12) falta.push("mínimo 12 caracteres");
  if (!/[0-9]/.test(valor)) falta.push("un número");
  if (!/[A-Z]/.test(valor)) falta.push("una mayúscula");
  if (!/[a-z]/.test(valor)) falta.push("una minúscula");
  if (!/[^A-Za-z0-9]/.test(valor)) falta.push("un símbolo");
  return falta;
}

form.addEventListener("submit", (e) => {

  const nombreOk = nombre.value.trim().length > 0;
  const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim());
  const falta = faltantes(password.value);
  const passOk = falta.length === 0;
  const confirmOk = confirmar.value === password.value && passOk;

  passwordError.textContent = passOk ? "" :
    "La contraseña no tiene " + falta.length + " característica" + (falta.length > 1 ? "s" : "") + ": " + falta.join(", ") + ".";

  marcar(nombre, nombreOk);
  marcar(email, emailOk);
  marcar(password, passOk);
  marcar(confirmar, confirmOk);
  if (!(nombreOk && emailOk && passOk && confirmOk)) e.preventDefault();
});

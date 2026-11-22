// Validacion basica del formulario de login.
const form = document.getElementById("login-form");
const email = document.getElementById("email");
const password = document.getElementById("password");

function marcar(input, valido) {
  input.closest(".field").classList.toggle("field--invalid", !valido);
}

form.addEventListener("submit", (e) => {

  const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim());
  const passOk = password.value.length > 0;
  marcar(email, emailOk);
  marcar(password, passOk);
  if (!emailOk || !passOk) e.preventDefault();
});

# Astir

Aplicacion de gestion de reservaciones hoteleras.

## Funcionalidades

General

- [ ] Registro de usuario: Los usuarios se registran con nombre, correo y contraseña, por defecto, todos los usuarios son usuarios comunes.
- [ ] Inicio de sesion: Acceso a una cuenta ya existente con el correo y contraseña adecuados.
- [ ] Perfil de usuario: Muestra datos personales y cierre de sesion.

Gestion del catalogo

- [ ] Agregar habitacion: Las habitaciones cuentan con nombre, descripcion, categoria, precio, cantidad e imagen.
- [ ] Editar habitacion.
- [ ] Eliminar habitacion.
- [ ] Busqueda de habitaciones.
- [ ] Filtrado de busqueda por categoria.

Reservaciones

- [ ] Busqueda para reservaciones
- [ ] Filtrado de busqueda.
- [ ] Vista previa y extendida.

Carrito

- [ ] Agregar al carrito.
- [ ] Editar numero de resrvas.
- [ ] Presentar costos subtotales y totales.

### Caracteristicas

General

- [ ] Pagina de introduccion y acceso limitado a usuarios sin sesion.
- [ ] Validar todas las entradas.
- [ ] Cada interaccion inadecuada del usuario debepresentar un mensaje de error.

Gestion del catalogo

- [ ] Solo administradores pueden gestionar los elementos del catalogo.

Reservaciones

- [ ] Se guardan los resultados de busquedas anteriores.
- [ ] Las busquedas se realizan mediante peticiones GET.

Carrito

- [ ] Se almacena utilizando JSON y `localstorage`.
- [ ] Valida el numero maximo de reservas.

Manejo de Sesion

- [ ] Se almacena la sesion mediante un header `HTTPOnly`, se genera y valida a traves del servidor.
- [ ] El rol del usuario se almacena en `localstorage`.
- [ ] Validacion para paginas protegidas que requieren una sesion.

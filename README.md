# Astir

Aplicacion de gestion de reservaciones hoteleras.

## Instalacion

Para instalar y ejecutar la aplicacion, es necesario contar con la version 8.2.12-0 o superior de XAMPP, los modulos de servidor web y MySQL e importar la base de datos con anterioridad.

Todos los archivos de la aplicacion se colocan en una carpeta, esa carpeta se coloca dentro de `htdocs` en la instalacion de XAMPP.

## Funcionalidades

General

- [ ] Registro de usuario: Los usuarios se registran con nombre, correo y contraseña, por defecto, todos los usuarios son usuarios comunes.
- [ ] Inicio de sesion: Acceso a una cuenta ya existente con el correo y contraseña adecuados.
- [ ] Perfil de usuario: Muestra datos personales y cierre de sesion.

Gestion

- [ ] Agregar habitacion: Las habitaciones cuentan con nombre, descripcion, categoria, precio, cantidad e imagen.
- [ ] Editar habitacion.
- [ ] Eliminar habitacion.
- [ ] Busqueda de habitaciones.
- [ ] Filtrado de busqueda por categoria.
- [ ] Ver las reservaciones realizadas.
- [ ] Cambiar los tipos de usuario.

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

Gestion

- [ ] Solo administradores pueden gestionar los elementos del catalogo.

Reservaciones

- [ ] Se guardan los resultados de busquedas anteriores.

Carrito

- [ ] Se almacena utilizando JSON y `localstorage`.
- [ ] Valida el numero maximo de reservas.

Manejo de Sesion

- [ ] Se almacena la sesion mediante un header `HTTPOnly`, se genera, guarda y valida a traves del servidor.
- [ ] El rol del usuario se almacena en `localstorage`.
- [ ] Validacion para paginas protegidas que requieren una sesion.

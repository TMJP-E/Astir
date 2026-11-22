# Astir

Aplicacion de gestion de reservaciones hoteleras.

## Instalacion

Para instalar y ejecutar la aplicacion, es necesario contar con la version 8.2.12-0 o superior de XAMPP, los modulos de servidor web y MySQL e importar la base de datos con anterioridad.

Coloca el proyecto en `htdocs/Proyecto-DAW`. Inicia Apache y MySQL, crea la base `urbano` con codificación `utf8mb4` e importa `urbano.sql` en la base vacía. Si utilizas otro nombre o credenciales, ajústalos en `config.inc.php`. Abre `http://localhost/Proyecto-DAW/`.

La portada, el catálogo y el carrito admiten visitas sin cuenta. Al confirmar una reservación se solicita iniciar sesión. La confirmación registra las habitaciones y actualiza las existencias en MariaDB. El proyecto no procesa pagos con tarjeta.

La sección **Gestión** del menú se muestra a los administradores. Desde allí se administran habitaciones, reservaciones y roles. Las notas y los frames en `docs/` corresponden al prototipo de diseño.

## Funcionalidades

General

- [x] Registro de usuario: Los usuarios se registran con nombre, correo y contraseña, por defecto, todos los usuarios son usuarios comunes.
- [x] Inicio de sesion: Acceso a una cuenta ya existente con el correo y contraseña adecuados.
- [x] Perfil de usuario: Muestra datos personales y cierre de sesion.

Gestion

- [x] Agregar habitacion: Las habitaciones cuentan con nombre, descripcion, categoria, precio, cantidad e imagen.
- [x] Editar habitacion.
- [x] Eliminar habitacion.
- [x] Busqueda de habitaciones.
- [x] Filtrado de busqueda por categoria.
- [x] Ver las reservaciones realizadas.
- [x] Cambiar los tipos de usuario.

Reservaciones

- [x] Busqueda para reservaciones
- [x] Filtrado de busqueda.
- [x] Vista previa y extendida.

Carrito

- [x] Agregar al carrito.
- [x] Editar número de reservas.
- [x] Presentar costos subtotales y totales.

### Caracteristicas

General

- [x] Pagina de introduccion y acceso limitado a usuarios sin sesion.
- [ ] Validar todas las entradas.
- [ ] Cada interaccion inadecuada del usuario debe presentar un mensaje de error.

Gestion

- [x] Solo administradores pueden gestionar los elementos del catalogo.

Reservaciones

- [ ] Se guardan los resultados de busquedas anteriores.

Carrito

- [x] Cada sesión del navegador tiene una clave propia en `localStorage`. El JSON asocia el UUID de cada habitación con su cantidad.
- [x] Valida el numero maximo de reservas.

Manejo de Sesion

- [x] El servidor genera y valida la sesión. La cookie de sesión utiliza `HttpOnly`.
- [x] El servidor conserva el UUID y el rol del usuario en la sesión PHP y consulta el rol vigente para autorizar el acceso.
- [x] Validacion para paginas protegidas que requieren una sesion.

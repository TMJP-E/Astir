# Astir

Aplicacion de gestion de reservaciones hoteleras.

## Instalacion

Para instalar y ejecutar la aplicacion, es necesario contar con la version 8.2.12-0 o superior de XAMPP, los modulos de servidor web y MySQL e importar la base de datos con anterioridad.

Coloca el proyecto en una carpeta dentro de `htdocs`, por ejemplo `htdocs/Proyecto-DAW`. El nombre de la carpeta puede ser cualquiera, porque todas las rutas son relativas. Inicia Apache y MySQL, crea la base `urbano` con codificación `utf8mb4` e importa `urbano.sql` en la base vacía. Si utilizas otro nombre de base o credenciales, ajústalos en `config.inc.php`. Abre `http://localhost/Proyecto-DAW/` (o el nombre de tu carpeta).

En macOS y Linux, Apache de XAMPP se ejecuta con otro usuario del sistema y necesita permiso para guardar las imágenes de las habitaciones. Desde la carpeta del proyecto ejecuta:

```bash
chmod 777 room_images
```

En Windows no hace falta este paso.

### Cuentas de prueba

`urbano.sql` crea usuarios de prueba. Las cuentas nuevas creadas desde el registro siempre son usuarios comunes, así que para entrar a **Gestión** usa una cuenta de administrador:

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | `ana.garcia@gmail.com` | `UrbanoDemo2026!` |
| Usuario | `sofia.ramirez@gmail.com` | `ClientePrueba26!` |

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
- [x] Eliminar habitacion. **Atención:** al eliminar una habitación también se eliminan todas sus reservaciones.
- [x] Busqueda de habitaciones.
- [x] Filtrado de busqueda por categoria.
- [x] Ver las reservaciones realizadas.
- [x] Cancelar reservaciones de cualquier cliente, una unidad o todas.
- [x] Cambiar los tipos de usuario.

Reservaciones

- [x] Busqueda para reservaciones
- [x] Filtrado de busqueda.
- [x] Vista previa y extendida.
- [x] Cancelar las reservaciones propias, una unidad o todas.

Carrito

- [x] Agregar al carrito.
- [x] Editar número de reservas.
- [x] Presentar costos subtotales y totales.
- [x] Deshacer al quitar una habitación del carrito.

### Caracteristicas

General

- [x] Pagina de introduccion y acceso limitado a usuarios sin sesion.
- [x] Validar todas las entradas. Los formularios, filtros y acciones comprueban los datos recibidos. Las imágenes admiten hasta 8 MB por archivo (JPEG, PNG, GIF o WebP).
- [x] Cada interacción inadecuada del usuario debe presentar un mensaje de error. Por ejemplo, se indica cuándo el rango de precios está invertido, la cantidad supera las existencias o la sesión ha caducado.

Gestion

- [x] Solo administradores pueden gestionar los elementos del catalogo.

Reservaciones

- [x] Se guardan los resultados de búsquedas anteriores. En **Mis reservaciones** y **Gestión de reservaciones**, pulsa **Buscar** o **Enter** para guardar una copia de los resultados del momento. Cada pantalla conserva sus últimas 25 búsquedas en **Historial de búsquedas** durante la sesión, incluidas las que tuvieron cero coincidencias. Cerrar sesión descarta ambos historiales.

Carrito

- [x] JavaScript guarda el UUID y la cantidad de cada habitación en una cookie de sesión. El carrito se conserva al iniciar sesión y se vacía al cerrarla.
- [x] Valida el numero maximo de reservas.

Manejo de Sesion

- [x] El servidor genera y valida la sesión. La cookie de sesión utiliza `HttpOnly`.
- [x] El servidor conserva el UUID y el rol del usuario en la sesión PHP y consulta el rol vigente para autorizar el acceso.
- [x] Validacion para paginas protegidas que requieren una sesion.

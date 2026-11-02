# Astir

Aplicacion de gestion de reservaciones hoteleras.

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
- [ ] Las busquedas se realizan mediante peticiones GET.

Carrito

- [ ] Se almacena utilizando JSON y `localstorage`.
- [ ] Valida el numero maximo de reservas.

Manejo de Sesion

- [ ] Se almacena la sesion mediante un header `HTTPOnly`, se genera y valida a traves del servidor.
- [ ] El rol del usuario se almacena en `localstorage`.
- [ ] Validacion para paginas protegidas que requieren una sesion.

## Base de Datos

### Codigo para [dbdiagram.io](https://dbdiagram.io/d)

```dbdiagram.io
Project astir {
  database_type: 'MySQL'
}

Table Users {
  uuid binary(16) [pk, not null]

  name varchar(48) [not null]

  email varchar(96) [not null, unique]

  password varchar(60) [not null, note: 'Bcrypt hashed by the backend']

  role enum('regular', 'admin') [not null, default: 'regular']
}

Table Rooms {
  uuid binary(16) [pk, not null]

  id int [not null, unique]

  name varchar(96) [not null]

  description varchar(512) [not null]

  category enum('express', 'regular', 'comfort', 'suite') [not null]

  price decimal(10,2) [not null]

  available int [not null, note: 'Must be >= 0']

  image varchar(128)

  indexes {
    category [name: 'idx_rooms_category']
  }
}

Table Commodities {
  type varchar(64) [pk, not null]
}

Table Room_Commodities {
  room_uuid binary(16) [not null]

  type varchar(64) [not null]

  indexes {
    (room_uuid, type) [pk]
    type [name: 'idx_room_commodities_type']
  }
}

Table User_Rooms {
  user_uuid binary(16) [not null]

  room_uuid binary(16) [not null]

  quantity int [not null, note: 'Must be > 0']

  indexes {
    (user_uuid, room_uuid) [pk]
    room_uuid [name: 'idx_user_rooms_room']
  }
}

Ref: Room_Commodities.room_uuid > Rooms.uuid

Ref: Room_Commodities.type > Commodities.type

Ref: User_Rooms.user_uuid > Users.uuid

Ref: User_Rooms.room_uuid > Rooms.uuid
```

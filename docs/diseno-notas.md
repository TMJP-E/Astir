# Notas del diseño (Figma "Negocios" - Astir)

Referencia para continuar el proyecto sin volver a explorar todo el Figma.
`docs/figma-estructura.xml` es la lista completa de frames (id, nombre, tamaño, posición) de la página `0:1`.

## Tokens
Colores: fondo `#FFFFFA`, primario `#44A1A0`, secundario `#247B7B`, terciario `#0D5C63`, contraste `#000`,
sombra `#484848`, tinte `#DCDCDC`, tinte primario `#78CDD7`, análogo `#789DD7`, acento verde `#73A144`,
error `#A14445`, dim `#B0B0B0`, cubierta `rgba(0,0,0,.6)`, botón carrito `#78D7B4`.
Tipografías: Aboreto (logo y títulos), Questrial (texto), Lora Bold/Bold Italic (banner del inicio).
Tamaños de texto: 48, 36, 32, 24, 20, 16, 14, 12, 10.

## Breakpoints
Small 360-639, Medium 640-1023, Large 1024-1919. En Large la barra superior muestra los enlaces en línea
(orden de izquierda a derecha: Nosotros, Perfil, Reservaciones, Habitaciones; sin hamburguesa).

## Roles y navegación (menú lateral)
- Admin: Habitaciones, Reservaciones, Gestión, Perfil, Nosotros
- User: Habitaciones, Reservaciones, Perfil, Nosotros
- Anon: Perfil, Nosotros
Ícono derecho de la barra: persona (visitante) o carrito (usuario/admin).

## Pantallas ya construidas
Login, Registro, Inicio, Habitaciones (con panel de Filtros), Comprar, Habitación (detalle), Carrito (lista, vacío,
pago, éxito, error), menú lateral, footer, snackbar y diálogos de confirmación.

## Pantallas construidas a partir de lo observado
- **Gestión (admin)**: barra gris con título y flecha que despliega las secciones Habitaciones, Reservaciones y Usuarios.
- **Usuarios**: búsqueda; avatar, nombre, correo, rol ("Usuario" `#44A1A0` / "Administrador" `#A14445`);
  botón "Otorgar Administrador" (rojo claro) o "Revocar Administrador" (rojo oscuro).
- **Reservaciones (admin)**: foto, nombre, "Cliente:", "Cantidad:"; "Retirar todos" (solo si cantidad > 1) y "Retirar 1".
- **Reservaciones (usuario)**: búsqueda + filtros; foto, nombre, cantidad, precio (o precio individual si cantidad > 1);
  "Cancelar todas" (solo si cantidad > 1) y "Cancelar 1".
- **Perfil**: avatar circular, nombre, correo gris, rol y botón rojo "Cerrar Sesión".
- **Nosotros**: título ASTIR y cuatro bloques de texto con imagen, alternando lado (derecha, izquierda, derecha, izquierda).

## Diálogos de confirmación ya vistos
- Eliminar: "¿Desea **eliminar** la habitación NOMBRE DE HABITACIÓN?" + "Esta acción **no** se puede deshacer."
- Guardar: "¿Desea guardar los cambios de la habitación NOMBRE DE HABITACIÓN?"
- Cancelar: "¿Desea cancelar los cambios de la habitación NOMBRE DE HABITACIÓN?"
Botones: Cancelar (`#44A1A0`) y Confirmar (`#A14445`).
- Retirar 1 (admin): "¿Desea retirar **una** reservación del cliente NOMBRE DEL CLIENTE?" + "Esta acción **no** se puede deshacer."
- Cancelar 1 (usuario): "¿Desea cancelar **una** reservación de la habitación NOMBRE DE HABITACIÓN?" + misma frase.
- Las variantes "todas" y los de otorgar/revocar administrador se redactaron por analogía (no vistos en Figma).
Snackbar: fondo negro, texto `#DCDCDC` 20 px, acción "Deshacer" en `#78CDD7`.

## Anotaciones del diseño que ya se aplicaron
- Registro: los errores salen debajo de cada campo; la contraseña lista las características que faltan.

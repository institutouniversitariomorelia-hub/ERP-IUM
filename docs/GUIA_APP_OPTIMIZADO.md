# 📘 Guía Detallada: app-optimized.js

## Tabla de Contenidos
- [Introducción](#introducción)
- [Arquitectura General](#arquitectura-general)
- [Módulos del Sistema](#módulos-del-sistema)
- [Flujo de Datos](#flujo-de-datos)
- [Impacto en Otros Archivos](#impacto-en-otros-archivos)
- [Guía de Mantenimiento](#guía-de-mantenimiento)

---

## Introducción

**app-optimized.js** es el archivo JavaScript principal del sistema ERP IUM. Contiene toda la lógica del frontend organizada en módulos independientes que gestionan:

- **Usuarios y perfiles**
- **Ingresos con pagos divididos**
- **Egresos vinculados a presupuestos**
- **Categorías**
- **Presupuestos jerárquicos**
- **Sistema de alertas**
- **Auditoría**

### Ventajas de la Nueva Arquitectura

✅ **Encapsulación**: Cada módulo tiene su propio scope privado  
✅ **Mantenibilidad**: Código organizado por funcionalidad  
✅ **Escalabilidad**: Fácil agregar nuevos módulos  
✅ **Debugging**: Logs estructurados por módulo  
✅ **Sin contaminación global**: Solo expone lo necesario  

---

## Arquitectura General

```javascript
ERPUtils (Módulo de Utilidades)
    ├── ajaxCall()
    ├── mostrarError()
    ├── ensureNumberEditable()
    └── escapeHtml()

UsuariosModule
    ├── Toggle de tabla usuarios
    ├── Editar perfil
    ├── Cambiar contraseña
    └── Gestión de usuarios

IngresosModule
    ├── Modal de ingreso
    ├── Sistema de pagos divididos
    ├── Validación de montos
    └── Buscador con filtros

EgresosModule
    ├── Modal de egreso
    ├── Vinculación con presupuestos
    └── Buscador con filtros

CategoriasModule
    ├── CRUD de categorías
    └── Refresh de tabla

PresupuestosModule
    ├── Presupuestos generales
    ├── Sub-presupuestos
    └── Jerarquía padre-hijo

AlertasPresupuestosModule
    ├── Badge en sidebar
    └── Actualización automática

AuditoriaModule
    └── Visor de detalles

SidebarModule
    └── Comportamiento responsive

Inicialización Global
    └── $(document).ready()
```

---

## Módulos del Sistema

### 1️⃣ ERPUtils (Utilidades Globales)

**Propósito**: Proporcionar funciones reutilizables en todo el sistema.

#### `ajaxCall(controller, action, data, method)`

**Descripción**: Wrapper para llamadas AJAX al backend.

**Parámetros**:
- `controller` (string): Nombre del controlador PHP (ej: 'user', 'ingreso')
- `action` (string): Método del controlador (ej: 'save', 'delete')
- `data` (object): Datos a enviar
- `method` (string): Método HTTP ('GET', 'POST', 'PUT', 'DELETE')

**Retorna**: jqXHR Promise

**Ejemplo de uso**:
```javascript
ajaxCall('ingreso', 'save', { monto: 1000 }, 'POST')
    .done(response => console.log(response))
    .fail(error => console.error(error));
```

**Impacto en otros archivos**:
- **Backend**: Enruta a `controllers/*Controller.php`
- **index.php**: Lee parámetros `controller` y `action`

---

#### `mostrarError(action, jqXHR)`

**Descripción**: Muestra mensajes de error amigables al usuario.

**Parámetros**:
- `action` (string): Descripción de la acción fallida
- `jqXHR` (object): Objeto de error jQuery

**Funcionalidad**:
1. Extrae el mensaje de error del servidor
2. Parsea errores PHP (Fatal error, Warning, Exception)
3. Muestra alert con detalle técnico
4. Registra en consola para debugging

**Ejemplo de uso**:
```javascript
ajaxCall('user', 'delete', { id: 5 })
    .fail(xhr => mostrarError('eliminar usuario', xhr));
```

---

#### `ensureNumberEditable(selector)`

**Descripción**: Asegura que un campo numérico sea editable y valide la entrada.

**Parámetros**:
- `selector` (string): Selector jQuery del elemento

**Funcionalidad**:
1. Quita atributos `readonly` y `disabled`
2. Agrega validación en tiempo real
3. Permite solo números y punto decimal
4. Previene múltiples puntos decimales

**Ejemplo de uso**:
```javascript
ensureNumberEditable('#eg_monto'); // Campo de monto en egresos
```

---

#### `escapeHtml(str)`

**Descripción**: Escapa caracteres HTML para prevenir XSS.

**Parámetros**:
- `str` (string): Cadena a escapar

**Retorna**: string escapado

**Ejemplo de uso**:
```javascript
const nombre = escapeHtml(userInput); // Previene inyección de código
```

---

### 2️⃣ UsuariosModule

**Propósito**: Gestionar usuarios, perfiles y cambios de contraseña.

#### `initToggleUsuarios()`

**Descripción**: Inicializa el botón que muestra/oculta la tabla de usuarios.

**Elementos DOM**:
- `#btnToggleUsuarios` - Botón toggle
- `#seccionUsuariosRegistrados` - Tabla de usuarios
- `#toggleUsuariosIcon` - Ícono chevron
- `#toggleUsuariosText` - Texto del botón

**Animación**: slideUp/slideDown (300ms)

**Impacto en otros archivos**:
- **views/profile.php**: Contiene la sección toggleable

---

#### `initModalEditarPerfil()`

**Descripción**: Maneja la apertura del modal de editar perfil.

**Elementos DOM**:
- `#modalEditarMiPerfil` - Modal Bootstrap 5
- `#formEditarMiPerfil` - Formulario
- `#perfil_id`, `#perfil_nombre`, `#perfil_username`, `#perfil_rol` - Campos

**Funcionalidad**:
1. Reset del formulario
2. Detecta si es edición de mi perfil o de otro usuario
3. Carga datos desde atributos `data-*` del botón
4. Usa variable global `CURRENT_USER` (viene de PHP)

**Impacto en otros archivos**:
- **views/layout.php**: Modal HTML
- **views/profile.php**: Botones que abren el modal

---

#### `initSubmitEditarPerfil()`

**Descripción**: Guarda los cambios del perfil.

**Backend**:
- **Controller**: `UserController::save()`
- **Acción**: Actualiza tabla `usuarios`

**Flujo**:
1. Serializa formulario
2. Envía via AJAX a `user/save`
3. Si success → reload página
4. Si error → muestra alert

---

#### `initCambiarPassword()`

**Descripción**: Sistema completo de cambio de contraseña.

**Sub-funciones**:

1. **Abrir modal**:
   - Cierra modal de perfil
   - Abre modal de contraseña
   - Pre-llena campo `username`

2. **Toggle mostrar/ocultar contraseñas**:
   - Botones con íconos `eye-outline` / `eye-off-outline`
   - Cambia type entre `password` y `text`

3. **Validación en tiempo real**:
   - Compara `password_nueva` con `password_confirmar`
   - Muestra mensaje: "✓ Coinciden" o "✗ No coinciden"
   - Deshabilita botón submit si no coinciden

4. **Submit**:
   - Valida contraseña actual
   - Verifica coincidencia
   - Backend: `AuthController::changePasswordWithValidation()`
   - Si success → logout automático

**Elementos DOM**:
- `#modalCambiarPasswordNuevo`
- `#formCambiarPasswordNuevo`
- `#password_actual`, `#password_nueva`, `#password_confirmar`
- `#passwordMatchMessage` - Mensaje de validación
- `#togglePasswordActual`, `#togglePasswordNueva`, `#togglePasswordConfirmar`

**Backend**:
- **Controller**: `AuthController::changePasswordWithValidation()`
- **Validación**: Verifica contraseña actual con hash bcrypt

---

#### `initGestionUsuarios()`

**Descripción**: CRUD de usuarios (crear y eliminar).

**Funcionalidad**:

1. **Modal registrar usuario**:
   - Reset del formulario
   - Campo `password` requerido (nuevo usuario)

2. **Submit nuevo usuario**:
   - Backend: `UserController::save()`
   - Crea registro en tabla `usuarios`

3. **Eliminar usuario**:
   - Confirmación con `confirm()`
   - Backend: `UserController::delete()`
   - Elimina de tabla `usuarios`

**Impacto en otros archivos**:
- **models/UserModel.php**: Métodos save() y delete()
- **views/profile.php**: Tabla con botones eliminar

---

### 3️⃣ IngresosModule

**Propósito**: Gestionar ingresos con sistema de pagos divididos (cobro mixto).

#### `agregarFilaPago(metodo, monto)`

**Descripción**: Agrega una fila de pago parcial al formulario.

**Parámetros**:
- `metodo` (string): Método de pago (Efectivo, Transferencia, etc.)
- `monto` (number): Monto del pago

**Elementos generados**:
```html
<div class="row pago-parcial-item" data-pago-id="N">
    <select class="pago-metodo">...</select>
    <input class="pago-monto" type="number">
    <button class="btn-eliminar-pago">🗑️</button>
</div>
```

**Variable global**: `contadorPagos` - Contador de filas

---

#### `actualizarBotonesEliminar()`

**Descripción**: Controla visibilidad de botones eliminar.

**Lógica**:
- Si solo hay 1 fila → oculta botón
- Si hay 2+ filas → muestra botones

**Razón**: Debe mantener al menos un método de pago.

---

#### `actualizarResumenPagos()`

**Descripción**: Actualiza el resumen de validación de pagos divididos.

**Cálculos**:
1. `montoTotal` = valor de `#in_monto`
2. `sumaParciales` = suma de todos `.pago-monto`
3. `diferencia` = montoTotal - sumaParciales

**Estados visuales**:
- ✓ **Cuadrado** (diferencia < 0.01): Badge verde
- ⚠ **Pendiente** (diferencia > 0): Badge rojo "FALTA"
- ⚠ **Exceso** (diferencia < 0): Badge amarillo "SOBRA"

**Elementos DOM**:
- `#display_monto_total`
- `#display_suma_parciales`
- `#display_diferencia`
- `#label_diferencia`

---

#### `initModalIngreso()`

**Descripción**: Maneja la apertura del modal de ingreso.

**Flujo**:

1. **Reset del formulario**:
   - Limpia todos los campos
   - Reinicia contador de pagos
   - Oculta sección de cobro dividido

2. **Cargar categorías**:
   - Backend: `IngresoController::getCategoriasIngreso()`
   - Solo categorías tipo "Ingreso"
   - Popula `#in_id_categoria`

3. **Si es edición**:
   - Backend: `IngresoController::getIngresoData()`
   - Carga datos del ingreso
   - Si tiene `pagos_parciales`:
     - Activa toggle cobro dividido
     - Crea filas por cada pago
   - Si tiene `metodo_de_pago` único:
     - Mantiene pago único
     - Llena `#in_metodo_unico`

**Elementos DOM**:
- `#modalIngreso`
- `#formIngreso`
- `#in_id_categoria` - Selector de categorías
- `#ingreso_id` - ID oculto (edición)
- `#toggleCobroDividido` - Switch pago único/dividido

**Backend**:
- `IngresoController::getCategoriasIngreso()` - Lista categorías
- `IngresoController::getIngresoData($id)` - Datos del ingreso
- `models/IngresoModel.php` - Queries

**Impacto en otros archivos**:
- **views/layout.php**: Modal HTML
- **views/ingresos_list.php**: Botones editar

---

#### `initTogglePagosDivididos()`

**Descripción**: Maneja el switch entre pago único y cobro dividido.

**Eventos**:

1. **Change en `#toggleCobroDividido`**:
   - Si está activo:
     - Oculta `#seccion_pago_unico`
     - Muestra `#seccion_cobro_dividido`
     - Agrega 2 filas de pago por defecto
   - Si está inactivo:
     - Muestra `#seccion_pago_unico`
     - Oculta `#seccion_cobro_dividido`
     - Copia monto total a `#in_monto_unico`

2. **Input en `#in_monto`**:
   - Actualiza `#in_monto_unico`
   - Recalcula resumen de pagos

3. **Click en `#btnAgregarPago`**:
   - Llama `agregarFilaPago()`
   - Agrega nueva fila vacía

4. **Click en `.btn-eliminar-pago`**:
   - Elimina fila si hay 2+
   - Actualiza resumen y botones

5. **Input en `.pago-monto`**:
   - Recalcula resumen en tiempo real

---

#### `initSubmitIngreso()`

**Descripción**: Maneja el envío del formulario de ingreso.

**Validación**:

**Pago Único**:
- Verifica que `#in_metodo_unico` tenga valor
- Crea JSON: `[{metodo: X, monto: Y}]`

**Cobro Dividido**:
- Valida que todas las filas tengan método y monto
- Suma todos los montos parciales
- Verifica que diferencia < 0.01
- Si no cuadra → alert con diferencia

**Backend**:
- **Controller**: `IngresoController::save()`
- **Parámetros**:
  - Datos del formulario
  - `metodo_de_pago`: "Mixto" o método único
  - `pagos`: JSON con array de pagos

**Flujo en el backend**:
1. Inserta/actualiza en `ingresos`
2. Si es "Mixto":
   - Elimina pagos anteriores de `ingresos_pagos_parciales`
   - Inserta nuevos pagos
3. Trigger `actualizar_metodo_ingreso` se ejecuta

**Impacto en otros archivos**:
- **models/IngresoModel.php**: Método save()
- **SQL**: Tabla `ingresos_pagos_parciales`
- **SQL**: Trigger `actualizar_metodo_ingreso`

---

#### `initEliminarIngreso()`

**Descripción**: Elimina un ingreso y sus pagos parciales.

**Confirmación**: Alert "Se eliminarán también todos los pagos parciales asociados"

**Backend**:
- **Controller**: `IngresoController::delete($id)`
- **Cascade**: ON DELETE CASCADE en `ingresos_pagos_parciales`

---

#### `initBuscadorIngresos()`

**Descripción**: Sistema de búsqueda y filtrado en tiempo real.

**Elementos DOM**:
- `#searchIngresos` - Input de búsqueda
- `#fechaInicioIngresos` - Filtro fecha inicio
- `#fechaFinIngresos` - Filtro fecha fin
- `#clearSearchIngresos` - Botón limpiar búsqueda
- `#clearDateIngresos` - Botón limpiar fechas
- `#resultCountIngresos` - Contador de resultados
- `#tablaIngresos` - Tbody de la tabla

**Funcionalidad**:

1. **Búsqueda por texto**:
   - Busca en folio (data-id del botón)
   - Busca en nombre del alumno
   - Case-insensitive

2. **Filtro por fechas**:
   - Rango fecha inicio - fecha fin
   - Solo fecha inicio
   - Solo fecha fin
   - Lee atributo `data-fecha` de cada `<tr>`

3. **Lógica combinada**: Debe cumplir ambos criterios (AND)

4. **Contador**:
   - "Mostrando X de Y ingresos" (verde)
   - "No se encontraron resultados" (rojo)

5. **Botones limpiar**:
   - Aparecen solo si hay filtros activos
   - Limpian y vuelven a filtrar

6. **Atajo ESC**: Limpia búsqueda

**Eventos**:
- `keyup` en input → filtrar
- `change` en fechas → filtrar
- `click` en limpiar → reset y filtrar
- `keydown` ESC → limpiar

**Impacto en otros archivos**:
- **views/ingresos_list.php**: Tabla con `data-fecha` en cada `<tr>`

---

### 4️⃣ EgresosModule

**Propósito**: Gestionar egresos vinculados a presupuestos.

#### `initModalEgreso()`

**Descripción**: Maneja la apertura del modal de egreso.

**Flujo**:

1. **Reset del formulario**

2. **Cargar sub-presupuestos**:
   - Backend: `PresupuestoController::getSubPresupuestos()`
   - Solo presupuestos con `id_padre IS NOT NULL`
   - Formato: "Nombre — Fecha (Categoría)"

3. **Cargar categorías de egreso**:
   - Backend: `EgresoController::getCategoriasEgreso()`
   - Solo categorías tipo "Egreso"

4. **Auto-sync categoría ↔ presupuesto**:
   - Al seleccionar presupuesto → auto-selecciona categoría
   - Al cambiar categoría → auto-selecciona presupuesto

5. **Si es edición**:
   - Backend: `EgresoController::getEgresoData($id)`
   - Carga todos los campos

**Elementos DOM**:
- `#modalEgreso`
- `#formEgreso`
- `#eg_id_categoria` - Selector categoría
- `#eg_id_presupuesto` - Selector presupuesto
- `#eg_monto` - Campo monto (usa `ensureNumberEditable`)

**Backend**:
- `PresupuestoController::getSubPresupuestos()`
- `EgresoController::getCategoriasEgreso()`
- `EgresoController::getEgresoData($id)`

**Impacto en otros archivos**:
- **views/layout.php**: Modal HTML
- **views/egresos_list.php**: Tabla y botones

---

#### `initSubmitEgreso()`

**Descripción**: Guarda un egreso.

**Backend**:
- **Controller**: `EgresoController::save()`
- **Tablas afectadas**:
  - `egresos` - Inserta/actualiza egreso
  - `presupuestos` - Se actualiza `monto_gastado` via trigger

**Trigger automático**: `actualizar_gasto_presupuesto`
- Se ejecuta después de INSERT/UPDATE/DELETE en `egresos`
- Recalcula `monto_gastado` del presupuesto
- Verifica alertas (>80% = alerta)

**Eventos disparados**:
- `$(document).trigger('egresoGuardado')` - Para actualizar badge de alertas

---

#### `initEliminarEgreso()`

**Descripción**: Elimina un egreso.

**Backend**:
- **Controller**: `EgresoController::delete($id)`
- **Trigger**: `actualizar_gasto_presupuesto` recalcula presupuesto

**Eventos disparados**:
- `$(document).trigger('egresoEliminado')` - Para actualizar badge de alertas

---

#### `initBuscadorEgresos()`

**Descripción**: Sistema de búsqueda y filtrado (idéntico al de ingresos).

**Elementos DOM**:
- `#searchEgresos`
- `#fechaInicioEgresos`
- `#fechaFinEgresos`
- `#clearSearchEgresos`
- `#clearDateEgresos`
- `#resultCountEgresos`
- `#tablaEgresos`

**Criterios de búsqueda**:
- Folio (data-id del botón editar)
- Destinatario (columna 3)
- Rango de fechas

**Impacto en otros archivos**:
- **views/egresos_list.php**: Tabla con `data-fecha`

---

### 5️⃣ CategoriasModule

**Propósito**: CRUD de categorías (Ingreso/Egreso).

#### `initModalCategoria()`

**Descripción**: Maneja la apertura del modal de categoría.

**Flujo**:
1. Reset del formulario
2. Si es edición:
   - Backend: `CategoriaController::getCategoriaData($id)`
   - Carga campos: nombre, tipo, descripción

**Elementos DOM**:
- `#modalCategoria`
- `#formCategoria`
- `#cat_nombre`, `#cat_tipo`, `#cat_descripcion`

---

#### `initSubmitCategoria()`

**Descripción**: Guarda una categoría.

**Backend**:
- **Controller**: `CategoriaController::save()`
- **Tabla**: `categorias`

---

#### `initEliminarCategoria()`

**Descripción**: Elimina una categoría.

**Backend**:
- **Controller**: `CategoriaController::delete($id)`
- **Restricción**: No se puede eliminar si tiene presupuestos/egresos/ingresos asociados (FK)

---

### 6️⃣ PresupuestosModule

**Propósito**: Gestionar presupuestos jerárquicos (generales y sub-presupuestos).

#### `populatePresupuestoCategoria(presId)`

**Descripción**: Popula el selector de categorías en el modal de presupuesto.

**Parámetros**:
- `presId` (number): ID del presupuesto (para edición)

**Backend**:
- **Controller**: `PresupuestoController::getCategoriasPresupuesto()`
- Solo categorías tipo "Egreso" (presupuestos controlan egresos)

**Retorna**: Promise con categorías

**Uso**: Se llama antes de editar para pre-cargar las opciones

---

#### `initModalPresupuestoGeneral()`

**Descripción**: Maneja el modal de presupuesto general (padre).

**Características**:
- No tiene `id_padre`
- No tiene `id_categoria`
- Solo tiene: nombre, monto, fecha, descripción

**Backend**:
- **Controller**: `PresupuestoController::savePresupuestoGeneral()`
- **Tabla**: `presupuestos` con `id_padre = NULL`

---

#### `initSubmitPresupuestoGeneral()`

**Descripción**: Guarda un presupuesto general.

**Validación**: Campos básicos requeridos

---

#### `initModalSubPresupuesto()`

**Descripción**: Maneja el modal de sub-presupuesto.

**Flujo**:

1. **Cargar presupuestos generales** como opciones para `id_padre`:
   - Backend: `PresupuestoController::getPresupuestosGenerales()`
   - WHERE `id_padre IS NULL`

2. **Cargar categorías**:
   - Usa `populatePresupuestoCategoria()`

3. **Auto-sync categoría con padre**:
   - (No aplica, son independientes)

4. **Si es edición**:
   - Backend: `PresupuestoController::getPresupuestoData($id)`
   - Carga `id_padre`, `id_categoria`, nombre, monto, fecha

**Elementos DOM**:
- `#modalPresupuesto`
- `#formPresupuesto`
- `#pres_id_padre` - Selector presupuesto general
- `#pres_id_categoria` - Selector categoría
- `#pres_monto` - Campo monto (editable)

---

#### `initSubmitSubPresupuesto()`

**Descripción**: Guarda un sub-presupuesto.

**Backend**:
- **Controller**: `PresupuestoController::save()`
- **Tabla**: `presupuestos` con `id_padre != NULL`

**Relaciones**:
- FK `id_padre` → `presupuestos(id_presupuesto)`
- FK `id_categoria` → `categorias(id_categoria)`

---

#### `initEliminarPresupuesto()`

**Descripción**: Elimina presupuestos (general o sub).

**Funcionalidad**:

1. **Eliminar presupuesto general** (`.btn-del-presgen`):
   - Confirmación: "Se eliminarán todos los sub-presupuestos asociados"
   - Backend: `PresupuestoController::deletePresupuestoGeneral($id)`
   - Cascade: Elimina hijos automáticamente (ON DELETE CASCADE)

2. **Eliminar sub-presupuesto** (`.btn-del-presupuesto`):
   - Backend: `PresupuestoController::delete($id)`
   - Solo elimina el sub-presupuesto

**Eventos disparados**:
- `$(document).trigger('egresoEliminado')` - Para actualizar alertas

---

#### `initRefrescarPresupuestos()`

**Descripción**: Botón refrescar tabla.

**Acción**: `window.location.reload()`

---

### 7️⃣ AlertasPresupuestosModule

**Propósito**: Sistema de alertas en tiempo real para presupuestos excedidos.

#### `actualizarBadgeAlertas()`

**Descripción**: Actualiza el badge de alertas en el sidebar.

**Flujo**:

1. **Consulta al backend**:
   - Backend: `PresupuestoController::getAlertasCount()`
   - Cuenta presupuestos con `monto_gastado >= monto * 0.8`

2. **Actualiza badge**:
   - Si count > 0:
     - Muestra badge con número
     - Agrega animación `pulse-animation`
   - Si count = 0:
     - Oculta badge

**Elementos DOM**:
- `#badgeAlertasPresupuestos` - Badge en el sidebar

**Backend**:
- **Controller**: `PresupuestoController::getAlertasCount()`
- **Query**: `SELECT COUNT(*) WHERE monto_gastado/monto >= 0.8`

**Impacto en otros archivos**:
- **views/layout.php**: Badge HTML en el sidebar

---

#### `init()`

**Descripción**: Inicializa el sistema de alertas.

**Configuración**:

1. **Primera actualización**: Al cargar la página
2. **Actualización periódica**: Cada 30 segundos (30000ms)
3. **Escucha eventos**:
   - `egresoGuardado` → actualizar
   - `egresoEliminado` → actualizar

**Razón**: Cada vez que se modifica un egreso, el `monto_gastado` del presupuesto cambia.

---

### 8️⃣ AuditoriaModule

**Propósito**: Visor de detalles de auditoría.

#### `initModalDetalleAuditoria()`

**Descripción**: Muestra detalles de un registro de auditoría.

**Flujo**:

1. **Obtener ID**: Desde `data-id` del botón
2. **Consultar backend**:
   - Backend: `AuditoriaController::getDetalle($id)`
   - Retorna: fecha, usuario, tabla, acción, datos anteriores/nuevos, IP

3. **Renderizar HTML**:
   - ID auditoría
   - Fecha/hora
   - Usuario que hizo la acción
   - Tabla afectada
   - Badge con acción (INSERT, UPDATE, DELETE)
   - JSON de datos anteriores
   - JSON de datos nuevos
   - IP address

**Elementos DOM**:
- `#modalDetalleAuditoria`
- `#detalleAuditoriaBody` - Contenedor del detalle

**Backend**:
- **Controller**: `AuditoriaController::getDetalle($id)`
- **Tabla**: `auditoria`

**Impacto en otros archivos**:
- **views/auditoria_list.php**: Tabla con botones "Ver Detalle"
- **SQL**: Tabla `auditoria` poblada por triggers

---

### 9️⃣ SidebarModule

**Propósito**: Comportamiento responsive del sidebar en móviles.

#### `init()`

**Descripción**: Cierra el sidebar al hacer click en un enlace (solo en móviles).

**Lógica**:
- Detecta `window.innerWidth < 992` (breakpoint de Bootstrap)
- Si es móvil:
  - Cierra sidebar (quita clase `open`, agrega `closed`)
  - Oculta overlay
  - Restaura scroll del body

**Elementos DOM**:
- `#sidebar .nav-link` - Enlaces del sidebar
- `#sidebarOverlay` - Overlay oscuro

**Impacto en otros archivos**:
- **views/layout.php**: Estructura del sidebar
- **CSS**: Clases `open`, `closed`

---

### 🔧 Inicialización Global

#### `$(document).ready()`

**Descripción**: Punto de entrada principal del sistema.

**Flujo**:

1. **Logs iniciales**:
   - Versión del sistema
   - Versión de jQuery
   - Disponibilidad de Bootstrap
   - Usuario actual
   - BASE_URL

2. **Inicialización de módulos**:
   ```javascript
   UsuariosModule.init();
   IngresosModule.init();
   EgresosModule.init();
   CategoriasModule.init();
   PresupuestosModule.init();
   AlertasPresupuestosModule.init();
   AuditoriaModule.init();
   SidebarModule.init();
   ```

3. **Manejo de errores**:
   - Try-catch global
   - Alert al usuario si falla
   - Log en consola

**Variables globales requeridas**:
- `CURRENT_USER` (object): Datos del usuario logueado (viene de PHP)
- `BASE_URL` (string): URL base del sistema (viene de PHP)
- `$` (jQuery): Librería jQuery
- `bootstrap` (object): Librería Bootstrap 5

**Impacto en otros archivos**:
- **views/layout.php**: Define variables globales en `<script>`

---

## Flujo de Datos

### Ejemplo: Registrar Ingreso con Pagos Divididos

```
Usuario completa formulario
    ↓
Click "Guardar"
    ↓
initSubmitIngreso() - Validación frontend
    ↓
ajaxCall('ingreso', 'save', data)
    ↓
index.php recibe controller=ingreso&action=save
    ↓
IngresoController::save()
    ↓
IngresoModel::save()
    ↓
INSERT en tabla `ingresos` (metodo_de_pago = 'Mixto')
    ↓
foreach (pagos) → INSERT en `ingresos_pagos_parciales`
    ↓
Trigger `actualizar_metodo_ingreso` se ejecuta
    ↓
UPDATE `ingresos` SET metodo_de_pago = (listado de métodos)
    ↓
RESPONSE JSON: {success: true, folio: X}
    ↓
.done() en JS → window.location.reload()
    ↓
IngresoController::index()
    ↓
views/ingresos_list.php renderiza tabla
    ↓
app-optimized.js se carga
    ↓
IngresosModule.init() registra eventos
```

---

### Ejemplo: Crear Egreso y Actualizar Alertas

```
Usuario crea egreso vinculado a presupuesto
    ↓
initSubmitEgreso()
    ↓
ajaxCall('egreso', 'save', data)
    ↓
EgresoController::save()
    ↓
EgresoModel::save()
    ↓
INSERT en `egresos`
    ↓
Trigger `actualizar_gasto_presupuesto` se ejecuta
    ↓
UPDATE `presupuestos` SET monto_gastado = (suma egresos)
    ↓
Si monto_gastado >= monto * 0.8 → estado = 'alertado'
    ↓
RESPONSE: {success: true}
    ↓
$(document).trigger('egresoGuardado')
    ↓
AlertasPresupuestosModule escucha evento
    ↓
actualizarBadgeAlertas()
    ↓
ajaxCall('presupuesto', 'getAlertasCount')
    ↓
PresupuestoController::getAlertasCount()
    ↓
Query: SELECT COUNT(*) WHERE estado = 'alertado'
    ↓
RESPONSE: {count: 3}
    ↓
$('#badgeAlertasPresupuestos').text(3).show()
```

---

## Impacto en Otros Archivos

### Backend (Controllers)

| Controlador | Métodos usados | Descripción |
|-------------|----------------|-------------|
| **AuthController** | `changePasswordWithValidation()` | Cambio de contraseña con validación |
| **UserController** | `save()`, `delete()` | CRUD de usuarios |
| **IngresoController** | `save()`, `delete()`, `getCategoriasIngreso()`, `getIngresoData()` | CRUD de ingresos + pagos divididos |
| **EgresoController** | `save()`, `delete()`, `getCategoriasEgreso()`, `getEgresoData()` | CRUD de egresos |
| **CategoriaController** | `save()`, `delete()`, `getCategoriaData()` | CRUD de categorías |
| **PresupuestoController** | `save()`, `delete()`, `savePresupuestoGeneral()`, `deletePresupuestoGeneral()`, `getPresupuestosGenerales()`, `getSubPresupuestos()`, `getCategoriasPresupuesto()`, `getPresupuestoData()`, `getAlertasCount()` | Gestión completa de presupuestos |
| **AuditoriaController** | `getDetalle()` | Visor de auditoría |

---

### Frontend (Views)

| Vista | Elementos usados | Descripción |
|-------|------------------|-------------|
| **layout.php** | Todos los modales, variables globales, sidebar, badge de alertas | Layout principal |
| **profile.php** | Toggle usuarios, tabla usuarios, formulario perfil | Gestión de perfil y usuarios |
| **ingresos_list.php** | Tabla con data-fecha, botones editar/eliminar, buscador | Lista de ingresos |
| **egresos_list.php** | Tabla con data-fecha, botones editar/eliminar, buscador | Lista de egresos |
| **categorias_list.php** | Tabla, botones editar/eliminar | Lista de categorías |
| **presupuestos_list.php** | Jerarquía de presupuestos, botones editar/eliminar | Lista de presupuestos |
| **auditoria_list.php** | Tabla, botones "Ver Detalle" | Logs de auditoría |

---

### Base de Datos (Tablas y Triggers)

| Tabla | Descripción | Triggers |
|-------|-------------|----------|
| **usuarios** | Usuarios del sistema | - |
| **ingresos** | Ingresos registrados | `actualizar_metodo_ingreso` (AFTER INSERT/UPDATE en ingresos_pagos_parciales) |
| **ingresos_pagos_parciales** | Pagos divididos de ingresos | - |
| **egresos** | Egresos registrados | `actualizar_gasto_presupuesto` (AFTER INSERT/UPDATE/DELETE) |
| **categorias** | Categorías de Ingreso/Egreso | - |
| **presupuestos** | Presupuestos generales y sub-presupuestos | - |
| **auditoria** | Logs de auditoría | Triggers en todas las tablas (AFTER INSERT/UPDATE/DELETE) |

---

## Guía de Mantenimiento

### ✅ Buenas Prácticas

1. **Agregar nueva funcionalidad**:
   - Crear nuevo módulo IIFE
   - Exponer solo lo necesario con `return`
   - Inicializar en `$(document).ready()`

2. **Debugging**:
   - Todos los módulos tienen logs `console.log('[✓] Módulo X inicializado')`
   - Logs de AJAX: `console.log('[AJAX] POST controller/action')`
   - Errores: `console.error('[ERROR] ...')`

3. **Convenciones de nombres**:
   - Funciones privadas: `initNombreComponente()`
   - Selectores: IDs con prefijo del módulo (ej: `#in_monto`, `#eg_fecha`)
   - Clases de botones: `.btn-del-ingreso`, `.btn-edit-egreso`

4. **Validación**:
   - Siempre validar en frontend Y backend
   - Usar `escapeHtml()` para prevenir XSS
   - Confirmar acciones destructivas con `confirm()`

---

### 🚫 Errores Comunes

1. **No encontrar elemento DOM**:
   - Usar `if (!$element.length) return;` antes de manipular
   - Verificar que el HTML existe en la vista

2. **Eventos duplicados**:
   - Usar `.off()` antes de `.on()` en modales
   - Usar delegación con `$(document).on('click', '#selector', ...)`

3. **Race conditions**:
   - Usar Promises para cargas asíncronas
   - Esperar respuesta antes de continuar

4. **Memory leaks**:
   - Limpiar intervalos con `clearInterval()` si se usa
   - No crear event listeners infinitos

---

### 📦 Agregar Nuevo Módulo

```javascript
// ============================================================================
// MÓDULO: Reportes
// ============================================================================
const ReportesModule = (function() {
    const { ajaxCall, mostrarError } = ERPUtils;

    function initGenerarReporte() {
        $('#btnGenerarReporte').on('click', function() {
            ajaxCall('reporte', 'generar', {})
                .done(r => console.log(r))
                .fail(xhr => mostrarError('generar reporte', xhr));
        });
    }

    function init() {
        initGenerarReporte();
        console.log('[✓] Módulo Reportes inicializado');
    }

    return { init };
})();

// En $(document).ready():
ReportesModule.init();
```

---

### 🧪 Testing Manual

**Checklist de pruebas**:

- [ ] Login y logout funcional
- [ ] Editar perfil guarda cambios
- [ ] Cambiar contraseña valida y logout
- [ ] Crear usuario nuevo
- [ ] Eliminar usuario
- [ ] Registrar ingreso con pago único
- [ ] Registrar ingreso con cobro dividido (cuadra monto)
- [ ] Editar ingreso existente
- [ ] Eliminar ingreso
- [ ] Buscador de ingresos filtra correctamente
- [ ] Crear egreso vinculado a presupuesto
- [ ] Editar egreso
- [ ] Eliminar egreso
- [ ] Buscador de egresos funciona
- [ ] Crear categoría
- [ ] Eliminar categoría (verifica restricción FK)
- [ ] Crear presupuesto general
- [ ] Crear sub-presupuesto
- [ ] Eliminar presupuesto general (elimina hijos)
- [ ] Badge de alertas actualiza cuando se excede presupuesto
- [ ] Ver detalle de auditoría
- [ ] Sidebar cierra en móvil al hacer click

---

## Resumen de Optimizaciones

### Antes (app.js original)
- ❌ 1898 líneas sin estructura
- ❌ Todas las funciones en scope global
- ❌ Código de debugging temporal
- ❌ Duplicación de eventos
- ❌ ~50+ console.log innecesarios
- ❌ Sin documentación

### Después (app-optimized.js)
- ✅ Código modular encapsulado
- ✅ 9 módulos independientes
- ✅ Solo expone lo necesario
- ✅ Documentación JSDoc completa
- ✅ Logs estructurados por módulo
- ✅ Mantenible y escalable

---

## Contacto y Soporte

**Sistema**: ERP IUM - Sistema de Gestión Financiera  
**Versión**: 2.0 Optimizada  
**Fecha**: 20 de Noviembre de 2025  
**Repositorio**: institutouniversitariomorelia-hub/ERP-IUM  
**Rama**: testing  

Para dudas o soporte, contacta al administrador del sistema.

---

**¡Gracias por usar ERP IUM!** 🚀

# Sistema de Pagos Divididos - ERP IUM

## 📋 Resumen de Cambios

Se ha implementado un sistema completo para manejar **pagos divididos** en el módulo de Ingresos, permitiendo que un solo ingreso pueda registrarse con múltiples métodos de pago.

### Fecha de Implementación

**18 de Noviembre de 2025**

---

## ✨ Funcionalidades Nuevas

### 1. **Pagos Divididos**

Ahora es posible registrar un ingreso con múltiples métodos de pago. Por ejemplo:

- Un alumno paga $1,000 de inscripción
- $800 con Tarjeta de Crédito
- $200 en Efectivo

### 2. **Eliminación del Campo "Día de Pago"**

Se removió el campo `dia_pago` del formulario y vistas de ingreso, simplificando la interfaz.

### 3. **Nuevos Métodos de Pago**

Se agregaron dos nuevas opciones:

- Tarjeta Débito
- Tarjeta Crédito
- Mixto (para pagos divididos)

---

## 🗄️ Cambios en Base de Datos

### Nueva Tabla: `pagos_parciales`

```sql
CREATE TABLE `pagos_parciales` (
  `id_pago_parcial` INT(11) NOT NULL AUTO_INCREMENT,
  `folio_ingreso` INT(11) NOT NULL,
  `metodo_pago` ENUM('Efectivo','Transferencia','Depósito','Tarjeta Débito','Tarjeta Crédito'),
  `monto` DECIMAL(10,2) NOT NULL,
  `orden` TINYINT(2) NOT NULL DEFAULT 1,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pago_parcial`),
  KEY `idx_folio_ingreso` (`folio_ingreso`),
  CONSTRAINT `fk_pago_parcial_ingreso`
    FOREIGN KEY (`folio_ingreso`)
    REFERENCES `ingresos` (`folio_ingreso`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
```

### Modificación en Tabla `ingresos`

```sql
ALTER TABLE `ingresos`
MODIFY COLUMN `metodo_de_pago`
ENUM('Efectivo','Transferencia','Depósito','Tarjeta Débito','Tarjeta Crédito','Mixto')
```

### Triggers Nuevos

- `trg_pagos_parciales_after_insert` - Sincroniza con BD espejo
- `trg_pagos_parciales_after_update` - Actualiza BD espejo
- `trg_pagos_parciales_before_delete` - Elimina de BD espejo

### Vista Nueva

```sql
CREATE VIEW `v_ingresos_con_pagos` AS
SELECT
    i.*,
    GROUP_CONCAT(
        CONCAT(pp.metodo_pago, ': $', FORMAT(pp.monto, 2))
        ORDER BY pp.orden
        SEPARATOR ' | '
    ) AS desglose_pagos,
    COUNT(pp.id_pago_parcial) AS num_pagos_parciales
FROM ingresos i
LEFT JOIN pagos_parciales pp ON i.folio_ingreso = pp.folio_ingreso
GROUP BY i.folio_ingreso;
```

### Procedimiento Almacenado

```sql
CALL sp_validar_pagos_parciales(folio_ingreso);
```

Valida que la suma de pagos parciales coincida con el monto total del ingreso.

---

## 📁 Archivos Modificados

### Backend (PHP)

#### `controllers/IngresoController.php`

- ✅ Método `save()` actualizado para manejar pagos divididos
- ✅ Método `getIngresoData()` incluye pagos parciales
- ✅ Validación de suma de pagos parciales

#### `models/IngresoModel.php`

- ✅ Método `savePagosParciales()` - Guarda múltiples pagos
- ✅ Método `getPagosParciales()` - Obtiene pagos de un ingreso
- ✅ Método `getAllIngresos()` incluye información de pagos parciales
- ✅ Eliminación en cascada de pagos parciales

#### `generate_receipt_ingreso.php`

- ✅ Muestra desglose de pagos parciales en el recibo
- ✅ Diseño adaptado para múltiples métodos de pago

### Frontend (HTML/CSS/JS)

#### `views/layout.php` (Modal de Ingreso)

- ✅ Eliminado campo "Día de Pago"
- ✅ Agregado selector "Tipo de Pago" (Único / Dividido)
- ✅ Sección de pago único con método y monto
- ✅ Sección de pagos divididos con tabla dinámica
- ✅ Resumen en tiempo real (monto total vs suma parciales)
- ✅ Validación visual de diferencias

#### `views/ingresos_list.php`

- ✅ Eliminado campo "Día de Pago" del modal de detalles
- ✅ Muestra badge "Pago Dividido" cuando aplica
- ✅ Desglose de métodos de pago en detalle de ingreso

#### `public/js/app.js`

- ✅ Lógica para agregar/eliminar filas de pagos
- ✅ Cálculo automático de suma de parciales
- ✅ Validación de que la suma cuadre con el total
- ✅ Manejo de edición de ingresos con pagos divididos
- ✅ Serialización de pagos en formato JSON

---

## 🚀 Cómo Instalar

### Paso 1: Ejecutar Migración SQL

```bash
mysql -u root -p erp_ium < migrations/2025-11-18_pagos_divididos.sql
```

**Importante:** El script debe ejecutarse en **ambas** bases de datos:

- `erp_ium` (principal)
- `erp_ium_espejo` (respaldo)

### Paso 2: Verificar Instalación

Ejecuta las siguientes consultas para verificar:

```sql
-- Verificar tabla creada
SELECT COUNT(*) FROM pagos_parciales;

-- Verificar vista
SELECT COUNT(*) FROM v_ingresos_con_pagos;

-- Verificar triggers
SELECT TRIGGER_NAME, EVENT_MANIPULATION, EVENT_OBJECT_TABLE
FROM information_schema.TRIGGERS
WHERE TRIGGER_SCHEMA = 'erp_ium'
AND EVENT_OBJECT_TABLE = 'pagos_parciales';
```

### Paso 3: Migrar Datos Existentes

El script automáticamente crea un pago parcial para cada ingreso existente que no tenga pagos divididos.

---

## 📖 Guía de Uso

### Registrar un Pago Único (como antes)

1. Abrir modal "Agregar Ingreso"
2. Llenar los datos del alumno
3. Seleccionar **"Pago Único"** en "Tipo de Pago"
4. Elegir el método de pago
5. El monto se copia automáticamente
6. Guardar

### Registrar un Pago Dividido (NUEVO)

1. Abrir modal "Agregar Ingreso"
2. Llenar los datos del alumno
3. Ingresar el **Monto Total** (ej: $1000)
4. Seleccionar **"Pago Dividido"** en "Tipo de Pago"
5. Se abre la sección de pagos parciales
6. Para cada método de pago:
   - Hacer clic en "➕ Agregar Pago"
   - Seleccionar método (Efectivo, Transferencia, etc.)
   - Ingresar el monto correspondiente
7. El sistema muestra en tiempo real:
   - ✅ Verde: La suma cuadra con el total
   - ⚠️ Rojo/Amarillo: Hay diferencia
8. Guardar cuando la suma cuadre

### Ver Detalles de un Ingreso con Pagos Divididos

1. En la lista de ingresos, hacer clic en el ícono 👁️ (ojo)
2. El modal de detalles mostrará:
   - Badge "Pago Dividido (N métodos)"
   - Desglose de cada método con su monto

### Generar Recibo con Pagos Divididos

1. Hacer clic en el botón 📄 (recibo)
2. El recibo PDF mostrará:
   - "Pago Dividido" en lugar del método único
   - Desglose completo: "Tarjeta Crédito: $800.00" + "Efectivo: $200.00"

---

## 🔍 Ejemplos de Consultas SQL

### Ver ingresos con pagos divididos

```sql
SELECT * FROM v_ingresos_con_pagos
WHERE metodo_de_pago = 'Mixto';
```

### Ver desglose de un ingreso específico

```sql
SELECT
    i.folio_ingreso,
    i.alumno,
    i.monto,
    pp.metodo_pago,
    pp.monto AS monto_parcial,
    pp.orden
FROM ingresos i
INNER JOIN pagos_parciales pp ON i.folio_ingreso = pp.folio_ingreso
WHERE i.folio_ingreso = 123
ORDER BY pp.orden;
```

### Validar integridad de pagos

```sql
CALL sp_validar_pagos_parciales(123);
```

### Reporte de métodos de pago más usados

```sql
SELECT
    metodo_pago,
    COUNT(*) AS cantidad,
    SUM(monto) AS monto_total
FROM pagos_parciales
GROUP BY metodo_pago
ORDER BY monto_total DESC;
```

---

## ⚠️ Consideraciones Importantes

### Validaciones Implementadas

1. **Suma de Pagos**: La suma de todos los pagos parciales **debe ser igual** al monto total del ingreso (con tolerancia de $0.01)

2. **Mínimo de Pagos**: En modo dividido, debe haber al menos 1 método de pago

3. **Método Obligatorio**: Cada pago parcial debe tener un método y un monto válido

4. **Eliminación en Cascada**: Al eliminar un ingreso, se eliminan automáticamente todos sus pagos parciales

### Migración de Datos Existentes

- ✅ Todos los ingresos existentes automáticamente tienen un registro en `pagos_parciales`
- ✅ No se pierde información histórica
- ✅ Los recibos antiguos siguen funcionando

### Compatibilidad

- ✅ Compatible con sistema de auditoría existente
- ✅ Compatible con triggers de sincronización con BD espejo
- ✅ Compatible con sistema de categorías y presupuestos
- ✅ Responsive en móviles y tablets

---

## 🐛 Solución de Problemas

### Problema: "La suma no coincide con el monto total"

**Solución:** Verifica que la suma de todos los montos parciales sea exactamente igual al monto total. Usa el resumen visual en el modal.

### Problema: "No se muestran los pagos parciales en la edición"

**Solución:**

1. Verifica que la tabla `pagos_parciales` exista
2. Ejecuta: `SELECT * FROM pagos_parciales WHERE folio_ingreso = X`
3. Si no hay registros, el ingreso se guardó antes de la migración

### Problema: "Error al guardar pagos divididos"

**Solución:**

1. Revisa los logs de PHP: `C:\xampp\php\logs\php_error_log`
2. Verifica permisos de la tabla `pagos_parciales`
3. Asegúrate de que los triggers estén activos

---

## 📞 Soporte

Para dudas o problemas, revisar:

- Logs de PHP: `C:\xampp\php\logs\php_error_log`
- Logs de MySQL: `C:\xampp\mysql\data\*.err`
- Consola del navegador (F12) para errores de JavaScript

---

## ✅ Checklist de Implementación

- [x] Crear tabla `pagos_parciales` en BD principal
- [x] Crear tabla `pagos_parciales` en BD espejo
- [x] Modificar ENUM de `metodo_de_pago` en tabla `ingresos`
- [x] Crear triggers de sincronización
- [x] Crear vista `v_ingresos_con_pagos`
- [x] Crear procedimiento `sp_validar_pagos_parciales`
- [x] Migrar datos existentes
- [x] Actualizar `IngresoController.php`
- [x] Actualizar `IngresoModel.php`
- [x] Actualizar modal en `layout.php`
- [x] Actualizar JavaScript en `app.js`
- [x] Actualizar vista de lista `ingresos_list.php`
- [x] Actualizar generador de recibos
- [x] Eliminar campo "Día de Pago"
- [x] Documentar cambios

---

## 📊 Impacto en el Sistema

### Ventajas

✅ Mayor flexibilidad en registro de pagos  
✅ Mejor trazabilidad de métodos de pago  
✅ Reportes más precisos por método  
✅ Interfaz más intuitiva  
✅ Validación automática de montos

### Sin Impacto Negativo

✅ No afecta funcionalidad existente  
✅ Datos históricos preservados  
✅ Compatibilidad total con módulos existentes

---

**Desarrollado para Instituto Universitario Morelia**  
_Versión 2.0 - Noviembre 2025_

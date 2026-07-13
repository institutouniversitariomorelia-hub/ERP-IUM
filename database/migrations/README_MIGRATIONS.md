# 📋 Índice de Migraciones SQL

## ✅ 00_active - MIGRACIONES ACTIVAS (Ejecutar en orden)

Estas son las migraciones que **SÍ debes aplicar** en instalaciones nuevas o actualizaciones:

### 1. `2025-11-20_refactor_categorias.sql`
**Descripción:** Refactorización completa del sistema de categorías  
**Cambios:**
- Agrega campo `concepto` ENUM para diferenciar tipos de ingresos
- Agrega campo `no_borrable` para proteger categorías del sistema
- Elimina campo `id_presupuesto` (independiza categorías de presupuestos)
- ALTER TABLE sobre `categorias`

**Ejecutar:** ✅ SÍ - Estructura base del nuevo sistema

---

### 2. `insert_categorias_predefinidas.sql`
**Descripción:** Inserta las 41 categorías predefinidas del sistema  
**Categorías:**
- 30 categorías de EGRESO
- 11 categorías de INGRESO (con conceptos asignados)
- Todas marcadas con `no_borrable = 1`

**Ejecutar:** ✅ SÍ - Categorías esenciales del sistema

---

### 3. `2025-11-21_remove_concepto_from_ingresos.sql`
**Descripción:** Elimina campo obsoleto de tabla ingresos  
**Cambios:**
- DROP COLUMN concepto de tabla `ingresos` (BD principal)
- DROP COLUMN concepto de tabla `ingresos` (BD espejo)

**Motivo:** El concepto ahora se obtiene de la categoría asociada

**Ejecutar:** ✅ SÍ - Limpieza de estructura

---

### 4. `2025-11-21_remove_activo_fijo_from_egresos.sql`
**Descripción:** Elimina campo obsoleto de tabla egresos  
**Cambios:**
- DROP COLUMN activo_fijo de tabla `egresos` (BD principal)
- DROP COLUMN activo_fijo de tabla `egresos` (BD espejo)

**Motivo:** Campo reemplazado por sistema de categorías

**Ejecutar:** ✅ SÍ - Limpieza de estructura

---

### 5. `2025-11-21_fix_triggers_ingresos_egresos.sql`
**Descripción:** Actualiza TODOS los triggers eliminando referencias a campos obsoletos  
**Cambios:**
- DROP y CREATE de 6 triggers de `ingresos`
- DROP y CREATE de 6 triggers de `egresos`
- Total: 12 triggers actualizados

**Triggers actualizados:**
- `trg_ingresos_after_insert_espejo`
- `trg_ingresos_after_insert_auditoria`
- `trg_ingresos_after_update`
- `trg_ingresos_after_update_espejo`
- `trg_ingresos_before_delete`
- `trg_ingresos_before_delete_espejo`
- `trg_egresos_after_insert_espejo`
- `trg_egresos_after_insert_auditoria`
- `trg_egresos_after_update`
- `trg_egresos_after_update_espejo`
- `trg_egresos_before_delete`
- `trg_egresos_before_delete_espejo`

**Ejecutar:** ✅ SÍ - Crítico para sincronización BD

---

## ⚠️ 01_deprecated - MIGRACIONES OBSOLETAS (NO ejecutar)

Estas migraciones son **OBSOLETAS** y solo se mantienen para historial/auditoría. **NO aplicar en nuevas instalaciones.**

### `2025-11-07_presupuesto_categoria.sql`
❌ **NO EJECUTAR** - Versión antigua de relación presupuesto-categoría (reemplazada)

### `2025-11-12_presupuesto_parent.sql`
❌ **NO EJECUTAR** - Sistema de presupuestos padre-hijo (modificado después)

### `2025-11-18_pagos_divididos.sql`
❌ **NO EJECUTAR** - Implementación de pagos parciales (modificada)

### `add_parent_presupuesto_fk.sql`
❌ **NO EJECUTAR** - FK de presupuesto padre (esquema cambiado)

### `add_nombre_to_presupuestos.sql`
❌ **NO EJECUTAR** - Agregar nombre a presupuestos (ya incluido en schema)

### `fix_categorias_triggers.sql`
❌ **NO EJECUTAR** - Versión vieja de triggers (reemplazada por `2025-11-21_fix_triggers_ingresos_egresos.sql`)

### `fix_integrity_check.sql`
❌ **NO EJECUTAR** - Fix de integridad referencial (problema resuelto con limpieza)

### `fix_orphaned_and_add_fk.sql`
❌ **NO EJECUTAR** - Fix de registros huérfanos (problema resuelto)

### `EJECUTAR_PRIMERO_presupuesto_categoria.sql`
❌ **NO EJECUTAR** - Parte de flujo antiguo (obsoleto)

### `EJECUTAR_AHORA_actualizar_ambas_BD.sql`
❌ **NO EJECUTAR** - Actualización de ambas BD (obsoleto)

### `update_espejo_categorias.sql`
❌ **NO EJECUTAR** - Versión vieja de sync espejo (reemplazada)

---

## 🔧 02_maintenance - SCRIPTS DE MANTENIMIENTO (Uso ocasional)

Estos scripts son **UTILIDADES** que se ejecutan solo cuando es necesario, no en instalación normal.

### `limpieza_total.sql`
**Descripción:** Reset completo del sistema (elimina TODOS los datos)  
**Acciones:**
- DELETE de todos los registros de `ingresos`
- DELETE de todos los registros de `egresos`
- DELETE de todos los registros de `presupuestos`
- DELETE de todos los registros de `pagos_parciales`
- MANTIENE las 41 categorías protegidas (`no_borrable = 1`)

**⚠️ CUIDADO:** Script destructivo  
**Uso:** Solo para resetear datos de prueba o iniciar desde cero  
**NO usar en producción con datos reales**

---

### `seed_realistic_data.sql`
**Descripción:** Inserta datos realistas de prueba  
**Datos:**
- Usuarios de ejemplo
- Ingresos de prueba
- Egresos de prueba
- Presupuestos de ejemplo
- Pagos parciales

**Uso:** Desarrollo y pruebas  
**NO ejecutar en producción**

---

## 📊 Orden de Ejecución Recomendado

### Para Instalación Nueva:

```bash
# 1. Importar schema base
mysql -u root < ../schema/erp_ium.sql
mysql -u root < ../schema/erp_ium_espejo.sql

# 2. Aplicar migraciones activas EN ORDEN
mysql -u root erp_ium < 00_active/2025-11-20_refactor_categorias.sql
mysql -u root erp_ium < 00_active/insert_categorias_predefinidas.sql
mysql -u root erp_ium < 00_active/2025-11-21_remove_concepto_from_ingresos.sql
mysql -u root erp_ium < 00_active/2025-11-21_remove_activo_fijo_from_egresos.sql
mysql -u root erp_ium < 00_active/2025-11-21_fix_triggers_ingresos_egresos.sql

# 3. (Opcional) Datos de prueba
mysql -u root erp_ium < 02_maintenance/seed_realistic_data.sql
```

---

## ✅ Validación Post-Migración

Ejecutar estas queries para verificar:

```sql
-- 1. Verificar 41 categorías protegidas
SELECT COUNT(*) FROM categorias WHERE no_borrable = 1;
-- Resultado esperado: 41

-- 2. Verificar estructura ingresos (sin concepto)
DESCRIBE ingresos;
-- No debe aparecer 'concepto'

-- 3. Verificar estructura egresos (sin activo_fijo)
DESCRIBE egresos;
-- No debe aparecer 'activo_fijo'

-- 4. Verificar triggers (12 total)
SELECT COUNT(*) FROM information_schema.triggers 
WHERE TRIGGER_SCHEMA = 'erp_ium';
-- Resultado esperado: 12 (6 ingresos + 6 egresos)

-- 5. Verificar sincronización espejo
SELECT COUNT(*) FROM erp_ium_espejo.categorias;
-- Debe coincidir con tabla principal
```

---

## 🚨 Notas Importantes

### Backups
**SIEMPRE** hacer backup antes de ejecutar migraciones:
```bash
mysqldump -u root erp_ium > backup_$(date +%Y%m%d_%H%M%S).sql
```

### BD Espejo
Muchas migraciones afectan **ambas bases de datos** (principal y espejo). Verificar que ambas queden sincronizadas.

### Triggers
Los triggers son **críticos** para la sincronización. Si se modifican tablas manualmente, actualizar triggers correspondientes.

### Rollback
Las migraciones deprecated se mantienen para poder hacer rollback si es necesario, pero **NO se recomienda** volver a versiones anteriores.

---

## 📅 Historial de Cambios

| Fecha | Migración | Estado |
|-------|-----------|--------|
| 2025-11-07 | presupuesto_categoria | ⚠️ OBSOLETA |
| 2025-11-12 | presupuesto_parent | ⚠️ OBSOLETA |
| 2025-11-18 | pagos_divididos | ⚠️ OBSOLETA |
| 2025-11-20 | refactor_categorias | ✅ ACTIVA |
| 2025-11-21 | remove_concepto | ✅ ACTIVA |
| 2025-11-21 | remove_activo_fijo | ✅ ACTIVA |
| 2025-11-21 | fix_triggers | ✅ ACTIVA |

---

**Última actualización:** Noviembre 23, 2025  
**Total migraciones:** 17 (5 activas + 11 obsoletas + 1 mantenimiento)

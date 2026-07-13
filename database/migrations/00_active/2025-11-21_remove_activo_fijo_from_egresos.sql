-- =====================================================
-- Migración: Eliminar campo 'activo_fijo' de tabla egresos
-- Fecha: 2025-11-21
-- Descripción: El activo_fijo ahora viene de la categoría, no del egreso
-- =====================================================

USE erp_ium;

-- Verificar si la columna existe antes de eliminarla
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'erp_ium'
AND TABLE_NAME = 'egresos'
AND COLUMN_NAME = 'activo_fijo';

-- Eliminar el campo activo_fijo de la tabla egresos
ALTER TABLE egresos DROP COLUMN activo_fijo;

-- Verificar estructura resultante
DESCRIBE egresos;

-- Hacer lo mismo en BD espejo
ALTER TABLE erp_ium_espejo.egresos DROP COLUMN activo_fijo;

SELECT 'Campo activo_fijo eliminado de egresos' as Status;

-- =====================================================
-- Migración: Eliminar campo 'concepto' de tabla ingresos
-- Fecha: 2025-11-21
-- Descripción: El concepto ahora viene de la categoría, no del ingreso
-- =====================================================

USE erp_ium;

-- Verificar si la columna existe antes de eliminarla
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'erp_ium'
AND TABLE_NAME = 'ingresos'
AND COLUMN_NAME = 'concepto';

-- Eliminar el campo concepto de la tabla ingresos
ALTER TABLE ingresos DROP COLUMN concepto;

-- Verificar estructura resultante
DESCRIBE ingresos;

-- Hacer lo mismo en BD espejo
ALTER TABLE erp_ium_espejo.ingresos DROP COLUMN concepto;

SELECT 'Campo concepto eliminado de ingresos' as Status;

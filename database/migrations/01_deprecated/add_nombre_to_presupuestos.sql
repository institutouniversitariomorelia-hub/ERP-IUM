-- ============================================================================
-- Migration: Add nombre field to presupuestos table
-- Date: 2025-11-18
-- Description: Adds nombre field to identify sub-budgets by name in forms
-- ============================================================================

USE erp_ium;

-- Add nombre column
ALTER TABLE presupuestos
ADD COLUMN nombre VARCHAR(100) NULL AFTER monto_limite;

-- Update existing records to have a default name
UPDATE presupuestos
SET nombre = CASE
    WHEN parent_presupuesto IS NULL THEN CONCAT('Presupuesto General ', DATE_FORMAT(fecha, '%b %Y'))
    ELSE CONCAT('Sub-presupuesto ', id_presupuesto)
END
WHERE nombre IS NULL;

-- Show updated records
SELECT 
    id_presupuesto,
    nombre,
    monto_limite,
    fecha,
    parent_presupuesto
FROM presupuestos
ORDER BY parent_presupuesto, id_presupuesto;

SELECT '✅ Campo nombre agregado exitosamente' AS status;

-- ============================================================================
-- ROLLBACK (if needed):
-- ALTER TABLE presupuestos DROP COLUMN nombre;
-- ============================================================================

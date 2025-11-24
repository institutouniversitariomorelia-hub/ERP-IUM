-- ============================================================================
-- Safe cleanup: Remove orphaned presupuestos and add FK constraint
-- Date: 2025-11-18
-- ============================================================================

USE erp_ium;

START TRANSACTION;

-- Step 1: Identify orphaned records
CREATE TEMPORARY TABLE temp_orphaned AS
SELECT p1.id_presupuesto
FROM presupuestos p1
LEFT JOIN presupuestos p2 ON p1.parent_presupuesto = p2.id_presupuesto
WHERE p1.parent_presupuesto IS NOT NULL AND p2.id_presupuesto IS NULL;

-- Step 2: Show what will be deleted
SELECT 
    p.id_presupuesto,
    p.monto_limite,
    p.fecha,
    c.nombre as categoria,
    p.parent_presupuesto as parent_inexistente
FROM presupuestos p
LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
WHERE p.id_presupuesto IN (SELECT id_presupuesto FROM temp_orphaned);

-- Step 3: Delete orphaned records (safe because no dependencies exist)
DELETE FROM presupuestos 
WHERE id_presupuesto IN (SELECT id_presupuesto FROM temp_orphaned);

-- Show deletion result
SELECT ROW_COUNT() AS registros_eliminados;

-- Step 4: Add FK constraint
ALTER TABLE presupuestos
ADD CONSTRAINT fk_presupuestos_parent
FOREIGN KEY (parent_presupuesto) 
REFERENCES presupuestos(id_presupuesto)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- Step 5: Verify
SELECT 
    CONSTRAINT_NAME,
    UPDATE_RULE,
    DELETE_RULE
FROM information_schema.REFERENTIAL_CONSTRAINTS
WHERE CONSTRAINT_NAME = 'fk_presupuestos_parent'
  AND CONSTRAINT_SCHEMA = 'erp_ium';

COMMIT;

-- Cleanup
DROP TEMPORARY TABLE IF EXISTS temp_orphaned;

SELECT '✅ Migration completada exitosamente' AS status;

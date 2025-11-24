-- ============================================================================
-- Migration: Add FK constraint for parent_presupuesto with CASCADE delete
-- Date: 2025-11-18
-- Description: Adds foreign key constraint to presupuestos.parent_presupuesto
--              to ensure cascade deletion when parent budget is deleted
-- ============================================================================

USE erp_ium;

-- Check if there are orphaned records before adding constraint
SELECT 
    p1.id_presupuesto, 
    p1.parent_presupuesto,
    'ORPHANED - will prevent FK creation' as status
FROM presupuestos p1
LEFT JOIN presupuestos p2 ON p1.parent_presupuesto = p2.id_presupuesto
WHERE p1.parent_presupuesto IS NOT NULL AND p2.id_presupuesto IS NULL;

-- If above query returns rows, you must clean them first:
-- DELETE FROM presupuestos WHERE id_presupuesto IN (2, 3, 5, 7);
-- Or manually delete orphaned records

-- Add FK constraint with CASCADE
ALTER TABLE presupuestos
ADD CONSTRAINT fk_presupuestos_parent
FOREIGN KEY (parent_presupuesto) 
REFERENCES presupuestos(id_presupuesto)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- Verify constraint was created
SELECT 
    constraint_name,
    table_name,
    column_name,
    referenced_table_name,
    referenced_column_name
FROM information_schema.key_column_usage
WHERE constraint_name = 'fk_presupuestos_parent'
  AND table_schema = 'erp_ium';

-- ============================================================================
-- ROLLBACK (if needed):
-- ALTER TABLE presupuestos DROP FOREIGN KEY fk_presupuestos_parent;
-- ============================================================================

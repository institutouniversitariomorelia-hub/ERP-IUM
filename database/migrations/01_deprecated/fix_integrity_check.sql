-- =====================================================
-- Script de Verificación y Reparación de Integridad
-- Fecha: 2025-11-21
-- Descripción: Verifica relaciones rotas después de borrar categorías
-- =====================================================

USE erp_ium;

-- =====================================================
-- 1. VERIFICAR ESTRUCTURA ACTUAL
-- =====================================================

-- Ver estructura de categorias (debe tener: concepto, no_borrable, SIN id_presupuesto)
DESCRIBE categorias;

-- Ver estructura de presupuestos
DESCRIBE presupuestos;

-- Ver estructura de ingresos
DESCRIBE ingresos;

-- Ver estructura de egresos
DESCRIBE egresos;

-- =====================================================
-- 2. ENCONTRAR REGISTROS HUÉRFANOS
-- =====================================================

-- Presupuestos que referencian categorías eliminadas
SELECT 
    p.id_presupuesto,
    p.id_categoria,
    p.nombre,
    p.monto_limite
FROM presupuestos p
LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
WHERE p.id_categoria IS NOT NULL AND c.id_categoria IS NULL;

-- Ingresos que referencian categorías eliminadas
SELECT 
    i.folio_ingreso,
    i.id_categoria,
    i.alumno,
    i.monto
FROM ingresos i
LEFT JOIN categorias c ON i.id_categoria = c.id_categoria
WHERE i.id_categoria IS NOT NULL AND c.id_categoria IS NULL;

-- Egresos que referencian categorías eliminadas
SELECT 
    e.folio_egreso,
    e.id_categoria,
    e.destinatario,
    e.monto
FROM egresos e
LEFT JOIN categorias c ON e.id_categoria = c.id_categoria
WHERE e.id_categoria IS NOT NULL AND c.id_categoria IS NULL;

-- =====================================================
-- 3. OPCIONES DE REPARACIÓN
-- =====================================================

-- Opción A: Eliminar presupuestos huérfanos (si ya borraste todo)
-- DELETE FROM presupuestos 
-- WHERE id_categoria NOT IN (SELECT id_categoria FROM categorias);

-- Opción B: Establecer categoría NULL en presupuestos huérfanos
-- UPDATE presupuestos 
-- SET id_categoria = NULL 
-- WHERE id_categoria NOT IN (SELECT id_categoria FROM categorias);

-- Opción C: Ver todas las categorías disponibles para reasignar
SELECT 
    id_categoria,
    nombre,
    tipo,
    concepto,
    no_borrable
FROM categorias
ORDER BY tipo, nombre;

-- =====================================================
-- 4. CONTAR REGISTROS ACTUALES
-- =====================================================

SELECT 
    'CATEGORIAS' as tabla,
    COUNT(*) as total,
    SUM(CASE WHEN tipo = 'Ingreso' THEN 1 ELSE 0 END) as ingresos,
    SUM(CASE WHEN tipo = 'Egreso' THEN 1 ELSE 0 END) as egresos,
    SUM(CASE WHEN no_borrable = 1 THEN 1 ELSE 0 END) as protegidas
FROM categorias

UNION ALL

SELECT 
    'PRESUPUESTOS' as tabla,
    COUNT(*) as total,
    0 as ingresos,
    0 as egresos,
    0 as protegidas
FROM presupuestos

UNION ALL

SELECT 
    'INGRESOS' as tabla,
    COUNT(*) as total,
    0 as ingresos,
    0 as egresos,
    0 as protegidas
FROM ingresos

UNION ALL

SELECT 
    'EGRESOS' as tabla,
    COUNT(*) as total,
    0 as ingresos,
    0 as egresos,
    0 as protegidas
FROM egresos;

-- =====================================================
-- 5. VERIFICAR FOREIGN KEYS
-- =====================================================

SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'erp_ium'
AND TABLE_NAME IN ('categorias', 'presupuestos', 'ingresos', 'egresos')
AND REFERENCED_TABLE_NAME IS NOT NULL;

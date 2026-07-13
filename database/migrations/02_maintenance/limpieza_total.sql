-- =====================================================
-- Script de Limpieza Total
-- Fecha: 2025-11-21
-- Descripción: Limpia todo el sistema dejando solo categorías predefinidas
-- =====================================================

USE erp_ium;

-- =====================================================
-- 1. ELIMINAR TODOS LOS REGISTROS
-- =====================================================

-- Eliminar pagos parciales primero (tienen FK con ingresos)
DELETE FROM pagos_parciales;

-- Eliminar ingresos
DELETE FROM ingresos;

-- Eliminar egresos
DELETE FROM egresos;

-- Eliminar presupuestos
DELETE FROM presupuestos;

-- Eliminar categorías NO protegidas (mantener las 41 predefinidas)
DELETE FROM categorias WHERE no_borrable = 0 OR no_borrable IS NULL;

-- =====================================================
-- 2. REINICIAR AUTO_INCREMENT
-- =====================================================

ALTER TABLE pagos_parciales AUTO_INCREMENT = 1;
ALTER TABLE ingresos AUTO_INCREMENT = 1;
ALTER TABLE egresos AUTO_INCREMENT = 1;
ALTER TABLE presupuestos AUTO_INCREMENT = 1;

-- NO reiniciar auto_increment de categorías para mantener los IDs de las predefinidas

-- =====================================================
-- 3. VERIFICACIÓN
-- =====================================================

SELECT 'LIMPIEZA COMPLETADA' as Status;

SELECT 
    'CATEGORIAS' as Tabla,
    COUNT(*) as Total,
    SUM(CASE WHEN tipo = 'Ingreso' THEN 1 ELSE 0 END) as Ingresos,
    SUM(CASE WHEN tipo = 'Egreso' THEN 1 ELSE 0 END) as Egresos,
    SUM(CASE WHEN no_borrable = 1 THEN 1 ELSE 0 END) as Protegidas
FROM categorias

UNION ALL

SELECT 'PRESUPUESTOS', COUNT(*), 0, 0, 0 FROM presupuestos
UNION ALL
SELECT 'INGRESOS', COUNT(*), 0, 0, 0 FROM ingresos
UNION ALL
SELECT 'EGRESOS', COUNT(*), 0, 0, 0 FROM egresos
UNION ALL
SELECT 'PAGOS_PARCIALES', COUNT(*), 0, 0, 0 FROM pagos_parciales;

-- Mostrar las categorías que quedaron
SELECT 
    id_categoria,
    nombre,
    tipo,
    concepto,
    no_borrable
FROM categorias
ORDER BY tipo, nombre;

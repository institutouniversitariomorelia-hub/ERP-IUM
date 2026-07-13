-- =====================================================
-- Migración: Refactorización completa del sistema de categorías
-- Fecha: 2025-11-20
-- Descripción: 
--   1. Agregar campo 'concepto' a categorías (solo para Ingresos)
--   2. Eliminar id_presupuesto de categorías
--   3. Agregar campo 'no_borrable' para proteger categorías predefinidas
--   4. Insertar categorías predefinidas de Egresos e Ingresos
-- =====================================================

USE erp_ium;

-- =====================================================
-- PASO 1: Modificar estructura de tabla categorias
-- =====================================================

-- Agregar campo 'concepto' (solo para Ingresos)
ALTER TABLE categorias 
ADD COLUMN concepto ENUM('Registro Diario', 'Titulaciones', 'Inscripciones y Reinscripciones') NULL 
AFTER tipo;

-- Agregar campo para marcar categorías no borrables
ALTER TABLE categorias 
ADD COLUMN no_borrable TINYINT(1) DEFAULT 0 
AFTER descripcion;

-- Eliminar la foreign key de id_presupuesto primero (si existe)
SET @constraint_name = (
    SELECT CONSTRAINT_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = 'erp_ium' 
    AND TABLE_NAME = 'categorias' 
    AND COLUMN_NAME = 'id_presupuesto' 
    AND REFERENCED_TABLE_NAME IS NOT NULL
    LIMIT 1
);

SET @drop_fk = IF(@constraint_name IS NOT NULL, 
    CONCAT('ALTER TABLE categorias DROP FOREIGN KEY ', @constraint_name), 
    'SELECT "No hay FK para eliminar"');

PREPARE stmt FROM @drop_fk;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ahora eliminar la columna id_presupuesto
ALTER TABLE categorias 
DROP COLUMN id_presupuesto;

-- =====================================================
-- PASO 2: Insertar categorías predefinidas de EGRESOS (Activos Fijos)
-- =====================================================

INSERT INTO categorias (nombre, tipo, concepto, descripcion, id_user, no_borrable) VALUES
('IUM COMISIONES', 'Egreso', NULL, 'Comisiones institucionales', 2, 1),
('IUM RENTAS', 'Egreso', NULL, 'Pagos de renta de instalaciones', 2, 1),
('IUM HONORARIOS LICENCIATURA', 'Egreso', NULL, 'Honorarios de docentes de licenciatura', 2, 1),
('IUM HONORARIOS MAESTRIA', 'Egreso', NULL, 'Honorarios de docentes de maestría', 2, 1),
('IUM HONORARIOS DOCTORADO', 'Egreso', NULL, 'Honorarios de docentes de doctorado', 2, 1),
('IUM IMSS', 'Egreso', NULL, 'Pagos de seguro social', 2, 1),
('IUM SUELDOS Y SALARIOS', 'Egreso', NULL, 'Sueldos y salarios del personal', 2, 1),
('IUM PUBLICIDAD', 'Egreso', NULL, 'Gastos de publicidad y marketing', 2, 1),
('IUM SERVICIOS', 'Egreso', NULL, 'Servicios generales (luz, agua, internet, etc.)', 2, 1),
('EDUCACION CONTINUA', 'Egreso', NULL, 'Gastos de educación continua', 2, 1),
('IUM MANTENIMIENTO', 'Egreso', NULL, 'Mantenimiento de instalaciones y equipo', 2, 1),
('IUM CAFETERIA Y LIMPIEZA', 'Egreso', NULL, 'Servicios de cafetería y limpieza', 2, 1),
('IUM SECRETARIA DE EDUCACION', 'Egreso', NULL, 'Pagos a Secretaría de Educación', 2, 1),
('IUM IMPUESTOS', 'Egreso', NULL, 'Pagos de impuestos', 2, 1),
('IUM CREDITOS', 'Egreso', NULL, 'Pagos de créditos', 2, 1),
('IUM OTROS', 'Egreso', NULL, 'Otros gastos no clasificados', 2, 1),
('IUM PAPELERIA', 'Egreso', NULL, 'Gastos de papelería y útiles', 2, 1),
('INSCRIPCIONES', 'Egreso', NULL, 'Gastos relacionados con inscripciones', 2, 1),
('IUM GASOLINA', 'Egreso', NULL, 'Gastos de combustible', 2, 1),
('IUM FRANQUICIA', 'Egreso', NULL, 'Pagos de franquicia', 2, 1),
('IUM TRAMITES', 'Egreso', NULL, 'Gastos de trámites administrativos', 2, 1),
('IUM REEMBOLSOS', 'Egreso', NULL, 'Reembolsos a personal o alumnos', 2, 1),
('IUM ISN', 'Egreso', NULL, 'Impuesto Sobre Nómina', 2, 1),
('IUM PAQUETERIA Y MENSAJERIA', 'Egreso', NULL, 'Servicios de paquetería y mensajería', 2, 1),
('REINSCRIPCIONES', 'Egreso', NULL, 'Gastos relacionados con reinscripciones', 2, 1),
('IUM COMPENSACION', 'Egreso', NULL, 'Compensaciones al personal', 2, 1),
('IUM EXTRAORDINARIOS', 'Egreso', NULL, 'Gastos extraordinarios', 2, 1),
('TITULACIONES', 'Egreso', NULL, 'Gastos relacionados con titulaciones', 2, 1),
('DIPLOMADOS', 'Egreso', NULL, 'Gastos de diplomados', 2, 1),
('IUM HONORARIOS SOLIDARIOS', 'Egreso', NULL, 'Honorarios solidarios', 2, 1),
('IUM EVENTOS', 'Egreso', NULL, 'Gastos de eventos institucionales', 2, 1);

-- =====================================================
-- PASO 3: Insertar categorías predefinidas de INGRESOS (Conceptos)
-- =====================================================

-- Titulaciones (1 registro)
INSERT INTO categorias (nombre, tipo, concepto, descripcion, id_user, no_borrable) VALUES
('TITULACION', 'Ingreso', 'Titulaciones', 'Ingresos por procesos de titulación', 2, 1);

-- Inscripciones y Reinscripciones (2 registros)
INSERT INTO categorias (nombre, tipo, concepto, descripcion, id_user, no_borrable) VALUES
('INSCRIPCION', 'Ingreso', 'Inscripciones y Reinscripciones', 'Ingresos por inscripciones', 2, 1),
('REINSCRIPCION', 'Ingreso', 'Inscripciones y Reinscripciones', 'Ingresos por reinscripciones', 2, 1);

-- Registro Diario (8 registros)
INSERT INTO categorias (nombre, tipo, concepto, descripcion, id_user, no_borrable) VALUES
('COLEGIATURA', 'Ingreso', 'Registro Diario', 'Ingresos por colegiaturas', 2, 1),
('CONSTANCIA SIMPLE', 'Ingreso', 'Registro Diario', 'Ingresos por constancias simples', 2, 1),
('CONSTANCIA CON CALIFICACIONES', 'Ingreso', 'Registro Diario', 'Ingresos por constancias con calificaciones', 2, 1),
('HISTORIALES', 'Ingreso', 'Registro Diario', 'Ingresos por historiales académicos', 2, 1),
('CERTIFICADOS', 'Ingreso', 'Registro Diario', 'Ingresos por certificados', 2, 1),
('EQUIVALENCIAS', 'Ingreso', 'Registro Diario', 'Ingresos por equivalencias', 2, 1),
('CREDENCIALES', 'Ingreso', 'Registro Diario', 'Ingresos por credenciales', 2, 1),
('OTROS', 'Ingreso', 'Registro Diario', 'Otros ingresos no clasificados', 2, 1);

-- =====================================================
-- PASO 4: Verificar datos insertados
-- =====================================================

SELECT 'Categorías de Egresos insertadas:' as mensaje;
SELECT id_categoria, nombre, tipo, no_borrable FROM categorias WHERE tipo = 'Egreso' AND no_borrable = 1;

SELECT 'Categorías de Ingresos insertadas:' as mensaje;
SELECT id_categoria, nombre, tipo, concepto, no_borrable FROM categorias WHERE tipo = 'Ingreso' AND no_borrable = 1;

-- =====================================================
-- NOTAS IMPORTANTES:
-- =====================================================
-- 1. Las categorías con no_borrable = 1 NO deben poder eliminarse desde el sistema
-- 2. El campo 'concepto' solo aplica para tipo = 'Ingreso'
-- 3. Actualizar CategoriaController para validar no_borrable antes de eliminar
-- 4. Actualizar formularios de Ingreso/Egreso para reflejar cambios de nomenclatura
-- 5. Crear 3 formatos de recibos para Ingresos + 1 para Egresos
-- =====================================================

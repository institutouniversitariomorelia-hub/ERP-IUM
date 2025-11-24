-- =====================================================
-- Solo insertar categorías predefinidas (sin modificar estructura)
-- =====================================================

USE erp_ium;

-- EGRESOS (31 categorías) - INSERT IGNORE para evitar duplicados
INSERT IGNORE INTO categorias (nombre, tipo, concepto, descripcion, id_user, no_borrable) VALUES
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

-- INGRESOS (11 categorías)
-- Titulaciones (1)
INSERT IGNORE INTO categorias (nombre, tipo, concepto, descripcion, id_user, no_borrable) VALUES
('TITULACION', 'Ingreso', 'Titulaciones', 'Ingresos por procesos de titulación', 2, 1);

-- Inscripciones y Reinscripciones (2)
INSERT IGNORE INTO categorias (nombre, tipo, concepto, descripcion, id_user, no_borrable) VALUES
('INSCRIPCION', 'Ingreso', 'Inscripciones y Reinscripciones', 'Ingresos por inscripciones', 2, 1),
('REINSCRIPCION', 'Ingreso', 'Inscripciones y Reinscripciones', 'Ingresos por reinscripciones', 2, 1);

-- Registro Diario (8)
INSERT IGNORE INTO categorias (nombre, tipo, concepto, descripcion, id_user, no_borrable) VALUES
('COLEGIATURA', 'Ingreso', 'Registro Diario', 'Ingresos por colegiaturas', 2, 1),
('CONSTANCIA SIMPLE', 'Ingreso', 'Registro Diario', 'Ingresos por constancias simples', 2, 1),
('CONSTANCIA CON CALIFICACIONES', 'Ingreso', 'Registro Diario', 'Ingresos por constancias con calificaciones', 2, 1),
('HISTORIALES', 'Ingreso', 'Registro Diario', 'Ingresos por historiales académicos', 2, 1),
('CERTIFICADOS', 'Ingreso', 'Registro Diario', 'Ingresos por certificados', 2, 1),
('EQUIVALENCIAS', 'Ingreso', 'Registro Diario', 'Ingresos por equivalencias', 2, 1),
('CREDENCIALES', 'Ingreso', 'Registro Diario', 'Ingresos por credenciales', 2, 1),
('OTROS', 'Ingreso', 'Registro Diario', 'Otros ingresos no clasificados', 2, 1);

SELECT 'Categorías insertadas correctamente' as resultado;
SELECT COUNT(*) as total_egresos FROM categorias WHERE tipo = 'Egreso' AND no_borrable = 1;
SELECT COUNT(*) as total_ingresos FROM categorias WHERE tipo = 'Ingreso' AND no_borrable = 1;

-- migrations/seed_realistic_data.sql
-- WARNING: Este script TRUNCATEA tablas (borra datos) excepto la tabla `usuarios`.
-- Revisa el contenido antes de ejecutar en producción. Se recomienda probar en copia de la BD.

START TRANSACTION;

-- Desactivar verificaciones de foreign key temporalmente
SET @OLD_FK_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;

-- IMPORTANTE: MySQL no permite TRUNCATE en una tabla que es referenciada por
-- una constraint FK, aunque se intente desactivar FOREIGN_KEY_CHECKS en
-- algunas interfaces (phpMyAdmin puede bloquearlo). Para evitar errores
-- usamos DELETE en el orden correcto y luego reset de AUTO_INCREMENT.

-- Eliminar datos de tablas en orden seguro (hijos primero)
DELETE FROM usuario_ingreso;
DELETE FROM usuario_historial;
DELETE FROM auditoria;
DELETE FROM egresos;
DELETE FROM ingresos;
DELETE FROM presupuestos;
DELETE FROM categorias;

-- Resetear AUTO_INCREMENT (opcional)
ALTER TABLE usuario_ingreso AUTO_INCREMENT = 1;
ALTER TABLE usuario_historial AUTO_INCREMENT = 1;
ALTER TABLE auditoria AUTO_INCREMENT = 1;
ALTER TABLE egresos AUTO_INCREMENT = 1;
ALTER TABLE ingresos AUTO_INCREMENT = 1;
ALTER TABLE presupuestos AUTO_INCREMENT = 1;
ALTER TABLE categorias AUTO_INCREMENT = 1;

-- Si existe una base espejo `erp_ium_espejo` y quieres dejarla consistente,
-- puedes ejecutar TRUNCATE sobre sus tablas. Está comentado por seguridad.
-- TRUNCATE TABLE erp_ium_espejo.usuario_ingreso;
-- TRUNCATE TABLE erp_ium_espejo.usuario_historial;
-- TRUNCATE TABLE erp_ium_espejo.auditoria;
-- TRUNCATE TABLE erp_ium_espejo.egresos;
-- TRUNCATE TABLE erp_ium_espejo.ingresos;
-- TRUNCATE TABLE erp_ium_espejo.presupuestos;
-- TRUNCATE TABLE erp_ium_espejo.categorias;

-- Ahora insertamos datos de ejemplo realistas.
-- Nota: ajusta `id_user` según tu entorno (aquí usamos 2 como usuario de ejemplo).

-- 1) Categorías (creamos 2 de Ingreso y 4 de Egreso)
INSERT INTO categorias (nombre, tipo, descripcion, id_user) VALUES
('Matriculación','Ingreso','Cobros por matriculas y reinscripciones', 2),
('Servicios','Ingreso','Pagos por servicios académicos', 2),
('Operaciones','Egreso','Gastos operativos generales', 2),
('RRHH','Egreso','Sueldos y prestaciones', 2),
('Materiales','Egreso','Materiales y suministros', 2),
('Tecnología','Egreso','Hardware, licencias y servicios TI', 2);

-- 2) Presupuesto general (sin id_categoria)
INSERT INTO presupuestos (monto_limite, fecha, id_categoria, id_user, parent_presupuesto)
VALUES (50000.00, CURDATE(), NULL, 2, NULL);
SET @parent_pres = LAST_INSERT_ID();

-- 3) Presupuestos por categoría que dependen del presupuesto general
-- Insertar presupuestos por categoría referenciando el presupuesto general recién creado
INSERT INTO presupuestos (monto_limite, fecha, id_categoria, id_user, parent_presupuesto)
VALUES
(15000.00, CURDATE(), (SELECT id_categoria FROM categorias WHERE nombre = 'Operaciones' LIMIT 1), 2, (SELECT id_presupuesto FROM presupuestos WHERE id_categoria IS NULL ORDER BY id_presupuesto DESC LIMIT 1)),
(18000.00, CURDATE(), (SELECT id_categoria FROM categorias WHERE nombre = 'RRHH' LIMIT 1), 2, (SELECT id_presupuesto FROM presupuestos WHERE id_categoria IS NULL ORDER BY id_presupuesto DESC LIMIT 1)),
(8000.00, CURDATE(), (SELECT id_categoria FROM categorias WHERE nombre = 'Materiales' LIMIT 1), 2, (SELECT id_presupuesto FROM presupuestos WHERE id_categoria IS NULL ORDER BY id_presupuesto DESC LIMIT 1)),
(7000.00, CURDATE(), (SELECT id_categoria FROM categorias WHERE nombre = 'Tecnología' LIMIT 1), 2, (SELECT id_presupuesto FROM presupuestos WHERE id_categoria IS NULL ORDER BY id_presupuesto DESC LIMIT 1));

-- 4) Ingresos: generar algunos registros en categorias de Ingreso
INSERT INTO ingresos (fecha, alumno, matricula, nivel, monto, metodo_de_pago, concepto, mes_correspondiente, anio, observaciones, dia_pago, modalidad, grado, programa, grupo, id_categoria)
VALUES
(CURDATE(), 'Ana Pérez', 'A1001', 'Licenciatura', 1200.00, 'Transferencia', 'Inscripción', 'noviembre', YEAR(CURDATE()), '', 15, 'Semestral', 1, 'Lic. Derecho', '1', (SELECT id_categoria FROM categorias WHERE nombre = 'Matriculación' LIMIT 1)),
(CURDATE(), 'Luis Torres', 'A1002', 'Maestría', 850.00, 'Depósito', 'Reinscripción', 'noviembre', YEAR(CURDATE()), '', 16, 'Semestral', 1, 'M.A. Administración', '1', (SELECT id_categoria FROM categorias WHERE nombre = 'Matriculación' LIMIT 1)),
(CURDATE(), 'Servicios Externos', 'SERV001', 'Licenciatura', 5000.00, 'Transferencia', 'Otros', 'noviembre', YEAR(CURDATE()), 'Pago por taller', 10, 'Cuatrimestral', 0, 'Servicios', '', (SELECT id_categoria FROM categorias WHERE nombre = 'Servicios' LIMIT 1));

-- 5) Egresos: crear gastos relacionados a presupuestos por categoria
INSERT INTO egresos (proveedor, activo_fijo, descripcion, monto, fecha, destinatario, forma_pago, documento_de_amparo, id_user, id_presupuesto, id_categoria)
VALUES
('Proveedor Suministros', 'NO', 'Compra de material de oficina', 1200.00, CURDATE(), 'Operaciones', 'Transferencia', 'FACT-001', 2,
 (SELECT id_presupuesto FROM presupuestos WHERE id_categoria = (SELECT id_categoria FROM categorias WHERE nombre = 'Operaciones' LIMIT 1) ORDER BY id_presupuesto DESC LIMIT 1),
 (SELECT id_categoria FROM categorias WHERE nombre = 'Operaciones' LIMIT 1)),
('Gastos Nómina', 'NO', 'Pago de nómina', 5000.00, CURDATE(), 'RRHH', 'Transferencia', 'NOM-2025-11', 2,
 (SELECT id_presupuesto FROM presupuestos WHERE id_categoria = (SELECT id_categoria FROM categorias WHERE nombre = 'RRHH' LIMIT 1) ORDER BY id_presupuesto DESC LIMIT 1),
 (SELECT id_categoria FROM categorias WHERE nombre = 'RRHH' LIMIT 1)),
('Proveedor TI', 'SI', 'Servidor y licencias', 3000.00, CURDATE(), 'Tecnología', 'Transferencia', 'FACT-TI-01', 2,
 (SELECT id_presupuesto FROM presupuestos WHERE id_categoria = (SELECT id_categoria FROM categorias WHERE nombre = 'Tecnología' LIMIT 1) ORDER BY id_presupuesto DESC LIMIT 1),
 (SELECT id_categoria FROM categorias WHERE nombre = 'Tecnología' LIMIT 1)),
('Proveedor Materiales', 'NO', 'Compra de materiales varios', 600.00, CURDATE(), 'Materiales', 'Efectivo', 'FACT-MAT-01', 2,
 (SELECT id_presupuesto FROM presupuestos WHERE id_categoria = (SELECT id_categoria FROM categorias WHERE nombre = 'Materiales' LIMIT 1) ORDER BY id_presupuesto DESC LIMIT 1),
 (SELECT id_categoria FROM categorias WHERE nombre = 'Materiales' LIMIT 1));

-- 6) Ajuste: establecer AUTO_INCREMENT start (opcional)
-- ALTER TABLE categorias AUTO_INCREMENT = 100;
-- ALTER TABLE presupuestos AUTO_INCREMENT = 100;
-- ALTER TABLE ingresos AUTO_INCREMENT = 1000;
-- ALTER TABLE egresos AUTO_INCREMENT = 1000;

-- Reactivar foreign key checks
SET FOREIGN_KEY_CHECKS = @OLD_FK_CHECKS;
COMMIT;

-- FIN del script

-- Instrucciones de uso:
-- 1) Revisa y ajusta `id_user` si es necesario antes de ejecutar.
-- 2) Haz copia de seguridad (backup) de tu BD.
-- 3) Ejecuta el script en phpMyAdmin o en CLI:
--    mysql -u usuario -p nombre_bd < migrations/seed_realistic_data.sql

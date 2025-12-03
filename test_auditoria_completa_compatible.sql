
-- 1. CATEGORÍAS
SET @auditoria_user_id = 2;
INSERT INTO categorias (nombre, tipo, descripcion, id_user, no_borrable) VALUES ('Test Categoria', 'Egreso', 'Prueba de inserción', 2, 0);
SET @cat_id = LAST_INSERT_ID();
UPDATE categorias SET nombre = 'Test Categoria Modificada', descripcion = 'Prueba de actualización' WHERE id_categoria = @cat_id;
DELETE FROM categorias WHERE id_categoria = @cat_id;


-- 2. PRESUPUESTOS
SET @auditoria_user_id = 2;
INSERT INTO presupuestos (nombre, fecha, monto_limite, id_user, parent_presupuesto, id_categoria) VALUES ('Test Presupuesto', '2025-11-25', 50000.00, 2, NULL, NULL);
SET @pres_id = LAST_INSERT_ID();
UPDATE presupuestos SET monto_limite = 60000.00, nombre = 'Test Presupuesto Modificado' WHERE id_presupuesto = @pres_id;
DELETE FROM presupuestos WHERE id_presupuesto = @pres_id;


-- 3. INGRESOS
SET @auditoria_user_id = 2;
SELECT @cat_ingreso := id_categoria FROM categorias WHERE tipo = 'Ingreso' LIMIT 1;
INSERT INTO ingresos (fecha, monto, alumno, matricula, nivel, programa, grado, modalidad, grupo, id_categoria, metodo_de_pago, mes_correspondiente, anio, observaciones) VALUES ('2025-11-25', 1000.00, 'Alumno Test', 'TEST001', 'Licenciatura', 'Test', 1, 'Semestral', '1', @cat_ingreso, 'Efectivo', 'noviembre', 2025, 'Prueba inserción');
SET @ing_folio = LAST_INSERT_ID();
UPDATE ingresos SET monto = 1500.00, observaciones = 'Prueba de actualización' WHERE folio_ingreso = @ing_folio;
DELETE FROM ingresos WHERE folio_ingreso = @ing_folio;


-- 4. EGRESOS
SET @auditoria_user_id = 2;
SELECT @cat_egreso := id_categoria FROM categorias WHERE tipo = 'Egreso' LIMIT 1;
SELECT @pres_egreso := id_presupuesto FROM presupuestos WHERE parent_presupuesto IS NULL LIMIT 1;
INSERT INTO egresos (proveedor, descripcion, monto, fecha, destinatario, forma_pago, documento_de_amparo, id_user, id_presupuesto, id_categoria) VALUES ('Proveedor Test', 'Test Egreso', 500.00, '2025-11-25', 'Destinatario Test', 'Efectivo', 'DocTest', 2, @pres_egreso, @cat_egreso);
SET @egr_folio = LAST_INSERT_ID();
UPDATE egresos SET monto = 750.00, descripcion = 'Test Egreso Modificado' WHERE folio_egreso = @egr_folio;
DELETE FROM egresos WHERE folio_egreso = @egr_folio;


-- 5. USUARIOS
SET @auditoria_user_id = 2;
INSERT INTO usuarios (nombre, username, password, rol) VALUES ('Usuario Test', 'test_user', '$2y$10$testhashdummyvalue', 'SU');
SET @user_id = LAST_INSERT_ID();
UPDATE usuarios SET nombre = 'Usuario Test Modificado' WHERE id_user = @user_id;
DELETE FROM usuarios WHERE id_user = @user_id;

-- VERIFICAR AUDITORÍA
SELECT id_auditoria, fecha_hora, seccion, accion, old_valor, new_valor, folio_egreso, folio_ingreso FROM auditoria ORDER BY fecha_hora DESC LIMIT 30;

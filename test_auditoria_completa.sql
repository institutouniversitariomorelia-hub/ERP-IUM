-- =====================================================
-- SCRIPT DE PRUEBA COMPLETA DE AUDITORÍA
-- Ejecutar línea por línea para revisar cada paso
-- =====================================================

-- 1. CATEGORÍAS
-- Inserción
INSERT INTO categorias (nombre, tipo, descripcion, id_user, no_borrable) 
VALUES ('Test Categoria', 'Egreso', 'Prueba de inserción', 2, 0);

-- Guardar el ID insertado
SET @cat_id = LAST_INSERT_ID();

-- Actualización
UPDATE categorias 
SET nombre = 'Test Categoria Modificada', descripcion = 'Prueba de actualización'
WHERE id_categoria = @cat_id;

-- Eliminación
DELETE FROM categorias WHERE id_categoria = @cat_id;


-- 2. PRESUPUESTOS
-- Inserción (Presupuesto General)
INSERT INTO presupuestos (nombre, fecha, monto_limite, id_user, parent_presupuesto, id_categoria)
VALUES ('Test Presupuesto', '2025-11-25', 50000.00, 2, NULL, NULL);

SET @pres_id = LAST_INSERT_ID();

-- Actualización
UPDATE presupuestos 
SET monto_limite = 60000.00, nombre = 'Test Presupuesto Modificado'
WHERE id_presupuesto = @pres_id;

-- Eliminación
DELETE FROM presupuestos WHERE id_presupuesto = @pres_id;


-- 3. INGRESOS
-- Obtener una categoría de ingreso existente
SELECT @cat_ingreso := id_categoria FROM categorias WHERE tipo = 'Ingreso' LIMIT 1;

-- Inserción
INSERT INTO ingresos (fecha, monto, alumno, matricula, nivel, programa, grado, modalidad, grupo, id_categoria, metodo_de_pago, mes_correspondiente, anio, observaciones)
VALUES ('2025-11-25', 1000.00, 'Alumno Test', 'TEST001', 'Licenciatura', 'Test', '1', 'Semestral', '1', @cat_ingreso, 'Efectivo', 11, 2025, 'Prueba inserción');

SET @ing_folio = LAST_INSERT_ID();

-- Actualización
UPDATE ingresos 
SET monto = 1500.00, observaciones = 'Prueba de actualización'
WHERE folio_ingreso = @ing_folio;

-- Eliminación
DELETE FROM ingresos WHERE folio_ingreso = @ing_folio;


-- 4. EGRESOS
-- Obtener una categoría de egreso y presupuesto
SELECT @cat_egreso := id_categoria FROM categorias WHERE tipo = 'Egreso' LIMIT 1;
SELECT @pres_egreso := id_presupuesto FROM presupuestos WHERE parent_presupuesto IS NULL LIMIT 1;

-- Inserción
INSERT INTO egresos (fecha, monto, descripcion, id_categoria, id_presupuesto, id_user)
VALUES ('2025-11-25', 500.00, 'Test Egreso', @cat_egreso, @pres_egreso, 2);

SET @egr_folio = LAST_INSERT_ID();

-- Actualización
UPDATE egresos 
SET monto = 750.00, descripcion = 'Test Egreso Modificado'
WHERE folio_egreso = @egr_folio;

-- Eliminación
DELETE FROM egresos WHERE folio_egreso = @egr_folio;


-- 5. USUARIOS
-- Inserción
INSERT INTO usuarios (nombre, username, password_hash, rol)
VALUES ('Usuario Test', 'test_user', '$2y$10$testhashdummyvalue', 'CA');

SET @user_id = LAST_INSERT_ID();

-- Actualización
UPDATE usuarios 
SET nombre = 'Usuario Test Modificado'
WHERE id_user = @user_id;

-- Eliminación
DELETE FROM usuarios WHERE id_user = @user_id;


-- =====================================================
-- VERIFICAR AUDITORÍA - Ver últimos 30 registros
-- =====================================================
SELECT 
    a.id_auditoria,
    a.fecha_hora,
    a.seccion,
    a.accion,
    CASE 
        WHEN a.old_valor IS NOT NULL THEN 'Sí'
        ELSE 'No'
    END as tiene_old,
    CASE 
        WHEN a.new_valor IS NOT NULL THEN 'Sí'
        ELSE 'No'
    END as tiene_new,
    a.folio_egreso,
    a.folio_ingreso,
    COALESCE(u.nombre, 'Sistema') as usuario
FROM auditoria a
LEFT JOIN usuario_historial uh ON uh.id_ha = a.id_auditoria
LEFT JOIN usuarios u ON u.id_user = uh.id_user
ORDER BY a.fecha_hora DESC
LIMIT 30;

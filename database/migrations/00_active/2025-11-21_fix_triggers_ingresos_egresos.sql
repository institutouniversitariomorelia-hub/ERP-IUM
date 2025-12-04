-- =====================================================
-- Actualizar triggers de ingresos y egresos
-- Eliminar referencias a 'concepto' y 'activo_fijo'
-- =====================================================

USE erp_ium;

-- =====================================================
-- TRIGGERS DE INGRESOS
-- =====================================================

-- Eliminar triggers existentes de ingresos
DROP TRIGGER IF EXISTS trg_ingresos_after_insert_espejo;
DROP TRIGGER IF EXISTS trg_ingresos_after_insert_aud;
DROP TRIGGER IF EXISTS trg_ingresos_after_update;
DROP TRIGGER IF EXISTS trg_ingresos_after_update_espejo;
DROP TRIGGER IF EXISTS trg_ingresos_before_delete;
DROP TRIGGER IF EXISTS trg_ingresos_before_delete_espejo;

-- Recrear trigger: INSERT a espejo (SIN concepto)
DELIMITER $$
CREATE TRIGGER trg_ingresos_after_insert_espejo
AFTER INSERT ON ingresos
FOR EACH ROW
BEGIN
    INSERT INTO `erp_ium_espejo`.`ingresos`
    (folio_ingreso, fecha, alumno, matricula, nivel, monto, metodo_de_pago, 
     mes_correspondiente, anio, observaciones, dia_pago, modalidad, grado, programa, grupo, id_categoria)
    VALUES (NEW.folio_ingreso, NEW.fecha, NEW.alumno, NEW.matricula, NEW.nivel, NEW.monto, NEW.metodo_de_pago,
     NEW.mes_correspondiente, NEW.anio, NEW.observaciones, NEW.dia_pago, NEW.modalidad, NEW.grado, NEW.programa, NEW.grupo, NEW.id_categoria);
END$$
DELIMITER ;

-- Recrear trigger: INSERT auditoría (SIN concepto)
DELIMITER $$
CREATE TRIGGER trg_ingresos_after_insert_aud
AFTER INSERT ON ingresos
FOR EACH ROW
BEGIN
    SET @new_ingreso_data = JSON_OBJECT(
        'folio_ingreso', NEW.folio_ingreso,
        'alumno', NEW.alumno,
        'monto', NEW.monto,
        'fecha', NEW.fecha,
        'id_categoria', NEW.id_categoria
    );
    CALL sp_auditar_accion(@auditoria_user_id, 'ingresos', 'Insercion', NULL, @new_ingreso_data, NULL, NEW.folio_ingreso);
END$$
DELIMITER ;

-- Recrear trigger: UPDATE auditoría
DELIMITER $$
CREATE TRIGGER trg_ingresos_after_update
AFTER UPDATE ON ingresos
FOR EACH ROW
BEGIN
    SET @old_data = JSON_OBJECT('folio_ingreso', OLD.folio_ingreso, 'monto', OLD.monto);
    SET @new_data = JSON_OBJECT('folio_ingreso', NEW.folio_ingreso, 'monto', NEW.monto);
    CALL sp_auditar_accion(@auditoria_user_id, 'ingresos', 'Actualizacion', @old_data, @new_data, NULL, NEW.folio_ingreso);
END$$
DELIMITER ;

-- Recrear trigger: UPDATE a espejo
DELIMITER $$
CREATE TRIGGER trg_ingresos_after_update_espejo
AFTER UPDATE ON ingresos
FOR EACH ROW
BEGIN
    UPDATE `erp_ium_espejo`.`ingresos`
    SET alumno = NEW.alumno,
        monto = NEW.monto,
        fecha = NEW.fecha,
        matricula = NEW.matricula,
        id_categoria = NEW.id_categoria
    WHERE folio_ingreso = NEW.folio_ingreso;
END$$
DELIMITER ;

-- Recrear trigger: DELETE auditoría (SIN concepto)
DELIMITER $$
CREATE TRIGGER trg_ingresos_before_delete
BEFORE DELETE ON ingresos
FOR EACH ROW
BEGIN
    SET @old_ingreso_data_del = JSON_OBJECT(
        'folio_ingreso', OLD.folio_ingreso,
        'alumno', OLD.alumno,
        'monto', OLD.monto,
        'fecha', OLD.fecha,
        'id_categoria', OLD.id_categoria
    );
    CALL sp_auditar_accion(@auditoria_user_id, 'ingresos', 'Eliminacion', @old_ingreso_data_del, NULL, NULL, OLD.folio_ingreso);
END$$
DELIMITER ;

-- Recrear trigger: DELETE a espejo
DELIMITER $$
CREATE TRIGGER trg_ingresos_before_delete_espejo
BEFORE DELETE ON ingresos
FOR EACH ROW
BEGIN
    DELETE FROM `erp_ium_espejo`.`ingresos` WHERE folio_ingreso = OLD.folio_ingreso;
END$$
DELIMITER ;

-- =====================================================
-- TRIGGERS DE EGRESOS
-- =====================================================

-- Eliminar triggers existentes de egresos
DROP TRIGGER IF EXISTS trg_egresos_after_insert_espejo;
DROP TRIGGER IF EXISTS trg_egresos_after_insert_aud;
DROP TRIGGER IF EXISTS trg_egresos_after_update;
DROP TRIGGER IF EXISTS trg_egresos_after_update_espejo;
DROP TRIGGER IF EXISTS trg_egresos_before_delete;
DROP TRIGGER IF EXISTS trg_egresos_before_delete_espejo;

-- Recrear trigger: INSERT a espejo (SIN activo_fijo)
DELIMITER $$
CREATE TRIGGER trg_egresos_after_insert_espejo
AFTER INSERT ON egresos
FOR EACH ROW
BEGIN
    INSERT INTO `erp_ium_espejo`.`egresos`
    (folio_egreso, proveedor, descripcion, monto, fecha, destinatario, forma_pago, documento_de_amparo, id_user, id_presupuesto, id_categoria)
    VALUES (NEW.folio_egreso, NEW.proveedor, NEW.descripcion, NEW.monto, NEW.fecha, NEW.destinatario, NEW.forma_pago, NEW.documento_de_amparo, NEW.id_user, NEW.id_presupuesto, NEW.id_categoria);
END$$
DELIMITER ;

-- Recrear trigger: INSERT auditoría (SIN activo_fijo)
DELIMITER $$
CREATE TRIGGER trg_egresos_after_insert_aud
AFTER INSERT ON egresos
FOR EACH ROW
BEGIN
    SET @new_egreso_data = JSON_OBJECT(
        'folio_egreso', NEW.folio_egreso,
        'proveedor', NEW.proveedor,
        'monto', NEW.monto,
        'fecha', NEW.fecha,
        'id_categoria', NEW.id_categoria
    );
    CALL sp_auditar_accion(@auditoria_user_id, 'egresos', 'Insercion', NULL, @new_egreso_data, NULL, NEW.folio_egreso);
END$$
DELIMITER ;

-- Recrear trigger: UPDATE auditoría
DELIMITER $$
CREATE TRIGGER trg_egresos_after_update
AFTER UPDATE ON egresos
FOR EACH ROW
BEGIN
    SET @old_data = JSON_OBJECT('folio_egreso', OLD.folio_egreso, 'monto', OLD.monto);
    SET @new_data = JSON_OBJECT('folio_egreso', NEW.folio_egreso, 'monto', NEW.monto);
    CALL sp_auditar_accion(@auditoria_user_id, 'egresos', 'Actualizacion', @old_data, @new_data, NULL, NEW.folio_egreso);
END$$
DELIMITER ;

-- Recrear trigger: UPDATE a espejo
DELIMITER $$
CREATE TRIGGER trg_egresos_after_update_espejo
AFTER UPDATE ON egresos
FOR EACH ROW
BEGIN
    UPDATE `erp_ium_espejo`.`egresos`
    SET proveedor = NEW.proveedor,
        monto = NEW.monto,
        fecha = NEW.fecha,
        descripcion = NEW.descripcion,
        id_categoria = NEW.id_categoria
    WHERE folio_egreso = NEW.folio_egreso;
END$$
DELIMITER ;

-- Recrear trigger: DELETE auditoría
DELIMITER $$
CREATE TRIGGER trg_egresos_before_delete
BEFORE DELETE ON egresos
FOR EACH ROW
BEGIN
    SET @old_egreso_data_del = JSON_OBJECT(
        'folio_egreso', OLD.folio_egreso,
        'proveedor', OLD.proveedor,
        'monto', OLD.monto,
        'fecha', OLD.fecha,
        'id_categoria', OLD.id_categoria
    );
    CALL sp_auditar_accion(@auditoria_user_id, 'egresos', 'Eliminacion', @old_egreso_data_del, NULL, NULL, OLD.folio_egreso);
END$$
DELIMITER ;

-- Recrear trigger: DELETE a espejo
DELIMITER $$
CREATE TRIGGER trg_egresos_before_delete_espejo
BEFORE DELETE ON egresos
FOR EACH ROW
BEGIN
    DELETE FROM `erp_ium_espejo`.`egresos` WHERE folio_egreso = OLD.folio_egreso;
END$$
DELIMITER ;

-- =====================================================
-- Verificación final
-- =====================================================
SELECT 'Triggers actualizados correctamente' AS Status;
SHOW TRIGGERS FROM erp_ium WHERE `Table` IN ('ingresos', 'egresos');

-- RESTAURAR TRIGGERS DE AUDITORÍA A USO DE VARIABLE @auditoria_user_id

-- CATEGORÍAS
DROP TRIGGER IF EXISTS trg_categorias_after_insert_aud;
DELIMITER $$
CREATE TRIGGER trg_categorias_after_insert_aud AFTER INSERT ON categorias FOR EACH ROW BEGIN
  SET @new_data = JSON_OBJECT('id_categoria', NEW.id_categoria, 'nombre', NEW.nombre, 'tipo', NEW.tipo);
  CALL sp_auditar_accion(@auditoria_user_id, 'categorias', 'Insercion', NULL, @new_data, NULL, NULL);
END $$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_categorias_after_update;
DELIMITER $$
CREATE TRIGGER trg_categorias_after_update AFTER UPDATE ON categorias FOR EACH ROW BEGIN
  SET @old_data = JSON_OBJECT('id_categoria', OLD.id_categoria, 'nombre', OLD.nombre, 'tipo', OLD.tipo);
  SET @new_data = JSON_OBJECT('id_categoria', NEW.id_categoria, 'nombre', NEW.nombre, 'tipo', NEW.tipo);
  CALL sp_auditar_accion(@auditoria_user_id, 'categorias', 'Actualizacion', @old_data, @new_data, NULL, NULL);
END $$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_categorias_before_delete;
DELIMITER $$
CREATE TRIGGER trg_categorias_before_delete BEFORE DELETE ON categorias FOR EACH ROW BEGIN
  SET @old_data = JSON_OBJECT('id_categoria', OLD.id_categoria, 'nombre', OLD.nombre, 'tipo', OLD.tipo);
  CALL sp_auditar_accion(@auditoria_user_id, 'categorias', 'Eliminacion', @old_data, NULL, NULL, NULL);
END $$
DELIMITER ;

-- PRESUPUESTOS
DROP TRIGGER IF EXISTS trg_presupuestos_after_insert_aud;
DELIMITER $$
CREATE TRIGGER trg_presupuestos_after_insert_aud AFTER INSERT ON presupuestos FOR EACH ROW BEGIN
  SET @new_data = JSON_OBJECT('id_presupuesto', NEW.id_presupuesto, 'monto_limite', NEW.monto_limite, 'nombre', NEW.nombre, 'fecha', NEW.fecha, 'id_categoria', NEW.id_categoria);
  CALL sp_auditar_accion(@auditoria_user_id, 'presupuestos', 'Insercion', NULL, @new_data, NULL, NULL);
END $$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_presupuestos_after_update_aud;
DELIMITER $$
CREATE TRIGGER trg_presupuestos_after_update_aud AFTER UPDATE ON presupuestos FOR EACH ROW BEGIN
  SET @old_data = JSON_OBJECT('id_presupuesto', OLD.id_presupuesto, 'monto_limite', OLD.monto_limite, 'nombre', OLD.nombre, 'fecha', OLD.fecha, 'id_categoria', OLD.id_categoria);
  SET @new_data = JSON_OBJECT('id_presupuesto', NEW.id_presupuesto, 'monto_limite', NEW.monto_limite, 'nombre', NEW.nombre, 'fecha', NEW.fecha, 'id_categoria', NEW.id_categoria);
  CALL sp_auditar_accion(@auditoria_user_id, 'presupuestos', 'Actualizacion', @old_data, @new_data, NULL, NULL);
END $$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_presupuestos_before_delete;
DELIMITER $$
CREATE TRIGGER trg_presupuestos_before_delete BEFORE DELETE ON presupuestos FOR EACH ROW BEGIN
  SET @old_data = JSON_OBJECT('id_presupuesto', OLD.id_presupuesto, 'monto_limite', OLD.monto_limite, 'nombre', OLD.nombre, 'fecha', OLD.fecha, 'id_categoria', OLD.id_categoria);
  CALL sp_auditar_accion(@auditoria_user_id, 'presupuestos', 'Eliminacion', @old_data, NULL, NULL, NULL);
END $$
DELIMITER ;

-- INGRESOS
DROP TRIGGER IF EXISTS trg_ingresos_after_insert_aud;
DELIMITER $$
CREATE TRIGGER trg_ingresos_after_insert_aud AFTER INSERT ON ingresos FOR EACH ROW BEGIN
  SET @new_ingreso_data = JSON_OBJECT('folio_ingreso', NEW.folio_ingreso, 'alumno', NEW.alumno, 'monto', NEW.monto, 'fecha', NEW.fecha, 'id_categoria', NEW.id_categoria);
  CALL sp_auditar_accion(@auditoria_user_id, 'ingresos', 'Insercion', NULL, @new_ingreso_data, NULL, NEW.folio_ingreso);
END $$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_ingresos_after_update;
DELIMITER $$
CREATE TRIGGER trg_ingresos_after_update AFTER UPDATE ON ingresos FOR EACH ROW BEGIN
  SET @old_data = JSON_OBJECT('folio_ingreso', OLD.folio_ingreso, 'monto', OLD.monto);
  SET @new_data = JSON_OBJECT('folio_ingreso', NEW.folio_ingreso, 'monto', NEW.monto);
  CALL sp_auditar_accion(@auditoria_user_id, 'ingresos', 'Actualizacion', @old_data, @new_data, NULL, NEW.folio_ingreso);
END $$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_ingresos_before_delete;
DELIMITER $$
CREATE TRIGGER trg_ingresos_before_delete BEFORE DELETE ON ingresos FOR EACH ROW BEGIN
  SET @old_ingreso_data_del = JSON_OBJECT('folio_ingreso', OLD.folio_ingreso, 'alumno', OLD.alumno, 'monto', OLD.monto, 'fecha', OLD.fecha, 'id_categoria', OLD.id_categoria);
  CALL sp_auditar_accion(@auditoria_user_id, 'ingresos', 'Eliminacion', @old_ingreso_data_del, NULL, NULL, OLD.folio_ingreso);
END $$
DELIMITER ;

-- EGRESOS
DROP TRIGGER IF EXISTS trg_egresos_after_insert_aud;
DELIMITER $$
CREATE TRIGGER trg_egresos_after_insert_aud AFTER INSERT ON egresos FOR EACH ROW BEGIN
  SET @new_egreso_data = JSON_OBJECT('folio_egreso', NEW.folio_egreso, 'proveedor', NEW.proveedor, 'monto', NEW.monto, 'fecha', NEW.fecha, 'id_categoria', NEW.id_categoria);
  CALL sp_auditar_accion(@auditoria_user_id, 'egresos', 'Insercion', NULL, @new_egreso_data, NULL, NEW.folio_egreso);
END $$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_egresos_after_update;
DELIMITER $$
CREATE TRIGGER trg_egresos_after_update AFTER UPDATE ON egresos FOR EACH ROW BEGIN
  SET @old_data = JSON_OBJECT('folio_egreso', OLD.folio_egreso, 'monto', OLD.monto);
  SET @new_data = JSON_OBJECT('folio_egreso', NEW.folio_egreso, 'monto', NEW.monto);
  CALL sp_auditar_accion(@auditoria_user_id, 'egresos', 'Actualizacion', @old_data, @new_data, NULL, NEW.folio_egreso);
END $$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_egresos_before_delete;
DELIMITER $$
CREATE TRIGGER trg_egresos_before_delete BEFORE DELETE ON egresos FOR EACH ROW BEGIN
  SET @old_egreso_data_del = JSON_OBJECT('folio_egreso', OLD.folio_egreso, 'proveedor', OLD.proveedor, 'monto', OLD.monto, 'fecha', OLD.fecha, 'id_categoria', OLD.id_categoria);
  CALL sp_auditar_accion(@auditoria_user_id, 'egresos', 'Eliminacion', @old_egreso_data_del, NULL, NULL, OLD.folio_egreso);
END $$
DELIMITER ;

-- =====================================================================
-- MIGRACIÓN: Agregar id_categoria a presupuestos
-- Fecha: 2025-11-07
-- Base de datos: erp_ium
-- =====================================================================
-- IMPORTANTE: Ejecuta esto en phpMyAdmin o consola MySQL ANTES de usar
-- el módulo de presupuestos con el nuevo código.
-- =====================================================================

USE `erp_ium`;

START TRANSACTION;

-- 1) Verificar y agregar columna id_categoria a presupuestos (si no existe)
SET @col_exists := (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = 'erp_ium' 
      AND TABLE_NAME = 'presupuestos' 
      AND COLUMN_NAME = 'id_categoria'
);

SET @sql := IF(
    @col_exists = 0,
    'ALTER TABLE `presupuestos` ADD COLUMN `id_categoria` INT NULL AFTER `fecha`',
    'SELECT "Columna id_categoria ya existe en presupuestos" AS Info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Verificar si categorias tiene id_presupuesto para migrar datos
SET @col_id_pres := (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = 'erp_ium' 
      AND TABLE_NAME = 'categorias' 
      AND COLUMN_NAME = 'id_presupuesto'
);

-- Solo migrar si existe la columna id_presupuesto en categorias
SET @sql_migrate := IF(
    @col_id_pres > 0,
    'UPDATE `presupuestos` p JOIN `categorias` c ON c.id_presupuesto = p.id_presupuesto SET p.id_categoria = c.id_categoria WHERE p.id_categoria IS NULL',
    'SELECT "No hay columna id_presupuesto en categorias, skip migración" AS Info'
);

PREPARE stmt FROM @sql_migrate;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3) Para presupuestos huérfanos (sin categoría), asignar la primera categoría disponible
UPDATE `presupuestos` 
SET id_categoria = (SELECT id_categoria FROM categorias ORDER BY id_categoria LIMIT 1)
WHERE id_categoria IS NULL;

-- 4) Agregar índice y FK (si no existe)
SET @idx_exists := (
    SELECT COUNT(*) 
    FROM information_schema.STATISTICS 
    WHERE TABLE_SCHEMA = 'erp_ium' 
      AND TABLE_NAME = 'presupuestos' 
      AND INDEX_NAME = 'fk_presupuestos_categoria'
);

SET @sql := IF(
    @idx_exists = 0,
    'ALTER TABLE `presupuestos` ADD INDEX `fk_presupuestos_categoria` (`id_categoria`)',
    'SELECT "Índice fk_presupuestos_categoria ya existe" AS Info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5) Agregar constraint FK (si no existe)
SET @fk_exists := (
    SELECT COUNT(*) 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = 'erp_ium' 
      AND TABLE_NAME = 'presupuestos' 
      AND CONSTRAINT_NAME = 'fk_presupuestos_categoria'
);

SET @sql := IF(
    @fk_exists = 0,
    'ALTER TABLE `presupuestos` ADD CONSTRAINT `fk_presupuestos_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "FK fk_presupuestos_categoria ya existe" AS Info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6) Hacer id_categoria NOT NULL (después de backfill)
ALTER TABLE `presupuestos` MODIFY COLUMN `id_categoria` INT NOT NULL;

-- 7) Actualizar triggers para incluir id_categoria
DROP TRIGGER IF EXISTS `trg_presupuestos_after_insert`;
CREATE TRIGGER `trg_presupuestos_after_insert` AFTER INSERT ON `presupuestos` FOR EACH ROW
BEGIN
    INSERT INTO `erp_ium_espejo`.`presupuestos` (`id_presupuesto`,`monto_limite`,`fecha`,`id_categoria`,`id_user`)
    VALUES (NEW.id_presupuesto, NEW.monto_limite, NEW.fecha, NEW.id_categoria, NEW.id_user);
END;

DROP TRIGGER IF EXISTS `trg_presupuestos_after_update_espejo`;
CREATE TRIGGER `trg_presupuestos_after_update_espejo` AFTER UPDATE ON `presupuestos` FOR EACH ROW
BEGIN
    UPDATE `erp_ium_espejo`.`presupuestos`
    SET monto_limite = NEW.monto_limite, fecha = NEW.fecha, id_categoria = NEW.id_categoria, id_user = NEW.id_user
    WHERE id_presupuesto = NEW.id_presupuesto;
END;

DROP TRIGGER IF EXISTS `trg_presupuestos_after_insert_aud`;
CREATE TRIGGER `trg_presupuestos_after_insert_aud` AFTER INSERT ON `presupuestos` FOR EACH ROW
BEGIN
    SET @new_data = JSON_OBJECT('id_presupuesto', NEW.id_presupuesto, 'monto_limite', NEW.monto_limite, 'fecha', NEW.fecha, 'id_categoria', NEW.id_categoria);
    CALL sp_auditar_accion(@auditoria_user_id, 'presupuestos', 'Insercion', NULL, @new_data, NULL, NULL);
END;

DROP TRIGGER IF EXISTS `trg_presupuestos_after_update`;
CREATE TRIGGER `trg_presupuestos_after_update` AFTER UPDATE ON `presupuestos` FOR EACH ROW
BEGIN
    SET @old_data = JSON_OBJECT('id_presupuesto', OLD.id_presupuesto, 'monto_limite', OLD.monto_limite, 'fecha', OLD.fecha, 'id_categoria', OLD.id_categoria);
    SET @new_data = JSON_OBJECT('id_presupuesto', NEW.id_presupuesto, 'monto_limite', NEW.monto_limite, 'fecha', NEW.fecha, 'id_categoria', NEW.id_categoria);
    CALL sp_auditar_accion(@auditoria_user_id, 'presupuestos', 'Actualizacion', @old_data, @new_data, NULL, NULL);
END;

COMMIT;

SELECT 'Migración completada exitosamente. Ahora presupuestos tiene id_categoria.' AS Resultado;

-- =====================================================================
-- ROLLBACK (si necesitas revertir):
-- =====================================================================
-- START TRANSACTION;
-- ALTER TABLE `presupuestos` DROP FOREIGN KEY `fk_presupuestos_categoria`;
-- ALTER TABLE `presupuestos` DROP INDEX `fk_presupuestos_categoria`;
-- ALTER TABLE `presupuestos` DROP COLUMN `id_categoria`;
-- COMMIT;
-- =====================================================================

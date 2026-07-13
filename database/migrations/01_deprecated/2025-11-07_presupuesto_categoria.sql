-- Migration: Move relationship to presupuestos.id_categoria and remove categorias.id_presupuesto
-- Run inside database `erp_ium` as a privileged user. Test on staging first!

START TRANSACTION;

-- 1) Presupuestos: add id_categoria (nullable for transition)
ALTER TABLE `presupuestos` ADD COLUMN `id_categoria` INT NULL AFTER `fecha`;

-- 2) Backfill id_categoria from categorias.id_presupuesto if existed
UPDATE `presupuestos` p
JOIN `categorias` c ON c.id_presupuesto = p.id_presupuesto
SET p.id_categoria = c.id_categoria;

-- 3) If any presupuestos remain without category, you may assign a default or review manually
-- SELECT p.* FROM presupuestos p WHERE p.id_categoria IS NULL;

-- 4) Add index + FK (temporarily allow NULLs to avoid constraint failures)
ALTER TABLE `presupuestos` ADD INDEX `fk_presupuestos_categoria` (`id_categoria`);
ALTER TABLE `presupuestos` ADD CONSTRAINT `fk_presupuestos_categoria`
  FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`)
  ON DELETE CASCADE ON UPDATE CASCADE;

-- 5) Make id_categoria NOT NULL once data is backfilled
UPDATE `presupuestos` SET id_categoria = (
    SELECT c2.id_categoria FROM categorias c2 ORDER BY c2.id_categoria LIMIT 1
) WHERE id_categoria IS NULL; -- Fallback: set to first category to enforce NOT NULL (adjust as needed)
ALTER TABLE `presupuestos` MODIFY COLUMN `id_categoria` INT NOT NULL;

-- 6) categorias: drop FK to presupuesto and column id_presupuesto if it exists
SET @fk_name := (SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categorias' AND REFERENCED_TABLE_NAME = 'presupuestos' LIMIT 1);
SET @sql := IF(@fk_name IS NOT NULL, CONCAT('ALTER TABLE `categorias` DROP FOREIGN KEY `', @fk_name, '`'), 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Drop index if exists
SET @idx := (SELECT INDEX_NAME FROM information_schema.statistics
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categorias' AND INDEX_NAME = 'fk_categorias_presupuesto' LIMIT 1);
SET @sql := IF(@idx IS NOT NULL, 'ALTER TABLE `categorias` DROP INDEX `fk_categorias_presupuesto`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Drop column id_presupuesto if exists
SET @col := (SELECT COLUMN_NAME FROM information_schema.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categorias' AND COLUMN_NAME = 'id_presupuesto' LIMIT 1);
SET @sql := IF(@col IS NOT NULL, 'ALTER TABLE `categorias` DROP COLUMN `id_presupuesto`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 7) Update triggers to reflect new columns
-- Drop old triggers if they include id_presupuesto in categorias espejo
DROP TRIGGER IF EXISTS `trg_categorias_after_insert`;
CREATE TRIGGER `trg_categorias_after_insert` AFTER INSERT ON `categorias` FOR EACH ROW
BEGIN
    INSERT INTO `erp_ium_espejo`.`categorias` (`id_categoria`,`nombre`,`tipo`,`descripcion`,`id_user`)
    VALUES (NEW.id_categoria, NEW.nombre, NEW.tipo, NEW.descripcion, NEW.id_user);
END;

DROP TRIGGER IF EXISTS `trg_categorias_after_update_espejo`;
CREATE TRIGGER `trg_categorias_after_update_espejo` AFTER UPDATE ON `categorias` FOR EACH ROW
BEGIN
    UPDATE `erp_ium_espejo`.`categorias`
    SET nombre = NEW.nombre, tipo = NEW.tipo, descripcion = NEW.descripcion, id_user = NEW.id_user
    WHERE id_categoria = NEW.id_categoria;
END;

-- Presupuestos espejo now includes id_categoria
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

-- Update audit triggers to include id_categoria (optional but recommended)
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

-- ROLLBACK plan (manual):
--  - Add categorias.id_presupuesto again and backfill from presupuestos.id_presupuesto where categories match
--  - Remove presupuestos.id_categoria and related FKs
--  - Restore previous triggers

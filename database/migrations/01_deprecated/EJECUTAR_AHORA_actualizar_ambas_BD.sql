-- =====================================================================
-- MIGRACIÓN COMPLETA: Actualizar erp_ium Y erp_ium_espejo
-- Fecha: 2025-11-07
-- =====================================================================
-- IMPORTANTE: Ejecuta este script completo en phpMyAdmin
-- Actualiza AMBAS bases de datos para agregar id_categoria a presupuestos
-- =====================================================================

-- =====================================================================
-- PARTE 1: Actualizar BASE DE DATOS PRINCIPAL (erp_ium)
-- =====================================================================
USE `erp_ium`;

START TRANSACTION;

-- 1) Agregar columna id_categoria a presupuestos (si no existe)
SET @col_exists := (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = 'erp_ium' 
      AND TABLE_NAME = 'presupuestos' 
      AND COLUMN_NAME = 'id_categoria'
);

SET @sql := IF(
    @col_exists = 0,
    'ALTER TABLE `erp_ium`.`presupuestos` ADD COLUMN `id_categoria` INT NULL AFTER `fecha`',
    'SELECT "Columna id_categoria ya existe en erp_ium.presupuestos" AS Info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Asignar primera categoría disponible a todos los presupuestos sin categoría
UPDATE `erp_ium`.`presupuestos` 
SET id_categoria = (SELECT id_categoria FROM erp_ium.categorias ORDER BY id_categoria LIMIT 1)
WHERE id_categoria IS NULL;

-- 3) Eliminar FK existente si existe (para poder hacer NOT NULL)
SET @fk_exists := (
    SELECT CONSTRAINT_NAME
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = 'erp_ium' 
      AND TABLE_NAME = 'presupuestos' 
      AND CONSTRAINT_NAME = 'fk_presupuestos_categoria'
    LIMIT 1
);

SET @sql_drop_fk := IF(
    @fk_exists IS NOT NULL,
    'ALTER TABLE `erp_ium`.`presupuestos` DROP FOREIGN KEY `fk_presupuestos_categoria`',
    'SELECT "No hay FK para eliminar" AS Info'
);

PREPARE stmt FROM @sql_drop_fk;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4) Agregar índice (si no existe)
SET @idx_exists := (
    SELECT COUNT(*) 
    FROM information_schema.STATISTICS 
    WHERE TABLE_SCHEMA = 'erp_ium' 
      AND TABLE_NAME = 'presupuestos' 
      AND INDEX_NAME = 'fk_presupuestos_categoria'
);

SET @sql := IF(
    @idx_exists = 0,
    'ALTER TABLE `erp_ium`.`presupuestos` ADD INDEX `fk_presupuestos_categoria` (`id_categoria`)',
    'SELECT "Índice ya existe en erp_ium" AS Info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5) Hacer id_categoria NOT NULL (ahora sin FK conflictiva)
ALTER TABLE `erp_ium`.`presupuestos` MODIFY COLUMN `id_categoria` INT NOT NULL;

-- 6) Crear FK con RESTRICT
ALTER TABLE `erp_ium`.`presupuestos` 
ADD CONSTRAINT `fk_presupuestos_categoria` 
FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) 
ON DELETE RESTRICT ON UPDATE CASCADE;

COMMIT;

SELECT 'Parte 1 completada: erp_ium.presupuestos actualizado' AS Resultado;

-- =====================================================================
-- PARTE 2: Actualizar BASE DE DATOS ESPEJO (erp_ium_espejo)
-- =====================================================================
USE `erp_ium_espejo`;

START TRANSACTION;

-- 1) Agregar columna id_categoria a presupuestos espejo (si no existe)
SET @col_exists_espejo := (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = 'erp_ium_espejo' 
      AND TABLE_NAME = 'presupuestos' 
      AND COLUMN_NAME = 'id_categoria'
);

SET @sql_espejo := IF(
    @col_exists_espejo = 0,
    'ALTER TABLE `erp_ium_espejo`.`presupuestos` ADD COLUMN `id_categoria` INT NULL AFTER `fecha`',
    'SELECT "Columna id_categoria ya existe en erp_ium_espejo.presupuestos" AS Info'
);

PREPARE stmt FROM @sql_espejo;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Sincronizar datos desde erp_ium a erp_ium_espejo
UPDATE `erp_ium_espejo`.`presupuestos` e
JOIN `erp_ium`.`presupuestos` p ON e.id_presupuesto = p.id_presupuesto
SET e.id_categoria = p.id_categoria
WHERE e.id_categoria IS NULL;

-- 3) Para registros huérfanos en espejo, asignar primera categoría
UPDATE `erp_ium_espejo`.`presupuestos` 
SET id_categoria = (SELECT id_categoria FROM erp_ium_espejo.categorias ORDER BY id_categoria LIMIT 1)
WHERE id_categoria IS NULL;

-- 4) Hacer id_categoria NOT NULL en espejo
ALTER TABLE `erp_ium_espejo`.`presupuestos` MODIFY COLUMN `id_categoria` INT NOT NULL;

COMMIT;

SELECT 'Parte 2 completada: erp_ium_espejo.presupuestos actualizado' AS Resultado;

-- =====================================================================
-- PARTE 3: Actualizar TRIGGERS en erp_ium
-- =====================================================================
USE `erp_ium`;

-- Trigger para INSERT (espejo)
DROP TRIGGER IF EXISTS `trg_presupuestos_after_insert`;

DELIMITER $$
CREATE TRIGGER `trg_presupuestos_after_insert` AFTER INSERT ON `presupuestos` FOR EACH ROW
BEGIN
    INSERT INTO `erp_ium_espejo`.`presupuestos` (`id_presupuesto`,`monto_limite`,`fecha`,`id_categoria`,`id_user`)
    VALUES (NEW.id_presupuesto, NEW.monto_limite, NEW.fecha, NEW.id_categoria, NEW.id_user);
END$$
DELIMITER ;

-- Trigger para UPDATE (espejo)
DROP TRIGGER IF EXISTS `trg_presupuestos_after_update_espejo`;

DELIMITER $$
CREATE TRIGGER `trg_presupuestos_after_update_espejo` AFTER UPDATE ON `presupuestos` FOR EACH ROW
BEGIN
    UPDATE `erp_ium_espejo`.`presupuestos`
    SET monto_limite = NEW.monto_limite, 
        fecha = NEW.fecha, 
        id_categoria = NEW.id_categoria, 
        id_user = NEW.id_user
    WHERE id_presupuesto = NEW.id_presupuesto;
END$$
DELIMITER ;

-- Trigger para INSERT (auditoría)
DROP TRIGGER IF EXISTS `trg_presupuestos_after_insert_aud`;

DELIMITER $$
CREATE TRIGGER `trg_presupuestos_after_insert_aud` AFTER INSERT ON `presupuestos` FOR EACH ROW
BEGIN
    SET @new_data = JSON_OBJECT(
        'id_presupuesto', NEW.id_presupuesto, 
        'monto_limite', NEW.monto_limite, 
        'fecha', NEW.fecha, 
        'id_categoria', NEW.id_categoria
    );
    CALL sp_auditar_accion(@auditoria_user_id, 'presupuestos', 'Insercion', NULL, @new_data, NULL, NULL);
END$$
DELIMITER ;

-- Trigger para UPDATE (auditoría)
DROP TRIGGER IF EXISTS `trg_presupuestos_after_update`;

DELIMITER $$
CREATE TRIGGER `trg_presupuestos_after_update` AFTER UPDATE ON `presupuestos` FOR EACH ROW
BEGIN
    SET @old_data = JSON_OBJECT(
        'id_presupuesto', OLD.id_presupuesto, 
        'monto_limite', OLD.monto_limite, 
        'fecha', OLD.fecha, 
        'id_categoria', OLD.id_categoria
    );
    SET @new_data = JSON_OBJECT(
        'id_presupuesto', NEW.id_presupuesto, 
        'monto_limite', NEW.monto_limite, 
        'fecha', NEW.fecha, 
        'id_categoria', NEW.id_categoria
    );
    CALL sp_auditar_accion(@auditoria_user_id, 'presupuestos', 'Actualizacion', @old_data, @new_data, NULL, NULL);
END$$
DELIMITER ;

SELECT 'Parte 3 completada: Todos los triggers actualizados con id_categoria' AS Resultado;

-- =====================================================================
-- VERIFICACIÓN FINAL
-- =====================================================================
SELECT 
    '✓ MIGRACIÓN COMPLETADA EXITOSAMENTE' AS Estado,
    'erp_ium y erp_ium_espejo ahora tienen id_categoria en presupuestos' AS Detalle;

-- Mostrar estructura actualizada
SELECT 'Estructura de erp_ium.presupuestos:' AS Info;
DESCRIBE `erp_ium`.`presupuestos`;

SELECT 'Estructura de erp_ium_espejo.presupuestos:' AS Info;
DESCRIBE `erp_ium_espejo`.`presupuestos`;

-- =====================================================================
-- FIN DE LA MIGRACIÓN
-- =====================================================================

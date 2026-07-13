-- Migration: Añadir relación padre (parent_presupuesto) para presupuestos
-- Agrega columna parent_presupuesto NULL y permite id_categoria NULL para presupuestos generales
-- Rollback incluido al final

ALTER TABLE `presupuestos`
  MODIFY `id_categoria` INT NULL;

ALTER TABLE `presupuestos`
  ADD COLUMN `parent_presupuesto` INT NULL AFTER `id_user`;

-- Opcional: índice/foreign key (si la BD lo permite)
ALTER TABLE `presupuestos`
  ADD INDEX `idx_presupuestos_parent` (`parent_presupuesto`);

-- Si se desea FK (revisar permisos):
-- ALTER TABLE `presupuestos`
--   ADD CONSTRAINT `fk_presupuestos_parent` FOREIGN KEY (`parent_presupuesto`) REFERENCES `presupuestos` (`id_presupuesto`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ROLLBACK (ejecutar manualmente si es necesario):
-- ALTER TABLE `presupuestos` DROP INDEX `idx_presupuestos_parent`;
-- ALTER TABLE `presupuestos` DROP COLUMN `parent_presupuesto`;
-- ALTER TABLE `presupuestos` MODIFY `id_categoria` INT NOT NULL;

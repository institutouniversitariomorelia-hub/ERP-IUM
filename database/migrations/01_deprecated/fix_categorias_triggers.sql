-- Actualizar triggers de categorías para eliminar referencia a id_presupuesto

USE erp_ium;

-- Eliminar triggers existentes
DROP TRIGGER IF EXISTS trg_categorias_after_insert;
DROP TRIGGER IF EXISTS trg_categorias_after_update_espejo;

-- Recrear trigger INSERT sin id_presupuesto
DELIMITER $$
CREATE TRIGGER trg_categorias_after_insert
AFTER INSERT ON categorias
FOR EACH ROW
BEGIN
    INSERT INTO `erp_ium_espejo`.`categorias` 
    VALUES (NEW.id_categoria, NEW.nombre, NEW.tipo, NEW.concepto, NEW.descripcion, NEW.no_borrable, NEW.id_user);
END$$
DELIMITER ;

-- Recrear trigger UPDATE sin id_presupuesto
DELIMITER $$
CREATE TRIGGER trg_categorias_after_update_espejo
AFTER UPDATE ON categorias
FOR EACH ROW
BEGIN
    UPDATE `erp_ium_espejo`.`categorias` 
    SET nombre = NEW.nombre, 
        tipo = NEW.tipo, 
        concepto = NEW.concepto,
        descripcion = NEW.descripcion, 
        no_borrable = NEW.no_borrable,
        id_user = NEW.id_user
    WHERE id_categoria = NEW.id_categoria;
END$$
DELIMITER ;

SELECT 'Triggers actualizados correctamente' as resultado;

-- =========================================================================
-- MIGRACIÓN: Sistema de Pagos Divididos para Ingresos
-- Fecha: 2025-11-18
-- Descripción: Permite registrar múltiples métodos de pago para un solo ingreso
-- Ejemplo: Un alumno paga $1000 de inscripción: $800 con tarjeta y $200 en efectivo
-- =========================================================================

-- IMPORTANTE: Ejecutar este script en ambas bases de datos:
-- 1. erp_ium (principal)
-- 2. erp_ium_espejo (respaldo)

USE erp_ium;

-- =========================================================================
-- PASO 1: Crear tabla para almacenar pagos parciales
-- =========================================================================

CREATE TABLE IF NOT EXISTS `pagos_parciales` (
  `id_pago_parcial` INT(11) NOT NULL AUTO_INCREMENT,
  `folio_ingreso` INT(11) NOT NULL COMMENT 'Referencia al ingreso principal',
  `metodo_pago` ENUM('Efectivo','Transferencia','Depósito','Tarjeta Débito','Tarjeta Crédito') NOT NULL,
  `monto` DECIMAL(10,2) NOT NULL,
  `orden` TINYINT(2) NOT NULL DEFAULT 1 COMMENT 'Orden del pago parcial (1, 2, 3...)',
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pago_parcial`),
  KEY `idx_folio_ingreso` (`folio_ingreso`),
  CONSTRAINT `fk_pago_parcial_ingreso` 
    FOREIGN KEY (`folio_ingreso`) 
    REFERENCES `ingresos` (`folio_ingreso`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Almacena los pagos parciales/divididos de un ingreso';

-- =========================================================================
-- PASO 2: Crear la misma tabla en la base de datos espejo
-- =========================================================================

CREATE TABLE IF NOT EXISTS `erp_ium_espejo`.`pagos_parciales` (
  `id_pago_parcial` INT(11) NOT NULL AUTO_INCREMENT,
  `folio_ingreso` INT(11) NOT NULL,
  `metodo_pago` ENUM('Efectivo','Transferencia','Depósito','Tarjeta Débito','Tarjeta Crédito') NOT NULL,
  `monto` DECIMAL(10,2) NOT NULL,
  `orden` TINYINT(2) NOT NULL DEFAULT 1,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pago_parcial`),
  KEY `idx_folio_ingreso` (`folio_ingreso`),
  CONSTRAINT `fk_pago_parcial_ingreso_espejo` 
    FOREIGN KEY (`folio_ingreso`) 
    REFERENCES `erp_ium_espejo`.`ingresos` (`folio_ingreso`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================================================================
-- PASO 3: Actualizar ENUM de metodo_de_pago en tabla ingresos (agregar opciones de tarjeta)
-- =========================================================================

ALTER TABLE `ingresos` 
MODIFY COLUMN `metodo_de_pago` 
ENUM('Efectivo','Transferencia','Depósito','Tarjeta Débito','Tarjeta Crédito','Mixto') 
NOT NULL COMMENT 'Mixto = Pagos divididos en múltiples métodos';

ALTER TABLE `erp_ium_espejo`.`ingresos` 
MODIFY COLUMN `metodo_de_pago` 
ENUM('Efectivo','Transferencia','Depósito','Tarjeta Débito','Tarjeta Crédito','Mixto') 
NOT NULL;

-- =========================================================================
-- PASO 4: Crear triggers para sincronizar pagos_parciales con la BD espejo
-- =========================================================================

DELIMITER $$

-- Trigger: Después de insertar un pago parcial, replicarlo al espejo
CREATE TRIGGER `trg_pagos_parciales_after_insert` 
AFTER INSERT ON `pagos_parciales` 
FOR EACH ROW 
BEGIN 
    INSERT INTO `erp_ium_espejo`.`pagos_parciales` 
    VALUES (
        NEW.id_pago_parcial, 
        NEW.folio_ingreso, 
        NEW.metodo_pago, 
        NEW.monto, 
        NEW.orden, 
        NEW.fecha_registro
    ); 
END$$

-- Trigger: Después de actualizar un pago parcial, actualizarlo en el espejo
CREATE TRIGGER `trg_pagos_parciales_after_update` 
AFTER UPDATE ON `pagos_parciales` 
FOR EACH ROW 
BEGIN 
    UPDATE `erp_ium_espejo`.`pagos_parciales` 
    SET 
        folio_ingreso = NEW.folio_ingreso,
        metodo_pago = NEW.metodo_pago,
        monto = NEW.monto,
        orden = NEW.orden,
        fecha_registro = NEW.fecha_registro
    WHERE id_pago_parcial = NEW.id_pago_parcial; 
END$$

-- Trigger: Antes de eliminar un pago parcial, eliminarlo del espejo
CREATE TRIGGER `trg_pagos_parciales_before_delete` 
BEFORE DELETE ON `pagos_parciales` 
FOR EACH ROW 
BEGIN 
    DELETE FROM `erp_ium_espejo`.`pagos_parciales` 
    WHERE id_pago_parcial = OLD.id_pago_parcial; 
END$$

DELIMITER ;

-- =========================================================================
-- PASO 5: Crear vista para consultar ingresos con sus pagos parciales
-- =========================================================================

CREATE OR REPLACE VIEW `v_ingresos_con_pagos` AS
SELECT 
    i.*,
    GROUP_CONCAT(
        CONCAT(pp.metodo_pago, ': $', FORMAT(pp.monto, 2)) 
        ORDER BY pp.orden 
        SEPARATOR ' | '
    ) AS desglose_pagos,
    COUNT(pp.id_pago_parcial) AS num_pagos_parciales,
    COALESCE(SUM(pp.monto), i.monto) AS total_verificado
FROM 
    ingresos i
LEFT JOIN 
    pagos_parciales pp ON i.folio_ingreso = pp.folio_ingreso
GROUP BY 
    i.folio_ingreso;

-- =========================================================================
-- PASO 6: Migrar datos existentes (crear pago parcial para ingresos sin pagos divididos)
-- =========================================================================

-- Para todos los ingresos existentes que no tengan pagos parciales,
-- crear un registro con su método de pago y monto original
INSERT INTO `pagos_parciales` (`folio_ingreso`, `metodo_pago`, `monto`, `orden`)
SELECT 
    i.folio_ingreso,
    i.metodo_de_pago,
    i.monto,
    1
FROM 
    ingresos i
WHERE 
    NOT EXISTS (
        SELECT 1 
        FROM pagos_parciales pp 
        WHERE pp.folio_ingreso = i.folio_ingreso
    )
    AND i.metodo_de_pago != 'Mixto';

-- =========================================================================
-- PASO 7: Procedimiento almacenado para validar consistencia de pagos
-- =========================================================================

DELIMITER $$

CREATE PROCEDURE `sp_validar_pagos_parciales`(IN p_folio_ingreso INT)
BEGIN
    DECLARE v_monto_ingreso DECIMAL(10,2);
    DECLARE v_suma_parciales DECIMAL(10,2);
    DECLARE v_diferencia DECIMAL(10,2);
    
    -- Obtener monto del ingreso
    SELECT monto INTO v_monto_ingreso 
    FROM ingresos 
    WHERE folio_ingreso = p_folio_ingreso;
    
    -- Obtener suma de pagos parciales
    SELECT COALESCE(SUM(monto), 0) INTO v_suma_parciales 
    FROM pagos_parciales 
    WHERE folio_ingreso = p_folio_ingreso;
    
    -- Calcular diferencia
    SET v_diferencia = v_monto_ingreso - v_suma_parciales;
    
    -- Retornar resultado
    SELECT 
        p_folio_ingreso AS folio,
        v_monto_ingreso AS monto_total,
        v_suma_parciales AS suma_parciales,
        v_diferencia AS diferencia,
        CASE 
            WHEN ABS(v_diferencia) < 0.01 THEN 'CORRECTO'
            WHEN v_diferencia > 0 THEN 'FALTA PAGO'
            ELSE 'EXCESO PAGO'
        END AS estado;
END$$

DELIMITER ;

-- =========================================================================
-- VERIFICACIONES POST-INSTALACIÓN
-- =========================================================================

-- 1. Verificar que la tabla fue creada
SELECT 
    'Tabla pagos_parciales creada' AS verificacion,
    COUNT(*) AS registros
FROM 
    pagos_parciales;

-- 2. Verificar vista
SELECT 
    'Vista v_ingresos_con_pagos creada' AS verificacion,
    COUNT(*) AS total_ingresos
FROM 
    v_ingresos_con_pagos;

-- 3. Verificar triggers
SELECT 
    TRIGGER_NAME, 
    EVENT_MANIPULATION, 
    EVENT_OBJECT_TABLE
FROM 
    information_schema.TRIGGERS
WHERE 
    TRIGGER_SCHEMA = 'erp_ium'
    AND EVENT_OBJECT_TABLE = 'pagos_parciales'
ORDER BY 
    TRIGGER_NAME;

-- =========================================================================
-- NOTAS IMPORTANTES
-- =========================================================================

/*
CÓMO USAR EL NUEVO SISTEMA:

1. Para un pago único (como antes):
   - Crear el ingreso normalmente
   - El trigger creará automáticamente un pago_parcial

2. Para un pago dividido (NUEVO):
   - Crear el ingreso con metodo_de_pago = 'Mixto'
   - Insertar múltiples registros en pagos_parciales:
     
     INSERT INTO pagos_parciales (folio_ingreso, metodo_pago, monto, orden) VALUES
     (123, 'Tarjeta Crédito', 800.00, 1),
     (123, 'Efectivo', 200.00, 2);

3. Para validar un ingreso:
   CALL sp_validar_pagos_parciales(123);

4. Para consultar ingresos con desglose de pagos:
   SELECT * FROM v_ingresos_con_pagos WHERE folio_ingreso = 123;

EJEMPLO DE USO:
- Alumno debe pagar $1000 de inscripción
- Paga $800 con tarjeta y $200 en efectivo
- Se registra:
  * Ingreso: folio_ingreso=123, monto=1000, metodo_de_pago='Mixto'
  * Pago 1: metodo_pago='Tarjeta Crédito', monto=800, orden=1
  * Pago 2: metodo_pago='Efectivo', monto=200, orden=2
*/

-- =========================================================================
-- FIN DE LA MIGRACIÓN
-- =========================================================================

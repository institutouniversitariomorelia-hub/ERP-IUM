-- Actualizar tabla categorias en BD espejo

USE erp_ium_espejo;

-- Agregar campo 'concepto'
ALTER TABLE categorias 
ADD COLUMN concepto ENUM('Registro Diario', 'Titulaciones', 'Inscripciones y Reinscripciones') NULL 
AFTER tipo;

-- Agregar campo no_borrable
ALTER TABLE categorias 
ADD COLUMN no_borrable TINYINT(1) DEFAULT 0 
AFTER descripcion;

-- Eliminar id_presupuesto si existe
ALTER TABLE categorias 
DROP COLUMN id_presupuesto;

SELECT 'BD Espejo actualizada correctamente' as resultado;

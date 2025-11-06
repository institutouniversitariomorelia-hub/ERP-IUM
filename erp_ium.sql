-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 03-11-2025 a las 17:35:28
-- Versión del servidor: 10.11.13-MariaDB-0ubuntu0.24.04.1
-- Versión de PHP: 8.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `erp_ium`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_auditar_accion` (IN `p_user_id` INT, IN `p_seccion` VARCHAR(100), IN `p_accion` ENUM('Actualizacion','Eliminacion','Insercion'), IN `p_old_valor` TEXT, IN `p_new_valor` TEXT, IN `p_folio_egreso` INT, IN `p_folio_ingreso` INT)   BEGIN
      DECLARE v_last_id_auditoria INT;
      INSERT INTO `auditoria` (fecha_hora, seccion, accion, old_valor, new_valor, folio_egreso, folio_ingreso)
      VALUES (NOW(), p_seccion, p_accion, p_old_valor, p_new_valor, p_folio_egreso, p_folio_ingreso);
      SET v_last_id_auditoria = LAST_INSERT_ID();
      INSERT INTO `usuario_historial` (id_user, id_ha)
      VALUES (p_user_id, v_last_id_auditoria);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id_auditoria` int(11) NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `seccion` varchar(100) NOT NULL,
  `accion` enum('Actualizacion','Eliminacion','Insercion') NOT NULL,
  `old_valor` text DEFAULT NULL,
  `new_valor` text DEFAULT NULL,
  `folio_egreso` int(11) DEFAULT NULL,
  `folio_ingreso` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`id_auditoria`, `fecha_hora`, `seccion`, `accion`, `old_valor`, `new_valor`, `folio_egreso`, `folio_ingreso`) VALUES
(1, '2025-10-27 20:51:21', 'usuarios', 'Insercion', NULL, '{\"id_user\": 1, \"nombre\": \"Super Administrador\", \"username\": \"super.admin\", \"rol\": \"SU\"}', NULL, NULL),
(2, '2025-10-27 22:13:35', 'usuarios', 'Insercion', NULL, '{\"id_user\": 2, \"nombre\": \"Super Administrador\", \"username\": \"su_admin\", \"rol\": \"SU\"}', NULL, NULL),
(3, '2025-10-27 22:17:44', 'usuarios', 'Actualizacion', '{\"id_user\": 1, \"nombre\": \"Super Administrador\", \"username\": \"super.admin\", \"rol\": \"SU\"}', '{\"id_user\": 1, \"nombre\": \"Super Administrador\", \"username\": \"super.admin\", \"rol\": \"SU\"}', NULL, NULL),
(4, '2025-10-27 22:18:02', 'usuarios', 'Actualizacion', '{\"id_user\": 2, \"nombre\": \"Super Administrador\", \"username\": \"su_admin\", \"rol\": \"SU\"}', '{\"id_user\": 2, \"nombre\": \"Super Administrador\", \"username\": \"su_admin\", \"rol\": \"SU\"}', NULL, NULL),
(5, '2025-10-28 16:26:47', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 1, \"nombre\": \"goku\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(6, '2025-11-03 17:12:41', 'usuarios', 'Insercion', NULL, '{\"id_user\": 3, \"nombre\": \"Lupe\", \"username\": \"Regresa Lupe\", \"rol\": \"ADM\"}', NULL, NULL),
(7, '2025-11-03 17:13:16', 'usuarios', 'Eliminacion', '{\"id_user\": 3, \"nombre\": \"Lupe\", \"username\": \"Regresa Lupe\", \"rol\": \"ADM\"}', NULL, NULL, NULL);

--
-- Disparadores `auditoria`
--
DELIMITER $$
CREATE TRIGGER `trg_auditoria_after_insert` AFTER INSERT ON `auditoria` FOR EACH ROW BEGIN INSERT INTO `erp_ium_espejo`.`auditoria` VALUES (NEW.id_auditoria, NEW.fecha_hora, NEW.seccion, NEW.accion, NEW.old_valor, NEW.new_valor, NEW.folio_egreso, NEW.folio_ingreso); END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('Ingreso','Egreso') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `id_presupuesto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`, `tipo`, `descripcion`, `id_user`, `id_presupuesto`) VALUES
(1, 'goku', 'Ingreso', 'goku', 2, NULL);

--
-- Disparadores `categorias`
--
DELIMITER $$
CREATE TRIGGER `trg_categorias_after_insert` AFTER INSERT ON `categorias` FOR EACH ROW BEGIN INSERT INTO `erp_ium_espejo`.`categorias` VALUES (NEW.id_categoria, NEW.nombre, NEW.tipo, NEW.descripcion, NEW.id_user, NEW.id_presupuesto); END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_categorias_after_insert_aud` AFTER INSERT ON `categorias` FOR EACH ROW BEGIN SET @new_data = JSON_OBJECT('id_categoria', NEW.id_categoria, 'nombre', NEW.nombre, 'tipo', NEW.tipo); CALL sp_auditar_accion(@auditoria_user_id, 'categorias', 'Insercion', NULL, @new_data, NULL, NULL); END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_categorias_after_update` AFTER UPDATE ON `categorias` FOR EACH ROW BEGIN SET @old_data = JSON_OBJECT('id_categoria', OLD.id_categoria, 'nombre', OLD.nombre, 'tipo', OLD.tipo); SET @new_data = JSON_OBJECT('id_categoria', NEW.id_categoria, 'nombre', NEW.nombre, 'tipo', NEW.tipo); CALL sp_auditar_accion(@auditoria_user_id, 'categorias', 'Actualizacion', @old_data, @new_data, NULL, NULL); END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_categorias_after_update_espejo` AFTER UPDATE ON `categorias` FOR EACH ROW BEGIN UPDATE `erp_ium_espejo`.`categorias` SET nombre = NEW.nombre, tipo = NEW.tipo, descripcion = NEW.descripcion, id_user = NEW.id_user, id_presupuesto = NEW.id_presupuesto WHERE id_categoria = NEW.id_categoria; END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_categorias_before_delete` BEFORE DELETE ON `categorias` FOR EACH ROW BEGIN SET @old_data = JSON_OBJECT('id_categoria', OLD.id_categoria, 'nombre', OLD.nombre, 'tipo', OLD.tipo); CALL sp_auditar_accion(@auditoria_user_id, 'categorias', 'Eliminacion', @old_data, NULL, NULL, NULL); END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_categorias_before_delete_espejo` BEFORE DELETE ON `categorias` FOR EACH ROW BEGIN DELETE FROM `erp_ium_espejo`.`categorias` WHERE id_categoria = OLD.id_categoria; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `egresos`
--

CREATE TABLE `egresos` (
  `folio_egreso` int(11) NOT NULL,
  `proveedor` varchar(100) NOT NULL,
  `activo_fijo` enum('SI','NO') DEFAULT NULL,
  `descripcion` text NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `destinatario` varchar(255) NOT NULL,
  `forma_pago` enum('Efectivo','Transferencia','Cheque','Tarjeta D.','Tarjeta C.') NOT NULL,
  `documento_de_amparo` varchar(255) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_presupuesto` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `egresos`
--
DELIMITER $$
CREATE TRIGGER `trg_egresos_after_insert` AFTER INSERT ON `egresos` FOR EACH ROW BEGIN INSERT INTO `erp_ium_espejo`.`egresos` VALUES (NEW.folio_egreso, NEW.proveedor, NEW.activo_fijo, NEW.descripcion, NEW.monto, NEW.fecha, NEW.destinatario, NEW.forma_pago, NEW.documento_de_amparo, NEW.id_user, NEW.id_presupuesto, NEW.id_categoria); END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_egresos_after_insert_aud` AFTER INSERT ON `egresos` FOR EACH ROW BEGIN SET @new_egreso_data = JSON_OBJECT('folio_egreso', NEW.folio_egreso, 'proveedor', NEW.proveedor, 'monto', NEW.monto, 'fecha', NEW.fecha, 'id_categoria', NEW.id_categoria); CALL sp_auditar_accion(@auditoria_user_id, 'egresos', 'Insercion', NULL, @new_egreso_data, NEW.folio_egreso, NULL); END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_egresos_after_update` AFTER UPDATE ON `egresos` FOR EACH ROW BEGIN SET @old_egreso_data = JSON_OBJECT('folio_egreso', OLD.folio_egreso, 'proveedor', OLD.proveedor, 'monto', OLD.monto, 'fecha', OLD.fecha, 'id_categoria', OLD.id_categoria); SET @new_egreso_data = JSON_OBJECT('folio_egreso', NEW.folio_egreso, 'proveedor', NEW.proveedor, 'monto', NEW.monto, 'fecha', NEW.fecha, 'id_categoria', NEW.id_categoria); CALL sp_auditar_accion(@auditoria_user_id, 'egresos', 'Actualizacion', @old_egreso_data, @new_egreso_data, NEW.folio_egreso, NULL); END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_egresos_after_update_espejo` AFTER UPDATE ON `egresos` FOR EACH ROW BEGIN UPDATE `erp_ium_espejo`.`egresos` SET proveedor = NEW.proveedor, monto = NEW.monto, fecha = NEW.fecha, id_categoria = NEW.id_categoria, id_presupuesto = NEW.id_presupuesto WHERE folio_egreso = NEW.folio_egreso; END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_egresos_before_delete` BEFORE DELETE ON `egresos` FOR EACH ROW BEGIN SET @old_egreso_data_del = JSON_OBJECT('folio_egreso', OLD.folio_egreso, 'proveedor', OLD.proveedor, 'monto', OLD.monto, 'fecha', OLD.fecha, 'id_categoria', OLD.id_categoria); CALL sp_auditar_accion(@auditoria_user_id, 'egresos', 'Eliminacion', @old_egreso_data_del, NULL, OLD.folio_egreso, NULL); END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_egresos_before_delete_espejo` BEFORE DELETE ON `egresos` FOR EACH ROW BEGIN DELETE FROM `erp_ium_espejo`.`egresos` WHERE folio_egreso = OLD.folio_egreso; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingresos`
--

CREATE TABLE `ingresos` (
  `folio_ingreso` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `alumno` varchar(255) NOT NULL,
  `matricula` varchar(50) NOT NULL,
  `nivel` enum('Licenciatura','Maestría','Doctorado') NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_de_pago` enum('Efectivo','Transferencia','Depósito') NOT NULL,
  `concepto` enum('Inscripción','Reinscripción','Titulación','Colegiatura','Constancia simple','Constancia con calificaciones','Historiales','Certificados','Equivalencias','Credenciales','Otros') NOT NULL,
  `mes_correspondiente` varchar(20) DEFAULT NULL,
  `anio` int(4) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `dia_pago` int(2) DEFAULT NULL,
  `modalidad` enum('Cuatrimestral','Semestral') DEFAULT NULL,
  `grado` int(2) DEFAULT NULL,
  `programa` varchar(100) NOT NULL,
  `grupo` varchar(20) DEFAULT NULL,
  `id_categoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `ingresos`
--
DELIMITER $$
CREATE TRIGGER `trg_ingresos_after_insert` AFTER INSERT ON `ingresos` FOR EACH ROW BEGIN INSERT INTO `erp_ium_espejo`.`ingresos` VALUES (NEW.folio_ingreso, NEW.fecha, NEW.alumno, NEW.matricula, NEW.nivel, NEW.monto, NEW.metodo_de_pago, NEW.concepto, NEW.mes_correspondiente, NEW.anio, NEW.observaciones, NEW.dia_pago, NEW.modalidad, NEW.grado, NEW.programa, NEW.grupo, NEW.id_categoria); END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_ingresos_after_insert_aud` AFTER INSERT ON `ingresos` FOR EACH ROW BEGIN SET @new_ingreso_data = JSON_OBJECT('folio_ingreso', NEW.folio_ingreso, 'alumno', NEW.alumno, 'monto', NEW.monto, 'fecha', NEW.fecha, 'concepto', NEW.concepto, 'id_categoria', NEW.id_categoria); CALL sp_auditar_accion(@auditoria_user_id, 'ingresos', 'Insercion', NULL, @new_ingreso_data, NULL, NEW.folio_ingreso); END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_ingresos_after_update` AFTER UPDATE ON `ingresos` FOR EACH ROW BEGIN SET @old_data = JSON_OBJECT('folio_ingreso', OLD.folio_ingreso, 'monto', OLD.monto); SET @new_data = JSON_OBJECT('folio_ingreso', NEW.folio_ingreso, 'monto', NEW.monto); CALL sp_auditar_accion(@auditoria_user_id, 'ingresos', 'Actualizacion', @old_data, @new_data, NULL, NEW.folio_ingreso); END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_ingresos_after_update_espejo` AFTER UPDATE ON `ingresos` FOR EACH ROW BEGIN UPDATE `erp_ium_espejo`.`ingresos` SET alumno = NEW.alumno, monto = NEW.monto, fecha = NEW.fecha, matricula = NEW.matricula, id_categoria = NEW.id_categoria WHERE folio_ingreso = NEW.folio_ingreso; END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_ingresos_before_delete` BEFORE DELETE ON `ingresos` FOR EACH ROW BEGIN SET @old_ingreso_data_del = JSON_OBJECT('folio_ingreso', OLD.folio_ingreso, 'alumno', OLD.alumno, 'monto', OLD.monto, 'fecha', OLD.fecha, 'concepto', OLD.concepto, 'id_categoria', OLD.id_categoria); CALL sp_auditar_accion(@auditoria_user_id, 'ingresos', 'Eliminacion', @old_ingreso_data_del, NULL, NULL, OLD.folio_ingreso); END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_ingresos_before_delete_espejo` BEFORE DELETE ON `ingresos` FOR EACH ROW BEGIN DELETE FROM `erp_ium_espejo`.`ingresos` WHERE folio_ingreso = OLD.folio_ingreso; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuestos`
--

CREATE TABLE `presupuestos` (
  `id_presupuesto` int(11) NOT NULL,
  `monto_limite` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `presupuestos`
--
DELIMITER $$
CREATE TRIGGER `trg_presupuestos_after_insert` AFTER INSERT ON `presupuestos` FOR EACH ROW BEGIN INSERT INTO `erp_ium_espejo`.`presupuestos` VALUES (NEW.id_presupuesto, NEW.monto_limite, NEW.fecha, NEW.id_user); END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_presupuestos_after_insert_aud` AFTER INSERT ON `presupuestos` FOR EACH ROW BEGIN SET @new_data = JSON_OBJECT('id_presupuesto', NEW.id_presupuesto, 'monto_limite', NEW.monto_limite, 'fecha', NEW.fecha); CALL sp_auditar_accion(@auditoria_user_id, 'presupuestos', 'Insercion', NULL, @new_data, NULL, NULL); END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_presupuestos_after_update` AFTER UPDATE ON `presupuestos` FOR EACH ROW BEGIN SET @old_data = JSON_OBJECT('id_presupuesto', OLD.id_presupuesto, 'monto_limite', OLD.monto_limite, 'fecha', OLD.fecha); SET @new_data = JSON_OBJECT('id_presupuesto', NEW.id_presupuesto, 'monto_limite', NEW.monto_limite, 'fecha', NEW.fecha); CALL sp_auditar_accion(@auditoria_user_id, 'presupuestos', 'Actualizacion', @old_data, @new_data, NULL, NULL); END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_presupuestos_after_update_espejo` AFTER UPDATE ON `presupuestos` FOR EACH ROW BEGIN UPDATE `erp_ium_espejo`.`presupuestos` SET monto_limite = NEW.monto_limite, fecha = NEW.fecha, id_user = NEW.id_user WHERE id_presupuesto = NEW.id_presupuesto; END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_presupuestos_before_delete` BEFORE DELETE ON `presupuestos` FOR EACH ROW BEGIN
    SET @old_data = JSON_OBJECT(
        'id_presupuesto', OLD.id_presupuesto, 
        'monto_limite', OLD.monto_limite, 
        'fecha', OLD.fecha
    );
    CALL sp_auditar_accion(
        @auditoria_user_id, 
        'presupuestos', 
        'Eliminacion', 
        @old_data, 
        NULL, 
        NULL, 
        NULL
    );
    
    DELETE FROM `erp_ium_espejo`.`presupuestos` WHERE id_presupuesto = OLD.id_presupuesto;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_user` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('ADM','COB','REC','SU') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_user`, `nombre`, `username`, `password`, `rol`) VALUES
(1, 'Super Administrador', 'super.admin', '$2y$10$mmjNR96vpa65C4d6wlswx.ME2vyQWXtgehKPCYh/FOoSlLeH0wQyK', 'SU'),
(2, 'Super Administrador', 'su_admin', '$2y$10$mmjNR96vpa65C4d6wlswx.ME2vyQWXtgehKPCYh/FOoSlLeH0wQyK', 'SU');

--
-- Disparadores `usuarios`
--
DELIMITER $$
CREATE TRIGGER `trg_usuarios_after_insert` AFTER INSERT ON `usuarios` FOR EACH ROW BEGIN INSERT INTO `erp_ium_espejo`.`usuarios` VALUES (NEW.id_user, NEW.nombre, NEW.username, NEW.password, NEW.rol); END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_usuarios_after_insert_aud` AFTER INSERT ON `usuarios` FOR EACH ROW BEGIN SET @new_data = JSON_OBJECT('id_user', NEW.id_user, 'nombre', NEW.nombre, 'username', NEW.username, 'rol', NEW.rol); CALL sp_auditar_accion(NEW.id_user, 'usuarios', 'Insercion', NULL, @new_data, NULL, NULL); END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_usuarios_after_update_aud` AFTER UPDATE ON `usuarios` FOR EACH ROW BEGIN SET @old_data = JSON_OBJECT('id_user', OLD.id_user, 'nombre', OLD.nombre, 'username', OLD.username, 'rol', OLD.rol); SET @new_data = JSON_OBJECT('id_user', NEW.id_user, 'nombre', NEW.nombre, 'username', NEW.username, 'rol', NEW.rol); CALL sp_auditar_accion(@auditoria_user_id, 'usuarios', 'Actualizacion', @old_data, @new_data, NULL, NULL); END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_usuarios_after_update_espejo` AFTER UPDATE ON `usuarios` FOR EACH ROW BEGIN UPDATE `erp_ium_espejo`.`usuarios` SET nombre = NEW.nombre, username = NEW.username, password = NEW.password, rol = NEW.rol WHERE id_user = NEW.id_user; END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_usuarios_before_delete` BEFORE DELETE ON `usuarios` FOR EACH ROW BEGIN SET @old_data = JSON_OBJECT('id_user', OLD.id_user, 'nombre', OLD.nombre, 'username', OLD.username, 'rol', OLD.rol); CALL sp_auditar_accion(@auditoria_user_id, 'usuarios', 'Eliminacion', @old_data, NULL, NULL, NULL); END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_usuarios_before_delete_espejo` BEFORE DELETE ON `usuarios` FOR EACH ROW BEGIN DELETE FROM `erp_ium_espejo`.`usuarios` WHERE id_user = OLD.id_user; END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_historial`
--

CREATE TABLE `usuario_historial` (
  `id_ha` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario_historial`
--

INSERT INTO `usuario_historial` (`id_ha`, `id_user`) VALUES
(3, NULL),
(4, NULL),
(5, NULL),
(6, NULL),
(7, NULL),
(1, 1),
(2, 2);

--
-- Disparadores `usuario_historial`
--
DELIMITER $$
CREATE TRIGGER `trg_uh_after_insert` AFTER INSERT ON `usuario_historial` FOR EACH ROW BEGIN
    INSERT INTO `erp_ium_espejo`.`usuario_historial` (id_ha, id_user) 
    VALUES (NEW.id_ha, NEW.id_user);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_ingreso`
--

CREATE TABLE `usuario_ingreso` (
  `id_user` int(11) NOT NULL,
  `folio_ingreso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `usuario_ingreso`
--
DELIMITER $$
CREATE TRIGGER `trg_ui_after_insert` AFTER INSERT ON `usuario_ingreso` FOR EACH ROW BEGIN INSERT INTO `erp_ium_espejo`.`usuario_ingreso` VALUES (NEW.id_user, NEW.folio_ingreso); END
$$
DELIMITER ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id_auditoria`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `fk_categorias_user` (`id_user`),
  ADD KEY `fk_categorias_presupuesto` (`id_presupuesto`);

--
-- Indices de la tabla `egresos`
--
ALTER TABLE `egresos`
  ADD PRIMARY KEY (`folio_egreso`),
  ADD KEY `fk_egresos_user` (`id_user`),
  ADD KEY `fk_egresos_presupuesto` (`id_presupuesto`),
  ADD KEY `fk_egresos_categoria` (`id_categoria`);

--
-- Indices de la tabla `ingresos`
--
ALTER TABLE `ingresos`
  ADD PRIMARY KEY (`folio_ingreso`),
  ADD KEY `fk_ingresos_categoria` (`id_categoria`);

--
-- Indices de la tabla `presupuestos`
--
ALTER TABLE `presupuestos`
  ADD PRIMARY KEY (`id_presupuesto`),
  ADD KEY `fk_presupuestos_user` (`id_user`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indices de la tabla `usuario_historial`
--
ALTER TABLE `usuario_historial`
  ADD PRIMARY KEY (`id_ha`),
  ADD KEY `fk_uh_user` (`id_user`);

--
-- Indices de la tabla `usuario_ingreso`
--
ALTER TABLE `usuario_ingreso`
  ADD PRIMARY KEY (`id_user`,`folio_ingreso`),
  ADD KEY `fk_ui_ingreso` (`folio_ingreso`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id_auditoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `egresos`
--
ALTER TABLE `egresos`
  MODIFY `folio_egreso` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ingresos`
--
ALTER TABLE `ingresos`
  MODIFY `folio_ingreso` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `presupuestos`
--
ALTER TABLE `presupuestos`
  MODIFY `id_presupuesto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD CONSTRAINT `fk_categorias_presupuesto` FOREIGN KEY (`id_presupuesto`) REFERENCES `presupuestos` (`id_presupuesto`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_categorias_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `egresos`
--
ALTER TABLE `egresos`
  ADD CONSTRAINT `fk_egresos_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_egresos_presupuesto` FOREIGN KEY (`id_presupuesto`) REFERENCES `presupuestos` (`id_presupuesto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_egresos_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ingresos`
--
ALTER TABLE `ingresos`
  ADD CONSTRAINT `fk_ingresos_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `presupuestos`
--
ALTER TABLE `presupuestos`
  ADD CONSTRAINT `fk_presupuestos_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario_historial`
--
ALTER TABLE `usuario_historial`
  ADD CONSTRAINT `fk_uh_ha` FOREIGN KEY (`id_ha`) REFERENCES `auditoria` (`id_auditoria`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_uh_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario_ingreso`
--
ALTER TABLE `usuario_ingreso`
  ADD CONSTRAINT `fk_ui_ingreso` FOREIGN KEY (`folio_ingreso`) REFERENCES `ingresos` (`folio_ingreso`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ui_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

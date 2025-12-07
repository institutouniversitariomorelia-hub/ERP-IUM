-- Script modificado para importar correctamente la base de datos
CREATE DATABASE IF NOT EXISTS `erp_ium`;
USE `erp_ium`;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-10-2025 a las 18:51:33
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id_auditoria` int(11) NOT NULL COMMENT 'Llave primaria',
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Cuándo ocurrió el evento',
  `usuario` varchar(100) NOT NULL COMMENT 'Qué usuario realizó la acción (username)',
  `seccion` varchar(100) NOT NULL COMMENT 'Módulo afectado (Ej: Egresos, Ingresos, Usuarios)',
  `accion` enum('Actualizacion','Eliminacion','Insercion') NOT NULL COMMENT 'Tipo de acción realizada',
  `old_valor` text DEFAULT NULL COMMENT 'Valores anteriores (Ej: formato JSON)',
  `new_valor` text DEFAULT NULL COMMENT 'Valores nuevos (Ej: formato JSON)',
  `folio_egreso` int(11) DEFAULT NULL COMMENT 'FK a egresos si la acción fue sobre un egreso',
  `folio_ingreso` int(11) DEFAULT NULL COMMENT 'FK a ingresos si la acción fue sobre un ingreso'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabla para registrar cambios en el sistema';

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`id_auditoria`, `fecha_hora`, `usuario`, `seccion`, `accion`, `old_valor`, `new_valor`, `folio_egreso`, `folio_ingreso`) VALUES
(1, '2025-10-27 17:35:26', 'su_admin', 'Egreso', 'Actualizacion', '', '', 1, NULL),
(2, '2025-10-27 17:36:24', 'su_admin', 'Egreso', 'Actualizacion', '', '', 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `tipo` enum('Ingreso','Egreso') NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `tipo`, `descripcion`) VALUES
(1, 'Colegiaturas', 'Ingreso', 'Pagos mensuales de alumnos'),
(5, 'gjshkdjk,', 'Egreso', 'gfhkjlk');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `egresos`
--

CREATE TABLE `egresos` (
  `folio_egreso` int(11) NOT NULL,
  `proveedor` varchar(100) DEFAULT NULL,
  `activo_fijo` enum('SI','NO') DEFAULT 'NO',
  `descripcion` text DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `destinatario` varchar(255) NOT NULL,
  `forma_pago` enum('Efectivo','Transferencia','Cheque','Tarjeta D.','Tarjeta C.') NOT NULL,
  `documento_de_amparo` varchar(255) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `id_presupuesto` int(11) DEFAULT NULL,
  `id_categoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `egresos`
--

INSERT INTO `egresos` (`folio_egreso`, `proveedor`, `activo_fijo`, `descripcion`, `monto`, `fecha`, `destinatario`, `forma_pago`, `documento_de_amparo`, `id_user`, `id_presupuesto`, `id_categoria`) VALUES
(1, 'FYJfyguhjkñ', 'NO', 'TYUJKNMdvbh', 4567.00, '2025-10-24', 'FGHfgdhjs', 'Transferencia', 'FGHJ', 1, NULL, 5),
(2, 'cvbn', 'SI', 'fghjk', 56789.00, '2025-10-16', 'ghj', 'Cheque', 'vbn', 1, NULL, 5);

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
  `año` int(4) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `dia_pago` int(2) DEFAULT NULL,
  `modalidad` enum('Cuatrimestral','Semestral') DEFAULT NULL,
  `grado` int(2) DEFAULT NULL,
  `programa` varchar(100) NOT NULL,
  `grupo` varchar(20) DEFAULT NULL,
  `id_categoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ingresos`
--

INSERT INTO `ingresos` (`folio_ingreso`, `fecha`, `alumno`, `matricula`, `nivel`, `monto`, `metodo_de_pago`, `concepto`, `mes_correspondiente`, `año`, `observaciones`, `dia_pago`, `modalidad`, `grado`, `programa`, `grupo`, `id_categoria`) VALUES
(1, '2025-10-24', 'GHJK', 'FGHJ', 'Licenciatura', 5678.00, 'Efectivo', 'Titulación', 'HJ', 2025, '0', 2, NULL, 2, 'GHJ', 'BN', 1),
(2, '2025-10-27', 'fgh', 'rtyhj', 'Maestría', 4567.00, 'Efectivo', 'Reinscripción', 'HJ', 2025, '0', 2, '', 6, 'vbn', 'BN', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuestos`
--

CREATE TABLE `presupuestos` (
  `id` int(11) NOT NULL,
  `categoria` varchar(255) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuestos`
--

INSERT INTO `presupuestos` (`id`, `categoria`, `monto`, `fecha`) VALUES
(1, 'Colegiaturas', 56.00, '2025-10-24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `username`, `password`, `rol`) VALUES
(1, 'Super Admin', 'su_admin', '$2y$10$9dwP028mqLaplmoNM0BI7e5ZxqWVDUrLlf2ZOZeuGF9AkFCMU9hZu', 'SU'),
(2, 'Administración', 'admin1', '$2y$10$WkXlO1Sg4wV.v.hS.hP/E.Kwvcs1/yNCSbg6zN/a3F54oJ5/5dbya', 'ADM');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id_auditoria`),
  ADD KEY `idx_seccion_accion` (`seccion`,`accion`),
  ADD KEY `idx_usuario` (`usuario`),
  ADD KEY `idx_folio_egreso` (`folio_egreso`),
  ADD KEY `idx_folio_ingreso` (`folio_ingreso`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `egresos`
--
ALTER TABLE `egresos`
  ADD PRIMARY KEY (`folio_egreso`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_presupuesto` (`id_presupuesto`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `ingresos`
--
ALTER TABLE `ingresos`
  ADD PRIMARY KEY (`folio_ingreso`),
  ADD UNIQUE KEY `matricula` (`matricula`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `presupuestos`
--
ALTER TABLE `presupuestos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categoria` (`categoria`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id_auditoria` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Llave primaria', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `egresos`
--
ALTER TABLE `egresos`
  MODIFY `folio_egreso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ingresos`
--
ALTER TABLE `ingresos`
  MODIFY `folio_ingreso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `presupuestos`
--
ALTER TABLE `presupuestos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

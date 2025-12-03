-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-11-2025 a las 20:13:33
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `erp_ium_espejo`
--

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
(7, '2025-11-03 17:13:16', 'usuarios', 'Eliminacion', '{\"id_user\": 3, \"nombre\": \"Lupe\", \"username\": \"Regresa Lupe\", \"rol\": \"ADM\"}', NULL, NULL, NULL),
(8, '2025-11-05 17:50:55', 'usuarios', 'Insercion', NULL, '{\"id_user\": 4, \"nombre\": \"Lupe\", \"username\": \"SU-LUPE \", \"rol\": \"SU\"}', NULL, NULL),
(9, '2025-11-05 17:57:26', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 2, \"nombre\": \"Lupe\", \"tipo\": \"Egreso\"}', NULL, NULL),
(10, '2025-11-05 17:57:39', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 1, \"monto_limite\": 1234523.00, \"fecha\": \"2025-11-05\"}', NULL, NULL),
(11, '2025-11-05 17:59:40', 'presupuestos', 'Actualizacion', '{\"id_presupuesto\": 1, \"monto_limite\": 1234523.00, \"fecha\": \"2025-11-05\"}', '{\"id_presupuesto\": 1, \"monto_limite\": 1234523.00, \"fecha\": \"2025-11-05\"}', NULL, NULL),
(12, '2025-11-05 18:11:58', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 3, \"nombre\": \"fgfdgfgf\", \"tipo\": \"Egreso\"}', NULL, NULL),
(13, '2025-11-05 18:19:59', 'categorias', 'Actualizacion', '{\"id_categoria\": 3, \"nombre\": \"fgfdgfgf\", \"tipo\": \"Egreso\"}', '{\"id_categoria\": 3, \"nombre\": \"fgfdgfgf\", \"tipo\": \"Egreso\"}', NULL, NULL),
(14, '2025-11-05 18:20:36', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 1, \"proveedor\": \"qwerth\", \"monto\": 123456.00, \"fecha\": \"2025-11-05\", \"id_categoria\": 1}', 1, NULL),
(15, '2025-11-05 18:28:06', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 1, \"alumno\": \"angel\", \"monto\": 1234567.00, \"fecha\": \"2025-11-05\", \"concepto\": \"Credenciales\", \"id_categoria\": 1}', NULL, 1),
(16, '2025-11-05 18:54:17', 'egresos', 'Eliminacion', '{\"folio_egreso\": 1, \"proveedor\": \"qwerth\", \"monto\": 123456.00, \"fecha\": \"2025-11-05\", \"id_categoria\": 1}', NULL, 1, NULL),
(17, '2025-11-05 18:54:43', 'usuarios', 'Insercion', NULL, '{\"id_user\": 6, \"nombre\": \"goku\", \"username\": \"goku\", \"rol\": \"ADM\"}', NULL, NULL),
(18, '2025-11-05 20:18:56', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 2, \"proveedor\": \"qwerth\", \"monto\": 1234567.00, \"fecha\": \"2025-11-05\", \"id_categoria\": 1}', 2, NULL),
(19, '2025-11-05 21:56:27', 'usuarios', 'Insercion', NULL, '{\"id_user\": 7, \"nombre\": \"bryan\", \"username\": \"bryan_su\", \"rol\": \"SU\"}', NULL, NULL),
(20, '2025-11-05 21:56:33', 'usuarios', 'Eliminacion', '{\"id_user\": 4, \"nombre\": \"Lupe\", \"username\": \"SU-LUPE \", \"rol\": \"SU\"}', NULL, NULL, NULL),
(21, '2025-11-05 21:57:15', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 3, \"proveedor\": \"qwerth\", \"monto\": 1234567.00, \"fecha\": \"2025-11-05\", \"id_categoria\": 1}', 3, NULL),
(22, '2025-11-06 16:47:35', 'presupuestos', 'Actualizacion', '{\"id_presupuesto\": 1, \"monto_limite\": 1234523.00, \"fecha\": \"2025-11-05\"}', '{\"id_presupuesto\": 1, \"monto_limite\": 1234523.00, \"fecha\": \"2025-11-05\"}', NULL, NULL),
(23, '2025-11-06 18:25:53', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 2, \"monto_limite\": 15000.00, \"fecha\": \"2025-11-06\"}', NULL, NULL),
(24, '2025-11-07 19:25:21', 'presupuestos', 'Actualizacion', '{\"id_presupuesto\": 1, \"monto_limite\": 1234523.00, \"fecha\": \"2025-11-05\"}', '{\"id_presupuesto\": 1, \"monto_limite\": 1234523.00, \"fecha\": \"2025-11-05\"}', NULL, NULL),
(25, '2025-11-07 19:25:21', 'presupuestos', 'Actualizacion', '{\"id_presupuesto\": 2, \"monto_limite\": 15000.00, \"fecha\": \"2025-11-06\"}', '{\"id_presupuesto\": 2, \"monto_limite\": 15000.00, \"fecha\": \"2025-11-06\"}', NULL, NULL),
(26, '2025-11-07 19:36:18', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 4, \"nombre\": \"Jitomate\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(27, '2025-11-07 19:36:28', 'categorias', 'Actualizacion', '{\"id_categoria\": 3, \"nombre\": \"fgfdgfgf\", \"tipo\": \"Egreso\"}', '{\"id_categoria\": 3, \"nombre\": \"fgfdgf\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(28, '2025-11-07 19:50:22', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 3, \"monto_limite\": 15000.00, \"fecha\": \"2025-11-07\", \"id_categoria\": 1}', NULL, NULL),
(29, '2025-11-07 19:50:45', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 4, \"monto_limite\": 5.00, \"fecha\": \"2025-11-29\", \"id_categoria\": 4}', NULL, NULL),
(30, '2025-11-07 19:50:55', 'presupuestos', 'Actualizacion', '{\"id_presupuesto\": 4, \"monto_limite\": 5.00, \"fecha\": \"2025-11-29\", \"id_categoria\": 4}', '{\"id_presupuesto\": 4, \"monto_limite\": 15005.00, \"fecha\": \"2025-11-29\", \"id_categoria\": 4}', NULL, NULL),
(31, '2025-11-07 19:51:01', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 4, \"monto_limite\": 15005.00, \"fecha\": \"2025-11-29\"}', NULL, NULL, NULL),
(32, '2025-11-07 19:51:03', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 3, \"monto_limite\": 15000.00, \"fecha\": \"2025-11-07\"}', NULL, NULL, NULL),
(33, '2025-11-07 19:51:14', 'categorias', 'Actualizacion', '{\"id_categoria\": 3, \"nombre\": \"fgfdgf\", \"tipo\": \"Ingreso\"}', '{\"id_categoria\": 3, \"nombre\": \"fgfdgfdddgf\", \"tipo\": \"Egreso\"}', NULL, NULL),
(34, '2025-11-07 19:51:25', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 5, \"nombre\": \"RPTech\", \"tipo\": \"Egreso\"}', NULL, NULL),
(35, '2025-11-07 19:52:42', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 2, \"alumno\": \"jose miguel perez hernadez\", \"monto\": 852.00, \"fecha\": \"2025-11-07\", \"concepto\": \"Inscripción\", \"id_categoria\": 1}', NULL, 2),
(36, '2025-11-07 19:53:27', 'ingresos', 'Actualizacion', '{\"folio_ingreso\": 2, \"monto\": 852.00}', '{\"folio_ingreso\": 2, \"monto\": 850.00}', NULL, 2),
(37, '2025-11-07 19:54:14', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 4, \"proveedor\": \"pkpkpk\", \"monto\": 8000.00, \"fecha\": \"2025-11-29\", \"id_categoria\": 1}', 4, NULL),
(38, '2025-11-07 19:54:49', 'egresos', 'Actualizacion', '{\"folio_egreso\": 4, \"proveedor\": \"pkpkpk\", \"monto\": 8000.00, \"fecha\": \"2025-11-29\", \"id_categoria\": 1}', '{\"folio_egreso\": 4, \"proveedor\": \"pkpkpk\", \"monto\": 800.00, \"fecha\": \"2025-11-29\", \"id_categoria\": 1}', 4, NULL),
(39, '2025-11-07 19:55:19', 'egresos', 'Eliminacion', '{\"folio_egreso\": 2, \"proveedor\": \"qwerth\", \"monto\": 1234567.00, \"fecha\": \"2025-11-05\", \"id_categoria\": 1}', NULL, 2, NULL),
(40, '2025-11-07 20:10:56', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 3, \"alumno\": \"jose miguel perez hernadez\", \"monto\": 8000.00, \"fecha\": \"2025-11-07\", \"concepto\": \"Credenciales\", \"id_categoria\": 4}', NULL, 3),
(41, '2025-11-07 20:19:46', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 4, \"alumno\": \"andres jorge chueco\", \"monto\": 7000.00, \"fecha\": \"2025-11-07\", \"concepto\": \"Constancia con calificaciones\", \"id_categoria\": 4}', NULL, 4),
(42, '2025-11-07 20:24:47', 'ingresos', 'Actualizacion', '{\"folio_ingreso\": 2, \"monto\": 850.00}', '{\"folio_ingreso\": 2, \"monto\": 850.00}', NULL, 2),
(43, '2025-11-07 20:25:39', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 5, \"alumno\": \"jose miguel perez hernadez\", \"monto\": 5000.00, \"fecha\": \"2025-11-07\", \"concepto\": \"Historiales\", \"id_categoria\": 1}', NULL, 5),
(44, '2025-11-07 20:35:59', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 5, \"monto_limite\": 99999999.99, \"fecha\": \"2025-11-20\", \"id_categoria\": 4}', NULL, NULL),
(45, '2025-11-10 18:04:51', 'usuarios', 'Eliminacion', '{\"id_user\": 6, \"nombre\": \"goku\", \"username\": \"goku\", \"rol\": \"ADM\"}', NULL, NULL, NULL),
(46, '2025-11-10 18:05:56', 'usuarios', 'Insercion', NULL, '{\"id_user\": 8, \"nombre\": \"KEVIN MIER\", \"username\": \"kevin_mier\", \"rol\": \"REC\"}', NULL, NULL),
(47, '2025-11-10 18:49:12', 'usuarios', 'Eliminacion', '{\"id_user\": 8, \"nombre\": \"KEVIN MIER\", \"username\": \"kevin_mier\", \"rol\": \"REC\"}', NULL, NULL, NULL),
(48, '2025-11-10 18:49:33', 'usuarios', 'Insercion', NULL, '{\"id_user\": 9, \"nombre\": \"Boris\", \"username\": \"boris\", \"rol\": \"COB\"}', NULL, NULL),
(49, '2025-11-10 18:49:48', 'usuarios', 'Insercion', NULL, '{\"id_user\": 10, \"nombre\": \"goku\", \"username\": \"goku\", \"rol\": \"ADM\"}', NULL, NULL),
(50, '2025-11-10 18:50:19', 'usuarios', 'Insercion', NULL, '{\"id_user\": 11, \"nombre\": \"renegul\", \"username\": \"renegul\", \"rol\": \"REC\"}', NULL, NULL),
(51, '2025-11-12 19:02:56', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 5, \"monto_limite\": 99999999.99, \"fecha\": \"2025-11-20\"}', NULL, NULL, NULL),
(56, '2025-11-12 19:04:56', 'egresos', 'Eliminacion', '{\"folio_egreso\": 4, \"proveedor\": \"pkpkpk\", \"monto\": 800.00, \"fecha\": \"2025-11-29\", \"id_categoria\": 1}', NULL, 4, NULL),
(57, '2025-11-12 19:04:59', 'egresos', 'Eliminacion', '{\"folio_egreso\": 3, \"proveedor\": \"qwerth\", \"monto\": 1234567.00, \"fecha\": \"2025-11-05\", \"id_categoria\": 1}', NULL, 3, NULL),
(59, '2025-11-12 19:05:15', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 2, \"monto_limite\": 15000.00, \"fecha\": \"2025-11-06\"}', NULL, NULL, NULL),
(60, '2025-11-12 19:05:18', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 1, \"monto_limite\": 1234523.00, \"fecha\": \"2025-11-05\"}', NULL, NULL, NULL),
(61, '2025-11-12 19:05:26', 'categorias', 'Eliminacion', '{\"id_categoria\": 1, \"nombre\": \"goku\", \"tipo\": \"Ingreso\"}', NULL, NULL, NULL),
(63, '2025-11-12 19:12:43', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 13, \"monto_limite\": 123456.00, \"fecha\": \"2025-11-12\", \"id_categoria\": null}', NULL, NULL),
(64, '2025-11-12 19:13:25', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 13, \"monto_limite\": 123456.00, \"fecha\": \"2025-11-12\"}', NULL, NULL, NULL),
(65, '2025-11-12 19:13:39', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 14, \"monto_limite\": 15000.00, \"fecha\": \"2025-11-12\", \"id_categoria\": null}', NULL, NULL),
(66, '2025-11-12 19:42:34', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 14, \"monto_limite\": 15000.00, \"fecha\": \"2025-11-12\"}', NULL, NULL, NULL),
(67, '2025-11-12 19:42:47', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 15, \"monto_limite\": 15000.00, \"fecha\": \"2025-11-12\", \"id_categoria\": null}', NULL, NULL),
(68, '2025-11-12 20:43:55', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 16, \"monto_limite\": 1234567.00, \"fecha\": \"2025-11-12\", \"id_categoria\": null}', NULL, NULL),
(69, '2025-11-13 16:47:15', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 16, \"monto_limite\": 1234567.00, \"fecha\": \"2025-11-12\"}', NULL, NULL, NULL),
(70, '2025-11-13 16:47:17', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 15, \"monto_limite\": 15000.00, \"fecha\": \"2025-11-12\"}', NULL, NULL, NULL),
(71, '2025-11-13 16:47:27', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 17, \"monto_limite\": 15000.00, \"fecha\": \"2025-11-13\", \"id_categoria\": null}', NULL, NULL),
(72, '2025-11-13 16:48:17', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 18, \"monto_limite\": 1000.00, \"fecha\": \"2025-11-13\", \"id_categoria\": 4}', NULL, NULL),
(73, '2025-11-13 18:12:47', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 19, \"monto_limite\": 555.00, \"fecha\": \"2025-11-13\", \"id_categoria\": 5}', NULL, NULL),
(74, '2025-11-13 18:13:08', 'presupuestos', 'Actualizacion', '{\"id_presupuesto\": 17, \"monto_limite\": 15000.00, \"fecha\": \"2025-11-13\", \"id_categoria\": null}', '{\"id_presupuesto\": 17, \"monto_limite\": 15000.00, \"fecha\": \"2025-11-13\", \"id_categoria\": 3}', NULL, NULL),
(75, '2025-11-13 18:13:50', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 20, \"monto_limite\": 99999999.99, \"fecha\": \"2025-11-11\", \"id_categoria\": null}', NULL, NULL),
(76, '2025-11-13 18:14:27', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 21, \"monto_limite\": 9999.00, \"fecha\": \"2025-11-06\", \"id_categoria\": 2}', NULL, NULL),
(117, '2025-11-13 18:56:21', 'usuarios', 'Eliminacion', '{\"id_user\": 1, \"nombre\": \"Super Administrador\", \"username\": \"super.admin\", \"rol\": \"SU\"}', NULL, NULL, NULL),
(118, '2025-11-13 18:56:21', 'usuarios', 'Eliminacion', '{\"id_user\": 9, \"nombre\": \"Boris\", \"username\": \"boris\", \"rol\": \"COB\"}', NULL, NULL, NULL),
(119, '2025-11-13 18:56:21', 'usuarios', 'Eliminacion', '{\"id_user\": 10, \"nombre\": \"goku\", \"username\": \"goku\", \"rol\": \"ADM\"}', NULL, NULL, NULL),
(120, '2025-11-13 18:56:21', 'usuarios', 'Eliminacion', '{\"id_user\": 11, \"nombre\": \"renegul\", \"username\": \"renegul\", \"rol\": \"REC\"}', NULL, NULL, NULL),
(121, '2025-11-13 18:56:21', 'usuarios', 'Insercion', NULL, '{\"id_user\": 16, \"nombre\": \"Usuario Administración\", \"username\": \"u_administracion\", \"rol\": \"ADM\"}', NULL, NULL),
(122, '2025-11-13 18:56:21', 'usuarios', 'Insercion', NULL, '{\"id_user\": 17, \"nombre\": \"Usuario Rectoría\", \"username\": \"u_rectoria\", \"rol\": \"REC\"}', NULL, NULL),
(123, '2025-11-13 18:56:21', 'usuarios', 'Insercion', NULL, '{\"id_user\": 18, \"nombre\": \"Usuario Cobranzas\", \"username\": \"u_cobranzas\", \"rol\": \"COB\"}', NULL, NULL),
(124, '2025-11-13 18:56:21', 'usuarios', 'Insercion', NULL, '{\"id_user\": 19, \"nombre\": \"Nuevo Super Usuario\", \"username\": \"u_superusuario\", \"rol\": \"SU\"}', NULL, NULL),
(125, '2025-11-13 18:56:21', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 16, \"nombre\": \"Nómina Administrativa\", \"tipo\": \"Egreso\"}', NULL, NULL),
(126, '2025-11-13 18:56:21', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 17, \"nombre\": \"Mantenimiento Campus\", \"tipo\": \"Egreso\"}', NULL, NULL),
(127, '2025-11-13 18:56:21', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 18, \"nombre\": \"Servicios Básicos\", \"tipo\": \"Egreso\"}', NULL, NULL),
(128, '2025-11-13 18:56:21', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 19, \"nombre\": \"Papelería y Oficina\", \"tipo\": \"Egreso\"}', NULL, NULL),
(129, '2025-11-13 18:56:21', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 20, \"nombre\": \"Publicidad y Marketing\", \"tipo\": \"Egreso\"}', NULL, NULL),
(130, '2025-11-13 18:56:21', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 26, \"monto_limite\": 250000.00, \"fecha\": \"2025-11-01\", \"id_categoria\": 16}', NULL, NULL),
(131, '2025-11-13 18:56:21', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 27, \"monto_limite\": 75000.00, \"fecha\": \"2025-11-01\", \"id_categoria\": 17}', NULL, NULL),
(132, '2025-11-13 18:56:21', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 28, \"monto_limite\": 40000.00, \"fecha\": \"2025-11-01\", \"id_categoria\": 18}', NULL, NULL),
(133, '2025-11-13 18:56:21', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 29, \"monto_limite\": 10000.00, \"fecha\": \"2025-11-01\", \"id_categoria\": 19}', NULL, NULL),
(134, '2025-11-13 18:56:21', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 8, \"proveedor\": \"CFE\", \"monto\": 18500.00, \"fecha\": \"2025-11-10\", \"id_categoria\": 18}', 8, NULL),
(135, '2025-11-13 18:56:21', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 9, \"proveedor\": \"Servilimpia SA de CV\", \"monto\": 12000.00, \"fecha\": \"2025-11-05\", \"id_categoria\": 17}', 9, NULL),
(136, '2025-11-13 18:56:21', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 10, \"proveedor\": \"Office Depot\", \"monto\": 4500.00, \"fecha\": \"2025-11-07\", \"id_categoria\": 19}', 10, NULL),
(137, '2025-11-13 18:56:21', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 21, \"nombre\": \"Colegiaturas Licenciatura\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(138, '2025-11-13 18:56:21', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 22, \"nombre\": \"Colegiaturas Posgrado\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(139, '2025-11-13 18:56:21', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 23, \"nombre\": \"Servicios Escolares\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(140, '2025-11-13 18:56:21', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 24, \"nombre\": \"Inscripciones\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(141, '2025-11-13 18:56:21', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 15, \"alumno\": \"Juana Martinez Garcia\", \"monto\": 3200.00, \"fecha\": \"2025-11-10\", \"concepto\": \"Colegiatura\", \"id_categoria\": 21}', NULL, 15),
(142, '2025-11-13 18:56:21', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 16, \"alumno\": \"Carlos Sanchez Ruiz\", \"monto\": 4500.00, \"fecha\": \"2025-11-10\", \"concepto\": \"Colegiatura\", \"id_categoria\": 22}', NULL, 16),
(143, '2025-11-13 18:56:21', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 17, \"alumno\": \"Ana Lopez Herrera\", \"monto\": 250.00, \"fecha\": \"2025-11-11\", \"concepto\": \"Constancia con calificaciones\", \"id_categoria\": 23}', NULL, 17),
(144, '2025-11-13 18:56:21', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 18, \"alumno\": \"Luis Fernandez Soto\", \"monto\": 1500.00, \"fecha\": \"2025-11-11\", \"concepto\": \"Reinscripción\", \"id_categoria\": 24}', NULL, 18),
(145, '2025-11-13 18:56:21', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 19, \"alumno\": \"Sofia Gomez Lima\", \"monto\": 5500.00, \"fecha\": \"2025-11-12\", \"concepto\": \"Colegiatura\", \"id_categoria\": 22}', NULL, 19),
(146, '2025-11-13 18:56:21', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 20, \"alumno\": \"Mario Alberto Kempes\", \"monto\": 3200.00, \"fecha\": \"2025-11-12\", \"concepto\": \"Colegiatura\", \"id_categoria\": 21}', NULL, 20),
(147, '2025-11-13 18:56:21', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 21, \"alumno\": \"Pedro Picapiedra\", \"monto\": 120.00, \"fecha\": \"2025-11-13\", \"concepto\": \"Constancia simple\", \"id_categoria\": 23}', NULL, 21),
(148, '2025-11-13 18:56:21', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 22, \"alumno\": \"Alumno Temporal 1\", \"monto\": 1000.00, \"fecha\": \"2025-11-13\", \"concepto\": \"Otros\", \"id_categoria\": 24}', NULL, 22),
(149, '2025-11-13 18:56:21', 'egresos', 'Actualizacion', '{\"folio_egreso\": 10, \"proveedor\": \"Office Depot\", \"monto\": 4500.00, \"fecha\": \"2025-11-07\", \"id_categoria\": 19}', '{\"folio_egreso\": 10, \"proveedor\": \"Office Depot\", \"monto\": 4650.00, \"fecha\": \"2025-11-07\", \"id_categoria\": 19}', 10, NULL),
(150, '2025-11-13 18:56:21', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 30, \"monto_limite\": 20000.00, \"fecha\": \"2025-11-01\", \"id_categoria\": 20}', NULL, NULL),
(151, '2025-11-13 18:56:21', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 11, \"proveedor\": \"Facebook Ads\", \"monto\": 5000.00, \"fecha\": \"2025-11-13\", \"id_categoria\": 20}', 11, NULL),
(152, '2025-11-13 18:56:21', 'ingresos', 'Actualizacion', '{\"folio_ingreso\": 15, \"monto\": 3200.00}', '{\"folio_ingreso\": 15, \"monto\": 3100.00}', NULL, 15),
(153, '2025-11-13 18:56:21', 'egresos', 'Eliminacion', '{\"folio_egreso\": 11, \"proveedor\": \"Facebook Ads\", \"monto\": 5000.00, \"fecha\": \"2025-11-13\", \"id_categoria\": 20}', NULL, 11, NULL),
(154, '2025-11-13 18:56:21', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 25, \"nombre\": \"Diplomado Finanzas\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(155, '2025-11-13 18:56:21', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 22, \"alumno\": \"Alumno Temporal 1\", \"monto\": 1000.00, \"fecha\": \"2025-11-13\", \"concepto\": \"Otros\", \"id_categoria\": 24}', NULL, NULL, 22),
(156, '2025-11-13 18:56:21', 'presupuestos', 'Actualizacion', '{\"id_presupuesto\": 29, \"monto_limite\": 10000.00, \"fecha\": \"2025-11-01\", \"id_categoria\": 19}', '{\"id_presupuesto\": 29, \"monto_limite\": 12000.00, \"fecha\": \"2025-11-01\", \"id_categoria\": 19}', NULL, NULL),
(157, '2025-11-13 18:56:21', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 23, \"alumno\": \"Externo - Pablo Dias\", \"monto\": 7500.00, \"fecha\": \"2025-11-13\", \"concepto\": \"Otros\", \"id_categoria\": 25}', NULL, 23),
(158, '2025-11-13 18:56:21', 'categorias', 'Actualizacion', '{\"id_categoria\": 25, \"nombre\": \"Diplomado Finanzas\", \"tipo\": \"Ingreso\"}', '{\"id_categoria\": 25, \"nombre\": \"Diplomado Finanzas (CERRADO)\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(159, '2025-11-13 18:56:21', 'categorias', 'Eliminacion', '{\"id_categoria\": 25, \"nombre\": \"Diplomado Finanzas (CERRADO)\", \"tipo\": \"Ingreso\"}', NULL, NULL, NULL),
(160, '2025-11-13 18:56:21', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 31, \"monto_limite\": 500.00, \"fecha\": \"2025-11-13\", \"id_categoria\": 19}', NULL, NULL),
(161, '2025-11-13 18:56:21', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 31, \"monto_limite\": 500.00, \"fecha\": \"2025-11-13\"}', NULL, NULL, NULL),
(162, '2025-11-13 18:56:21', 'ingresos', 'Actualizacion', '{\"folio_ingreso\": 20, \"monto\": 3200.00}', '{\"folio_ingreso\": 20, \"monto\": 3200.00}', NULL, 20),
(163, '2025-11-13 18:56:21', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 4, \"alumno\": \"andres jorge chueco\", \"monto\": 7000.00, \"fecha\": \"2025-11-07\", \"concepto\": \"Constancia con calificaciones\", \"id_categoria\": 4}', NULL, NULL, 4),
(166, '2025-11-13 18:57:54', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 2, \"alumno\": \"jose miguel perez hernadez\", \"monto\": 850.00, \"fecha\": \"2025-11-29\", \"concepto\": \"Reinscripción\", \"id_categoria\": 4}', NULL, NULL, 2),
(167, '2025-11-13 18:57:59', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 3, \"alumno\": \"jose miguel perez hernadez\", \"monto\": 8000.00, \"fecha\": \"2025-11-07\", \"concepto\": \"Credenciales\", \"id_categoria\": 4}', NULL, NULL, 3),
(169, '2025-11-13 19:00:02', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 20, \"monto_limite\": 99999999.99, \"fecha\": \"2025-11-11\"}', NULL, NULL, NULL),
(175, '2025-11-13 19:11:01', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 32, \"monto_limite\": 1000000.00, \"fecha\": \"2025-11-01\", \"id_categoria\": null}', NULL, NULL),
(176, '2025-11-13 19:11:01', 'presupuestos', 'Actualizacion', '{\"id_presupuesto\": 17, \"monto_limite\": 15000.00, \"fecha\": \"2025-11-13\", \"id_categoria\": 3}', '{\"id_presupuesto\": 17, \"monto_limite\": 15000.00, \"fecha\": \"2025-11-13\", \"id_categoria\": 3}', NULL, NULL),
(177, '2025-11-13 19:11:01', 'presupuestos', 'Actualizacion', '{\"id_presupuesto\": 18, \"monto_limite\": 1000.00, \"fecha\": \"2025-11-13\", \"id_categoria\": 4}', '{\"id_presupuesto\": 18, \"monto_limite\": 1000.00, \"fecha\": \"2025-11-13\", \"id_categoria\": 4}', NULL, NULL),
(178, '2025-11-13 19:11:01', 'presupuestos', 'Actualizacion', '{\"id_presupuesto\": 19, \"monto_limite\": 555.00, \"fecha\": \"2025-11-13\", \"id_categoria\": 5}', '{\"id_presupuesto\": 19, \"monto_limite\": 555.00, \"fecha\": \"2025-11-13\", \"id_categoria\": 5}', NULL, NULL),
(179, '2025-11-13 19:11:01', 'presupuestos', 'Actualizacion', '{\"id_presupuesto\": 21, \"monto_limite\": 9999.00, \"fecha\": \"2025-11-06\", \"id_categoria\": 2}', '{\"id_presupuesto\": 21, \"monto_limite\": 9999.00, \"fecha\": \"2025-11-06\", \"id_categoria\": 2}', NULL, NULL),
(180, '2025-11-13 19:11:01', 'presupuestos', 'Actualizacion', '{\"id_presupuesto\": 26, \"monto_limite\": 250000.00, \"fecha\": \"2025-11-01\", \"id_categoria\": 16}', '{\"id_presupuesto\": 26, \"monto_limite\": 250000.00, \"fecha\": \"2025-11-01\", \"id_categoria\": 16}', NULL, NULL),
(181, '2025-11-13 19:11:01', 'presupuestos', 'Actualizacion', '{\"id_presupuesto\": 27, \"monto_limite\": 75000.00, \"fecha\": \"2025-11-01\", \"id_categoria\": 17}', '{\"id_presupuesto\": 27, \"monto_limite\": 75000.00, \"fecha\": \"2025-11-01\", \"id_categoria\": 17}', NULL, NULL),
(182, '2025-11-13 19:11:01', 'presupuestos', 'Actualizacion', '{\"id_presupuesto\": 28, \"monto_limite\": 40000.00, \"fecha\": \"2025-11-01\", \"id_categoria\": 18}', '{\"id_presupuesto\": 28, \"monto_limite\": 40000.00, \"fecha\": \"2025-11-01\", \"id_categoria\": 18}', NULL, NULL),
(183, '2025-11-13 19:11:01', 'presupuestos', 'Actualizacion', '{\"id_presupuesto\": 29, \"monto_limite\": 12000.00, \"fecha\": \"2025-11-01\", \"id_categoria\": 19}', '{\"id_presupuesto\": 29, \"monto_limite\": 12000.00, \"fecha\": \"2025-11-01\", \"id_categoria\": 19}', NULL, NULL),
(184, '2025-11-13 19:11:01', 'presupuestos', 'Actualizacion', '{\"id_presupuesto\": 30, \"monto_limite\": 20000.00, \"fecha\": \"2025-11-01\", \"id_categoria\": 20}', '{\"id_presupuesto\": 30, \"monto_limite\": 20000.00, \"fecha\": \"2025-11-01\", \"id_categoria\": 20}', NULL, NULL),
(185, '2025-11-13 19:11:19', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 17, \"monto_limite\": 15000.00, \"fecha\": \"2025-11-13\"}', NULL, NULL, NULL),
(186, '2025-11-13 19:11:23', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 18, \"monto_limite\": 1000.00, \"fecha\": \"2025-11-13\"}', NULL, NULL, NULL),
(187, '2025-11-13 19:11:26', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 19, \"monto_limite\": 555.00, \"fecha\": \"2025-11-13\"}', NULL, NULL, NULL),
(188, '2025-11-13 19:11:30', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 21, \"monto_limite\": 9999.00, \"fecha\": \"2025-11-06\"}', NULL, NULL, NULL),
(189, '2025-11-13 19:11:38', 'categorias', 'Eliminacion', '{\"id_categoria\": 2, \"nombre\": \"Lupe\", \"tipo\": \"Egreso\"}', NULL, NULL, NULL),
(190, '2025-11-13 19:11:42', 'categorias', 'Eliminacion', '{\"id_categoria\": 3, \"nombre\": \"fgfdgfdddgf\", \"tipo\": \"Egreso\"}', NULL, NULL, NULL),
(191, '2025-11-13 19:11:46', 'categorias', 'Eliminacion', '{\"id_categoria\": 4, \"nombre\": \"Jitomate\", \"tipo\": \"Ingreso\"}', NULL, NULL, NULL),
(192, '2025-11-13 19:11:50', 'categorias', 'Eliminacion', '{\"id_categoria\": 5, \"nombre\": \"RPTech\", \"tipo\": \"Egreso\"}', NULL, NULL, NULL),
(193, '2025-11-13 19:15:34', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 33, \"monto_limite\": 1000000.00, \"fecha\": \"2025-09-01\", \"id_categoria\": null}', NULL, NULL),
(194, '2025-11-13 19:15:34', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 34, \"monto_limite\": 250000.00, \"fecha\": \"2025-09-01\", \"id_categoria\": 16}', NULL, NULL),
(195, '2025-11-13 19:15:34', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 35, \"monto_limite\": 75000.00, \"fecha\": \"2025-09-01\", \"id_categoria\": 17}', NULL, NULL),
(196, '2025-11-13 19:15:34', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 36, \"monto_limite\": 40000.00, \"fecha\": \"2025-09-01\", \"id_categoria\": 18}', NULL, NULL),
(197, '2025-11-13 19:15:34', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 37, \"monto_limite\": 10000.00, \"fecha\": \"2025-09-01\", \"id_categoria\": 19}', NULL, NULL),
(198, '2025-11-13 19:15:34', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 38, \"monto_limite\": 20000.00, \"fecha\": \"2025-09-01\", \"id_categoria\": 20}', NULL, NULL),
(199, '2025-11-13 19:15:35', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 12, \"proveedor\": \"CFE\", \"monto\": 17800.00, \"fecha\": \"2025-09-10\", \"id_categoria\": 18}', 12, NULL),
(200, '2025-11-13 19:15:35', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 13, \"proveedor\": \"Servilimpia SA de CV\", \"monto\": 12000.00, \"fecha\": \"2025-09-05\", \"id_categoria\": 17}', 13, NULL),
(201, '2025-11-13 19:15:35', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 14, \"proveedor\": \"Google Ads\", \"monto\": 7500.00, \"fecha\": \"2025-09-15\", \"id_categoria\": 20}', 14, NULL),
(202, '2025-11-13 19:15:35', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 24, \"alumno\": \"Juana Martinez Garcia\", \"monto\": 3200.00, \"fecha\": \"2025-09-10\", \"concepto\": \"Colegiatura\", \"id_categoria\": 21}', NULL, 24),
(203, '2025-11-13 19:15:35', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 25, \"alumno\": \"Carlos Sanchez Ruiz\", \"monto\": 4500.00, \"fecha\": \"2025-09-10\", \"concepto\": \"Colegiatura\", \"id_categoria\": 22}', NULL, 25),
(204, '2025-11-13 19:15:35', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 26, \"alumno\": \"Miguel Hernandez\", \"monto\": 1500.00, \"fecha\": \"2025-09-11\", \"concepto\": \"Inscripción\", \"id_categoria\": 24}', NULL, 26),
(205, '2025-11-13 19:15:35', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 27, \"alumno\": \"Sofia Gomez Lima\", \"monto\": 5500.00, \"fecha\": \"2025-09-12\", \"concepto\": \"Colegiatura\", \"id_categoria\": 22}', NULL, 27),
(206, '2025-11-13 19:15:35', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 28, \"alumno\": \"Pedro Picapiedra\", \"monto\": 120.00, \"fecha\": \"2025-09-13\", \"concepto\": \"Constancia simple\", \"id_categoria\": 23}', NULL, 28),
(207, '2025-11-13 19:15:35', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 39, \"monto_limite\": 1050000.00, \"fecha\": \"2025-10-01\", \"id_categoria\": null}', NULL, NULL),
(208, '2025-11-13 19:15:35', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 40, \"monto_limite\": 250000.00, \"fecha\": \"2025-10-01\", \"id_categoria\": 16}', NULL, NULL),
(209, '2025-11-13 19:15:35', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 41, \"monto_limite\": 75000.00, \"fecha\": \"2025-10-01\", \"id_categoria\": 17}', NULL, NULL),
(210, '2025-11-13 19:15:35', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 42, \"monto_limite\": 40000.00, \"fecha\": \"2025-10-01\", \"id_categoria\": 18}', NULL, NULL),
(211, '2025-11-13 19:15:35', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 43, \"monto_limite\": 15000.00, \"fecha\": \"2025-10-01\", \"id_categoria\": 19}', NULL, NULL),
(212, '2025-11-13 19:15:35', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 44, \"monto_limite\": 25000.00, \"fecha\": \"2025-10-01\", \"id_categoria\": 20}', NULL, NULL),
(213, '2025-11-13 19:15:35', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 15, \"proveedor\": \"CFE\", \"monto\": 18100.00, \"fecha\": \"2025-10-10\", \"id_categoria\": 18}', 15, NULL),
(214, '2025-11-13 19:15:35', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 16, \"proveedor\": \"Servilimpia SA de CV\", \"monto\": 12000.00, \"fecha\": \"2025-10-05\", \"id_categoria\": 17}', 16, NULL),
(215, '2025-11-13 19:15:35', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 17, \"proveedor\": \"Office Depot\", \"monto\": 6200.00, \"fecha\": \"2025-10-08\", \"id_categoria\": 19}', 17, NULL),
(216, '2025-11-13 19:15:35', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 29, \"alumno\": \"Juana Martinez Garcia\", \"monto\": 3200.00, \"fecha\": \"2025-10-10\", \"concepto\": \"Colegiatura\", \"id_categoria\": 21}', NULL, 29),
(217, '2025-11-13 19:15:35', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 30, \"alumno\": \"Carlos Sanchez Ruiz\", \"monto\": 4500.00, \"fecha\": \"2025-10-10\", \"concepto\": \"Colegiatura\", \"id_categoria\": 22}', NULL, 30),
(218, '2025-11-13 19:15:35', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 31, \"alumno\": \"Ana Lopez Herrera\", \"monto\": 250.00, \"fecha\": \"2025-10-11\", \"concepto\": \"Constancia con calificaciones\", \"id_categoria\": 23}', NULL, 31),
(219, '2025-11-13 19:15:35', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 32, \"alumno\": \"Sofia Gomez Lima\", \"monto\": 5500.00, \"fecha\": \"2025-10-12\", \"concepto\": \"Colegiatura\", \"id_categoria\": 22}', NULL, 32),
(220, '2025-11-13 19:15:35', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 33, \"alumno\": \"Mario Alberto Kempes\", \"monto\": 3200.00, \"fecha\": \"2025-10-12\", \"concepto\": \"Colegiatura\", \"id_categoria\": 21}', NULL, 33),
(226, '2025-11-13 19:18:39', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 50, \"monto_limite\": 700000.00, \"fecha\": \"2025-07-01\", \"id_categoria\": null}', NULL, NULL),
(227, '2025-11-13 19:18:39', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 51, \"monto_limite\": 250000.00, \"fecha\": \"2025-07-01\", \"id_categoria\": 16}', NULL, NULL),
(228, '2025-11-13 19:18:39', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 52, \"monto_limite\": 50000.00, \"fecha\": \"2025-07-01\", \"id_categoria\": 17}', NULL, NULL),
(229, '2025-11-13 19:18:39', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 53, \"monto_limite\": 30000.00, \"fecha\": \"2025-07-01\", \"id_categoria\": 18}', NULL, NULL),
(230, '2025-11-13 19:18:39', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 54, \"monto_limite\": 5000.00, \"fecha\": \"2025-07-01\", \"id_categoria\": 19}', NULL, NULL),
(231, '2025-11-13 19:18:40', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 18, \"proveedor\": \"CFE\", \"monto\": 14500.00, \"fecha\": \"2025-07-10\", \"id_categoria\": 18}', 18, NULL),
(232, '2025-11-13 19:18:40', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 19, \"proveedor\": \"Servilimpia SA de CV\", \"monto\": 6000.00, \"fecha\": \"2025-07-05\", \"id_categoria\": 17}', 19, NULL),
(233, '2025-11-13 19:18:40', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 34, \"alumno\": \"Egresado-Roberto Perez\", \"monto\": 1500.00, \"fecha\": \"2025-07-15\", \"concepto\": \"Certificados\", \"id_categoria\": 23}', NULL, 34),
(234, '2025-11-13 19:18:40', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 35, \"alumno\": \"Ana Lopez Herrera\", \"monto\": 850.00, \"fecha\": \"2025-07-16\", \"concepto\": \"Equivalencias\", \"id_categoria\": 23}', NULL, 35),
(235, '2025-11-13 19:18:40', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 55, \"monto_limite\": 1200000.00, \"fecha\": \"2025-08-01\", \"id_categoria\": null}', NULL, NULL),
(236, '2025-11-13 19:18:40', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 56, \"monto_limite\": 250000.00, \"fecha\": \"2025-08-01\", \"id_categoria\": 16}', NULL, NULL),
(237, '2025-11-13 19:18:40', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 57, \"monto_limite\": 75000.00, \"fecha\": \"2025-08-01\", \"id_categoria\": 17}', NULL, NULL),
(238, '2025-11-13 19:18:40', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 58, \"monto_limite\": 40000.00, \"fecha\": \"2025-08-01\", \"id_categoria\": 18}', NULL, NULL),
(239, '2025-11-13 19:18:40', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 59, \"monto_limite\": 20000.00, \"fecha\": \"2025-08-01\", \"id_categoria\": 19}', NULL, NULL),
(240, '2025-11-13 19:18:40', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 60, \"monto_limite\": 50000.00, \"fecha\": \"2025-08-01\", \"id_categoria\": 20}', NULL, NULL),
(241, '2025-11-13 19:18:41', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 20, \"proveedor\": \"CFE\", \"monto\": 19000.00, \"fecha\": \"2025-08-10\", \"id_categoria\": 18}', 20, NULL),
(242, '2025-11-13 19:18:41', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 21, \"proveedor\": \"Facebook / Google Ads\", \"monto\": 25000.00, \"fecha\": \"2025-08-05\", \"id_categoria\": 20}', 21, NULL),
(243, '2025-11-13 19:18:41', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 36, \"alumno\": \"Nuevo Alumno 1\", \"monto\": 2500.00, \"fecha\": \"2025-08-05\", \"concepto\": \"Inscripción\", \"id_categoria\": 24}', NULL, 36),
(244, '2025-11-13 19:18:41', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 37, \"alumno\": \"Nuevo Alumno 2\", \"monto\": 3500.00, \"fecha\": \"2025-08-05\", \"concepto\": \"Inscripción\", \"id_categoria\": 24}', NULL, 37),
(245, '2025-11-13 19:18:41', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 38, \"alumno\": \"Juana Martinez Garcia\", \"monto\": 1500.00, \"fecha\": \"2025-08-06\", \"concepto\": \"Reinscripción\", \"id_categoria\": 24}', NULL, 38),
(246, '2025-11-13 19:18:41', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 39, \"alumno\": \"Carlos Sanchez Ruiz\", \"monto\": 2000.00, \"fecha\": \"2025-08-06\", \"concepto\": \"Reinscripción\", \"id_categoria\": 24}', NULL, 39),
(247, '2025-11-13 19:18:41', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 40, \"alumno\": \"Ana Lopez Herrera\", \"monto\": 1500.00, \"fecha\": \"2025-08-07\", \"concepto\": \"Reinscripción\", \"id_categoria\": 24}', NULL, 40),
(248, '2025-11-13 19:18:41', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 41, \"alumno\": \"Luis Fernandez Soto\", \"monto\": 1500.00, \"fecha\": \"2025-08-07\", \"concepto\": \"Reinscripción\", \"id_categoria\": 24}', NULL, 41),
(249, '2025-11-13 19:18:41', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 42, \"alumno\": \"Sofia Gomez Lima\", \"monto\": 2500.00, \"fecha\": \"2025-08-07\", \"concepto\": \"Reinscripción\", \"id_categoria\": 24}', NULL, 42),
(250, '2025-11-13 19:18:41', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 43, \"alumno\": \"Mario Alberto Kempes\", \"monto\": 1500.00, \"fecha\": \"2025-08-08\", \"concepto\": \"Reinscripción\", \"id_categoria\": 24}', NULL, 43),
(251, '2025-11-13 19:18:41', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 44, \"alumno\": \"Pedro Picapiedra\", \"monto\": 1500.00, \"fecha\": \"2025-08-08\", \"concepto\": \"Reinscripción\", \"id_categoria\": 24}', NULL, 44),
(252, '2025-11-13 19:18:41', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 45, \"alumno\": \"Nuevo Alumno 3\", \"monto\": 2500.00, \"fecha\": \"2025-08-09\", \"concepto\": \"Inscripción\", \"id_categoria\": 24}', NULL, 45),
(253, '2025-11-13 19:18:41', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 46, \"alumno\": \"Nuevo Alumno 4\", \"monto\": 4500.00, \"fecha\": \"2025-08-10\", \"concepto\": \"Inscripción\", \"id_categoria\": 24}', NULL, 46),
(254, '2025-11-13 19:18:41', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 47, \"alumno\": \"Juana Martinez Garcia\", \"monto\": 3200.00, \"fecha\": \"2025-08-10\", \"concepto\": \"Colegiatura\", \"id_categoria\": 21}', NULL, 47),
(255, '2025-11-13 20:09:45', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 48, \"alumno\": \"andres jorge chueco\", \"monto\": 500.00, \"fecha\": \"2025-11-13\", \"concepto\": \"Inscripción\", \"id_categoria\": 24}', NULL, 48),
(256, '2025-11-19 02:01:59', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 49, \"alumno\": \"jose miguel perez hernadez\", \"monto\": 70.00, \"fecha\": \"2025-11-18\", \"concepto\": \"Credenciales\", \"id_categoria\": 24}', NULL, 49),
(257, '2025-11-19 02:02:13', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 49, \"alumno\": \"jose miguel perez hernadez\", \"monto\": 70.00, \"fecha\": \"2025-11-18\", \"concepto\": \"Credenciales\", \"id_categoria\": 24}', NULL, NULL, 49),
(258, '2025-11-19 02:02:34', 'ingresos', 'Actualizacion', '{\"folio_ingreso\": 48, \"monto\": 500.00}', '{\"folio_ingreso\": 48, \"monto\": 500.00}', NULL, 48),
(259, '2025-11-19 02:04:42', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 26, \"nombre\": \"prueba esta\", \"tipo\": \"Egreso\"}', NULL, NULL),
(260, '2025-11-19 02:04:46', 'categorias', 'Eliminacion', '{\"id_categoria\": 26, \"nombre\": \"prueba esta\", \"tipo\": \"Egreso\"}', NULL, NULL, NULL),
(279, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 204, \"nombre\": \"IUM COMISIONES\", \"tipo\": \"Egreso\"}', NULL, NULL),
(280, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 205, \"nombre\": \"IUM RENTAS\", \"tipo\": \"Egreso\"}', NULL, NULL),
(281, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 206, \"nombre\": \"IUM HONORARIOS LICENCIATURA\", \"tipo\": \"Egreso\"}', NULL, NULL),
(282, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 207, \"nombre\": \"IUM HONORARIOS MAESTRIA\", \"tipo\": \"Egreso\"}', NULL, NULL),
(283, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 208, \"nombre\": \"IUM HONORARIOS DOCTORADO\", \"tipo\": \"Egreso\"}', NULL, NULL),
(284, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 209, \"nombre\": \"IUM IMSS\", \"tipo\": \"Egreso\"}', NULL, NULL),
(285, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 210, \"nombre\": \"IUM SUELDOS Y SALARIOS\", \"tipo\": \"Egreso\"}', NULL, NULL),
(286, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 211, \"nombre\": \"IUM PUBLICIDAD\", \"tipo\": \"Egreso\"}', NULL, NULL),
(287, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 212, \"nombre\": \"IUM SERVICIOS\", \"tipo\": \"Egreso\"}', NULL, NULL),
(288, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 213, \"nombre\": \"EDUCACION CONTINUA\", \"tipo\": \"Egreso\"}', NULL, NULL),
(289, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 214, \"nombre\": \"IUM MANTENIMIENTO\", \"tipo\": \"Egreso\"}', NULL, NULL),
(290, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 215, \"nombre\": \"IUM CAFETERIA Y LIMPIEZA\", \"tipo\": \"Egreso\"}', NULL, NULL),
(291, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 216, \"nombre\": \"IUM SECRETARIA DE EDUCACION\", \"tipo\": \"Egreso\"}', NULL, NULL),
(292, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 217, \"nombre\": \"IUM IMPUESTOS\", \"tipo\": \"Egreso\"}', NULL, NULL),
(293, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 218, \"nombre\": \"IUM CREDITOS\", \"tipo\": \"Egreso\"}', NULL, NULL),
(294, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 219, \"nombre\": \"IUM OTROS\", \"tipo\": \"Egreso\"}', NULL, NULL),
(295, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 220, \"nombre\": \"IUM PAPELERIA\", \"tipo\": \"Egreso\"}', NULL, NULL),
(296, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 221, \"nombre\": \"IUM GASOLINA\", \"tipo\": \"Egreso\"}', NULL, NULL),
(297, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 222, \"nombre\": \"IUM FRANQUICIA\", \"tipo\": \"Egreso\"}', NULL, NULL),
(298, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 223, \"nombre\": \"IUM TRAMITES\", \"tipo\": \"Egreso\"}', NULL, NULL),
(299, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 224, \"nombre\": \"IUM REEMBOLSOS\", \"tipo\": \"Egreso\"}', NULL, NULL),
(300, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 225, \"nombre\": \"IUM ISN\", \"tipo\": \"Egreso\"}', NULL, NULL),
(301, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 226, \"nombre\": \"IUM PAQUETERIA Y MENSAJERIA\", \"tipo\": \"Egreso\"}', NULL, NULL),
(302, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 227, \"nombre\": \"REINSCRIPCIONES\", \"tipo\": \"Egreso\"}', NULL, NULL),
(303, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 228, \"nombre\": \"IUM COMPENSACION\", \"tipo\": \"Egreso\"}', NULL, NULL),
(304, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 229, \"nombre\": \"IUM EXTRAORDINARIOS\", \"tipo\": \"Egreso\"}', NULL, NULL),
(305, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 230, \"nombre\": \"TITULACIONES\", \"tipo\": \"Egreso\"}', NULL, NULL),
(306, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 231, \"nombre\": \"DIPLOMADOS\", \"tipo\": \"Egreso\"}', NULL, NULL),
(307, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 232, \"nombre\": \"IUM HONORARIOS SOLIDARIOS\", \"tipo\": \"Egreso\"}', NULL, NULL),
(308, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 233, \"nombre\": \"IUM EVENTOS\", \"tipo\": \"Egreso\"}', NULL, NULL),
(309, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 235, \"nombre\": \"TITULACION\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(310, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 236, \"nombre\": \"INSCRIPCION\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(311, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 237, \"nombre\": \"REINSCRIPCION\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(312, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 238, \"nombre\": \"COLEGIATURA\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(313, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 239, \"nombre\": \"CONSTANCIA SIMPLE\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(314, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 240, \"nombre\": \"CONSTANCIA CON CALIFICACIONES\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(315, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 241, \"nombre\": \"HISTORIALES\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(316, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 242, \"nombre\": \"CERTIFICADOS\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(317, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 243, \"nombre\": \"EQUIVALENCIAS\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(318, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 244, \"nombre\": \"CREDENCIALES\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(319, '2025-11-20 21:39:37', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 245, \"nombre\": \"OTROS\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(320, '2025-11-20 22:02:44', 'categorias', 'Insercion', NULL, '{\"id_categoria\": 246, \"nombre\": \"Jitomate\", \"tipo\": \"Ingreso\"}', NULL, NULL),
(321, '2025-11-20 22:02:53', 'categorias', 'Eliminacion', '{\"id_categoria\": 246, \"nombre\": \"Jitomate\", \"tipo\": \"Ingreso\"}', NULL, NULL, NULL),
(322, '2025-11-20 22:04:26', 'egresos', 'Eliminacion', '{\"folio_egreso\": 8, \"proveedor\": \"CFE\", \"monto\": 18500.00, \"fecha\": \"2025-11-10\", \"id_categoria\": 18}', NULL, 8, NULL),
(323, '2025-11-20 22:06:41', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 15, \"alumno\": \"Juana Martinez Garcia\", \"monto\": 3100.00, \"fecha\": \"2025-11-10\", \"concepto\": \"Colegiatura\", \"id_categoria\": 21}', NULL, NULL, 15),
(324, '2025-11-20 22:06:44', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 25, \"alumno\": \"Carlos Sanchez Ruiz\", \"monto\": 4500.00, \"fecha\": \"2025-09-10\", \"concepto\": \"Colegiatura\", \"id_categoria\": 22}', NULL, NULL, 25),
(325, '2025-11-20 22:06:47', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 27, \"alumno\": \"Sofia Gomez Lima\", \"monto\": 5500.00, \"fecha\": \"2025-09-12\", \"concepto\": \"Colegiatura\", \"id_categoria\": 22}', NULL, NULL, 27),
(326, '2025-11-20 22:06:56', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 29, \"alumno\": \"Juana Martinez Garcia\", \"monto\": 3200.00, \"fecha\": \"2025-10-10\", \"concepto\": \"Colegiatura\", \"id_categoria\": 21}', NULL, NULL, 29),
(327, '2025-11-20 22:06:58', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 28, \"alumno\": \"Pedro Picapiedra\", \"monto\": 120.00, \"fecha\": \"2025-09-13\", \"concepto\": \"Constancia simple\", \"id_categoria\": 23}', NULL, NULL, 28),
(328, '2025-11-20 22:07:01', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 30, \"alumno\": \"Carlos Sanchez Ruiz\", \"monto\": 4500.00, \"fecha\": \"2025-10-10\", \"concepto\": \"Colegiatura\", \"id_categoria\": 22}', NULL, NULL, 30),
(329, '2025-11-20 22:07:05', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 32, \"alumno\": \"Sofia Gomez Lima\", \"monto\": 5500.00, \"fecha\": \"2025-10-12\", \"concepto\": \"Colegiatura\", \"id_categoria\": 22}', NULL, NULL, 32),
(330, '2025-11-20 22:07:08', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 34, \"alumno\": \"Egresado-Roberto Perez\", \"monto\": 1500.00, \"fecha\": \"2025-07-15\", \"concepto\": \"Certificados\", \"id_categoria\": 23}', NULL, NULL, 34),
(331, '2025-11-20 22:07:11', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 31, \"alumno\": \"Ana Lopez Herrera\", \"monto\": 250.00, \"fecha\": \"2025-10-11\", \"concepto\": \"Constancia con calificaciones\", \"id_categoria\": 23}', NULL, NULL, 31),
(332, '2025-11-20 22:07:15', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 33, \"alumno\": \"Mario Alberto Kempes\", \"monto\": 3200.00, \"fecha\": \"2025-10-12\", \"concepto\": \"Colegiatura\", \"id_categoria\": 21}', NULL, NULL, 33),
(333, '2025-11-20 22:07:24', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 21, \"alumno\": \"Pedro Picapiedra\", \"monto\": 120.00, \"fecha\": \"2025-11-13\", \"concepto\": \"Constancia simple\", \"id_categoria\": 23}', NULL, NULL, 21),
(334, '2025-11-20 22:07:27', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 35, \"alumno\": \"Ana Lopez Herrera\", \"monto\": 850.00, \"fecha\": \"2025-07-16\", \"concepto\": \"Equivalencias\", \"id_categoria\": 23}', NULL, NULL, 35),
(335, '2025-11-20 22:07:30', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 38, \"alumno\": \"Juana Martinez Garcia\", \"monto\": 1500.00, \"fecha\": \"2025-08-06\", \"concepto\": \"Reinscripción\", \"id_categoria\": 24}', NULL, NULL, 38),
(336, '2025-11-20 22:07:33', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 40, \"alumno\": \"Ana Lopez Herrera\", \"monto\": 1500.00, \"fecha\": \"2025-08-07\", \"concepto\": \"Reinscripción\", \"id_categoria\": 24}', NULL, NULL, 40);
INSERT INTO `auditoria` (`id_auditoria`, `fecha_hora`, `seccion`, `accion`, `old_valor`, `new_valor`, `folio_egreso`, `folio_ingreso`) VALUES
(337, '2025-11-20 22:07:36', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 37, \"alumno\": \"Nuevo Alumno 2\", \"monto\": 3500.00, \"fecha\": \"2025-08-05\", \"concepto\": \"Inscripción\", \"id_categoria\": 24}', NULL, NULL, 37),
(338, '2025-11-20 22:07:42', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 39, \"alumno\": \"Carlos Sanchez Ruiz\", \"monto\": 2000.00, \"fecha\": \"2025-08-06\", \"concepto\": \"Reinscripción\", \"id_categoria\": 24}', NULL, NULL, 39),
(339, '2025-11-20 22:07:48', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 41, \"alumno\": \"Luis Fernandez Soto\", \"monto\": 1500.00, \"fecha\": \"2025-08-07\", \"concepto\": \"Reinscripción\", \"id_categoria\": 24}', NULL, NULL, 41),
(340, '2025-11-20 22:07:51', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 44, \"alumno\": \"Pedro Picapiedra\", \"monto\": 1500.00, \"fecha\": \"2025-08-08\", \"concepto\": \"Reinscripción\", \"id_categoria\": 24}', NULL, NULL, 44),
(341, '2025-11-20 22:07:54', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 45, \"alumno\": \"Nuevo Alumno 3\", \"monto\": 2500.00, \"fecha\": \"2025-08-09\", \"concepto\": \"Inscripción\", \"id_categoria\": 24}', NULL, NULL, 45),
(342, '2025-11-20 22:07:57', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 46, \"alumno\": \"Nuevo Alumno 4\", \"monto\": 4500.00, \"fecha\": \"2025-08-10\", \"concepto\": \"Inscripción\", \"id_categoria\": 24}', NULL, NULL, 46),
(343, '2025-11-20 22:07:59', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 43, \"alumno\": \"Mario Alberto Kempes\", \"monto\": 1500.00, \"fecha\": \"2025-08-08\", \"concepto\": \"Reinscripción\", \"id_categoria\": 24}', NULL, NULL, 43),
(344, '2025-11-20 22:08:04', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 16, \"alumno\": \"Carlos Sanchez Ruiz\", \"monto\": 4500.00, \"fecha\": \"2025-11-10\", \"concepto\": \"Colegiatura\", \"id_categoria\": 22}', NULL, NULL, 16),
(345, '2025-11-20 22:08:06', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 48, \"alumno\": \"andres jorge chueco\", \"monto\": 500.00, \"fecha\": \"2025-11-13\", \"concepto\": \"Inscripción\", \"id_categoria\": 24}', NULL, NULL, 48),
(346, '2025-11-20 22:08:09', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 47, \"alumno\": \"Juana Martinez Garcia\", \"monto\": 3200.00, \"fecha\": \"2025-08-10\", \"concepto\": \"Colegiatura\", \"id_categoria\": 21}', NULL, NULL, 47),
(347, '2025-11-20 22:08:12', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 42, \"alumno\": \"Sofia Gomez Lima\", \"monto\": 2500.00, \"fecha\": \"2025-08-07\", \"concepto\": \"Reinscripción\", \"id_categoria\": 24}', NULL, NULL, 42),
(348, '2025-11-20 22:08:16', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 36, \"alumno\": \"Nuevo Alumno 1\", \"monto\": 2500.00, \"fecha\": \"2025-08-05\", \"concepto\": \"Inscripción\", \"id_categoria\": 24}', NULL, NULL, 36),
(349, '2025-11-20 22:08:19', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 26, \"alumno\": \"Miguel Hernandez\", \"monto\": 1500.00, \"fecha\": \"2025-09-11\", \"concepto\": \"Inscripción\", \"id_categoria\": 24}', NULL, NULL, 26),
(350, '2025-11-20 22:08:23', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 24, \"alumno\": \"Juana Martinez Garcia\", \"monto\": 3200.00, \"fecha\": \"2025-09-10\", \"concepto\": \"Colegiatura\", \"id_categoria\": 21}', NULL, NULL, 24),
(351, '2025-11-20 22:08:29', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 20, \"alumno\": \"Mario Alberto Kempes\", \"monto\": 3200.00, \"fecha\": \"2025-11-12\", \"concepto\": \"Colegiatura\", \"id_categoria\": 21}', NULL, NULL, 20),
(352, '2025-11-20 22:08:31', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 19, \"alumno\": \"Sofia Gomez Lima\", \"monto\": 5500.00, \"fecha\": \"2025-11-12\", \"concepto\": \"Colegiatura\", \"id_categoria\": 22}', NULL, NULL, 19),
(353, '2025-11-20 22:08:35', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 18, \"alumno\": \"Luis Fernandez Soto\", \"monto\": 1500.00, \"fecha\": \"2025-11-11\", \"concepto\": \"Reinscripción\", \"id_categoria\": 24}', NULL, NULL, 18),
(354, '2025-11-20 22:08:37', 'ingresos', 'Eliminacion', '{\"folio_ingreso\": 17, \"alumno\": \"Ana Lopez Herrera\", \"monto\": 250.00, \"fecha\": \"2025-11-11\", \"concepto\": \"Constancia con calificaciones\", \"id_categoria\": 23}', NULL, NULL, 17),
(355, '2025-11-20 22:08:54', 'categorias', 'Eliminacion', '{\"id_categoria\": 24, \"nombre\": \"Inscripciones\", \"tipo\": \"Ingreso\"}', NULL, NULL, NULL),
(356, '2025-11-20 22:08:57', 'categorias', 'Eliminacion', '{\"id_categoria\": 23, \"nombre\": \"Servicios Escolares\", \"tipo\": \"Ingreso\"}', NULL, NULL, NULL),
(357, '2025-11-20 22:08:59', 'categorias', 'Eliminacion', '{\"id_categoria\": 22, \"nombre\": \"Colegiaturas Posgrado\", \"tipo\": \"Ingreso\"}', NULL, NULL, NULL),
(358, '2025-11-20 22:09:02', 'categorias', 'Eliminacion', '{\"id_categoria\": 21, \"nombre\": \"Colegiaturas Licenciatura\", \"tipo\": \"Ingreso\"}', NULL, NULL, NULL),
(359, '2025-11-20 22:09:15', 'egresos', 'Eliminacion', '{\"folio_egreso\": 10, \"proveedor\": \"Office Depot\", \"monto\": 4650.00, \"fecha\": \"2025-11-07\", \"id_categoria\": 19}', NULL, 10, NULL),
(360, '2025-11-20 22:09:18', 'egresos', 'Eliminacion', '{\"folio_egreso\": 9, \"proveedor\": \"Servilimpia SA de CV\", \"monto\": 12000.00, \"fecha\": \"2025-11-05\", \"id_categoria\": 17}', NULL, 9, NULL),
(361, '2025-11-20 22:09:21', 'egresos', 'Eliminacion', '{\"folio_egreso\": 15, \"proveedor\": \"CFE\", \"monto\": 18100.00, \"fecha\": \"2025-10-10\", \"id_categoria\": 18}', NULL, 15, NULL),
(362, '2025-11-20 22:09:24', 'egresos', 'Eliminacion', '{\"folio_egreso\": 17, \"proveedor\": \"Office Depot\", \"monto\": 6200.00, \"fecha\": \"2025-10-08\", \"id_categoria\": 19}', NULL, 17, NULL),
(363, '2025-11-20 22:09:27', 'egresos', 'Eliminacion', '{\"folio_egreso\": 16, \"proveedor\": \"Servilimpia SA de CV\", \"monto\": 12000.00, \"fecha\": \"2025-10-05\", \"id_categoria\": 17}', NULL, 16, NULL),
(364, '2025-11-20 22:09:30', 'egresos', 'Eliminacion', '{\"folio_egreso\": 14, \"proveedor\": \"Google Ads\", \"monto\": 7500.00, \"fecha\": \"2025-09-15\", \"id_categoria\": 20}', NULL, 14, NULL),
(365, '2025-11-20 22:09:34', 'egresos', 'Eliminacion', '{\"folio_egreso\": 12, \"proveedor\": \"CFE\", \"monto\": 17800.00, \"fecha\": \"2025-09-10\", \"id_categoria\": 18}', NULL, 12, NULL),
(366, '2025-11-20 22:09:38', 'egresos', 'Eliminacion', '{\"folio_egreso\": 13, \"proveedor\": \"Servilimpia SA de CV\", \"monto\": 12000.00, \"fecha\": \"2025-09-05\", \"id_categoria\": 17}', NULL, 13, NULL),
(367, '2025-11-20 22:09:41', 'egresos', 'Eliminacion', '{\"folio_egreso\": 20, \"proveedor\": \"CFE\", \"monto\": 19000.00, \"fecha\": \"2025-08-10\", \"id_categoria\": 18}', NULL, 20, NULL),
(368, '2025-11-20 22:09:44', 'egresos', 'Eliminacion', '{\"folio_egreso\": 21, \"proveedor\": \"Facebook / Google Ads\", \"monto\": 25000.00, \"fecha\": \"2025-08-05\", \"id_categoria\": 20}', NULL, 21, NULL),
(369, '2025-11-20 22:09:47', 'egresos', 'Eliminacion', '{\"folio_egreso\": 18, \"proveedor\": \"CFE\", \"monto\": 14500.00, \"fecha\": \"2025-07-10\", \"id_categoria\": 18}', NULL, 18, NULL),
(370, '2025-11-20 22:09:50', 'egresos', 'Eliminacion', '{\"folio_egreso\": 19, \"proveedor\": \"Servilimpia SA de CV\", \"monto\": 6000.00, \"fecha\": \"2025-07-05\", \"id_categoria\": 17}', NULL, 19, NULL),
(372, '2025-11-20 22:10:17', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 55, \"monto_limite\": 1200000.00, \"nombre\": null, \"fecha\": \"2025-08-01\"}', NULL, NULL, NULL),
(373, '2025-11-20 22:10:25', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 50, \"monto_limite\": 700000.00, \"nombre\": null, \"fecha\": \"2025-07-01\"}', NULL, NULL, NULL),
(374, '2025-11-20 22:10:31', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 39, \"monto_limite\": 1050000.00, \"nombre\": null, \"fecha\": \"2025-10-01\"}', NULL, NULL, NULL),
(375, '2025-11-20 22:10:34', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 33, \"monto_limite\": 1000000.00, \"nombre\": null, \"fecha\": \"2025-09-01\"}', NULL, NULL, NULL),
(376, '2025-11-20 22:10:36', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 32, \"monto_limite\": 1000000.00, \"nombre\": null, \"fecha\": \"2025-11-01\"}', NULL, NULL, NULL),
(377, '2025-11-20 22:10:52', 'categorias', 'Eliminacion', '{\"id_categoria\": 20, \"nombre\": \"Publicidad y Marketing\", \"tipo\": \"Egreso\"}', NULL, NULL, NULL),
(378, '2025-11-20 22:10:55', 'categorias', 'Eliminacion', '{\"id_categoria\": 19, \"nombre\": \"Papelería y Oficina\", \"tipo\": \"Egreso\"}', NULL, NULL, NULL),
(379, '2025-11-20 22:10:57', 'categorias', 'Eliminacion', '{\"id_categoria\": 17, \"nombre\": \"Mantenimiento Campus\", \"tipo\": \"Egreso\"}', NULL, NULL, NULL),
(380, '2025-11-20 22:11:01', 'categorias', 'Eliminacion', '{\"id_categoria\": 16, \"nombre\": \"Nómina Administrativa\", \"tipo\": \"Egreso\"}', NULL, NULL, NULL),
(381, '2025-11-20 22:11:04', 'categorias', 'Eliminacion', '{\"id_categoria\": 18, \"nombre\": \"Servicios Básicos\", \"tipo\": \"Egreso\"}', NULL, NULL, NULL),
(382, '2025-11-21 18:29:57', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 61, \"monto_limite\": 15000000.00, \"nombre\": \"Seth Rollins\", \"fecha\": \"2025-11-21\", \"id_categoria\": null}', NULL, NULL),
(383, '2025-11-21 18:49:52', 'presupuestos', 'Eliminacion', '{\"id_presupuesto\": 61, \"monto_limite\": 15000000.00, \"nombre\": \"Seth Rollins\", \"fecha\": \"2025-11-21\"}', NULL, NULL, NULL),
(384, '2025-11-21 18:55:46', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 1, \"monto_limite\": 99999999.99, \"nombre\": \"Seth Rollins\", \"fecha\": \"2025-11-21\", \"id_categoria\": null}', NULL, NULL),
(385, '2025-11-21 18:56:16', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 2, \"monto_limite\": 1234.00, \"nombre\": \"Seth Rollins\", \"fecha\": \"2025-11-21\", \"id_categoria\": 245}', NULL, NULL),
(386, '2025-11-21 18:56:39', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 3, \"monto_limite\": 123545.00, \"nombre\": \"Super Administrador\", \"fecha\": \"2025-11-21\", \"id_categoria\": 219}', NULL, NULL),
(387, '2025-11-21 18:57:28', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 1, \"proveedor\": \"pkpkpk\", \"monto\": 888.00, \"fecha\": \"2025-11-21\", \"id_categoria\": 219}', 1, NULL),
(388, '2025-11-23 08:18:19', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 2, \"alumno\": \"jose miguel perez hernadez\", \"monto\": 7000.00, \"fecha\": \"2025-11-23\", \"id_categoria\": 243}', NULL, 2),
(389, '2025-11-23 08:18:21', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 3, \"alumno\": \"jose miguel perez hernadez\", \"monto\": 7000.00, \"fecha\": \"2025-11-23\", \"id_categoria\": 243}', NULL, 3),
(390, '2025-11-23 08:18:21', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 4, \"alumno\": \"jose miguel perez hernadez\", \"monto\": 7000.00, \"fecha\": \"2025-11-23\", \"id_categoria\": 243}', NULL, 4),
(391, '2025-11-23 08:20:24', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 6, \"alumno\": \"andres jorge chueco\", \"monto\": 700.00, \"fecha\": \"2025-11-23\", \"id_categoria\": 235}', NULL, 6),
(392, '2025-11-23 08:26:37', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 2, \"proveedor\": \"pkpkpk\", \"monto\": 700.00, \"fecha\": \"2025-11-23\", \"id_categoria\": 219}', NULL, 2),
(393, '2025-11-24 06:56:33', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 4, \"monto_limite\": 2000.00, \"nombre\": \"RPTech\", \"fecha\": \"2025-11-24\", \"id_categoria\": 243}', NULL, NULL),
(394, '2025-11-24 06:57:08', 'presupuestos', 'Insercion', NULL, '{\"id_presupuesto\": 5, \"monto_limite\": 1500000.00, \"nombre\": \"Jitomate\", \"fecha\": \"2025-10-24\", \"id_categoria\": null}', NULL, NULL),
(395, '2025-11-24 07:02:51', 'egresos', 'Insercion', NULL, '{\"folio_egreso\": 3, \"proveedor\": \"pkpkpk\", \"monto\": 800.00, \"fecha\": \"2025-11-24\", \"id_categoria\": 243}', NULL, 3),
(396, '2025-11-24 07:32:54', 'ingresos', 'Insercion', NULL, '{\"folio_ingreso\": 7, \"alumno\": \"andres jorge chueco\", \"monto\": 200.00, \"fecha\": \"2025-11-24\", \"id_categoria\": 239}', NULL, 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('Ingreso','Egreso') NOT NULL,
  `concepto` enum('Registro Diario','Titulaciones','Inscripciones y Reinscripciones') DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `no_borrable` tinyint(1) DEFAULT 0,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`, `tipo`, `concepto`, `descripcion`, `no_borrable`, `id_user`) VALUES
(204, 'IUM COMISIONES', 'Egreso', NULL, 'Comisiones institucionales', 1, 2),
(205, 'IUM RENTAS', 'Egreso', NULL, 'Pagos de renta de instalaciones', 1, 2),
(206, 'IUM HONORARIOS LICENCIATURA', 'Egreso', NULL, 'Honorarios de docentes de licenciatura', 1, 2),
(207, 'IUM HONORARIOS MAESTRIA', 'Egreso', NULL, 'Honorarios de docentes de maestr├¡a', 1, 2),
(208, 'IUM HONORARIOS DOCTORADO', 'Egreso', NULL, 'Honorarios de docentes de doctorado', 1, 2),
(209, 'IUM IMSS', 'Egreso', NULL, 'Pagos de seguro social', 1, 2),
(210, 'IUM SUELDOS Y SALARIOS', 'Egreso', NULL, 'Sueldos y salarios del personal', 1, 2),
(211, 'IUM PUBLICIDAD', 'Egreso', NULL, 'Gastos de publicidad y marketing', 1, 2),
(212, 'IUM SERVICIOS', 'Egreso', NULL, 'Servicios generales (luz, agua, internet, etc.)', 1, 2),
(213, 'EDUCACION CONTINUA', 'Egreso', NULL, 'Gastos de educaci├│n continua', 1, 2),
(214, 'IUM MANTENIMIENTO', 'Egreso', NULL, 'Mantenimiento de instalaciones y equipo', 1, 2),
(215, 'IUM CAFETERIA Y LIMPIEZA', 'Egreso', NULL, 'Servicios de cafeter├¡a y limpieza', 1, 2),
(216, 'IUM SECRETARIA DE EDUCACION', 'Egreso', NULL, 'Pagos a Secretar├¡a de Educaci├│n', 1, 2),
(217, 'IUM IMPUESTOS', 'Egreso', NULL, 'Pagos de impuestos', 1, 2),
(218, 'IUM CREDITOS', 'Egreso', NULL, 'Pagos de cr├®ditos', 1, 2),
(219, 'IUM OTROS', 'Egreso', NULL, 'Otros gastos no clasificados', 1, 2),
(220, 'IUM PAPELERIA', 'Egreso', NULL, 'Gastos de papeler├¡a y ├║tiles', 1, 2),
(221, 'IUM GASOLINA', 'Egreso', NULL, 'Gastos de combustible', 1, 2),
(222, 'IUM FRANQUICIA', 'Egreso', NULL, 'Pagos de franquicia', 1, 2),
(223, 'IUM TRAMITES', 'Egreso', NULL, 'Gastos de tr├ímites administrativos', 1, 2),
(224, 'IUM REEMBOLSOS', 'Egreso', NULL, 'Reembolsos a personal o alumnos', 1, 2),
(225, 'IUM ISN', 'Egreso', NULL, 'Impuesto Sobre N├│mina', 1, 2),
(226, 'IUM PAQUETERIA Y MENSAJERIA', 'Egreso', NULL, 'Servicios de paqueter├¡a y mensajer├¡a', 1, 2),
(227, 'REINSCRIPCIONES', 'Egreso', NULL, 'Gastos relacionados con reinscripciones', 1, 2),
(228, 'IUM COMPENSACION', 'Egreso', NULL, 'Compensaciones al personal', 1, 2),
(229, 'IUM EXTRAORDINARIOS', 'Egreso', NULL, 'Gastos extraordinarios', 1, 2),
(230, 'TITULACIONES', 'Egreso', NULL, 'Gastos relacionados con titulaciones', 1, 2),
(231, 'DIPLOMADOS', 'Egreso', NULL, 'Gastos de diplomados', 1, 2),
(232, 'IUM HONORARIOS SOLIDARIOS', 'Egreso', NULL, 'Honorarios solidarios', 1, 2),
(233, 'IUM EVENTOS', 'Egreso', NULL, 'Gastos de eventos institucionales', 1, 2),
(235, 'TITULACION', 'Ingreso', 'Titulaciones', 'Ingresos por procesos de titulaci├│n', 1, 2),
(236, 'INSCRIPCION', 'Ingreso', 'Inscripciones y Reinscripciones', 'Ingresos por inscripciones', 1, 2),
(237, 'REINSCRIPCION', 'Ingreso', 'Inscripciones y Reinscripciones', 'Ingresos por reinscripciones', 1, 2),
(238, 'COLEGIATURA', 'Ingreso', 'Registro Diario', 'Ingresos por colegiaturas', 1, 2),
(239, 'CONSTANCIA SIMPLE', 'Ingreso', 'Registro Diario', 'Ingresos por constancias simples', 1, 2),
(240, 'CONSTANCIA CON CALIFICACIONES', 'Ingreso', 'Registro Diario', 'Ingresos por constancias con calificaciones', 1, 2),
(241, 'HISTORIALES', 'Ingreso', 'Registro Diario', 'Ingresos por historiales acad├®micos', 1, 2),
(242, 'CERTIFICADOS', 'Ingreso', 'Registro Diario', 'Ingresos por certificados', 1, 2),
(243, 'EQUIVALENCIAS', 'Ingreso', 'Registro Diario', 'Ingresos por equivalencias', 1, 2),
(244, 'CREDENCIALES', 'Ingreso', 'Registro Diario', 'Ingresos por credenciales', 1, 2),
(245, 'OTROS', 'Ingreso', 'Registro Diario', 'Otros ingresos no clasificados', 1, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `egresos`
--

CREATE TABLE `egresos` (
  `folio_egreso` int(11) NOT NULL,
  `proveedor` varchar(100) NOT NULL,
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
-- Volcado de datos para la tabla `egresos`
--

INSERT INTO `egresos` (`folio_egreso`, `proveedor`, `descripcion`, `monto`, `fecha`, `destinatario`, `forma_pago`, `documento_de_amparo`, `id_user`, `id_presupuesto`, `id_categoria`) VALUES
(1, 'pkpkpk', 'lll', 888.00, '2025-11-21', 'jninion', 'Cheque', 'lomlo', 2, 3, 219),
(2, 'pkpkpk', 'ooo', 700.00, '2025-11-23', 'jninion', 'Transferencia', 'lomlo', 2, 3, 219),
(3, 'pkpkpk', 'p', 800.00, '2025-11-24', 'k, ,', 'Tarjeta C.', 'lomlo', 2, 4, 243);

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
  `metodo_de_pago` enum('Efectivo','Transferencia','Depósito','Tarjeta Débito','Tarjeta Crédito','Mixto') NOT NULL DEFAULT 'Efectivo',
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
-- Volcado de datos para la tabla `ingresos`
--

INSERT INTO `ingresos` (`folio_ingreso`, `fecha`, `alumno`, `matricula`, `nivel`, `monto`, `metodo_de_pago`, `mes_correspondiente`, `anio`, `observaciones`, `dia_pago`, `modalidad`, `grado`, `programa`, `grupo`, `id_categoria`) VALUES
(1, '2025-11-05', 'angel', '12345', 'Licenciatura', 1234567.00, 'Efectivo', 'octubre', 2025, '0', 12, '', 5, 'derecho', '101a', 1),
(2, '2025-11-23', 'jose miguel perez hernadez', 'chemamike123', 'Licenciatura', 7000.00, 'Mixto', 'enero', 2025, 'j', NULL, 'Cuatrimestral', 4, 'lic en drerecho', '1', 243),
(3, '2025-11-23', 'jose miguel perez hernadez', 'chemamike123', 'Licenciatura', 7000.00, 'Mixto', 'enero', 2025, 'j', NULL, 'Cuatrimestral', 4, 'lic en drerecho', '1', 243),
(4, '2025-11-23', 'jose miguel perez hernadez', 'chemamike123', 'Licenciatura', 7000.00, 'Mixto', 'enero', 2025, 'j', NULL, 'Cuatrimestral', 4, 'lic en drerecho', '1', 243),
(5, '2025-11-07', 'jose miguel perez hernadez', 'chemamike123', 'Doctorado', 5000.00, 'Depósito', 'noviembre', 2028, 'muchas', 4, 'Cuatrimestral', 4, 'lic en izquierdo', '4', 1),
(6, '2025-11-23', 'andres jorge chueco', 'chueco22', 'Maestría', 700.00, 'Mixto', 'noviembre', 2025, 'si', NULL, 'Cuatrimestral', 1, 'lic en izquierdo', NULL, 235),
(7, '2025-11-24', 'andres jorge chueco', 'ppp', 'Maestría', 200.00, 'Efectivo', 'noviembre', 2025, 'l', NULL, 'Cuatrimestral', 5, 'lic en izquierdo', '1', 239),
(23, '2025-11-13', 'Externo - Pablo Dias', 'DIP-001', 'Licenciatura', 7500.00, 'Transferencia', 'Noviembre', 2025, 'Pago Diplomado Finanzas', 13, NULL, NULL, 'Diplomado', 'DIP', 25);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_parciales`
--

CREATE TABLE `pagos_parciales` (
  `id_pago_parcial` int(11) NOT NULL,
  `folio_ingreso` int(11) NOT NULL,
  `metodo_pago` enum('Efectivo','Transferencia','Depósito','Tarjeta Débito','Tarjeta Crédito') NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `orden` tinyint(2) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos_parciales`
--

INSERT INTO `pagos_parciales` (`id_pago_parcial`, `folio_ingreso`, `metodo_pago`, `monto`, `orden`, `fecha_registro`) VALUES
(1, 2, 'Transferencia', 3500.00, 1, '2025-11-23 08:18:19'),
(2, 2, 'Transferencia', 3500.00, 2, '2025-11-23 08:18:19'),
(3, 3, 'Transferencia', 3500.00, 1, '2025-11-23 08:18:21'),
(4, 3, 'Transferencia', 3500.00, 2, '2025-11-23 08:18:21'),
(5, 4, 'Transferencia', 3500.00, 1, '2025-11-23 08:18:21'),
(6, 4, 'Transferencia', 3500.00, 2, '2025-11-23 08:18:21'),
(7, 6, 'Efectivo', 350.00, 1, '2025-11-23 08:20:24'),
(8, 6, 'Transferencia', 350.00, 2, '2025-11-23 08:20:24'),
(9, 7, 'Efectivo', 200.00, 1, '2025-11-24 07:32:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuestos`
--

CREATE TABLE `presupuestos` (
  `id_presupuesto` int(11) NOT NULL,
  `monto_limite` decimal(10,2) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `fecha` date NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `parent_presupuesto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuestos`
--

INSERT INTO `presupuestos` (`id_presupuesto`, `monto_limite`, `nombre`, `fecha`, `id_categoria`, `id_user`, `parent_presupuesto`) VALUES
(1, 99999999.99, 'Seth Rollins', '2025-11-21', NULL, 2, NULL),
(2, 1234.00, 'Seth Rollins', '2025-11-21', 245, 2, 1),
(3, 123545.00, 'Super Administrador', '2025-11-21', 219, 2, 1),
(4, 2000.00, 'RPTech', '2025-11-24', 243, 2, 1),
(5, 1500000.00, 'Jitomate', '2025-10-24', NULL, 2, NULL),
(26, 250000.00, NULL, '2025-11-01', 16, 16, NULL),
(27, 75000.00, NULL, '2025-11-01', 17, 16, NULL),
(28, 40000.00, NULL, '2025-11-01', 18, 16, NULL),
(29, 12000.00, NULL, '2025-11-01', 19, 16, NULL),
(30, 20000.00, NULL, '2025-11-01', 20, 16, NULL),
(34, 250000.00, NULL, '2025-09-01', 16, 16, NULL),
(35, 75000.00, NULL, '2025-09-01', 17, 16, NULL),
(36, 40000.00, NULL, '2025-09-01', 18, 16, NULL),
(37, 10000.00, NULL, '2025-09-01', 19, 16, NULL),
(38, 20000.00, NULL, '2025-09-01', 20, 16, NULL),
(40, 250000.00, NULL, '2025-10-01', 16, 16, NULL),
(41, 75000.00, NULL, '2025-10-01', 17, 16, NULL),
(42, 40000.00, NULL, '2025-10-01', 18, 16, NULL),
(43, 15000.00, NULL, '2025-10-01', 19, 16, NULL),
(44, 25000.00, NULL, '2025-10-01', 20, 16, NULL),
(51, 250000.00, NULL, '2025-07-01', 16, 16, NULL),
(52, 50000.00, NULL, '2025-07-01', 17, 16, NULL),
(53, 30000.00, NULL, '2025-07-01', 18, 16, NULL),
(54, 5000.00, NULL, '2025-07-01', 19, 16, NULL),
(56, 250000.00, NULL, '2025-08-01', 16, 16, NULL),
(57, 75000.00, NULL, '2025-08-01', 17, 16, NULL),
(58, 40000.00, NULL, '2025-08-01', 18, 16, NULL),
(59, 20000.00, NULL, '2025-08-01', 19, 16, NULL),
(60, 50000.00, NULL, '2025-08-01', 20, 16, NULL);

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
(2, 'Super Administrador', 'su_admin', '$2y$10$mmjNR96vpa65C4d6wlswx.ME2vyQWXtgehKPCYh/FOoSlLeH0wQyK', 'SU'),
(7, 'bryan', 'bryan_su', '$2y$10$iBxrkSHZb2iYa/ttp5N18uMoXlDOl3us0Xc/fmW2pZ4WZzC44u8fq', 'SU'),
(16, 'Usuario Administración', 'u_administracion', '$2y$10$mmjNR96vpa65C4d6wlswx.ME2vyQWXtgehKPCYh/FOoSlLeH0wQyK', 'ADM'),
(17, 'Usuario Rectoría', 'u_rectoria', '$2y$10$mmjNR96vpa65C4d6wlswx.ME2vyQWXtgehKPCYh/FOoSlLeH0wQyK', 'REC'),
(18, 'Usuario Cobranzas', 'u_cobranzas', '$2y$10$mmjNR96vpa65C4d6wlswx.ME2vyQWXtgehKPCYh/FOoSlLeH0wQyK', 'COB'),
(19, 'Nuevo Super Usuario', 'u_superusuario', '$2y$10$mmjNR96vpa65C4d6wlswx.ME2vyQWXtgehKPCYh/FOoSlLeH0wQyK', 'SU');

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
(7, NULL),
(24, NULL),
(25, NULL),
(279, NULL),
(280, NULL),
(281, NULL),
(282, NULL),
(283, NULL),
(284, NULL),
(285, NULL),
(286, NULL),
(287, NULL),
(288, NULL),
(289, NULL),
(290, NULL),
(291, NULL),
(292, NULL),
(293, NULL),
(294, NULL),
(295, NULL),
(296, NULL),
(297, NULL),
(298, NULL),
(299, NULL),
(300, NULL),
(301, NULL),
(302, NULL),
(303, NULL),
(304, NULL),
(305, NULL),
(306, NULL),
(307, NULL),
(308, NULL),
(309, NULL),
(310, NULL),
(311, NULL),
(312, NULL),
(313, NULL),
(314, NULL),
(315, NULL),
(316, NULL),
(317, NULL),
(318, NULL),
(319, NULL),
(383, NULL),
(1, 1),
(63, 1),
(2, 2),
(9, 2),
(10, 2),
(11, 2),
(12, 2),
(13, 2),
(14, 2),
(15, 2),
(16, 2),
(18, 2),
(20, 2),
(21, 2),
(22, 2),
(23, 2),
(26, 2),
(27, 2),
(28, 2),
(29, 2),
(30, 2),
(31, 2),
(32, 2),
(33, 2),
(34, 2),
(35, 2),
(36, 2),
(37, 2),
(38, 2),
(39, 2),
(40, 2),
(41, 2),
(42, 2),
(43, 2),
(44, 2),
(45, 2),
(47, 2),
(51, 2),
(56, 2),
(57, 2),
(59, 2),
(60, 2),
(61, 2),
(64, 2),
(65, 2),
(66, 2),
(67, 2),
(68, 2),
(69, 2),
(70, 2),
(71, 2),
(72, 2),
(73, 2),
(74, 2),
(75, 2),
(76, 2),
(117, 2),
(118, 2),
(119, 2),
(120, 2),
(158, 2),
(166, 2),
(167, 2),
(169, 2),
(185, 2),
(186, 2),
(187, 2),
(188, 2),
(189, 2),
(190, 2),
(191, 2),
(192, 2),
(255, 2),
(256, 2),
(257, 2),
(258, 2),
(259, 2),
(260, 2),
(320, 2),
(321, 2),
(322, 2),
(323, 2),
(324, 2),
(325, 2),
(326, 2),
(327, 2),
(328, 2),
(329, 2),
(330, 2),
(331, 2),
(332, 2),
(333, 2),
(334, 2),
(335, 2),
(336, 2),
(337, 2),
(338, 2),
(339, 2),
(340, 2),
(341, 2),
(342, 2),
(343, 2),
(344, 2),
(345, 2),
(346, 2),
(347, 2),
(348, 2),
(349, 2),
(350, 2),
(351, 2),
(352, 2),
(353, 2),
(354, 2),
(355, 2),
(356, 2),
(357, 2),
(358, 2),
(359, 2),
(360, 2),
(361, 2),
(362, 2),
(363, 2),
(364, 2),
(365, 2),
(366, 2),
(367, 2),
(368, 2),
(369, 2),
(370, 2),
(372, 2),
(373, 2),
(374, 2),
(375, 2),
(376, 2),
(377, 2),
(378, 2),
(379, 2),
(380, 2),
(381, 2),
(382, 2),
(384, 2),
(385, 2),
(386, 2),
(387, 2),
(388, 2),
(389, 2),
(390, 2),
(391, 2),
(392, 2),
(393, 2),
(394, 2),
(395, 2),
(396, 2),
(6, 3),
(8, 4),
(17, 6),
(19, 7),
(46, 8),
(48, 9),
(49, 10),
(50, 11),
(121, 16),
(125, 16),
(126, 16),
(127, 16),
(128, 16),
(129, 16),
(130, 16),
(131, 16),
(132, 16),
(133, 16),
(134, 16),
(135, 16),
(136, 16),
(149, 16),
(150, 16),
(151, 16),
(153, 16),
(156, 16),
(160, 16),
(161, 16),
(163, 16),
(175, 16),
(176, 16),
(177, 16),
(178, 16),
(179, 16),
(180, 16),
(181, 16),
(182, 16),
(183, 16),
(184, 16),
(193, 16),
(194, 16),
(195, 16),
(196, 16),
(197, 16),
(198, 16),
(199, 16),
(200, 16),
(201, 16),
(207, 16),
(208, 16),
(209, 16),
(210, 16),
(211, 16),
(212, 16),
(213, 16),
(214, 16),
(215, 16),
(226, 16),
(227, 16),
(228, 16),
(229, 16),
(230, 16),
(231, 16),
(232, 16),
(235, 16),
(236, 16),
(237, 16),
(238, 16),
(239, 16),
(240, 16),
(241, 16),
(242, 16),
(122, 17),
(123, 18),
(137, 18),
(138, 18),
(139, 18),
(140, 18),
(141, 18),
(142, 18),
(143, 18),
(144, 18),
(145, 18),
(146, 18),
(147, 18),
(148, 18),
(152, 18),
(154, 18),
(155, 18),
(157, 18),
(159, 18),
(162, 18),
(202, 18),
(203, 18),
(204, 18),
(205, 18),
(206, 18),
(216, 18),
(217, 18),
(218, 18),
(219, 18),
(220, 18),
(233, 18),
(234, 18),
(243, 18),
(244, 18),
(245, 18),
(246, 18),
(247, 18),
(248, 18),
(249, 18),
(250, 18),
(251, 18),
(252, 18),
(253, 18),
(254, 18),
(124, 19);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_ingreso`
--

CREATE TABLE `usuario_ingreso` (
  `id_user` int(11) NOT NULL,
  `folio_ingreso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD KEY `fk_categorias_user` (`id_user`);

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
-- Indices de la tabla `pagos_parciales`
--
ALTER TABLE `pagos_parciales`
  ADD PRIMARY KEY (`id_pago_parcial`),
  ADD KEY `idx_folio_ingreso` (`folio_ingreso`);

--
-- Indices de la tabla `presupuestos`
--
ALTER TABLE `presupuestos`
  ADD PRIMARY KEY (`id_presupuesto`),
  ADD KEY `fk_presupuestos_user` (`id_user`),
  ADD KEY `idx_presupuestos_parent` (`parent_presupuesto`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_user`);

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
-- AUTO_INCREMENT de la tabla `pagos_parciales`
--
ALTER TABLE `pagos_parciales`
  MODIFY `id_pago_parcial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pagos_parciales`
--
ALTER TABLE `pagos_parciales`
  ADD CONSTRAINT `fk_pago_parcial_ingreso_espejo` FOREIGN KEY (`folio_ingreso`) REFERENCES `ingresos` (`folio_ingreso`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

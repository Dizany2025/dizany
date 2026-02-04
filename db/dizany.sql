-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-01-2026 a las 01:42:41
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
-- Base de datos: `dizany`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`) VALUES
(31, 'ACEITE'),
(43, 'GALLETA'),
(44, 'CERVEZA'),
(45, 'BEBIDAS'),
(46, 'SAL');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `ruc` varchar(255) DEFAULT NULL,
  `dni` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `direccion`, `telefono`, `ruc`, `dni`) VALUES
(19, 'DILSER', 'PACAYZAPA', '958196510', NULL, '76363332'),
(20, 'ANALY', 'PACAYZAPA', '935965841', NULL, '71885485'),
(21, 'VICTOR', 'GOICOCHEA CARRANZA', '936584598', NULL, '27419354'),
(22, 'CELINDA', 'MOYOBAMBA', NULL, NULL, '45950469'),
(23, 'ELENA', 'GOZEN', NULL, NULL, '00829710'),
(24, 'SILVIA', NULL, NULL, NULL, '71885486'),
(25, 'JOSE', 'JLJL', '985456222', NULL, '98762315'),
(26, 'DEYVIS', NULL, NULL, NULL, '71609740'),
(27, 'DIANA CORDOBA', 'KKK', NULL, NULL, '71558985'),
(28, 'EVER', NULL, NULL, NULL, '20152025'),
(29, 'FABIAN', NULL, NULL, NULL, '92946268'),
(30, 'IZAN', NULL, NULL, NULL, '96358488'),
(31, 'SILVESTRE', 'DDDD', NULL, NULL, '27266552'),
(32, 'ELICIA', 'KKKKK', NULL, NULL, '85848584'),
(33, 'GERMAN', NULL, NULL, NULL, '45254525'),
(34, 'NEVADA ENTRETENIMIENTOS S.A.C.', 'JR. PARRA DEL RIEGO NRO. 367 DPTO. 603', NULL, '20530811001', NULL),
(35, 'CMAC PIURA S.A.C.', 'JR. AYACUCHO NRO. 353  CENTRO PIURA.', NULL, '20113604248', NULL),
(36, 'PACO RAUL VARGAS ROJAS', 'No disponible', NULL, NULL, '00821525'),
(37, 'GRUPO DELTRON S.A.', 'CAL. RAUL REBAGLIATI NRO. 170 URB. SANTA CATALINA', NULL, '20212331377', NULL),
(38, 'HOMECENTERS PERUANOS S.A.', 'AV. AVIACION NRO. 2405', NULL, '20536557858', NULL),
(39, 'HIPERMERCADOS TOTTUS S.A', 'AV. ANGAMOS ESTE NRO. 1805 INT. P10', NULL, '20508565934', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `nombre_empresa` varchar(100) DEFAULT NULL,
  `ruc` varchar(20) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `moneda` varchar(10) DEFAULT NULL,
  `igv` decimal(5,2) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `tema` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `nombre_empresa`, `ruc`, `logo`, `moneda`, `igv`, `direccion`, `telefono`, `correo`, `tema`) VALUES
(1, 'DIZANY', '10763633328', 'uploads/logos/1769127060_DA.png', 'S/', 0.00, 'AV. MARGINAL - PACAYZAPA', '958196510', 'admin@dizany.com', 'claro');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_lote_ventas`
--

CREATE TABLE `detalle_lote_ventas` (
  `id` int(11) NOT NULL,
  `detalle_venta_id` int(11) NOT NULL,
  `lote_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `precio_lote` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_lote_ventas`
--

INSERT INTO `detalle_lote_ventas` (`id`, `detalle_venta_id`, `lote_id`, `cantidad`, `fecha_vencimiento`, `precio_lote`, `created_at`, `updated_at`) VALUES
(1, 74, 26, 1, '2026-04-29', 15.00, '2026-01-27 03:56:26', '2026-01-27 03:56:26'),
(2, 75, 24, 5, '2026-04-23', 2.50, '2026-01-27 03:56:26', '2026-01-27 03:56:26'),
(3, 76, 23, 10, '2026-08-26', 2.00, '2026-01-27 03:56:26', '2026-01-27 03:56:26'),
(4, 77, 26, 2, '2026-04-29', 15.00, '2026-01-27 03:59:26', '2026-01-27 03:59:26'),
(5, 78, 27, 1, '2026-05-27', 2.50, '2026-01-27 04:03:05', '2026-01-27 04:03:05'),
(6, 79, 26, 12, '2026-04-29', 15.00, '2026-01-27 04:03:05', '2026-01-27 04:03:05'),
(7, 80, 25, 1, '2026-09-30', 21.00, '2026-01-27 04:03:05', '2026-01-27 04:03:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_ventas`
--

CREATE TABLE `detalle_ventas` (
  `id` int(11) NOT NULL,
  `venta_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `presentacion` enum('unidad','paquete','caja') NOT NULL DEFAULT 'unidad',
  `cantidad` int(11) NOT NULL,
  `unidades_afectadas` int(11) NOT NULL DEFAULT 1,
  `precio_presentacion` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ganancia` decimal(10,2) NOT NULL DEFAULT 0.00,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_ventas`
--

INSERT INTO `detalle_ventas` (`id`, `venta_id`, `producto_id`, `presentacion`, `cantidad`, `unidades_afectadas`, `precio_presentacion`, `precio_unitario`, `subtotal`, `ganancia`, `activo`) VALUES
(74, 42, 37, 'unidad', 1, 1, 15.00, 15.00, 15.00, 3.00, 1),
(75, 42, 39, 'unidad', 5, 5, 2.50, 0.50, 12.50, 7.50, 1),
(76, 42, 39, 'unidad', 10, 10, 2.00, 0.20, 20.00, 10.00, 1),
(77, 43, 37, 'unidad', 2, 2, 15.00, 7.50, 30.00, 6.00, 1),
(78, 44, 40, 'unidad', 1, 1, 2.50, 2.50, 2.50, 1.50, 1),
(79, 44, 37, 'unidad', 12, 12, 15.00, 1.25, 180.00, 36.00, 1),
(80, 44, 37, 'unidad', 1, 1, 21.00, 21.00, 21.00, 6.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id` int(11) NOT NULL,
  `venta_id` int(11) NOT NULL,
  `numero_factura` varchar(20) NOT NULL,
  `fecha_emision` date NOT NULL,
  `ruc_emisor` varchar(11) NOT NULL,
  `razon_social_emisor` varchar(100) NOT NULL,
  `direccion_emisor` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `metodo_pago` varchar(50) DEFAULT 'Efectivo',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estado` enum('activo','anulado') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gastos`
--

INSERT INTO `gastos` (`id`, `usuario_id`, `descripcion`, `monto`, `fecha`, `metodo_pago`, `created_at`, `updated_at`, `estado`) VALUES
(17, 4, 'pago de luz', 56.00, '2026-01-09 20:41:00', 'efectivo', '2026-01-09 20:41:12', '2026-01-09 20:44:43', 'anulado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lotes`
--

CREATE TABLE `lotes` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `codigo_lote` varchar(100) DEFAULT NULL,
  `fecha_ingreso` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `stock_inicial` int(11) NOT NULL,
  `stock_actual` int(11) NOT NULL,
  `precio_compra` decimal(10,2) NOT NULL,
  `precio_unidad` decimal(10,2) NOT NULL,
  `precio_paquete` decimal(10,2) DEFAULT NULL,
  `precio_caja` decimal(10,2) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `lotes`
--

INSERT INTO `lotes` (`id`, `producto_id`, `proveedor_id`, `codigo_lote`, `fecha_ingreso`, `fecha_vencimiento`, `stock_inicial`, `stock_actual`, `precio_compra`, `precio_unidad`, `precio_paquete`, `precio_caja`, `activo`, `created_at`, `updated_at`) VALUES
(23, 39, NULL, NULL, '2026-01-23', '2026-08-26', 10, 0, 1.00, 2.00, 12.00, 18.00, 1, '2026-01-24 03:49:38', '2026-01-27 03:56:26'),
(24, 39, NULL, NULL, '2026-01-23', '2026-04-23', 5, 0, 1.00, 2.50, 12.00, 20.00, 1, '2026-01-24 03:49:59', '2026-01-27 03:56:26'),
(25, 37, NULL, NULL, '2026-01-23', '2026-09-30', 20, 19, 15.00, 21.00, NULL, NULL, 1, '2026-01-24 04:15:14', '2026-01-27 04:03:05'),
(26, 37, NULL, NULL, '2026-01-23', '2026-04-29', 15, 0, 12.00, 15.00, NULL, NULL, 1, '2026-01-24 04:15:29', '2026-01-27 04:03:05'),
(27, 40, NULL, NULL, '2026-01-23', '2026-05-27', 25, 24, 1.00, 2.50, 50.00, NULL, 1, '2026-01-24 04:17:05', '2026-01-27 04:03:05'),
(28, 40, NULL, NULL, '2026-01-23', '2026-08-26', 25, 25, 1.00, 2.00, 48.00, NULL, 1, '2026-01-24 04:17:34', '2026-01-24 04:17:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcas`
--

CREATE TABLE `marcas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marcas`
--

INSERT INTO `marcas` (`id`, `nombre`, `descripcion`) VALUES
(11, 'BORGOÑA', NULL),
(12, 'SAN MATEO', NULL),
(13, 'PILSEN', NULL),
(14, 'VOLT', NULL),
(15, 'COCA COLA', NULL),
(16, 'GOLDEN', NULL),
(17, 'PRIMOR', NULL),
(18, 'COCHADO', NULL),
(19, 'GLORIA', NULL),
(20, 'MIKASA', NULL),
(21, 'VACA', NULL),
(22, 'SAN LUIS', NULL),
(23, 'SODA', NULL),
(24, 'CRISTAL', NULL),
(25, 'DELMER', NULL),
(26, 'TONDERO', NULL),
(27, 'CAPRI', NULL),
(28, 'SODA V', NULL),
(29, 'VOLTS', NULL),
(30, 'MARINA', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(6, '2025_12_09_190818_add_timestamps_to_productos_table', 1),
(7, '2025_12_19_003109_add_indexes_to_movimientos_table', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos`
--

CREATE TABLE `movimientos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `hora` time DEFAULT NULL,
  `tipo` enum('ingreso','egreso') NOT NULL COMMENT 'Ingreso = dinero que entra, Egreso = dinero que sale',
  `subtipo` varchar(50) NOT NULL,
  `concepto` varchar(255) NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `metodo_pago` varchar(20) DEFAULT NULL,
  `estado` enum('pagado','pendiente','anulado') NOT NULL DEFAULT 'pagado',
  `referencia_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'ID relacionado: venta_id, gasto_id, etc',
  `referencia_tipo` varchar(50) DEFAULT NULL COMMENT 'venta, gasto, ajuste, compra',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos`
--

INSERT INTO `movimientos` (`id`, `fecha`, `hora`, `tipo`, `subtipo`, `concepto`, `monto`, `metodo_pago`, `estado`, `referencia_id`, `referencia_tipo`, `created_at`, `updated_at`) VALUES
(131, '2026-01-26', NULL, 'ingreso', 'venta', 'Venta boleta B001-000001', 47.50, 'efectivo', 'pagado', 42, 'venta', '2026-01-27 03:56:29', '2026-01-27 03:56:29'),
(132, '2026-01-26', NULL, 'ingreso', 'venta', 'Venta pendiente factura F001-000001', 30.00, 'fiado', 'pendiente', 43, 'venta', '2026-01-27 03:59:40', '2026-01-27 03:59:40'),
(133, '2026-01-26', NULL, 'ingreso', 'venta', 'Venta factura F001-000002', 203.50, 'yape', 'pagado', 44, 'venta', '2026-01-27 04:03:19', '2026-01-27 04:03:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_venta`
--

CREATE TABLE `pagos_venta` (
  `id` int(11) NOT NULL,
  `venta_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) NOT NULL,
  `fecha_pago` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos_venta`
--

INSERT INTO `pagos_venta` (`id`, `venta_id`, `usuario_id`, `monto`, `metodo_pago`, `fecha_pago`, `created_at`, `updated_at`) VALUES
(102, 42, 4, 47.50, 'efectivo', '2026-01-26 22:56:26', '2026-01-27 03:56:26', '2026-01-27 03:56:26'),
(103, 44, 4, 203.50, 'yape', '2026-01-26 23:03:05', '2026-01-27 04:03:05', '2026-01-27 04:03:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `codigo_barras` varchar(50) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `slug` varchar(150) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `unidades_por_paquete` int(11) DEFAULT NULL,
  `paquetes_por_caja` int(11) DEFAULT NULL,
  `unidades_por_caja` int(11) DEFAULT NULL,
  `ubicacion` varchar(100) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `maneja_vencimiento` tinyint(1) NOT NULL DEFAULT 0,
  `categoria_id` int(11) NOT NULL,
  `marca_id` int(11) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `visible_en_catalogo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `codigo_barras`, `nombre`, `slug`, `descripcion`, `unidades_por_paquete`, `paquetes_por_caja`, `unidades_por_caja`, `ubicacion`, `imagen`, `maneja_vencimiento`, `categoria_id`, `marca_id`, `activo`, `visible_en_catalogo`, `created_at`, `updated_at`) VALUES
(37, '0000000001', 'TONDERO', 'tondero', '20LT', NULL, NULL, NULL, 'P1', 'tondero-1768874193.jpeg', 1, 31, 26, 1, 1, '2026-01-20 01:56:33', '2026-01-22 02:39:14'),
(38, '0000000002', 'SODA', 'soda', 'saladitas', 6, 10, NULL, 'p3', 'soda-1769050739.webp', 1, 43, 28, 1, 1, '2026-01-22 01:06:02', '2026-01-22 02:58:59'),
(39, '0000000003', 'SPORADE', 'sporade', 'ENERGISANTE', 12, NULL, NULL, 'p1', 'sporade-1769045970.webp', 1, 45, 21, 1, 1, '2026-01-22 01:39:30', '2026-01-22 02:38:54'),
(40, '0000000004', 'SAL', 'sal', 'SALADA', 20, NULL, NULL, 'p3', 'sal-1769046562.jpeg', 1, 46, 30, 1, 1, '2026-01-22 01:49:22', '2026-01-22 02:39:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `ruc` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'Administrador'),
(2, 'Empleado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `clave` varchar(255) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `usuario`, `email`, `clave`, `rol_id`, `created_at`, `updated_at`) VALUES
(4, 'Dilser', 'admin', 'dilser95@gmail.com', '$2y$12$zMKx97A4EIhk68ImMCWQXeMQnHCXmL1MiT3dmMoVqm.0JwoyLtq8K', 1, '2025-06-12 23:36:29', '2025-06-12 23:36:29'),
(9, 'any', 'any25', 'analy@gmail.com', '$2y$12$wUYveWN3vGhhOQRw4d342O43K2CFrMWnEgtpJSgtP6c/b45uLUwTS', 2, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `tipo_comprobante` varchar(255) DEFAULT NULL,
  `serie` varchar(10) DEFAULT NULL,
  `correlativo` int(11) DEFAULT NULL,
  `metodo_pago` varchar(50) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` varchar(20) DEFAULT 'Pagada',
  `estado_sunat` varchar(20) DEFAULT 'pendiente',
  `hash` varchar(255) DEFAULT NULL,
  `xml_url` text DEFAULT NULL,
  `pdf_url` text DEFAULT NULL,
  `cdr_url` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `op_gravadas` decimal(10,2) DEFAULT 0.00,
  `igv` decimal(10,2) DEFAULT 0.00,
  `saldo` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `cliente_id`, `usuario_id`, `fecha`, `tipo_comprobante`, `serie`, `correlativo`, `metodo_pago`, `total`, `estado`, `estado_sunat`, `hash`, `xml_url`, `pdf_url`, `cdr_url`, `activo`, `op_gravadas`, `igv`, `saldo`) VALUES
(42, 34, 4, '2026-01-26 22:56:26', 'boleta', 'B001', 1, 'efectivo', 47.50, 'pagado', 'pendiente', NULL, NULL, NULL, NULL, 1, 47.50, 0.00, 0.00),
(43, 22, 4, '2026-01-26 22:59:25', 'factura', 'F001', 1, NULL, 30.00, 'pendiente', 'pendiente', NULL, NULL, NULL, NULL, 1, 30.00, 0.00, 30.00),
(44, 20, 4, '2026-01-26 23:03:04', 'factura', 'F001', 2, 'yape', 203.50, 'pagado', 'pendiente', NULL, NULL, NULL, NULL, 1, 203.50, 0.00, 0.00);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ruc` (`ruc`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalle_lote_ventas`
--
ALTER TABLE `detalle_lote_ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_detalle_venta` (`detalle_venta_id`),
  ADD KEY `idx_lote` (`lote_id`);

--
-- Indices de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_factura` (`numero_factura`),
  ADD KEY `venta_id` (`venta_id`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `lotes`
--
ALTER TABLE `lotes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lotes_fifo` (`producto_id`,`activo`,`stock_actual`,`fecha_ingreso`);

--
-- Indices de la tabla `marcas`
--
ALTER TABLE `marcas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_movimientos_fecha` (`fecha`),
  ADD KEY `idx_movimientos_tipo_estado` (`tipo`,`estado`),
  ADD KEY `idx_movimientos_metodo_pago` (`metodo_pago`);

--
-- Indices de la tabla `pagos_venta`
--
ALTER TABLE `pagos_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pagos_venta_venta` (`venta_id`),
  ADD KEY `idx_pagos_venta_usuario` (`usuario_id`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_barras` (`codigo_barras`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `fk_marca_producto` (`marca_id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `rol_id` (`rol_id`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `detalle_lote_ventas`
--
ALTER TABLE `detalle_lote_ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `lotes`
--
ALTER TABLE `lotes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

--
-- AUTO_INCREMENT de la tabla `pagos_venta`
--
ALTER TABLE `pagos_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_lote_ventas`
--
ALTER TABLE `detalle_lote_ventas`
  ADD CONSTRAINT `fk_dlv_detalle_venta` FOREIGN KEY (`detalle_venta_id`) REFERENCES `detalle_ventas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dlv_lote` FOREIGN KEY (`lote_id`) REFERENCES `lotes` (`id`);

--
-- Filtros para la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD CONSTRAINT `detalle_ventas_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`),
  ADD CONSTRAINT `detalle_ventas_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `facturas_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`);

--
-- Filtros para la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD CONSTRAINT `fk_gastos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `lotes`
--
ALTER TABLE `lotes`
  ADD CONSTRAINT `fk_lotes_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pagos_venta`
--
ALTER TABLE `pagos_venta`
  ADD CONSTRAINT `fk_pagos_venta_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_pagos_venta_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_marca_producto` FOREIGN KEY (`marca_id`) REFERENCES `marcas` (`id`),
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

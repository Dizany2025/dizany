-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-12-2025 a las 23:01:43
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
(23, 'VINO'),
(24, 'BEBIDAS'),
(25, 'CERVEZA'),
(26, 'ENERGISANTES'),
(27, 'GALLETAS'),
(28, 'CIGARRO'),
(29, 'PISCO'),
(30, 'RON'),
(31, 'ACEITE'),
(32, 'COCTEL'),
(33, 'MERMELADA'),
(34, 'GALLETA'),
(35, 'PELOTAS'),
(36, 'PELOTA'),
(37, 'CACHOS'),
(38, 'AGUA');

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
(19, 'DILSER', 'PACAYZAPA', NULL, NULL, '76363332'),
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
(33, 'GERMAN', NULL, NULL, NULL, '45254525');

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
(1, 'DIZANY', '10763633328', 'uploads/logos/1752217103_logo.png', 'S/', 0.00, 'AV. MARGINAL - PACAYZAPA', '958196510', 'admin@dizany.com', 'claro');

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
(214, 165, 32, 'unidad', 1, 1, 8.00, 8.00, 8.00, 2.30, 1),
(215, 166, 31, 'unidad', 1, 1, 25.00, 25.00, 25.00, 10.00, 1),
(216, 167, 31, 'unidad', 1, 1, 25.00, 25.00, 25.00, 10.00, 1),
(217, 167, 32, 'unidad', 1, 1, 8.00, 8.00, 8.00, 2.30, 1),
(218, 168, 32, 'unidad', 1, 1, 8.00, 8.00, 8.00, 2.30, 1),
(219, 168, 33, 'unidad', 1, 1, 2.50, 2.50, 2.50, 1.50, 1),
(220, 169, 31, 'unidad', 1, 1, 25.00, 25.00, 25.00, 10.00, 1),
(221, 169, 34, 'caja', 1, 60, 48.00, 0.80, 48.00, 18.00, 1),
(222, 169, 33, 'paquete', 1, 15, 35.00, 2.33, 35.00, 20.00, 1),
(223, 169, 32, 'unidad', 1, 1, 8.00, 8.00, 8.00, 2.30, 1);

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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gastos`
--

INSERT INTO `gastos` (`id`, `usuario_id`, `descripcion`, `monto`, `fecha`, `metodo_pago`, `created_at`, `updated_at`) VALUES
(1, 4, 'pago de luz', 20.00, '2025-12-05 23:55:12', 'Efectivo', '2025-12-05 23:56:37', '2025-12-05 23:56:37');

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
(23, 'SODA', NULL);

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
  `subtipo` enum('venta','gasto','ajuste','otro') NOT NULL,
  `concepto` varchar(255) NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `metodo_pago` enum('efectivo','yape','plin','transferencia','tarjeta','credito') NOT NULL,
  `estado` enum('pagado','pendiente') NOT NULL DEFAULT 'pagado' COMMENT 'Pendiente = por cobrar o por pagar',
  `referencia_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'ID relacionado: venta_id, gasto_id, etc',
  `referencia_tipo` varchar(50) DEFAULT NULL COMMENT 'venta, gasto, ajuste, compra',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos`
--

INSERT INTO `movimientos` (`id`, `fecha`, `hora`, `tipo`, `subtipo`, `concepto`, `monto`, `metodo_pago`, `estado`, `referencia_id`, `referencia_tipo`, `created_at`, `updated_at`) VALUES
(1, '2025-12-19', NULL, 'ingreso', 'venta', 'Venta Boleta B001-123', 150.00, 'efectivo', 'pagado', 123, 'venta', '2025-12-19 05:33:22', '2025-12-19 05:33:22'),
(2, '2025-12-19', NULL, 'ingreso', 'venta', 'Venta crédito cliente Juan', 300.00, 'credito', 'pendiente', NULL, NULL, '2025-12-19 05:34:14', '2025-12-19 05:34:14'),
(3, '2025-12-19', NULL, 'egreso', 'gasto', 'Pago proveedor', 500.00, 'transferencia', 'pendiente', NULL, NULL, '2025-12-19 05:34:14', '2025-12-19 05:34:14');

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
  `precio_compra` decimal(10,2) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `precio_paquete` decimal(10,2) DEFAULT NULL,
  `unidades_por_paquete` int(11) DEFAULT NULL,
  `paquetes_por_caja` int(11) DEFAULT NULL,
  `tipo_paquete` varchar(50) DEFAULT NULL,
  `precio_caja` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `ubicacion` varchar(100) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
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

INSERT INTO `productos` (`id`, `codigo_barras`, `nombre`, `slug`, `descripcion`, `precio_compra`, `precio_venta`, `precio_paquete`, `unidades_por_paquete`, `paquetes_por_caja`, `tipo_paquete`, `precio_caja`, `stock`, `ubicacion`, `imagen`, `fecha_vencimiento`, `categoria_id`, `marca_id`, `activo`, `visible_en_catalogo`, `created_at`, `updated_at`) VALUES
(31, '0000000001', 'PELOTA MIKASA', 'pelota-mikasa', 'MIKASA DE CUERO', 15.00, 25.00, NULL, NULL, NULL, NULL, NULL, 45, 'p1', 'pelota-mikasa-1765915389.jpeg', NULL, 36, 20, 1, 1, '2025-12-16 20:03:09', '2025-12-19 03:44:42'),
(32, '0000000002', 'PILSEN', 'pilsen', 'PILSEN - CONT. 630 ml', 5.70, 8.00, NULL, 12, NULL, NULL, 96.00, 20, 'p2', 'pilsen-1765918555.jpg', '2027-03-18', 25, 13, 1, 1, '2025-12-16 20:55:55', '2025-12-19 03:44:42'),
(33, '0000000003', 'SAN LUIS', 'san-luis', 'AGUA CONT. 625ml', 1.00, 2.50, 35.00, 15, NULL, NULL, NULL, 58, 'p3', 'san-luis-1765923081.jpg', '2026-02-15', 38, 22, 1, 1, '2025-12-16 20:59:24', '2025-12-19 03:44:42'),
(34, '0000000004', 'SODA', 'soda', 'CROCANTES Y DORADITAS', 0.50, 1.00, 5.00, 6, 10, NULL, 48.00, 120, 'p1', 'soda-1765920842.webp', '2026-05-05', 34, 23, 1, 1, '2025-12-16 21:04:56', '2025-12-19 03:44:42');

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
  `igv` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `cliente_id`, `usuario_id`, `fecha`, `tipo_comprobante`, `serie`, `correlativo`, `metodo_pago`, `total`, `estado`, `estado_sunat`, `hash`, `xml_url`, `pdf_url`, `cdr_url`, `activo`, `op_gravadas`, `igv`) VALUES
(165, 19, 4, '2025-12-17 22:44:07', 'boleta', 'B001', 1, 'efectivo', 8.00, 'pagado', 'pendiente', NULL, NULL, NULL, NULL, 1, 8.00, 0.00),
(166, 19, 4, '2025-12-17 22:44:53', 'boleta', 'B001', 2, 'efectivo', 25.00, 'pagado', 'pendiente', NULL, NULL, NULL, NULL, 1, 25.00, 0.00),
(167, 19, 4, '2025-12-17 22:45:43', 'boleta', 'B001', 3, 'efectivo', 33.00, 'pagado', 'pendiente', NULL, NULL, NULL, NULL, 1, 33.00, 0.00),
(168, 33, 4, '2025-12-18 22:00:45', 'boleta', 'B001', 4, 'efectivo', 10.50, 'pagado', 'pendiente', NULL, NULL, NULL, NULL, 1, 10.50, 0.00),
(169, 20, 4, '2025-12-18 22:44:41', 'boleta', 'B001', 5, 'plin', 116.00, 'pagado', 'pendiente', NULL, NULL, NULL, NULL, 1, 116.00, 0.00);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=224;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=170;

--
-- Restricciones para tablas volcadas
--

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

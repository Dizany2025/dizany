-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-12-2025 a las 22:58:04
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
(33, 'MERMELADA');

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
(1, 'NEVADA ENTRETENIMIENTOS S.A.C.', 'JR. PARRA DEL RIEGO NRO. 367 DPTO. 603', '942356356', '20530811001', NULL),
(2, 'ANALY CARRANZA ROMERO', 'No disponible', NULL, NULL, '71885485'),
(3, 'DARWIN ALEXIS VASQUEZ DELGADO', 'No disponible', NULL, NULL, '75511073'),
(5, 'RECREATIVOS FARGO SAC', 'JR. GMO.CACERES NRO. 284  CERCADO', NULL, '20526938535', NULL),
(6, 'GRUPO DELTRON S.A.', 'CAL. RAUL REBAGLIATI NRO. 170 URB. SANTA CATALINA', NULL, '20212331377', NULL),
(7, 'CABANILLAS RODRIGUEZ DILSER', 'Sin dirección', NULL, '10763633328', NULL),
(8, 'DILSER CABANILLAS RODRIGUEZ', 'No disponible', NULL, NULL, '76363332'),
(9, 'FABIAN', 'PACAYZAPA', '984456312', NULL, '93946268'),
(10, 'ERICK JHAIR LINARES SABOYA', 'No disponible', NULL, NULL, '71885482'),
(11, 'CMAC PIURA S.A.C.', 'JR. AYACUCHO NRO. 353  CENTRO PIURA.', NULL, '20113604248', NULL),
(12, 'ELENA RODRIGUEZ SANCHEZ', 'No disponible', NULL, NULL, '00829710'),
(13, 'CELINDA VICTORIA MENDOZA RENGIFO', 'No disponible', NULL, NULL, '00829716'),
(14, 'SANDY ARACELI HURTADO HUERTAS', 'No disponible', NULL, NULL, '71885486');

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
(1, 'DIZANY', '10763633328', 'uploads/logos/1752217103_logo.png', 'S/', 18.00, 'AV. MARGINAL - PACAYZAPA', '958196510', 'admin@dizany.com', 'claro');

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
(132, 93, 22, 'unidad', 8, 1, 0.00, 2.50, 20.00, 12.00, 1),
(133, 94, 24, 'unidad', 3, 1, 0.00, 96.76, 290.28, 269.28, 1),
(134, 95, 26, 'unidad', 7, 1, 0.00, 7.50, 52.50, 17.50, 1);

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
(19, 'GLORIA', NULL);

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
(6, '2025_12_09_190818_add_timestamps_to_productos_table', 1);

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
(16, '7750243000012', 'Coca Cola 500ml', NULL, 'Gaseosa sabor cola 500ml botella', 1.50, 2.50, 14.00, 15, 1, 'BOLSA', 55.00, 139, 'Bebidas / Estante 2', 'coca-cola-500ml-1764653377.webp', '2025-12-31', 24, 15, 1, 1, NULL, NULL),
(17, '7750243000203', 'PILSEN', NULL, '355 ML', 3.00, 5.00, 28.00, 5, 1, 'BOLSA', NULL, 5, 'Estante 5', 'arroz-costeno-5kg-1763439600.webp', '2026-01-01', 25, 13, 1, 1, NULL, NULL),
(18, '7750243000301', 'Detergente Ace 1kg', NULL, 'Detergente en polvo para ropa 1kg', 8.00, 9.00, 50.00, 15, 1, 'BOLSA', 200.00, 87, 'Limpieza / Estante 3', 'detergente-ace-1kg-1764733094.jpeg', '2025-12-30', 23, 12, 1, 1, NULL, NULL),
(19, '760123456789', 'INCA KOLA', 'inca-kola', '355ML', 2.00, 3.00, 43.00, 15, 1, 'BOLSA', 45.00, 72, 'ESTANTE 5', 'inca-kola-1764286060.webp', NULL, 24, 12, 1, 1, NULL, NULL),
(20, '44553523232332', 'VINO', 'vino', 'SEMI SECO', 10.00, 15.00, NULL, 15, NULL, 'CAJA', 180.00, 70, 'p3', 'vino-1764292334.jpeg', NULL, 23, 11, 1, 1, NULL, NULL),
(21, '444445544', 'VISIO', 'visio', 'CHOCOLATE DE ALMENDRA', 1.00, 1.50, 16.00, 15, 2, 'CAJITA', 50.00, 293, 'p1', 'visio-1764546248.jpg', '2025-12-10', 24, 12, 1, 1, NULL, '2025-12-10 00:54:51'),
(22, '201122222333', 'VOLT', 'volt', '255ML', 1.00, 2.50, 28.00, 15, 1, 'BOLSA', NULL, 6, 'pasillo 2', 'volt-1764546233.webp', '2026-01-30', 26, 14, 1, 1, NULL, '2025-12-10 01:01:26'),
(23, '5232323556655', 'GOLDEN BEACH', 'golden-beach', 'Golden Beach Rojo', 30.00, 0.25, 4.00, 20, 1, 'CAJA', 40.00, 20, 'p3', 'golden-beach-1764714364.jpg', NULL, 28, 16, 1, 1, NULL, NULL),
(24, '6565666556', 'PRIMOR', 'primor', 'Aceite Vegetal, Premium Botella 900ml', 7.00, 9.50, NULL, 12, 1, 'CAJA', 82.00, 24, 'P2', 'primor-1765254564.webp', '2026-02-12', 31, 17, 1, 1, NULL, '2025-12-10 01:01:56'),
(25, '5451326596987', 'COCTEL DE CAFE', 'coctel-de-cafe', 'SABOR CAFE 255ML', 20.00, 25.00, NULL, 12, 1, 'CAJA', 250.00, 24, 'P1', 'coctel-de-cafe-1765326043.jpeg', '2026-03-15', 32, 18, 1, 1, NULL, '2025-12-10 00:27:44'),
(26, '55666358596663', 'MERMELADA DE FRESA', 'mermelada-de-fresa', 'MERMELADA SABOR FRESA', 5.00, 7.50, NULL, 10, 1, 'CAJA', 50.00, 12, 'P1', 'mermelada-de-fresa-1765326776.webp', '2026-02-10', 33, 19, 1, 1, '2025-12-10 00:32:56', '2025-12-10 01:02:28');

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
(4, 'Administrador', 'admin', 'dilser95@gmail.com', '$2y$12$zMKx97A4EIhk68ImMCWQXeMQnHCXmL1MiT3dmMoVqm.0JwoyLtq8K', 1, '2025-06-12 23:36:29', '2025-06-12 23:36:29'),
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
(93, 1, 4, '2025-12-09 20:01:25', 'boleta', 'B001', 1, 'tarjeta', 23.60, 'pagado', 'pendiente', NULL, NULL, 'http://localhost:8000/comprobantes/B001-000001.pdf', NULL, 1, 0.00, 0.00),
(94, 8, 4, '2025-12-09 20:01:55', 'boleta', 'B001', 2, 'plin', 342.53, 'pagado', 'pendiente', NULL, NULL, 'http://localhost:8000/comprobantes/B001-000002.pdf', NULL, 1, 0.00, 0.00),
(95, 1, 4, '2025-12-09 20:02:28', 'boleta', 'B001', 3, 'plin', 61.95, 'pagado', 'pendiente', NULL, NULL, 'http://localhost:8000/comprobantes/B001-000003.pdf', NULL, 1, 0.00, 0.00);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

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

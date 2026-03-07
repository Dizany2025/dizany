-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-03-2026 a las 02:39:47
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
(157, '2026-03-06', NULL, 'ingreso', 'venta', 'Venta boleta B001-000014', 4.00, 'plin', 'pagado', 87, 'venta', '2026-03-07 01:34:48', '2026-03-07 01:34:48'),
(158, '2026-03-06', NULL, 'ingreso', 'venta', 'Venta pendiente boleta B001-000015', 2.50, 'fiado', 'pendiente', 88, 'venta', '2026-03-07 01:35:26', '2026-03-07 01:35:26'),
(159, '2026-03-06', NULL, 'ingreso', 'venta', 'Adelanto venta boleta B001-000016', 2.00, 'yape', 'pagado', 89, 'venta', '2026-03-07 01:35:47', '2026-03-07 01:35:47'),
(160, '2026-03-06', NULL, 'ingreso', 'venta', 'Saldo venta boleta B001-000016', 2.00, 'credito', 'pendiente', 89, 'venta', '2026-03-07 01:35:47', '2026-03-07 01:35:47');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_movimientos_fecha` (`fecha`),
  ADD KEY `idx_movimientos_tipo_estado` (`tipo`,`estado`),
  ADD KEY `idx_movimientos_metodo_pago` (`metodo_pago`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: dizany
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (23,'VINO'),(24,'BEBIDAS'),(25,'CERVEZA'),(26,'ENERGISANTES'),(27,'GALLETAS'),(28,'CIGARRO'),(29,'PISCO'),(30,'RON'),(31,'ACEITE'),(32,'COCTEL'),(33,'MERMELADA'),(34,'GALLETA'),(35,'PELOTAS'),(36,'PELOTA'),(37,'CACHOS'),(38,'AGUA');
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `direccion` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ruc` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dni` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ruc` (`ruc`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (19,'DILSER','PACAYZAPA',NULL,NULL,'76363332'),(20,'ANALY','PACAYZAPA','935965841',NULL,'71885485'),(21,'VICTOR','GOICOCHEA CARRANZA','936584598',NULL,'27419354'),(22,'CELINDA','MOYOBAMBA',NULL,NULL,'45950469'),(23,'ELENA','GOZEN',NULL,NULL,'00829710'),(24,'SILVIA',NULL,NULL,NULL,'71885486'),(25,'JOSE','JLJL','985456222',NULL,'98762315'),(26,'DEYVIS',NULL,NULL,NULL,'71609740'),(27,'DIANA CORDOBA','KKK',NULL,NULL,'71558985'),(28,'EVER',NULL,NULL,NULL,'20152025'),(29,'FABIAN',NULL,NULL,NULL,'92946268'),(30,'IZAN',NULL,NULL,NULL,'96358488'),(31,'SILVESTRE','DDDD',NULL,NULL,'27266552'),(32,'ELICIA','KKKKK',NULL,NULL,'85848584'),(33,'GERMAN',NULL,NULL,NULL,'45254525');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuracion`
--

DROP TABLE IF EXISTS `configuracion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracion` (
  `id` int NOT NULL,
  `nombre_empresa` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ruc` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `moneda` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `igv` decimal(5,2) DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_general_ci,
  `telefono` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `correo` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tema` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracion`
--

LOCK TABLES `configuracion` WRITE;
/*!40000 ALTER TABLE `configuracion` DISABLE KEYS */;
INSERT INTO `configuracion` VALUES (1,'DIZANY','10763633328','uploads/logos/1766438312_logo.png','S/',0.00,'AV. MARGINAL - PACAYZAPA','958196510','admin@dizany.com','claro');
/*!40000 ALTER TABLE `configuracion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_ventas`
--

DROP TABLE IF EXISTS `detalle_ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_ventas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `venta_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `presentacion` enum('unidad','paquete','caja') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'unidad',
  `cantidad` int NOT NULL,
  `unidades_afectadas` int NOT NULL DEFAULT '1',
  `precio_presentacion` decimal(10,2) NOT NULL DEFAULT '0.00',
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ganancia` decimal(10,2) NOT NULL DEFAULT '0.00',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `venta_id` (`venta_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `detalle_ventas_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`),
  CONSTRAINT `detalle_ventas_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=360 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_ventas`
--

LOCK TABLES `detalle_ventas` WRITE;
/*!40000 ALTER TABLE `detalle_ventas` DISABLE KEYS */;
INSERT INTO `detalle_ventas` VALUES (341,248,31,'unidad',1,1,25.00,25.00,25.00,10.00,1),(342,249,32,'caja',1,12,96.00,8.00,96.00,27.60,1),(343,250,31,'unidad',1,1,25.00,25.00,25.00,10.00,1),(344,250,32,'unidad',1,1,8.00,8.00,8.00,2.30,1),(345,251,31,'unidad',1,1,25.00,25.00,25.00,10.00,1),(346,251,34,'unidad',1,1,1.00,1.00,1.00,0.50,1),(347,252,31,'unidad',1,1,25.00,25.00,25.00,10.00,1),(348,253,31,'unidad',1,1,25.00,25.00,25.00,10.00,1),(349,254,31,'unidad',1,1,25.00,25.00,25.00,10.00,1),(350,255,32,'unidad',1,1,8.00,8.00,8.00,2.30,1),(351,255,33,'paquete',1,15,35.00,2.33,35.00,20.00,1),(352,255,34,'unidad',1,1,1.00,1.00,1.00,0.50,1),(353,256,31,'unidad',1,1,25.00,25.00,25.00,10.00,1),(354,256,32,'unidad',1,1,8.00,8.00,8.00,2.30,1),(355,257,31,'unidad',1,1,25.00,25.00,25.00,10.00,1),(356,258,33,'unidad',1,1,2.50,2.50,2.50,1.50,1),(357,258,32,'unidad',1,1,8.00,8.00,8.00,2.30,1),(358,258,31,'unidad',1,1,25.00,25.00,25.00,10.00,1),(359,259,33,'unidad',1,1,2.50,2.50,2.50,1.50,1);
/*!40000 ALTER TABLE `detalle_ventas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facturas`
--

DROP TABLE IF EXISTS `facturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facturas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `venta_id` int NOT NULL,
  `numero_factura` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_emision` date NOT NULL,
  `ruc_emisor` varchar(11) COLLATE utf8mb4_general_ci NOT NULL,
  `razon_social_emisor` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `direccion_emisor` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_factura` (`numero_factura`),
  KEY `venta_id` (`venta_id`),
  CONSTRAINT `facturas_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facturas`
--

LOCK TABLES `facturas` WRITE;
/*!40000 ALTER TABLE `facturas` DISABLE KEYS */;
/*!40000 ALTER TABLE `facturas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gastos`
--

DROP TABLE IF EXISTS `gastos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gastos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  `metodo_pago` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Efectivo',
  `estado` enum('activo','anulado') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'activo',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `fk_gastos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gastos`
--

LOCK TABLES `gastos` WRITE;
/*!40000 ALTER TABLE `gastos` DISABLE KEYS */;
INSERT INTO `gastos` VALUES (10,4,'compra de gaseosa',52.00,'2026-01-09 11:55:00','efectivo','activo','2026-01-09 11:55:17','2026-01-09 11:55:17'),(11,4,'pago de luz',20.00,'2026-01-09 12:37:00','efectivo','activo','2026-01-09 12:37:27','2026-01-09 12:37:27'),(12,4,'pago de luz',10.00,'2026-01-09 13:18:00','YAPE','activo','2026-01-09 13:18:50','2026-01-09 13:18:50');
/*!40000 ALTER TABLE `gastos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lotes`
--

DROP TABLE IF EXISTS `lotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lotes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` int NOT NULL,
  `proveedor_id` bigint unsigned DEFAULT NULL,
  `cantidad` int NOT NULL,
  `costo_unitario` decimal(10,2) NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `estado` enum('activo','agotado','vencido','anulado') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_lotes_proveedor` (`proveedor_id`),
  KEY `idx_lotes_producto_venc` (`producto_id`,`fecha_vencimiento`),
  KEY `idx_lotes_estado` (`estado`),
  CONSTRAINT `fk_lotes_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `fk_lotes_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lotes`
--

LOCK TABLES `lotes` WRITE;
/*!40000 ALTER TABLE `lotes` DISABLE KEYS */;
INSERT INTO `lotes` VALUES (1,31,1,3,10.00,'2026-01-10',NULL,25.00,'activo','2026-01-10 16:43:18','2026-01-10 16:43:18');
/*!40000 ALTER TABLE `lotes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marcas`
--

DROP TABLE IF EXISTS `marcas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marcas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marcas`
--

LOCK TABLES `marcas` WRITE;
/*!40000 ALTER TABLE `marcas` DISABLE KEYS */;
INSERT INTO `marcas` VALUES (11,'BORGOÑA',NULL),(12,'SAN MATEO',NULL),(13,'PILSEN',NULL),(14,'VOLT',NULL),(15,'COCA COLA',NULL),(16,'GOLDEN',NULL),(17,'PRIMOR',NULL),(18,'COCHADO',NULL),(19,'GLORIA',NULL),(20,'MIKASA',NULL),(21,'VACA',NULL),(22,'SAN LUIS',NULL),(23,'SODA',NULL);
/*!40000 ALTER TABLE `marcas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (6,'2025_12_09_190818_add_timestamps_to_productos_table',1),(7,'2025_12_19_003109_add_indexes_to_movimientos_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos`
--

DROP TABLE IF EXISTS `movimientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimientos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `hora` time DEFAULT NULL,
  `tipo` enum('ingreso','egreso') COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Ingreso = dinero que entra, Egreso = dinero que sale',
  `subtipo` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `concepto` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `metodo_pago` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estado` enum('pagado','pendiente','anulado') COLLATE utf8mb4_general_ci NOT NULL,
  `referencia_id` bigint unsigned DEFAULT NULL COMMENT 'ID relacionado: venta_id, gasto_id, etc',
  `referencia_tipo` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'venta, gasto, ajuste, compra',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_movimientos_fecha` (`fecha`),
  KEY `idx_movimientos_tipo_estado` (`tipo`,`estado`),
  KEY `idx_movimientos_metodo_pago` (`metodo_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos`
--

LOCK TABLES `movimientos` WRITE;
/*!40000 ALTER TABLE `movimientos` DISABLE KEYS */;
INSERT INTO `movimientos` VALUES (70,'2025-12-22',NULL,'ingreso','venta','Adelanto venta boleta B001-000004',20.00,'otro','pagado',251,'venta','2025-12-23 04:26:29','2025-12-23 04:26:29'),(72,'2025-12-22',NULL,'ingreso','cobro_credito','Cobro crédito venta B001-000004',6.00,'yape','pagado',251,'venta','2025-12-23 04:26:50','2025-12-23 04:26:50'),(73,'2025-12-22',NULL,'ingreso','venta','Venta pendiente boleta B001-000005',25.00,'efectivo','pagado',252,'venta','2025-12-23 04:27:16','2025-12-23 04:27:33'),(74,'2025-12-22',NULL,'ingreso','venta','Venta boleta B001-000006',25.00,'yape','pagado',253,'venta','2025-12-23 04:27:59','2025-12-23 04:27:59'),(75,'2025-12-26',NULL,'ingreso','venta','Venta pendiente boleta B001-000007',25.00,'efectivo','pagado',254,'venta','2025-12-26 15:02:24','2025-12-26 15:04:34'),(76,'2025-12-26',NULL,'ingreso','venta','Adelanto venta boleta B001-000008',40.00,'otro','pagado',255,'venta','2025-12-26 15:03:00','2025-12-26 15:03:00'),(78,'2025-12-26',NULL,'ingreso','cobro_credito','Cobro crédito venta B001-000008',4.00,'efectivo','pagado',255,'venta','2025-12-26 15:04:09','2025-12-26 15:04:09'),(79,'2026-01-03',NULL,'ingreso','venta','Venta boleta B001-000009',33.00,'efectivo','pagado',256,'venta','2026-01-03 14:00:08','2026-01-03 14:00:08'),(80,'2026-01-08',NULL,'ingreso','venta','Venta boleta B001-000010',25.00,'efectivo','pagado',257,'venta','2026-01-08 15:42:27','2026-01-08 15:42:27'),(82,'2026-01-09','11:55:17','egreso','gasto','compra de gaseosa',52.00,'efectivo','anulado',10,'gasto','2026-01-09 16:55:17','2026-01-09 17:37:07'),(83,'2026-01-09','12:37:27','egreso','gasto','pago de luz',20.00,'efectivo','anulado',11,'gasto','2026-01-09 17:37:27','2026-01-09 18:13:37'),(84,'2026-01-09',NULL,'ingreso','venta','Venta boleta B001-000011',35.50,'efectivo','pagado',258,'venta','2026-01-09 17:39:42','2026-01-09 17:39:42'),(85,'2026-01-09',NULL,'ingreso','venta','Venta pendiente boleta B001-000012',2.50,'fiado','pendiente',259,'venta','2026-01-09 17:40:10','2026-01-09 17:40:10'),(86,'2026-01-09','13:18:51','egreso','gasto','pago de luz',10.00,'YAPE','pagado',12,'gasto','2026-01-09 18:18:51','2026-01-09 18:18:51');
/*!40000 ALTER TABLE `movimientos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos_venta`
--

DROP TABLE IF EXISTS `pagos_venta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos_venta` (
  `id` int NOT NULL AUTO_INCREMENT,
  `venta_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_pago` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pagos_venta_venta` (`venta_id`),
  KEY `idx_pagos_venta_usuario` (`usuario_id`),
  CONSTRAINT `fk_pagos_venta_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_pagos_venta_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos_venta`
--

LOCK TABLES `pagos_venta` WRITE;
/*!40000 ALTER TABLE `pagos_venta` DISABLE KEYS */;
INSERT INTO `pagos_venta` VALUES (56,248,4,25.00,'transferencia','2025-12-22 23:13:33','2025-12-23 04:13:33','2025-12-23 04:13:33'),(57,250,4,30.00,'otro','2025-12-22 23:14:53','2025-12-23 04:14:53','2025-12-23 04:14:53'),(58,250,4,3.00,'efectivo','2025-12-22 23:20:54','2025-12-23 04:20:54','2025-12-23 04:20:54'),(59,249,4,96.00,'efectivo','2025-12-22 23:21:24','2025-12-23 04:21:24','2025-12-23 04:21:24'),(60,251,4,20.00,'otro','2025-12-22 23:26:27','2025-12-23 04:26:27','2025-12-23 04:26:27'),(61,251,4,6.00,'yape','2025-12-22 23:26:50','2025-12-23 04:26:50','2025-12-23 04:26:50'),(62,252,4,25.00,'efectivo','2025-12-22 23:27:33','2025-12-23 04:27:33','2025-12-23 04:27:33'),(63,253,4,25.00,'yape','2025-12-22 23:27:57','2025-12-23 04:27:57','2025-12-23 04:27:57'),(64,255,4,40.00,'otro','2025-12-26 10:02:57','2025-12-26 15:02:57','2025-12-26 15:02:57'),(65,255,4,4.00,'efectivo','2025-12-26 10:04:09','2025-12-26 15:04:09','2025-12-26 15:04:09'),(66,254,4,25.00,'efectivo','2025-12-26 10:04:34','2025-12-26 15:04:34','2025-12-26 15:04:34'),(67,256,4,33.00,'efectivo','2026-01-03 09:00:01','2026-01-03 14:00:01','2026-01-03 14:00:01'),(68,257,4,25.00,'efectivo','2026-01-08 10:42:18','2026-01-08 15:42:18','2026-01-08 15:42:18'),(69,258,4,35.50,'efectivo','2026-01-09 12:39:33','2026-01-09 17:39:33','2026-01-09 17:39:33');
/*!40000 ALTER TABLE `pagos_venta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo_barras` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `precio_compra` decimal(10,2) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `precio_paquete` decimal(10,2) DEFAULT NULL,
  `unidades_por_paquete` int DEFAULT NULL,
  `paquetes_por_caja` int DEFAULT NULL,
  `tipo_paquete` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `precio_caja` decimal(10,2) DEFAULT NULL,
  `stock` int DEFAULT '0',
  `ubicacion` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `imagen` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `categoria_id` int NOT NULL,
  `marca_id` int DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `maneja_vencimiento` tinyint(1) NOT NULL DEFAULT '0',
  `visible_en_catalogo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_barras` (`codigo_barras`),
  UNIQUE KEY `slug` (`slug`),
  KEY `categoria_id` (`categoria_id`),
  KEY `fk_marca_producto` (`marca_id`),
  CONSTRAINT `fk_marca_producto` FOREIGN KEY (`marca_id`) REFERENCES `marcas` (`id`),
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (31,'0000000001','PELOTA MIKASA','pelota-mikasa','MIKASA DE CUERO',15.00,25.00,NULL,NULL,NULL,NULL,NULL,17,'p1','pelota-mikasa-1765915389.jpeg',NULL,36,20,1,0,1,'2025-12-16 20:03:09','2026-01-09 17:39:33'),(32,'0000000002','PILSEN','pilsen','PILSEN - CONT. 630 ml',5.70,8.00,NULL,12,NULL,NULL,96.00,19,'p2','pilsen-1765918555.jpg','2027-03-18',25,13,1,0,1,'2025-12-16 20:55:55','2026-01-09 17:39:33'),(33,'0000000003','SAN LUIS','san-luis','AGUA CONT. 625ml',1.00,2.50,35.00,15,NULL,NULL,NULL,40,'p3','san-luis-1765923081.jpg','2026-02-15',38,22,1,0,1,'2025-12-16 20:59:24','2026-01-09 17:40:07'),(34,'0000000004','SODA','soda','CROCANTES Y DORADITAS',0.50,1.00,5.00,6,10,NULL,48.00,20,'p1','soda-1765920842.webp','2026-05-05',34,23,1,0,1,'2025-12-16 21:04:56','2026-01-03 13:41:07');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `ruc` varchar(20) DEFAULT NULL,
  `documento` varchar(30) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_proveedores_ruc` (`ruc`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'agro selva','',NULL,NULL,'moyobamba','agroselva@gmail.com','activo','2026-01-10 15:02:32','2026-01-10 15:02:32');
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrador'),(2,'Empleado');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `usuario` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `clave` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `rol_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`),
  UNIQUE KEY `email` (`email`),
  KEY `rol_id` (`rol_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (4,'Dilser','admin','dilser95@gmail.com','$2y$12$zMKx97A4EIhk68ImMCWQXeMQnHCXmL1MiT3dmMoVqm.0JwoyLtq8K',1,'2025-06-12 23:36:29','2025-06-12 23:36:29'),(9,'any','any25','analy@gmail.com','$2y$12$wUYveWN3vGhhOQRw4d342O43K2CFrMWnEgtpJSgtP6c/b45uLUwTS',2,NULL,NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ventas`
--

DROP TABLE IF EXISTS `ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ventas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int DEFAULT NULL,
  `usuario_id` int NOT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  `tipo_comprobante` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `serie` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `correlativo` int DEFAULT NULL,
  `metodo_pago` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'Pagada',
  `estado_sunat` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'pendiente',
  `hash` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `xml_url` text COLLATE utf8mb4_general_ci,
  `pdf_url` text COLLATE utf8mb4_general_ci,
  `cdr_url` text COLLATE utf8mb4_general_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `op_gravadas` decimal(10,2) DEFAULT '0.00',
  `igv` decimal(10,2) DEFAULT '0.00',
  `saldo` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=260 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas`
--

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
INSERT INTO `ventas` VALUES (248,20,4,'2025-12-22 23:13:31','boleta','B001',1,'transferencia',25.00,'pagado','pendiente',NULL,NULL,NULL,NULL,1,25.00,0.00,0.00),(249,19,4,'2025-12-22 23:14:06','boleta','B001',2,'efectivo',96.00,'pagado','pendiente',NULL,NULL,NULL,NULL,1,96.00,0.00,0.00),(250,19,4,'2025-12-22 23:14:52','boleta','B001',3,'otro',33.00,'pagado','pendiente',NULL,NULL,NULL,NULL,1,33.00,0.00,0.00),(251,20,4,'2025-12-22 23:26:26','boleta','B001',4,'otro',26.00,'pagado','pendiente',NULL,NULL,NULL,NULL,1,26.00,0.00,0.00),(252,19,4,'2025-12-22 23:27:13','boleta','B001',5,'efectivo',25.00,'pagado','pendiente',NULL,NULL,NULL,NULL,1,25.00,0.00,0.00),(253,20,4,'2025-12-22 23:27:56','boleta','B001',6,'yape',25.00,'pagado','pendiente',NULL,NULL,NULL,NULL,1,25.00,0.00,0.00),(254,19,4,'2025-12-26 10:02:21','boleta','B001',7,'efectivo',25.00,'pagado','pendiente',NULL,NULL,NULL,NULL,1,25.00,0.00,0.00),(255,19,4,'2025-12-26 10:02:57','boleta','B001',8,'otro',44.00,'pagado','pendiente',NULL,NULL,NULL,NULL,1,44.00,0.00,0.00),(256,22,4,'2026-01-03 09:00:00','boleta','B001',9,'efectivo',33.00,'pagado','pendiente',NULL,NULL,NULL,NULL,1,33.00,0.00,0.00),(257,22,4,'2026-01-08 10:42:18','boleta','B001',10,'efectivo',25.00,'pagado','pendiente',NULL,NULL,NULL,NULL,1,25.00,0.00,0.00),(258,19,4,'2026-01-09 12:39:32','boleta','B001',11,'efectivo',35.50,'pagado','pendiente',NULL,NULL,NULL,NULL,1,35.50,0.00,0.00),(259,19,4,'2026-01-09 12:40:06','boleta','B001',12,NULL,2.50,'pendiente','pendiente',NULL,NULL,NULL,NULL,1,2.50,0.00,2.50);
/*!40000 ALTER TABLE `ventas` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-10 12:51:10

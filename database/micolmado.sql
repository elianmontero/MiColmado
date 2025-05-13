-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-05-2025 a las 04:43:52
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `micolmado`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colmado`
--

CREATE TABLE `colmado` (
  `id` int(11) NOT NULL,
  `nombre_colmado` varchar(150) DEFAULT NULL,
  `direccion` varchar(255) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `colmado`
--

INSERT INTO `colmado` (`id`, `nombre_colmado`, `direccion`, `id_usuario`) VALUES
(5, 'Colmado Presidente', 'Calle J esquina 31 #11', 12),
(6, 'La esquina de Papo', 'calle papo esquina papolo #33', 14);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','procesado','entregado','cancelado') DEFAULT 'pendiente',
  `total` decimal(10,2) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `metodo_pago` enum('tarjeta','efectivo') NOT NULL,
  `proveedor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido`
--

INSERT INTO `pedido` (`id`, `id_usuario`, `fecha_pedido`, `estado`, `total`, `direccion`, `metodo_pago`, `proveedor_id`) VALUES
(1, 12, '2025-05-13 00:03:53', 'entregado', 3075.00, 'Calle J3 esquina Francisco Sánchez #11', 'efectivo', 12),
(2, 12, '2025-05-13 00:12:06', 'pendiente', 120.00, 'Calle J3 esquina Francisco Sánchez #11', 'efectivo', 12),
(3, 13, '2025-05-13 01:38:21', 'pendiente', 55.00, 'Calle la vecina de al lado', 'efectivo', 14);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_producto`
--

CREATE TABLE `pedido_producto` (
  `id` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido_producto`
--

INSERT INTO `pedido_producto` (`id`, `id_pedido`, `id_producto`, `cantidad`, `subtotal`) VALUES
(5, 6, 4, 2, 150.00),
(6, 7, 6, 2, 70.00),
(7, 8, 6, 2, 70.00),
(8, 1, 4, 5, 375.00),
(9, 1, 3, 3, 360.00),
(10, 1, 11, 5, 1500.00),
(11, 1, 10, 7, 840.00),
(12, 2, 10, 1, 120.00),
(13, 3, 16, 1, 55.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio` float DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `imagen` varchar(255) DEFAULT NULL,
  `id_colmado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id`, `nombre`, `precio`, `stock`, `imagen`, `id_colmado`) VALUES
(3, 'Pan tostado integral', 120, 22, '../public/assets/imagenes-productos/producto.jpg', 5),
(4, 'Digestive Chocolate', 75, 21, '/public/uploads/img_681169b0652cd4.46444630.jpg', 5),
(5, 'Krit krititas galletas saladas', 50, 0, '/public/uploads/img_681169b3a405b7.24754230.jpg', 5),
(6, 'Coco', 35, 6, '/public/uploads/img_68116b75e255c1.18890848.png', 5),
(7, 'Agua de bronchales', 20, 9, '/public/uploads/img_68116bce4faca2.10819144.jpg', 5),
(9, 'Platano Verde', 35, 100, '/public/uploads/img_6821265ecd5a33.90675615.png', 5),
(10, 'Doña gallina paquete 12u', 120, 22, '/public/uploads/img_6821267c6e84a7.51297204.png', 5),
(11, 'Jamón Caserio de pavo', 300, 15, '/public/uploads/img_682126928c0cf2.22365479.png', 5),
(12, 'Leche carnation', 75, 55, '/public/uploads/img_682126a4bd0251.51756430.png', 5),
(13, 'Leche la vaquita Rica', 75, 30, '/public/uploads/img_682126b3a68545.75279624.png', 5),
(14, 'Lata pequeña de maiz dulce', 55, 66, '/public/uploads/img_682126c24929e8.67191533.png', 5),
(15, 'Sazon Ranchero', 120, 11, '/public/uploads/img_68212d3d147622.07324174.png', 5),
(16, 'Sazon Ranchero', 55, 10, '/public/uploads/img_682296c6936c25.16556295.png', 6),
(17, 'Pringles Original', 100, 10, '/public/uploads/img_6822a1799db392.44531141.jpg', 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contraseña` varchar(100) NOT NULL,
  `cedula` varchar(11) DEFAULT NULL,
  `tipo_usuario` enum('Proveedor','Consumidor') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `nombre_completo`, `direccion`, `telefono`, `email`, `contraseña`, `cedula`, `tipo_usuario`) VALUES
(12, 'Elian Montero', 'Calle J esquina 31 #11', '8095554744', 'elian-proveedor04@gmail.com', '$2y$10$e1Z7RBNNicWwA6Bjv3aRd.tnQqUmvlbOFbCZVJF5yMEzZLoohuD1y', '00191823871', 'Proveedor'),
(13, 'Elian Montero', 'Calle J3 esquina Francisco Sánchez #11', '8091176127', 'elian-consumidor04@gmail.com', '$2y$10$vYRd44T5osGn0yc6WMmBEu4BPKf75I2.kMzUmXEXvCAHKFyrS/wMa', NULL, 'Consumidor'),
(14, 'Chrystopher Jael', 'calle papo esquina papolo #33', '8298172381', 'chrystopher@gmail.com', '$2y$10$KbayRRZTBMkzunAf6yYFmuAOZXbZBQnwZeshhRmGoOn3fMAW5G0kS', '12736178381', 'Proveedor');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `colmado`
--
ALTER TABLE `colmado`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_nombre_colmadero` (`id_usuario`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`id_usuario`),
  ADD KEY `proveedor_id` (`proveedor_id`);

--
-- Indices de la tabla `pedido_producto`
--
ALTER TABLE `pedido_producto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`id_pedido`),
  ADD KEY `producto_id` (`id_producto`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_colmado` (`id_colmado`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `colmado`
--
ALTER TABLE `colmado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pedido_producto`
--
ALTER TABLE `pedido_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `colmado`
--
ALTER TABLE `colmado`
  ADD CONSTRAINT `fk_nombre_colmadero` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `fk_pedido_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pedido_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedido_producto`
--
ALTER TABLE `pedido_producto`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `fk_colmado` FOREIGN KEY (`id_colmado`) REFERENCES `colmado` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

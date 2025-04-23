-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-04-2025 a las 05:59:23
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
(5, 'Colmado Presidente', 'Calle J esquina 31 #11', 12);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envio`
--

CREATE TABLE `envio` (
  `id` int(11) NOT NULL,
  `nombre_producto` varchar(100) NOT NULL,
  `fecha_envio` date NOT NULL,
  `estado` varchar(50) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `colmado_id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envio_producto`
--

CREATE TABLE `envio_producto` (
  `envio_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio` float NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `imagen` varchar(255) DEFAULT NULL,
  `id_colmado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id`, `nombre`, `precio`, `stock`, `imagen`, `id_colmado`) VALUES
(15, 'Malta morena', 50, 31, '../public/assets/imagenes-productos/malta-morena.webp', 5),
(16, 'Platano Verde', 35, 50, '../public/assets/imagenes-productos/platano.png', 5),
(17, 'Café domincano', 50, 20, '../public/assets/imagenes-productos/cafedominicano.png', 5),
(18, 'Doña gallina paquete 12 unidades', 150, 21, '../public/assets/imagenes-productos/doña gallina paquete 12U.png', 5),
(19, 'Jamon Caserio de pavo', 300, 5, '../public/assets/imagenes-productos/jamón Caserio pechuga de pavo.png', 5),
(20, 'Leche carnation', 80, 44, '../public/assets/imagenes-productos/leche carnation.png', 5),
(21, 'Leche Rica la vaquita', 65, 100, '../public/assets/imagenes-productos/leche la vaquita Rica.png', 5),
(22, 'Lata de maíz dulce pequeña', 55, 100, '../public/assets/imagenes-productos/maiz dulce.png', 5),
(23, 'Mango', 35, 8, '../public/assets/imagenes-productos/mango.webp', 5),
(24, 'Sazon Ranchero', 125, 4, '../public/assets/imagenes-productos/sazonranchero.png', 5);

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
(13, 'Elian Montero', 'Calle J3 esquina Francisco Sánchez #11', '8091176127', 'elian-consumidor04@gmail.com', '$2y$10$vYRd44T5osGn0yc6WMmBEu4BPKf75I2.kMzUmXEXvCAHKFyrS/wMa', NULL, 'Consumidor');

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
-- Indices de la tabla `envio`
--
ALTER TABLE `envio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `colmado_id` (`colmado_id`),
  ADD KEY `fk_envio_producto` (`id_producto`);

--
-- Indices de la tabla `envio_producto`
--
ALTER TABLE `envio_producto`
  ADD PRIMARY KEY (`envio_id`,`producto_id`),
  ADD KEY `producto_id` (`producto_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `envio`
--
ALTER TABLE `envio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `colmado`
--
ALTER TABLE `colmado`
  ADD CONSTRAINT `fk_nombre_colmadero` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `envio`
--
ALTER TABLE `envio`
  ADD CONSTRAINT `envio_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `envio_ibfk_2` FOREIGN KEY (`colmado_id`) REFERENCES `colmado` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_envio_producto` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `envio_producto`
--
ALTER TABLE `envio_producto`
  ADD CONSTRAINT `envio_producto_ibfk_1` FOREIGN KEY (`envio_id`) REFERENCES `envio` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `envio_producto_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `fk_colmado` FOREIGN KEY (`id_colmado`) REFERENCES `colmado` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

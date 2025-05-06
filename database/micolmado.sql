-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-05-2025 a las 05:53:10
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
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `marca` varchar(100) NOT NULL,
  `precio` float NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `imagen` varchar(255) DEFAULT NULL,
  `id_colmado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id`, `nombre`, `marca`, `precio`, `stock`, `imagen`, `id_colmado`) VALUES
(3, 'Pan tostado integral', '', 120, 25, '../public/assets/imagenes-productos/producto.jpg', 5),
(4, 'Digestive Chocolate', '', 75, 30, '/public/uploads/img_681169b0652cd4.46444630.jpg', 5),
(5, 'Krit krititas galletas saladas', '', 50, 10, '/public/uploads/img_681169b3a405b7.24754230.jpg', 5),
(6, 'Coco', '', 35, 10, '/public/uploads/img_68116b75e255c1.18890848.png', 5),
(7, 'Agua de bronchales', '', 20, 9, '/public/uploads/img_68116bce4faca2.10819144.jpg', 5);

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
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `fk_colmado` FOREIGN KEY (`id_colmado`) REFERENCES `colmado` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

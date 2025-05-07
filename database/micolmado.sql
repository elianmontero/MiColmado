-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 07, 2025 at 05:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `micolmado`
--

-- --------------------------------------------------------

--
-- Table structure for table `colmado`
--

CREATE TABLE `colmado` (
  `id` int(11) NOT NULL,
  `nombre_colmado` varchar(150) DEFAULT NULL,
  `direccion` varchar(255) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `colmado`
--

INSERT INTO `colmado` (`id`, `nombre_colmado`, `direccion`, `id_usuario`) VALUES
(5, 'Colmado Presidente', 'Calle J esquina 31 #11', 12);

-- --------------------------------------------------------

--
-- Table structure for table `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) GENERATED ALWAYS AS (`cantidad` * `precio_unitario`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pedido`
--

CREATE TABLE `pedido` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','procesado','entregado','cancelado') DEFAULT 'pendiente',
  `total` decimal(10,2) NOT NULL,
  `direccion_envio` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `producto`
--

CREATE TABLE `producto` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio` float NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `imagen` varchar(255) DEFAULT NULL,
  `id_colmado` int(11) NOT NULL,
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `producto`
--

INSERT INTO `producto` (`id`, `nombre`, `precio`, `stock`, `imagen`, `id_colmado`, `fecha_modificacion`) VALUES
(1, 'Digestive Chocolate', 100, 31, '/public/uploads/img_6812332889d262.58422897.jpg', 5, '2025-04-30 14:27:03'),
(2, 'Pan espelta integral', 125, 30, '/public/uploads/img_681b70b02fc530.65622389.jpg', 5, '2025-05-07 14:48:26'),
(3, 'Pan tostado', 70, 30, '/public/uploads/img_681b70b5d7bb17.09031691.jpg', 5, '2025-05-07 14:52:22'),
(4, 'Pan tostado Cereales y semillas', 200, 11, '/public/uploads/img_681b70b87e8650.87269135.jpg', 5, '2025-05-07 14:53:04'),
(5, 'Pan tostado integral', 80, 2, '/public/uploads/img_681b70bb0c2191.98363597.jpg', 5, '2025-05-07 14:53:23'),
(6, 'Pan de molde blanco natural rústico', 80, 5, '/public/uploads/img_681b70c046d963.49133462.jpg', 5, '2025-05-07 14:53:41'),
(7, 'Biscuits Nutella x3 biscuits fourrés - 41,4g', 50, 55, '/public/uploads/img_681b72a5158398.14988169.jpg', 5, '2025-05-07 14:53:53'),
(8, 'Ronditas 32% Chocolate', 80, 13, '/public/uploads/img_681b72ab522bf6.21806784.jpg', 5, '2025-05-07 14:54:06');

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
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
-- Dumping data for table `usuario`
--

INSERT INTO `usuario` (`id`, `nombre_completo`, `direccion`, `telefono`, `email`, `contraseña`, `cedula`, `tipo_usuario`) VALUES
(12, 'Elian Montero', 'Calle J esquina 31 #11', '8095554744', 'elian-proveedor04@gmail.com', '$2y$10$e1Z7RBNNicWwA6Bjv3aRd.tnQqUmvlbOFbCZVJF5yMEzZLoohuD1y', '00191823871', 'Proveedor'),
(13, 'Elian Montero', 'Calle J3 esquina Francisco Sánchez #11', '8091176127', 'elian-consumidor04@gmail.com', '$2y$10$vYRd44T5osGn0yc6WMmBEu4BPKf75I2.kMzUmXEXvCAHKFyrS/wMa', NULL, 'Consumidor');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `colmado`
--
ALTER TABLE `colmado`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_nombre_colmadero` (`id_usuario`);

--
-- Indexes for table `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indexes for table `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_colmado` (`id_colmado`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `colmado`
--
ALTER TABLE `colmado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `producto`
--
ALTER TABLE `producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `colmado`
--
ALTER TABLE `colmado`
  ADD CONSTRAINT `fk_nombre_colmadero` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `pedido_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `fk_colmado` FOREIGN KEY (`id_colmado`) REFERENCES `colmado` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

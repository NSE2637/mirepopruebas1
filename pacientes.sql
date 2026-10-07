-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 05-10-2026 a las 21:54:33
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_pacientes`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

DROP TABLE IF EXISTS `pacientes`;
CREATE TABLE IF NOT EXISTS `pacientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ubicacion` enum('Recepción','Sala de preparación','Cirugía','Sala de recuperación') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Recepción',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pacientes`
--

INSERT INTO `pacientes` (`id`, `nombre`, `ubicacion`, `fecha_registro`) VALUES
(1, 'Carlos Arango', 'Sala de preparación', '2026-09-24 15:16:33'),
(2, 'Laura Gómez', 'Cirugía', '2026-09-24 15:16:33'),
(3, 'Yulieth Galvis', 'Recepción', '2026-09-24 15:33:27'),
(5, 'MARTHA SOTO', 'Sala de recuperación', '2026-09-24 19:48:44'),
(6, 'Dayana Alzate', 'Sala de recuperación', '2026-09-24 19:56:32'),
(7, 'Maria Daniela Rosas', 'Cirugía', '2026-09-25 16:17:32'),
(8, 'Paola Ruíz', 'Recepción', '2026-09-25 16:19:28'),
(9, 'Daniela Manrrique', 'Sala de preparación', '2026-09-28 19:33:28'),
(10, 'TATIANA PULGARIN', 'Cirugía', '2026-09-28 19:40:40'),
(11, 'JULIANA', 'Sala de preparación', '2026-10-05 20:14:34'),
(12, 'DANIELA MANRRIQUE', 'Sala de preparación', '2026-10-05 20:45:36');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

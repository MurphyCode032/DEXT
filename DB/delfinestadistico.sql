-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-01-2024 a las 21:59:24
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.0.28


-- Base de datos para el funcionamiento de los archvios, su editaje y filtros. 
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `delfinestadistico`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumnos`
--

CREATE TABLE `alumnos` (
  `id` int(11) NOT NULL,
  `Numero_de_Control` varchar(20) NOT NULL,
  `Nombre_Completo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Fecha_de_Nacimiento` date NOT NULL,
  `Sexo` enum('Masculino','Femenino') NOT NULL,
  `Correo_Institucional` varchar(255) NOT NULL,
  `Correo_Personal` varchar(255) NOT NULL,
  `Telefono_Celular` varchar(20) NOT NULL,
  `Ingenieria` varchar(255) NOT NULL,
  `Estado` varchar(255) NOT NULL,
  `Municipio` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estancia`
--

CREATE TABLE `estancia` (
  `id` int(11) NOT NULL,
  `Alumno_id` int(11) NOT NULL,
  `Tipo_Programa` enum('Delfín','Movilidad','Pila') NOT NULL,
  `Fecha_Registro` date DEFAULT NULL COMMENT 'Formato DD/MM/AAAA',
  `Periodo` varchar(20) NOT NULL,
  `Promedio` int(11) NOT NULL,
  `Semestre` int(11) NOT NULL,
  `Idioma` varchar(255) NOT NULL,
  `Nivel_Idioma` varchar(255) NOT NULL,
  `Documento_Aval` varchar(255) NOT NULL,
  `Fecha_Expiracion` date DEFAULT NULL COMMENT 'Formato DD/MM/AAAA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `universidadestancia`
--

CREATE TABLE `universidadestancia` (
  `id` int(11) NOT NULL,
  `Alumno_id` int(11) NOT NULL,
  `Nombre_Universidad` varchar(255) NOT NULL,
  `Pais` varchar(255) NOT NULL,
  `Coordinador` varchar(255) CHARACTER SET ucs2 COLLATE ucs2_spanish2_ci NOT NULL,
  `Telefono_Coordinador` varchar(20) NOT NULL,
  `Correo_Coordinador` varchar(255) NOT NULL,
  `Nombre_Proyecto_Investigacion` varchar(255) DEFAULT NULL,
  `Observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `estancia`
--
ALTER TABLE `estancia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Alumno_id` (`Alumno_id`);

--
-- Indices de la tabla `universidadestancia`
--
ALTER TABLE `universidadestancia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Alumno_id` (`Alumno_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1158;

--
-- AUTO_INCREMENT de la tabla `estancia`
--
ALTER TABLE `estancia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1033;

--
-- AUTO_INCREMENT de la tabla `universidadestancia`
--
ALTER TABLE `universidadestancia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1050;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `estancia`
--
ALTER TABLE `estancia`
  ADD CONSTRAINT `estancia_ibfk_1` FOREIGN KEY (`Alumno_id`) REFERENCES `alumnos` (`id`);

--
-- Filtros para la tabla `universidadestancia`
--
ALTER TABLE `universidadestancia`
  ADD CONSTRAINT `universidadestancia_ibfk_1` FOREIGN KEY (`Alumno_id`) REFERENCES `alumnos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 3.3.7deb5build0.10.10.1
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generaci�n: 16-03-2011 a las 19:41:12
-- Versi�n del servidor: 5.1.49
-- Versi�n de PHP: 5.3.3-1ubuntu9.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de datos: 'goteo'
-- DUMP COMPLETO
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla 'charge'
--

DROP TABLE IF EXISTS charge;
CREATE TABLE charge (
  id int(11) NOT NULL AUTO_INCREMENT,
  invest int(11) NOT NULL,
  entity varchar(50) NOT NULL,
  `code` varchar(256) NOT NULL,
  `date` date NOT NULL,
  result varchar(8) NOT NULL COMMENT 'FAIL / SUCCESS',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Transacciones en banco o paypal';

--
-- Volcar la base de datos para la tabla 'charge'
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla 'invest'
--

DROP TABLE IF EXISTS invest;
CREATE TABLE invest (
  id int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(50) NOT NULL,
  project varchar(50) NOT NULL,
  amount int(6) NOT NULL,
  `status` int(1) NOT NULL COMMENT '0 pendiente, 1 cobrado, 2 devuelto',
  anonymous tinyint(1) DEFAULT NULL,
  resign tinyint(1) DEFAULT NULL,
  invested date DEFAULT NULL,
  charged date DEFAULT NULL,
  returned date DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Aportes monetarios a proyectos';

--
-- Volcar la base de datos para la tabla 'invest'
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla 'node'
--

DROP TABLE IF EXISTS node;
CREATE TABLE node (
  id varchar(50) NOT NULL,
  `name` varchar(256) NOT NULL,
  active tinyint(1) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Nodos';

--
-- Volcar la base de datos para la tabla 'node'
--

INSERT INTO node VALUES('goteo', 'Master node', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla 'project'
--

DROP TABLE IF EXISTS project;
CREATE TABLE project (
  id varchar(50) NOT NULL,
  `name` tinytext NOT NULL,
  `status` int(1) NOT NULL,
  progress int(3) NOT NULL,
  `owner` varchar(50) NOT NULL COMMENT 'usuario que lo ha creado',
  node varchar(50) NOT NULL COMMENT 'nodo en el que se ha creado',
  amount int(6) DEFAULT NULL COMMENT 'acumulado actualmente',
  created date DEFAULT NULL,
  published date DEFAULT NULL,
  success date DEFAULT NULL,
  closed date DEFAULT NULL,
  contract_name varchar(255) DEFAULT NULL,
  contract_surname varchar(255) DEFAULT NULL,
  contract_nif varchar(10) DEFAULT NULL COMMENT 'Guardar sin espacios ni puntos ni guiones',
  contract_email varchar(256) DEFAULT NULL,
  phone varchar(9) DEFAULT NULL COMMENT 'guardar sin espacios ni puntos',
  address tinytext,
  zipcode varchar(10) DEFAULT NULL,
  location varchar(255) DEFAULT NULL,
  country varchar(50) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY `owner` (`owner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Proyectos de la plataforma';

--
-- Volcar la base de datos para la tabla 'project'
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla 'user'
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  id varchar(50) NOT NULL,
  `name` varchar(256) NOT NULL,
  email varchar(256) NOT NULL,
  `password` varchar(32) NOT NULL,
  about text,
  signup date NOT NULL,
  active tinyint(1) NOT NULL DEFAULT '0',
  avatar tinytext,
  contribution text,
  blog varchar(256) DEFAULT NULL,
  twitter varchar(256) DEFAULT NULL,
  facebook varchar(256) DEFAULT NULL,
  linkedin varchar(256) DEFAULT NULL,
  worth int(7) DEFAULT NULL,
  lastedit date DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Usuarios';

--
-- Volcar la base de datos para la tabla 'user'
--

INSERT INTO `user` VALUES('root', 'Super administrador', 'goteo@doukeshi.org', 'e053011ca09aab4a67703ef2350514b3', 'Super administrador de la plataforma Goteo.org', '2011-03-16', 1, NULL, 'Super administrador de la plataforma Goteo.org', NULL, NULL, NULL, NULL, NULL, '2011-03-16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla 'worthcracy'
--

DROP TABLE IF EXISTS worthcracy;
CREATE TABLE worthcracy (
  id int(2) NOT NULL AUTO_INCREMENT,
  `name` tinytext NOT NULL,
  amount int(6) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Niveles de meritocracia';

--
-- Volcar la base de datos para la tabla 'worthcracy'
--

INSERT INTO worthcracy VALUES(1, 'Fan', 25);
INSERT INTO worthcracy VALUES(2, 'Patrocinador', 50);
INSERT INTO worthcracy VALUES(3, 'Apostador', 100);
INSERT INTO worthcracy VALUES(4, 'Abonado', 500);
INSERT INTO worthcracy VALUES(5, 'Visionario', 1000);

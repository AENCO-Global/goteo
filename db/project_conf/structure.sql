CREATE TABLE `project_conf` (
`project` VARCHAR( 50 ) NOT NULL ,
`noinvest` INT( 1 ) NOT NULL DEFAULT '0' COMMENT 'No se permiten m�s aportes',
PRIMARY KEY ( `project` )
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = 'Configuraciones para proyectos';

ALTER TABLE `project_conf` ADD `watch` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Vigilar el proyecto';
ALTER TABLE `project_conf` ADD `days_round1` INT(4) DEFAULT 40 COMMENT 'D�as que dura la primera ronda';
ALTER TABLE `project_conf` ADD `days_round2` INT(4) DEFAULT 40 COMMENT 'D�as que dura la segunda ronda';
ALTER TABLE `project_conf` ADD `one_round` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Si el proyecto tiene una unica ronda';

-- Charset
ALTER TABLE `project_conf` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `project_conf` CHANGE `project` `project` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

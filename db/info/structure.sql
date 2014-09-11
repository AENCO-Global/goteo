CREATE TABLE IF NOT EXISTS `info` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `node` VARCHAR(50) NOT NULL ,
  `title` tinytext,
  `text` longtext COMMENT 'texto de la entrada',
  `media` tinytext,
  `publish` BOOLEAN NOT NULL DEFAULT '0',
  `order` int(11) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Entradas about';

-- los alters
ALTER TABLE `info` ADD `legend` TEXT NULL ;

-- Campo calculado para im�genes de la galer�a
ALTER TABLE `info` ADD `gallery` VARCHAR( 2000 ) NULL COMMENT 'Galer�a de imagenes';

-- imagen principal
ALTER TABLE `info` ADD `image` VARCHAR( 255 ) NULL DEFAULT NULL COMMENT 'Imagen principal';

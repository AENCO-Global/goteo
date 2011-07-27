CREATE TABLE IF NOT EXISTS `icon_lang` (
`id` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`lang` varchar(2) NOT NULL,
`name` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
`description` TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
 UNIQUE KEY `id_lang` (`id`,`lang`)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

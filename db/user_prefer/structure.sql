CREATE TABLE IF NOT EXISTS `user_prefer` (
  `user` varchar(50) NOT NULL,
  `updates` int(1) NOT NULL DEFAULT 0,
  `threads` int(1) NOT NULL DEFAULT 0,
  `rounds` int(1) NOT NULL DEFAULT 0,
  `mailing` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Preferencias de notificaci�n de usuario';


-- campo para marcar que no quiere que se le de su email a los proyectos que cofinancia
ALTER TABLE `user_prefer` ADD `email` INT( 1 ) NOT NULL DEFAULT '0';

-- bloquear consejos de difusión
ALTER TABLE `user_prefer` ADD `tips` INT( 1 ) NOT NULL DEFAULT '0';

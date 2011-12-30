CREATE TABLE IF NOT EXISTS invest (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(50) NOT NULL,
  project varchar(50) NOT NULL,
  account varchar(256) NOT NULL,
  amount int(6) NOT NULL,
  `status` int(1) NOT NULL COMMENT '0 pendiente, 1 cobrado, 2 devuelto',
  anonymous tinyint(1) DEFAULT NULL,
  resign tinyint(1) DEFAULT NULL,
  invested date DEFAULT NULL,
  charged date DEFAULT NULL,
  returned date DEFAULT NULL,
  preapproval varchar(256) DEFAULT NULL COMMENT 'PreapprovalKey',
  payment varchar(256) DEFAULT NULL COMMENT 'PayKey',
  `transaction` varchar(256) DEFAULT NULL COMMENT 'TransactionId',
  `method` varchar(20) NOT NULL COMMENT 'Metodo de pago',
  PRIMARY KEY (id),
  UNIQUE KEY id (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Aportes monetarios a proyectos';

-- Alteraciones de la tabla original por si no se puede pasar el create de arriba
-- Cambiando ids num�ricos a SERIAL
ALTER TABLE `invest` CHANGE `id` `id` SERIAL NOT NULL AUTO_INCREMENT;
-- campo para guardar el codigo preapproval
ALTER TABLE `invest` ADD `code` VARCHAR( 256 ) NULL COMMENT 'PreapprovalKey';
ALTER TABLE `invest` CHANGE `code` `preapproval` VARCHAR( 256 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'PreapprovalKey';

ALTER TABLE `invest` ADD `payment` VARCHAR( 256 ) NULL COMMENT 'PayKey';
ALTER TABLE `invest` ADD `transaction` VARCHAR( 256 ) NULL COMMENT 'PaypalId';

ALTER TABLE `invest` ADD `account` VARCHAR( 256 ) NOT NULL AFTER `project` ;

ALTER TABLE `invest` ADD `method` VARCHAR( 20 ) NOT NULL COMMENT 'Metodo de pago';

-- Para aportes manuales y aportes de campa�a
ALTER TABLE `invest` ADD `admin` VARCHAR( 50 ) NULL COMMENT 'Admin que cre� el aporte manual';
ALTER TABLE `invest` ADD `campaign` BIGINT UNSIGNED NULL COMMENT 'campa�a de la que forma parte este dinero';

-- Para aportes de capital riego
ALTER TABLE `invest` CHANGE `campaign` `campaign` INT( 1 ) UNSIGNED NULL DEFAULT NULL COMMENT 'si es un aporte de capital riego';
ALTER TABLE `invest` ADD `drops` BIGINT( 20 ) UNSIGNED NULL DEFAULT NULL COMMENT 'id del aporte que provoca este riego';
ALTER TABLE `invest` ADD `droped` BIGINT( 20 ) UNSIGNED NULL DEFAULT NULL COMMENT 'id del riego generado por este aporte';
ALTER TABLE `invest` ADD `call` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'campa�a dedonde sale el dinero';

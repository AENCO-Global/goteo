-- Glossary 

-- Campo calculado para im�genes de la galer�a
ALTER TABLE `glossary` ADD `gallery` VARCHAR( 2000 ) NULL COMMENT 'Galer�a de imagenes';

-- imagen principal
ALTER TABLE `glossary` ADD `image` VARCHAR( 255 ) NULL DEFAULT NULL COMMENT 'Imagen principal';


-- Info

-- Campo calculado para im�genes de la galer�a
ALTER TABLE `info` ADD `gallery` VARCHAR( 2000 ) NULL COMMENT 'Galer�a de imagenes';

-- imagen principal
ALTER TABLE `info` ADD `image` VARCHAR( 255 ) NULL DEFAULT NULL COMMENT 'Imagen principal';


-- Post

-- Campo calculado para im�genes de la galer�a
ALTER TABLE `post` ADD `gallery` VARCHAR( 2000 ) NULL COMMENT 'Galer�a de imagenes';


-- Proyecto

-- Campo calculado para im�genes de la galer�a  (mayor porque tiene secciones)
ALTER TABLE `project` ADD `gallery` VARCHAR( 10000 ) NULL COMMENT 'Galer�a de imagenes';


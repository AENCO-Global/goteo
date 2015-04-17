<?php
//Main path
define('GOTEO_PATH', realpath(dirname(__DIR__)) . '/');
//Public Web path
define('GOTEO_WEB_PATH', __DIR__ . '/');
//Log path
define('GOTEO_LOG_PATH', GOTEO_PATH . 'var/logs/');
//Uploads
define('GOTEO_DATA_PATH', GOTEO_PATH . 'var/data/');
//cache
define('GOTEO_CACHE_PATH', GOTEO_PATH . 'var/cache/');

require_once GOTEO_PATH . 'src/Goteo/Core/Helpers.php';
require_once GOTEO_PATH . 'src/autoload.php';


//Cache dir in libs
\Goteo\Library\Cacher::setCacheDir(GOTEO_CACHE_PATH);
//Default views
//General views
\Goteo\Application\View::factory(GOTEO_PATH . 'templates'); //Old system fallback
//new templates
\Goteo\Application\View::addFolder('base',  GOTEO_WEB_PATH . 'templates', true);

// LEGACY
\Goteo\Core\View::addViewPath(GOTEO_WEB_PATH . 'view');
//NormalForm views
\Goteo\Core\View::addViewPath(GOTEO_PATH . 'src/Goteo/Library/NormalForm/view');
//SuperForm views
\Goteo\Core\View::addViewPath(GOTEO_PATH . 'src/Goteo/Library/SuperForm/view');
//TODO: PROVISIONAL
//add view
\Goteo\Core\View::addViewPath(GOTEO_WEB_PATH . 'nodesys');

/**
 * Carga de configuración local si existe
 * Si no se carga el real (si existe)
**/
$config_file = GOTEO_PATH . 'config/settings.php';
if (file_exists($config_file)) { //en .gitignore
    require $config_file;
} else {
    $demo_config_file = GOTEO_PATH . 'config/demo-settings.php';
    die('<h2>No se encuentra el archivo de configuraci&oacute;n <code><strong>config/settings.php</strong></code>, debes crear este archivo en el subdirectorio config/.</h2><p>Puedes usar el siguiente c&oacute;digo modificado con los credenciales adecuados.</p>' . highlight_string(file_get_contents($demo_config_file), true) );
}

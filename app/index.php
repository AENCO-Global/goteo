<?php

use Goteo\Core\Resource,
    Goteo\Core\Error,
    Goteo\Core\Redirection,
    Goteo\Core\ACL,
    Goteo\Core\NodeSys,
    Goteo\Library\Text,
    Goteo\Library\Message,
    Goteo\Library\Lang;

define('START_TIME', microtime(true));
//si el parametro GET vale:
// 0 se muestra estadísticas de SQL, pero no los logs
// 1 se hace un log con las queries no cacheadas
// 2 se hace un log con las queries no cacheadas y también las cacheadas
if(isset($_GET['sqldebug']) && !defined('DEBUG_SQL_QUERIES')) {
    define('DEBUG_SQL_QUERIES', intval($_GET['sqldebug']));
}

require_once __DIR__ . '/config.php';

//clean all caches if requested
if(isset($_GET['cleancache'])) {
    \Goteo\Core\Model::cleanCache();
}


/*
 * Pagina de en mantenimiento
 */
if (GOTEO_MAINTENANCE === true && $_SERVER['REQUEST_URI'] != '/about/maintenance'
     && !isset($_POST['Num_operacion'])
    ) {
    header('Location: /about/maintenance');
    die;
}


/**
 * Sesión.
 */
session_name('goteo-'.GOTEO_ENV);
session_start();
if(!$_SESSION['init_time']) {
    $_SESSION['init_time'] = START_TIME;
}
if(START_TIME > $_SESSION['init_time'] + (defined('GOTEO_SESSION_TIME') ? GOTEO_SESSION_TIME : 3600 )) {
    // session expirada
    session_unset();
    session_destroy();
    session_write_close();
    session_regenerate_id(true);
    session_start();
    Message::Info(Text::get('session-expired'));
}

/* Sistema nodos */
// Get Node and check it
$host = strtok($_SERVER['HTTP_HOST'], '.');

if (NodeSys::isValid($host)) {
    define('NODE_ID', $host);
} else {
    define('NODE_ID', GOTEO_NODE);
}
// configuracion estatica
$conf_file = 'nodesys/'.NODE_ID.'/config.php';
if (file_exists($conf_file)) {
    require_once $conf_file;
}
/* Fin inicializacion nodo */

/* Iniciación constantes *_URL */

// Verificar settings
if (defined('SITE_URL') || !defined('GOTEO_URL'))
    die('En los settings hay que definir la constante GOTEO_URL en vez de SITE_URL.');

// if ssl enabled
$SSL = (defined('GOTEO_SSL') && GOTEO_SSL === true );

// segun sea nodo o central
$SITE_URL = (NODE_ID != GOTEO_NODE) ? NODE_URL : GOTEO_URL;
$raw_url = str_replace('http:', '', $SITE_URL);

// SEC_URL (siempre https, si ssl activado)
define('SEC_URL', ($SSL) ? 'https:'.$raw_url : $SITE_URL);

// si estamos en entorno seguro
define('HTTPS_ON', ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ));

// SITE_URL, según si estamos en entorno seguro o si el usuario esta autenticado
if ($SSL
    && (\HTTPS_ON
        || $_SESSION['user'] instanceof \Goteo\Model\User)
    ) {
    define('SITE_URL', SEC_URL);
} else {
    define('SITE_URL', $SITE_URL);
}

// si el usuario ya está validado debemos mantenerlo en entorno seguro
// usamos la funcionalidad de salto entre nodos para mantener la sesión
if ($SSL
    && $_SESSION['user'] instanceof \Goteo\Model\User
    && !\HTTPS_ON
) {
    header('Location: ' . SEC_URL . $_SERVER['REQUEST_URI']);
    die;
}
/* Fin inicializacion constantes *_URL */


// Get URI without query string
$uri = strtok($_SERVER['REQUEST_URI'], '?');

// Get requested segments
$segments = preg_split('!\s*/+\s*!', $uri, -1, \PREG_SPLIT_NO_EMPTY);

// Normalize URI
$uri = '/' . implode('/', $segments);

// set Lang (forzado para el cron y el admin)
$forceLang = (strpos($uri, 'cron') !== false || strpos($uri, 'admin') !== false) ? 'es' : null;
Lang::set($forceLang);

// cambiamos el locale
\setlocale(\LC_TIME, Lang::locale());
/* Cookie para la ley de cookies */
if (empty($_COOKIE['goteo_cookies'])) {
    setcookie("goteo_cookies", '1', time() + 3600 * 24 * 365);
    Message::Info(Text::get('message-cookies'));
}

try {
    // Check permissions on requested URI
    if (!ACL::check($uri)) {

        //si es directorio data/cache se supone que es un archivo cache que no existe y que hay que generar
        if(strpos($uri, 'data/cache/') !== false && $segments && $segments[3]) {
            //simularemos la llamada al controlador img: img/XXXxXXX/imagen.jpg
            array_shift($segments);
            $segments[0] = 'img';
        }
        //si es un cron (ejecutandose) con los parámetros adecuados, no redireccionamos
        elseif ((strpos($uri, 'cron') !== false || strpos($uri, 'system') !== false) && strcmp($_GET[md5(CRON_PARAM)], md5(CRON_VALUE)) === 0) {
            define('CRON_EXEC', true);
        } else {
            Message::Info(Text::get('user-login-required-access'));
            throw new Redirection(SEC_URL."/user/login/?return=".rawurlencode($uri));
        }
    }

    // Get controller name
    if (!empty($segments) && class_exists("Goteo\\Controller\\{$segments[0]}")) {
        // Take first segment as controller
        $controller = array_shift($segments);
    } else {
        $controller = 'index';
    }

    // Continue
    try {

        $class = new ReflectionClass("Goteo\\Controller\\{$controller}");

        if (!empty($segments) && $class->hasMethod($segments[0])) {
            $method = array_shift($segments);
        } else {
            // Try default method
            $method = 'index';
        }

        // ReflectionMethod
        $method = $class->getMethod($method);

        // Number of params defined in method
        $numParams = $method->getNumberOfParameters();
        // Number of required params
        $reqParams = $method->getNumberOfRequiredParameters();
        // Given params
        $gvnParams = count($segments);

        if ($gvnParams >= $reqParams && (!($gvnParams > $numParams && $numParams <= $reqParams))) {

            // Try to instantiate
            $instance = $class->newInstance();


            // Invoke method
            $result = $method->invokeArgs($instance, $segments);

            if ($result === null) {
                // Start output buffer
                ob_start();
                // Get buffer contents
                $result = ob_get_contents();
                ob_end_clean();
            }


            if ($result instanceof Resource\MIME) {
                $mime_type = $result->getMIME();
                header("Content-type: $mime_type");
                if($mime_type == 'text/html') {
                    //renovar tiempo de sesion si es tipo html
                    $_SESSION['init_time'] = START_TIME;
                    $_SESSION['init_time_advised'] = false;
                }
            }

            //esto suele llamar a un metodo magic: __toString de la vista View
            echo $result;

            // if($mime_type == "text/html" && GOTEO_ENV != 'real') {
            if($mime_type == "text/html"){

                if(defined('DEBUG_SQL_QUERIES')) {
                    echo '<div style="position:static;top:10px;left:10px;padding:10px;z-index:1000;background:rgba(255,255,255,0.6)">[<a href="#" onclick="$(this).parent().remove();return false;">cerrar</a>]<pre>';
                    echo '<b>Server IP:</b> '.$_SERVER['SERVER_ADDR'] . '<br>';
                    echo '<b>Client IP:</b> '.$_SERVER['REMOTE_ADDR'] . '<br>';
                    echo '<b>X-Forwarded-for:</b> '.$_SERVER['HTTP_X_FORWARDED_FOR'] . '<br>';
                    echo '<b>SQL STATS:</b><br> '.print_r(Goteo\Core\DB::getQueryStats(), 1);
                    echo '<b>END:</b> '.(microtime(true) - START_TIME ) . 's';
                    echo '</pre></div>';
                }

               echo '<!-- '.(microtime(true) - START_TIME ) . 's -->';
            }


            // Farewell
            die;

        }

    } catch (\ReflectionException $e) {}

    throw new Error(Error::NOT_FOUND);

} catch (Redirection $redirection) {
    $url = $redirection->getURL();
    $code = $redirection->getCode();
    header("Location: {$url}");

} catch (Error $error) {
    include "view/error.html.php";

} catch (Exception $exception) {
    // Default error (500)
    include "view/error.html.php";
}

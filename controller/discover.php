<?php

namespace Goteo\Controller {

    use Goteo\Core\View,
        Goteo\Model,
        Goteo\Core\Redirection,
        Goteo\Library\Text,
        Goteo\Library\Message,
        Goteo\Library\Listing;

    class Discover extends \Goteo\Core\Controller {

        public static $types = array(
                'popular',
                'recent',
                'success',
                'outdate',
                'archive',
                'fulfilled'
            );

        public function __construct() {
            //activamos la cache para todo el controlador index
            \Goteo\Core\DB::cache(true);
        }

        /*
         * Descubre proyectos, página general
         */
        public function index () {

            $types = self::$types;

            $viewData = array(
                'lists' => array()
            );

            if (\NODE_ID != \GOTEO_NODE) {
                $types[] = 'others';
            }

            // cada tipo tiene sus grupos
            foreach ($types as $type) {
                $projects = Model\Project::published($type, 33);
                if (empty($projects)) continue;
                // random para exitosos y retorno cumplido
                if ($type == 'success' || $type == 'fulfilled') shuffle ($projects);

                $viewData['lists'][$type] = Listing::get($projects);
            }

            return new View(
                'view/discover/index.html.php',
                $viewData
             );

        }

        /*
         * Descubre proyectos, resultados de búsqueda
         */
        public function results ($category = null) {

            $message = '';
            $results = null;

            // si recibimos categoria por get emulamos post con un parametro 'category'
            if (!empty($category)) {
                $_POST['category'][] = $category;
            }

			if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['query']) && !isset($category)) {
                $errors = array();

                $query = \strip_tags($_GET['query']); // busqueda de texto

                $results = \Goteo\Library\Search::params(array('query' => $query), false, 33);

			} elseif (($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['searcher']) || !empty($category))) {

                // vamos montando $params con los 3 parametros y las opciones marcadas en cada uno
                $params = array('category'=>array(), 'location'=>array(), 'reward'=>array());

                foreach ($params as $param => $empty) {
                    foreach ($_POST[$param] as $key => $value) {
                        if ($value == 'all') {
                            $params[$param] = array();
                            break;
                        }
                        $params[$param][] = "'{$value}'";
                    }
                }

                if (isset($_GET['status'])) {
                    $params['status'] = $_GET['status'];
                }


                $query = \strip_tags($_POST['query']); // busqueda de texto
                $params['query'] = $query;

                // para cada parametro, si no hay ninguno es todos los valores
                $results = \Goteo\Library\Search::params($params, false, 33);

            } else {
                throw new Redirection('/discover', Redirection::PERMANENT);
            }

            return new View(
                'view/discover/results.html.php',
                array(
                    'message' => $message,
                    'results' => $results,
                    'query'   => $query,
                    'params'  => $params
                )
             );

        }

        /*
         * Descubre proyectos, ver todos los de un tipo
         */
        public function view ($type = 'all') {

            $types = self::$types;
            $types[] = 'all';
            if (\NODE_ID != \GOTEO_NODE) {
                $types[] = 'others';
            }

            if (!in_array($type, $types)) {
                throw new Redirection('/discover');
            }

            $viewData = array();

            // segun el tipo cargamos el título de la página
            $viewData['title'] = Text::get('discover-group-'.$type.'-header');

            $page = (is_numeric($_GET['page'])) ? $_GET['page'] : 1;
            $items_per_page = 9;
            $viewData['list'] = Model\Project::published($type, $items_per_page, $page, $pages);
            $viewData['pages'] = $pages;
            
            // segun el tipo cargamos la lista
            if (isset($_GET['list'])) {

                return new View(
                    'view/discover/list.html.php',
                    $viewData
                 );
                
            } else {
                
                // random para retorno cumplido
                if ($type == 'fulfilled') {
                    shuffle($viewData['list']);
                }

                return new View(
                    'view/discover/view.html.php',
                    $viewData
                 );

            }
        }

        /*
         * Alias a mostrar todas las convocatorias
         */
        public function call () {
            throw new Redirection('/discover/calls');
        }

         /*
         * Ver todas las convocatorias
         */
        public function calls () {

            $viewData = array();

            // segun el tipo cargamos el título de la página
            $viewData['title'] = Text::html('discover-calls-header');

            // segun el tipo cargamos la lista
            $viewData['list']  = Model\Call::getActive(null, true);


            return new View(
                'view/discover/calls.html.php',
                $viewData
             );

        }


        /*
         * proyectos recomendados por usuario padrino (patron)
         */
        public function patron ($user) {

            $viewData = array();

            // título de la página
            $viewData['title'] = Text::get('discover-patron-header', $user);

            // segun el tipo cargamos la lista
            $viewData['list']  = Model\Patron::getList($user);


            return new View(
                'view/discover/patron.html.php',
                $viewData
             );

        }

    }

}

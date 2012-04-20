<?php

namespace Goteo\Controller {

    use Goteo\Library\Page,
        Goteo\Core\Redirection,
        Goteo\Core\View,
        Goteo\Model,
        Goteo\Library\Text,
        Goteo\Library\Mail,
        Goteo\Library\Template;

    class About extends \Goteo\Core\Controller {
        
        public function index ($id = null) {

            // si llegan a la de mantenimiento sin estar en mantenimiento
            if ($id == 'maintenance' && GOTEO_MAINTENANCE !== true) {
                $id = 'credits';
            }

            // paginas especificas
            if ($id == 'faq' || $id == 'contact') {
                throw new Redirection('/'.$id, Redirection::TEMPORARY);
            }

            // en estos casos se usa el contenido de goteo
            if ($id == 'howto' || $id == 'call') {
                $page = Page::get($id);
                return new View(
                    'view/about/howto.html.php',
                    array(
                        'name' => $page->name,
                        'description' => $page->description,
                        'content' => $page->content
                    )
                 );
            }

            // el tipo de contenido de la pagina about es diferente
            if ( empty($id) ||
                 $id == 'about' ||
                ( NODE_ID != \GOTEO_NODE
                 && !\in_array($id, array('about', 'contact', 'press', 'service', 'maintenance'))
                 )
                ) {
                $id = 'about';

                if (NODE_ID == \GOTEO_NODE) {
                    $posts = Model\Info::getAll(true, \GOTEO_NODE);

                    return new View(
                        'view/about/info.html.php',
                        array(
                            'posts' => $posts
                        )
                     );
                } else {

                    return \Goteo\Controller\Index::node_index('about');
/*
                    $page = Page::get($id, \NODE_ID);

                    return new View(
                        'view/node/about.html.php',
                        array(
                            'name' => $page->name,
                            'description' => $page->description,
                            'content' => $page->content
                        )
                     );
 * 
 */
                }


            }

            // resto de casos
            $page = Page::get($id);

            return new View(
                'view/about/sample.html.php',
                array(
                    'name' => $page->name,
                    'description' => $page->description,
                    'content' => $page->content
                )
             );

        }
        
    }
    
}
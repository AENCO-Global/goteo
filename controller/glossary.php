<?php

namespace Goteo\Controller {

    use Goteo\Core\View,
        Goteo\Model;

    class Glossary extends \Goteo\Core\Controller {
        
        public function index () {

            // Términos por página
            $tpp = 5;

            // indice de letras
            $index = array();

            // sacamos todo el glosario
            $glossary = Model\Glossary::getAll();

            //recolocamos los post para la paginacion
            $p = 1;
            $page = 1;
            $posts = array();
            foreach ($glossary as $id=>$post) {

                // tratar el texto para las entradas
                $post->text = nl2br(str_replace(array('%SITE_URL%'), array(SITE_URL), $post->text));
                
                $posts[] = $post;

                // y la inicial en el indice
                $letra = \strtolower($post->title[0]);
                $index[$letra][] = (object) array(
                    'title' => $post->title,
                    'url'   => '/glossary/?page='.$page.'#term' . $post->id
                );

                $p++;
                if ($p > $tpp) {
                    $p = 1;
                    $page++;
                }
            }

            return new View(
                'view/glossary/index.html.php',
                array(
                    'tpp'   => $tpp,
                    'index' => $index,
                    'posts' => $posts
                )
             );

        }
        
    }
    
}
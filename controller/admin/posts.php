<?php

namespace Goteo\Controller\Admin {

    use Goteo\Core\View,
        Goteo\Core\Redirection,
        Goteo\Core\Error,
		Goteo\Library\Feed,
        Goteo\Model;

    class Posts {

        public static function process ($action = 'list', $id = null) {

            $errors = array();

            if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'add') {

                // esto es para añadir una entrada en la portada
                // objeto
                $post = new Model\Post(array(
                    'id' => $_POST['post'],
                    'order' => $_POST['order'],
                    'home' => $_POST['home']
                ));

				if ($post->update($errors)) {
                    $success[] = 'Entrada colocada en la portada correctamente';
				}
				else {
                    return new View(
                        'view/admin/index.html.php',
                        array(
                            'folder' => 'posts',
                            'file' => 'add',
                            'action' => 'add',
                            'post' => $post,
                            'errors' => $errors
                        )
                    );
				}
			}

            switch ($action) {
                case 'up':
                    Model\Post::up($id, 'home');
                    break;
                case 'down':
                    Model\Post::down($id, 'home');
                    break;
                case 'add':
                    // siguiente orden
                    $next = Model\Post::next('home');

                    return new View(
                        'view/admin/index.html.php',
                        array(
                            'folder' => 'posts',
                            'file' => 'add',
                            'action' => 'add',
                            'post' => (object) array('order' => $next)
                        )
                    );
                    break;
                case 'edit':
                    throw new Redirection('/admin/blog');
                    break;
                case 'remove':
                    // se quita de la portada solamente
                    Model\Post::remove($id, 'home');
                    break;
            }

            $posts = Model\Post::getAll('home');

            return new View(
                'view/admin/index.html.php',
                array(
                    'folder' => 'posts',
                    'file' => 'list',
                    'posts' => $posts,
                    'errors' => $errors,
                    'success' => $success
                )
            );
            
        }

    }

}

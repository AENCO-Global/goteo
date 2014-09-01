<?php

namespace Goteo\Controller\Admin {

    use Goteo\Core\View,
        Goteo\Core\Redirection,
        Goteo\Core\Error,
		Goteo\Library\Text,
		Goteo\Library\Feed,
		Goteo\Library\Message,
        Goteo\Model;

    class Sponsors {

        public static function process ($action = 'list', $id = null) {

            $node = isset($_SESSION['admin_node']) ? $_SESSION['admin_node'] : \GOTEO_NODE;

            $model = 'Goteo\Model\Sponsor';
            $url = '/admin/sponsors';

            $errors = array();

            switch ($action) {
                case 'add':
                    return new View(
                        'view/admin/index.html.php',
                        array(
                            'folder' => 'base',
                            'file' => 'edit',
                            'data' => (object) array('order' => $model::next($node), 'node' => $node ),
                            'form' => array(
                                'action' => "$url/edit/",
                                'submit' => array(
                                    'name' => 'update',
                                    'label' => 'Añadir'
                                ),
                                'fields' => array (
                                    'id' => array(
                                        'label' => '',
                                        'name' => 'id',
                                        'type' => 'hidden'

                                    ),
                                    'node' => array(
                                        'label' => '',
                                        'name' => 'node',
                                        'type' => 'hidden'

                                    ),
                                    'name' => array(
                                        'label' => 'Patrocinador',
                                        'name' => 'name',
                                        'type' => 'text'
                                    ),
                                    'url' => array(
                                        'label' => 'Enlace',
                                        'name' => 'url',
                                        'type' => 'text',
                                        'properties' => 'size=100'
                                    ),
                                    'image' => array(
                                        'label' => 'Logo',
                                        'name' => 'image',
                                        'type' => 'image'
                                    ),
                                    'order' => array(
                                        'label' => 'Posición',
                                        'name' => 'order',
                                        'type' => 'text'
                                    )
                                )

                            )
                        )
                    );

                    break;
                case 'edit':

                    // gestionar post
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                        // instancia
                        $item = new $model(array(
                            'id' => $_POST['id'],
                            'name' => $_POST['name'],
                            'node' => $_POST['node'],
                            'image' => $_POST['image'],
                            'url' => $_POST['url'],
                            'order' => $_POST['order']
                        ));

                        // tratar si quitan la imagen
                        if (isset($_POST['image-' . md5($item->image) .  '-remove'])) {
                            $image = Model\Image::get($item->image);
                            $image->remove($errors);
                            $item->image = null;
                            $removed = true;
                        }

                        // tratar la imagen y ponerla en la propiedad image
                        if(!empty($_FILES['image']['name'])) {
                            $item->image = $_FILES['image'];
                        }

                        if ($item->save($errors)) {
                            Message::Info('Datos grabados correctamente');
                            throw new Redirection($url);
                        } else {
                            Message::Error('No se han podido grabar los datos. ' . implode(', ', $errors));
                        }
                    } else {
                        $item = $model::get($id);
                    }

                    return new View(
                        'view/admin/index.html.php',
                        array(
                            'folder' => 'base',
                            'file' => 'edit',
                            'data' => $item,
                            'form' => array(
                                'action' => "$url/edit/$id",
                                'submit' => array(
                                    'name' => 'update',
                                    'label' => Text::get('regular-save')
                                ),
                                'fields' => array (
                                    'id' => array(
                                        'label' => '',
                                        'name' => 'id',
                                        'type' => 'hidden'

                                    ),
                                    'node' => array(
                                        'label' => '',
                                        'name' => 'node',
                                        'type' => 'hidden'

                                    ),
                                    'name' => array(
                                        'label' => 'Patrocinador',
                                        'name' => 'name',
                                        'type' => 'text'
                                    ),
                                    'url' => array(
                                        'label' => 'Enlace',
                                        'name' => 'url',
                                        'type' => 'text',
                                        'properties' => 'size=100'
                                    ),
                                    'image' => array(
                                        'label' => 'Logo',
                                        'name' => 'image',
                                        'type' => 'image'
                                    ),
                                    'order' => array(
                                        'label' => 'Posición',
                                        'name' => 'order',
                                        'type' => 'text'
                                    )
                                )

                            )
                        )
                    );

                    break;
                case 'up':
                    $model::up($id, $node);
                    throw new Redirection($url);
                    break;
                case 'down':
                    $model::down($id, $node);
                    throw new Redirection($url);
                    break;
                case 'remove':
                    if ($model::delete($id)) {
                        Message::Info('Se ha eliminado el registro');
                        throw new Redirection($url);
                    } else {
                        Message::Info('No se ha podido eliminar el registro');
                    }
                    break;
            }

            return new View(
                'view/admin/index.html.php',
                array(
                    'folder' => 'base',
                    'file' => 'list',
                    'addbutton' => 'Nuevo patrocinador',
                    'data' => $model::getAll($node),
                    'columns' => array(
                        'edit' => '',
                        'name' => 'Patrocinador',
                        'url' => 'Enlace',
                        'image' => 'Imagen',
                        'order' => 'Posición',
                        'up' => '',
                        'down' => '',
                        'remove' => ''
                    ),
                    'url' => "$url"
                )
            );
            
        }

    }

}

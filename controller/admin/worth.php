<?php

namespace Goteo\Controller\Admin {

    use Goteo\Core\View,
        Goteo\Core\Redirection,
        Goteo\Core\Error,
		Goteo\Library\Feed,
        Goteo\Library\Worth as WorthLib;

    class Worth {

        public static function process ($action = 'list', $id = null) {

            $errors = array();

            if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'edit') {

                // instancia
                $data = array(
                    'id' => $_POST['id'],
                    'name' => $_POST['name'],
                    'amount' => $_POST['amount']
                );

				if (WorthLib::save($data, $errors)) {
                    $action = 'list';
                    $success[] = 'Nivel de meritocracia modificado';

                    // Evento Feed
                    $log = new Feed();
                    $log->populate('modificacion de meritocracia (admin)', '/admin/worth',
                        \vsprintf("El admin %s ha %s el nivel de meritocrácia %s", array(
                            Feed::item('user', $_SESSION['user']->name, $_SESSION['user']->id),
                            Feed::item('relevant', 'Modificado'),
                            Feed::item('project', $icon->name)
                    )));
                    $log->doAdmin('admin');
                    unset($log);
				}
				else {
                    return new View(
                        'view/admin/index.html.php',
                        array(
                            'folder' => 'worth',
                            'file' => 'edit',
                            'action' => 'edit',
                            'worth' => (object) $data,
                            'errors' => $errors
                        )
                    );
				}
			}

            switch ($action) {
                case 'edit':
                    $worth = WorthLib::getAdmin($id);

                    return new View(
                        'view/admin/index.html.php',
                        array(
                            'folder' => 'worth',
                            'file' => 'edit',
                            'action' => 'edit',
                            'worth' => $worth
                        )
                    );
                    break;
            }

            $worthcracy = WorthLib::getAll();

            return new View(
                'view/admin/index.html.php',
                array(
                    'folder' => 'worth',
                    'file' => 'list',
                    'worthcracy' => $worthcracy,
                    'errors' => $errors,
                    'success' => $success
                )
            );
            
        }

    }

}

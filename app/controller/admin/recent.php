<?php

namespace Goteo\Controller\Admin {

    use Goteo\Core\View,
        Goteo\Core\Redirection,
        Goteo\Core\Error,
		Goteo\Library\Message,
		Goteo\Library\Feed,
        Goteo\Model;

    class Recent {

        public static function process ($action = 'list', $id = null) {

            $node = isset($_SESSION['admin_node']) ? $_SESSION['admin_node'] : \GOTEO_NODE;
            
            $feed = empty($_GET['feed']) ? 'all' : $_GET['feed'];

            $items = Feed::getAll($feed, 'admin', 50, $node);

            return new View(
                'view/admin/index.html.php',
                array(
                    'folder' => 'recent',
                    'file' => $action,
                    'feed' => $feed,
                    'items' => $items
                )
            );

        }

    }

}

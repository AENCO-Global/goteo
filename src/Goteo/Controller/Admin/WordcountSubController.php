<?php
/**
 * Cuenta palabras...
 */
namespace Goteo\Controller\Admin;

use Goteo\Application\Config;

class WordcountSubController extends AbstractSubController {

    static protected $labels = array (
      'list' => 'Listando',
    );


    static protected $label = 'Conteo de palabras';


    /**
     * Overwrite some permissions
     * @inherit
     */
    static public function isAllowed(\Goteo\Model\User $user, $node) {
        // Only central node and superadmins allowed here
        if( ! Config::isMasterNode($node) || !$user->hasRoleInNode($node, ['superadmin', 'root']) ) return false;
        return parent::isAllowed($user, $node);
    }

    public function listAction($id = null, $subaction = null) {
        // Action code should go here instead of all in one process funcion
        return call_user_func_array(array($this, 'process'), array('list', $id, $this->getFilters(), $subaction));
    }


    public function process ($action = 'list', $id = null) {

        $wordcount = array();

        return array(
                'folder' => 'base',
                'file' => 'wordcount',
                'wordcount' => $wordcount
        );

    }

}


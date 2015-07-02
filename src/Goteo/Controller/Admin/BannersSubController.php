<?php
/**
 * Gestion de banners
 */
namespace Goteo\Controller\Admin;

use Goteo\Application\Message;
use Goteo\Application\Config,
	Goteo\Application\Session,
	Goteo\Library\Feed,
    Goteo\Model;

class BannersSubController extends AbstractSubController {

    static protected $labels = array (
      'list' => 'Listando',
      'details' => 'Detalles del aporte',
      'update' => 'Cambiando el estado al aporte',
      'add' => 'Nuevo Banner',
      'move' => 'Reubicando el aporte',
      'execute' => 'Ejecución del cargo',
      'cancel' => 'Cancelando aporte',
      'report' => 'Informe de proyecto',
      'viewer' => 'Viendo logs',
      'edit' => 'Editando Banner',
      'translate' => 'Traduciendo Banner',
    );


    static protected $label = 'Banners';

    /**
     * Overwrite some permissions
     * @inherit
     */
    static public function isAllowed(\Goteo\Model\User $user, $node) {
        // Only central node or superadmins allowed here
        if( ! (Config::isMasterNode($node) || $user->hasRoleInNode($node, ['superadmin', 'root'])) ) return false;
        return parent::isAllowed($user, $node);
    }

    public function translateAction($id = null, $subaction = null) {
        // Action code should go here instead of all in one process funcion
        return call_user_func_array(array($this, 'process'), array('translate', $id, $this->getFilters(), $subaction));
    }


    public function editAction($id = null, $subaction = null) {
        // Action code should go here instead of all in one process funcion
        return call_user_func_array(array($this, 'process'), array('edit', $id, $this->getFilters(), $subaction));
    }


    public function addAction($id = null, $subaction = null) {
        // Action code should go here instead of all in one process funcion
        return call_user_func_array(array($this, 'process'), array('add', $id, $this->getFilters(), $subaction));
    }

    public function activeAction($id = null, $subaction = null) {
        // Action code should go here instead of all in one process funcion
        return call_user_func_array(array($this, 'process'), array('active', $id, $this->getFilters(), $subaction));
    }

    public function upAction($id = null, $subaction = null) {
        // Action code should go here instead of all in one process funcion
        return call_user_func_array(array($this, 'process'), array('up', $id, $this->getFilters(), $subaction));
    }

    public function downAction($id = null, $subaction = null) {
        // Action code should go here instead of all in one process funcion
        return call_user_func_array(array($this, 'process'), array('down', $id, $this->getFilters(), $subaction));
    }

    public function removeAction($id = null, $subaction = null) {
        // Action code should go here instead of all in one process funcion
        return call_user_func_array(array($this, 'process'), array('remove', $id, $this->getFilters(), $subaction));
    }


    public function listAction($id = null, $subaction = null) {
        // Action code should go here instead of all in one process funcion
        return call_user_func_array(array($this, 'process'), array('list', $id, $this->getFilters(), $subaction));
    }


    public function process ($action = 'list', $id = null, $filters = array(), $flag = null) {

        $errors = array();

        $node = $this->node;

        if ($this->isPost()) {

            // objeto
            $banner = $id ? Model\Banner::get($id) : new Model\Banner;
            $banner->node = $node;
            $banner->project = $this->getPost('item');
            $banner->title = $this->getPost('title');
            $banner->description = $this->getPost('description');
            $banner->url = $this->getPost('url');
            $banner->order = $this->getPost('order');
            $banner->active = $this->getPost('active');

            // tratar si quitan la imagen
            if ($this->getPost('image-' . $banner->image->hash .  '-remove')) {
                if ($banner->image instanceof Model\Image) $banner->image->remove($errors);
                $banner->image = null;
            }

            // nueva imagen
            if(!empty($_FILES['image']['name'])) {
                if ($banner->image instanceof Model\Image) $banner->image->remove($errors);
                $banner->image = $_FILES['image'];
            } else {
                $banner->image = $banner->image->id;
            }

			if ($banner->save($errors)) {
                Message::info('Datos guardados');

                if ($this->getPost('action') == 'add') {
                    if(!empty($this->getPost('item')))
                        $projectData = Model\Project::getMini($this->getPost('item'));
                    // Evento Feed
                    $log = new Feed();
                    $log->setTarget($projectData->id);
                    $log->populate('nuevo banner de proyecto destacado en portada (admin)', '/admin/promote',
                        \vsprintf('El admin %s ha %s', array(
                        Feed::item('user', $this->user->name, $this->user->id),
                        Feed::item('relevant', 'Publicado un banner', '/')
                    )));
                    $log->doAdmin('admin');
                    unset($log);
                }

                // tratar si han marcado pendiente de traducir
                if ($this->hasPost('pending') && $this->getPost('pending') == 1
                    && !Model\Banner::setPending($banner->id, 'banner')) {
                    Message::error('NO se ha marcado como pendiente de traducir!');
                }
                
                return $this->redirect('/admin/banners');
			}
			else {
                Message::error(implode('<br />', $errors));

                switch ($this->getPost('action')) {
                    case 'add':
                        return array(
                                'folder' => 'banners',
                                'file' => 'edit',
                                'action' => 'add',
                                'banner' => $banner,
                                'autocomplete' => true
                        );
                        break;
                    case 'edit':
                        return array(
                                'folder' => 'banners',
                                'file' => 'edit',
                                'action' => 'edit',
                                'banner' => $banner,
                                'autocomplete' => true
                        );
                        break;
                }
			}
		}

        switch ($action) {
            case 'active':
                $set = $flag == 'on' ? true : false;
                Model\Banner::setActive($id, $set);
                return $this->redirect('/admin/banners');
                break;
            case 'up':
                Model\Banner::up($id, $node);
                return $this->redirect('/admin/banners');
                break;
            case 'down':
                Model\Banner::down($id, $node);
                return $this->redirect('/admin/banners');
                break;
            case 'remove':
                if (Model\Banner::delete($id)) {
                    Message::info('Banner quitado correctamente');
                } else {
                    Message::error('No se ha podido quitar el banner');
                }
                return $this->redirect('/admin/banners');
                break;
            case 'add':
                // siguiente orden
                $next = Model\Banner::next($node);

                return array(
                        'folder' => 'banners',
                        'file' => 'edit',
                        'action' => 'add',
                        'banner' => (object) array('order' => $next),
                        'autocomplete' => true
                );
                break;
            case 'edit':
                $banner = Model\Banner::get($id);

                return array(
                        'folder' => 'banners',
                        'file' => 'edit',
                        'action' => 'edit',
                        'banner' => $banner,
                        'autocomplete' => true
                );
                break;
        }


        $bannered = Model\Banner::getAll(false, $node);

        return array(
                'folder' => 'banners',
                'file' => 'list',
                'bannered' => $bannered,
                'node' => $node
        );

    }

}


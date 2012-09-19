<?php

namespace Goteo\Controller\Admin {

    use Goteo\Core\View,
        Goteo\Core\Redirection,
        Goteo\Core\Error,
		Goteo\Library\Message,
		Goteo\Library\Feed,
        Goteo\Library\Mail,
		Goteo\Library\Template,
        Goteo\Model;

    class Transcalls {

        public static function process ($action = 'list', $id = null, $filters = array()) {

            $errors  = array();

            switch ($action) {
                case 'add':
                    // convocatorias que están más allá de edición y con traducción deshabilitada
                    $availables = Model\User\Translate::getAvailables('call');
                case 'edit':
                case 'assign':
                case 'unassign':
                case 'send':

                    // a ver si tenemos convocatoria
                    if (empty($id) && !empty($_POST['call'])) {
                        $id = $_POST['call'];
                    }

                    if (!empty($id)) {
                        $call = Model\Call::getMini($id);
                    } elseif ($action != 'add') {
                        Message::Error('No hay convocatoria sobre la que operar');
                        throw new Redirection('/admin/transcalls');
                    }

                    // asignar o desasignar
                    // la id de revision llega en $id
                    // la id del usuario llega por get
                    $user = $_GET['user'];
                    if (!empty($user)) {
                        $userData = Model\User::getMini($user);

                        $assignation = new Model\User\Translate(array(
                            'item' => $call->id,
                            'type' => 'call',
                            'user' => $user
                        ));

                        switch ($action) {
                            case 'assign': // se la ponemos
                                $assignation->save($errors);
                                $what = 'Asignado';
                                break;
                            case 'unassign': // se la quitamos
                                $assignation->remove($errors);
                                $what = 'Desasignado';
                                break;
                        }

                        if (empty($errors)) {
                            // Evento Feed
                            $log = new Feed();
                            $log->setTarget($userData->id, 'user');
                            $log->populate($what . ' traduccion de convocatoria (admin)', '/admin/transcalls',
                                \vsprintf('El admin %s ha %s a %s la traducción de la convocatoria %s', array(
                                    Feed::item('user', $_SESSION['user']->name, $_SESSION['user']->id),
                                    Feed::item('relevant', $what),
                                    Feed::item('user', $userData->name, $userData->id),
                                    Feed::item('call', $call->name, $call->id)
                            )));
                            $log->doAdmin('admin');
                            unset($log);
                            
                            throw new Redirection('/admin/transcalls/edit/'.$call->id);
                        } else {
                            Message::Error(implode('<br />', $errors));
                        }

                        $action = 'edit';
                    }
                    // fin asignar o desasignar

                    // añadir o actualizar
                    // se guarda el idioma original y si la traducción está abierta o cerrada
                    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save'])) {

                        echo \trace($_POST);

                        // ponemos los datos que llegan
                        $sql = "UPDATE `call` SET lang = :lang, translate = 1 WHERE id = :id";
                        if (Model\Project::query($sql, array(':lang'=>$_POST['lang'], ':id'=>$id))) {
                            if ($action == 'add') {
                                Message::Info('La convocatoria '.$call->name.' se ha habilitado para traducir');
                            } else {
                                Message::Info('Datos de traducción actualizados');
                            }

                            if ($action == 'add') {

                                // Evento Feed
                                $log = new Feed();
                                $log->setTarget($call->id, 'call');
                                $log->populate('convocatoria habilitada para traducirse (admin)', '/admin/transcalls',
                                    \vsprintf('El admin %s ha %s la traducción de la convocatoria %s', array(
                                        Feed::item('user', $_SESSION['user']->name, $_SESSION['user']->id),
                                        Feed::item('relevant', 'Habilitado'),
                                        Feed::item('call', $call->name, $call->id)
                                )));
                                $log->doAdmin('admin');
                                unset($log);

                                throw new Redirection('/admin/transcalls/edit/'.$call->id);
                            }
                        } else {
                            Message::Error('Ha fallado al habilitar la traducción de la convocatoria ' . $call->name);
                        }
                    }

                    if ($action == 'send') {
                        // Informar al autor de que la traduccion está habilitada
                        // Obtenemos la plantilla para asunto y contenido

                        $template = Template::get(32);
                        // Sustituimos los datos
                        $subject = str_replace('%CALLNAME%', $call->name, $template->title);
                        $search  = array('%OWNERNAME%', '%CALLNAME%', '%SITEURL%');
                        $replace = array($call->user->name, $call->name, SITE_URL);
                        $content = \str_replace($search, $replace, $template->text);
                        // iniciamos mail
                        $mailHandler = new Mail();
                        $mailHandler->to = $call->user->email;
                        $mailHandler->toName = $call->user->name;
                        $mailHandler->subject = $subject;
                        $mailHandler->content = $content;
                        $mailHandler->html = true;
                        $mailHandler->template = $template->id;
                        if ($mailHandler->send()) {
                            Message::Info('Se ha enviado un email a <strong>'.$call->user->name.'</strong> a la dirección <strong>'.$project->user->email.'</strong>');
                        } else {
                            Message::Error('Ha fallado informar a <strong>'.$call->user->name.'</strong> de la posibilidad de traducción de su convocatoria');
                        }
                        unset($mailHandler);

                        $action = 'edit';
                    }


                    $call->translators = Model\User\Translate::translators($id, 'call');
                    $translators = Model\User::getAll(array('role'=>'translator'));
                    // añadimos al dueño del proyecto en el array de traductores
                    array_unshift($translators, $call->user);


                    return new View(
                        'view/admin/index.html.php',
                        array(
                            'folder' => 'transcalls',
                            'file'   => 'edit',
                            'action' => $action,
                            'availables' => $availables,
                            'translators' => $translators,
                            'call'=> $call
                        )
                    );

                    break;
                case 'close':
                    // la sentencia aqui mismo
                    // el campo translate de la convocatoria $id a false
                    $sql = "UPDATE `call` SET translate = 0 WHERE id = :id";
                    if (Model\Call::query($sql, array(':id'=>$id))) {
                        Message::Info('La traducción de la convocatoria '.$call->name.' se ha finalizado');

                        Model\Call::query("DELETE FROM user_translate WHERE type = 'call' AND item = :id", array(':id'=>$id));

                        // Evento Feed
                        $log = new Feed();
                        $log->setTarget($call->id, 'call');
                        $log->populate('traducción convocatoria finalizada (admin)', '/admin/transcalls',
                            \vsprintf('El admin %s ha dado por %s la traducción de la convocatoria %s', array(
                                Feed::item('user', $_SESSION['user']->name, $_SESSION['user']->id),
                                Feed::item('relevant', 'Finalizada'),
                                Feed::item('call', $call->name, $call->id)
                        )));
                        $log->doAdmin('admin');
                        unset($log);

                        throw new Redirection('/admin/transcalls/edit/'.$call->id);
                 } else {
                        Message::Error('Falló al finalizar la traducción de la convocatoria ' . $call->name);
                    }
                    break;
            }

            $calls = Model\Call::getTranslates($filters);
            $owners = Model\User::getCallers();
            $translators = Model\User::getAll(array('role'=>'translator'));

            return new View(
                'view/admin/index.html.php',
                array(
                    'folder' => 'transcalls',
                    'file' => 'list',
                    'calls' => $calls,
                    'filters' => $filters,
                    'fields'  => array('owner', 'translator'),
                    'owners' => $owners,
                    'translators' => $translators
                )
            );
            
        }

    }

}

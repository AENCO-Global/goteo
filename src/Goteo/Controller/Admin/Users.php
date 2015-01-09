<?php

namespace Goteo\Controller\Admin {

    use Goteo\Core\View,
        Goteo\Core\Redirection,
        Goteo\Core\Error,
		Goteo\Library\Text,
		Goteo\Library\Feed,
		Goteo\Library\Template,
        Goteo\Library\Message,
        Goteo\Library\Lang,
        Goteo\Model;

    class Users {

        public static $manageSubAct = array(
            "ban" => array (
                'sql' => "UPDATE user SET active = 0 WHERE id = :user",
                'log' => "Desactivado"
                ),
            "unban" => array (
                'sql' => "UPDATE user SET active = 1 WHERE id = :user",
                'log' => "Activado"
                ),
            "show" => array (
                'sql' => "UPDATE user SET hide = 0 WHERE id = :user",
                'log' => "Mostrado"
                ),
            "hide" => array (
                'sql' => "UPDATE user SET hide = 1 WHERE id = :user",
                'log' => "Ocultado"
                ),
            "checker" => array (
                'sql' => "REPLACE INTO user_role (user_id, role_id) VALUES (:user, 'checker')",
                'log' => "Hecho revisor"
                ),
            "nochecker" => array (
                'sql' => "DELETE FROM user_role WHERE role_id = 'checker' AND user_id = :user",
                'log' => "Quitado de revisor"
                ),
            "translator" => array (
                'sql' => "REPLACE INTO user_role (user_id, role_id) VALUES (:user, 'translator')",
                'log' => "Hecho traductor"
                ),
            "notranslator" => array (
                'sql' => "DELETE FROM user_role WHERE role_id = 'translator' AND user_id = :user",
                'log' => "Quitado de traductor"
                ),
            "caller" => array (
                'sql' => "REPLACE INTO user_role (user_id, role_id) VALUES (:user, 'caller')",
                'log' => "Hecho convocador"
                ),
            "nocaller" => array (
                'sql' => "DELETE FROM user_role WHERE role_id = 'caller' AND user_id = :user",
                'log' => "Quitado de convocador"
                ),
            "admin" => array (
                'sql' => "REPLACE INTO user_role (user_id, role_id) VALUES (:user, 'admin')",
                'log' => "Hecho admin"
                ),
            "noadmin" => array (
                'sql' => "DELETE FROM user_role WHERE role_id = 'admin' AND user_id = :user",
                'log' => "Quitado de admin"
                ),
            "vip" => array (
                'sql' => "REPLACE INTO user_role (user_id, role_id) VALUES (:user, 'vip')",
                'log' => "Hecho VIP"
                ),
            "novip" => array (
                'sql' => "DELETE FROM user_role WHERE role_id = 'vip' AND user_id = :user",
                'log' => "Quitado el VIP"
                ),
            "manager" => array (
                'sql' => "REPLACE INTO user_role (user_id, role_id) VALUES (:user, 'manager')",
                'log' => "Hecho gestor"
                ),
            "nomanager" => array (
                'sql' => "DELETE FROM user_role WHERE role_id = 'manager' AND user_id = :user",
                'log' => "Quitado de gestor"
                )
        );


        public static function process ($action = 'list', $id = null, $filters = array(), $subaction = '') {

            // multiples usos
            $nodes = Model\Node::getList();
            $admin_subnode = false;

            if (isset($_SESSION['admin_node'])) {
                $node = $_SESSION['admin_node'];
                if ($node != \GOTEO_NODE) {
                    // Fuerza el filtro de nodo para que el admin de un nodo no pueda cambiarlo
                    $filters['node'] = $_SESSION['admin_node'];
                    $admin_subnode = true;
                }
            } else {
                $node = \GOTEO_NODE;
            }

            $errors = array();

            switch ($action)  {
                case 'add':

                    // si llega post: creamos
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                        // para crear se usa el mismo método save del modelo, hay que montar el objeto
                        $user = new Model\User();
                        $user->userid = $_POST['userid'];
                        $user->name = $_POST['name'];
                        $user->email = $_POST['email'];
                        $user->password = $_POST['password'];
                        $user->node = !empty($_POST['node']) ? $_POST['node'] : \GOTEO_NODE;
                        if (isset($_SESSION['admin_node']) && $user->node != $_SESSION['admin_node']) {
                            $user->node = $_SESSION['admin_node'];
                        }
                        $user->save($errors);

                        if(empty($errors)) {
                          // mensaje de ok y volvemos a la lista de usuarios
                          Message::Info(Text::get('user-register-success'));
                          throw new Redirection('/admin/users/manage/'.$user->id);
                        } else {
                            // si hay algun error volvemos a poner los datos en el formulario
                            $data = $_POST;
                            Message::Error(implode('<br />', $errors));
                        }
                    }

                    // vista de crear usuario
                    return new View(
                        'admin/index.html.php',
                        array(
                            'folder' => 'users',
                            'file' => 'add',
                            'data'=>$data,
                            'nodes' => $nodes
                        )
                    );

                    break;
                case 'edit':

                    $user = Model\User::get($id);

                    // si llega post: actualizamos
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        $tocado = array();
                        // para crear se usa el mismo método save del modelo, hay que montar el objeto
                        if (!empty($_POST['email'])) {
                            $user->email = $_POST['email'];
                            $tocado[] = 'el email';
                        }
                        if (!empty($_POST['password'])) {
                            $user->password = $_POST['password'];
                            $tocado[] = 'la contraseña';
                        }

                        if(!empty($tocado) && $user->update($errors)) {

                            // Evento Feed
                            $log = new Feed();
                            $log->setTarget($user->id, 'user');
                            $log->populate('Operación sobre usuario (admin)', '/admin/users', \vsprintf('El admin %s ha %s del usuario %s', array(
                                Feed::item('user', $_SESSION['user']->name, $_SESSION['user']->id),
                                Feed::item('relevant', 'Tocado ' . implode (' y ', $tocado)),
                                Feed::item('user', $user->name, $user->id)
                            )));
                            $log->doAdmin('user');
                            unset($log);

                            // mensaje de ok y volvemos a la lista de usuarios
                            Message::Info('Datos actualizados');
                            throw new Redirection('/admin/users');

                        } else {
                            // si hay algun error volvemos a poner los datos en el formulario
                            $data = $_POST;
                            Message::Error(implode('<br />', $errors));
                        }
                    }

                    // vista de editar usuario
                    return new View(
                        'admin/index.html.php',
                        array(
                            'folder' => 'users',
                            'file' => 'edit',
                            'user'=>$user,
                            'data'=>$data,
                            'nodes'=>$nodes
                        )
                    );

                    break;
                case 'manage':

                    // si llega post: ejecutamos + mensaje + seguimos editando

                    // operación y acción para el feed

                    $sql = self::$manageSubAct[$subaction]['sql'];
                    $log_action = self::$manageSubAct[$subaction]['log'];

                    if (!empty($sql)) {

                        $user = Model\User::getMini($id);

                        if (Model\User::query($sql, array(':user'=>$id))) {

                            // mensaje de ok y volvemos a la gestion del usuario
//                            Message::Info('Ha <strong>' . $log_action . '</strong> al usuario <strong>'.$user->name.'</strong> CORRECTAMENTE');
                            $log_text = 'El admin %s ha %s al usuario %s';

                            $onNode = Model\Node::get($node);

                            // procesos adicionales
                            switch ($subaction) {
                                case 'admin':
                                    if ($onNode->assign($id)) {
                                        Message::Info('El nuevo admin se ha añadido a los administradores del nodo <strong>'.$onNode->name.'</strong>.');
                                    } else{
                                        Message::Error('ERROR!!! El nuevo admin no se ha podido añadir a los administradores del nodo <strong>'.$onNode->name.'</strong>. Contactar con el superadmin');
                                    }
                                    break;

                                case 'noadmin':
                                    if ($onNode->unassign($id)) {
                                        Message::Info('El ex-admin se ha quitado de los administradores del nodo <strong>'.$onNode->name.'</strong>.');
                                    } else{
                                        Message::Error('ERROR!!! El ex-admin no se ha podido quitar de los administradores del nodo <strong>'.$onNode->name.'</strong>. Contactar con el superadmin');
                                    }
                                    break;

                                case 'translator':
                                    // le ponemos todos los idiomas (excepto el español)
                                    $sql = "INSERT INTO user_translang (user, lang) SELECT '{$id}' as user, id as lang FROM `lang` WHERE id != 'es'";
                                    Model\User::query($sql);
                                    break;

                                case 'notranslator':
                                    // quitamos los idiomas
                                    $sql = "DELETE FROM user_translang WHERE user = :user";
                                    Model\User::query($sql, array(':user'=>$id));
                                    break;
                            }


                        } else {

                            // mensaje de error y volvemos a la gestion del usuario
                            Message::Error('Ha FALLADO cuando ha <strong>' . $log_action . '</strong> al usuario <strong>'.$id.'</strong>');
                            $log_text = 'Al admin %s le ha <strong>FALLADO</strong> cuando ha %s al usuario %s';

                        }

                        // Evento Feed
                        $log = new Feed();
                        $log->setTarget($user->id, 'user');
                        $log->populate('Operación sobre usuario (admin)', '/admin/users',
                            \vsprintf($log_text, array(
                                Feed::item('user', $_SESSION['user']->name, $_SESSION['user']->id),
                                Feed::item('relevant', $log_action),
                                Feed::item('user', $user->name, $user->id)
                        )));
                        $log->doAdmin('user');
                        unset($log);

                        throw new Redirection('/admin/users/manage/'.$id);
                    }

                    $user = Model\User::get($id);

                    $viewData = array(
                            'folder' => 'users',
                            'file' => 'manage',
                            'user'=>$user,
                            'nodes'=>$nodes
                        );

                    $viewData['roles'] = Model\User::getRolesList();
                    $viewData['langs'] = Lang::getAll();
                    // quitamos el español
                    unset($viewData['langs']['es']);

                    // vista de gestión de usuario
                    return new View(
                        'admin/index.html.php',
                        $viewData
                    );


                    break;

                // aplicar idiomas
                case 'translang':

                    if (!isset($_POST['user'])) {
                        Message::Error('Hemos perdido de vista al usuario');
                        throw new Redirection('/admin/users');
                    } else {
                        $user = $_POST['user'];
                    }

                    $sql = "DELETE FROM user_translang WHERE user = :user";
                    Model\User::query($sql, array(':user'=>$user));

                    $anylang = false;
                    foreach ($_POST as $key => $value) {
                        if (\substr($key, 0, \strlen('lang_')) == 'lang_')  {
                            $sql = "INSERT INTO user_translang (user, lang) VALUES (:user, :lang)";
                            if (Model\User::query($sql, array(':user'=>$user, ':lang'=>$value))) {
                                $anylang = true;
                            }
                        }
                    }

                    if (!$anylang) {
                        Message::Error('No se ha seleccionado ningún idioma, este usuario tendrá problemas en su panel de traducción!');
                    } else {
                        Message::Info('Se han aplicado al traductor los idiomas seleccionados');
                    }

                    throw new Redirection('/admin/users/manage/'.$user);

                    break;
                case 'impersonate':

                    $user = Model\User::get($id);

                    // vista de acceso a suplantación de usuario
                    return new View(
                        'admin/index.html.php',
                        array(
                            'folder' => 'users',
                            'file'   => 'impersonate',
                            'user'   => $user,
                            'nodes'=>$nodes
                        )
                    );

                    break;
                case 'move':
                    $user = Model\User::get($id);

                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        $values = array(':id' => $id, ':node' => $_POST['node']);
                        try {
                            $sql = "UPDATE user SET node = :node WHERE id = :id";
                            if (Model\User::query($sql, $values)) {
                                $log_text = 'El admin %s ha <span class="red">movido</span> el usuario %s al nodo %s';
                            } else {
                                $log_text = 'Al admin %s le ha <span class="red">fallado al mover</span> el usuario %s al nodo %s';
                            }
                            // Evento Feed
                            $log = new Feed();
                            $log->setTarget($user->id, 'user');
                            $log->populate('User cambiado de nodo (admin)', '/admin/users',
                                \vsprintf($log_text, array(
                                    Feed::item('user', $_SESSION['user']->name, $_SESSION['user']->id),
                                    Feed::item('user', $user->name, $user->id),
                                    Feed::item('user', $nodes[$_POST['node']])
                            )));
                            Message::Error($log->html);
                            $log->doAdmin('user');
                            unset($log);

                            throw new Redirection('/admin/users');

                        } catch(\PDOException $e) {
                            Message::Error("Ha fallado! " . $e->getMessage());
                        }
                    }

                    // vista de acceso a suplantación de usuario
                    return new View(
                        'admin/index.html.php',
                        array(
                            'folder' => 'users',
                            'file'   => 'move',
                            'user'   => $user,
                            'nodes' => $nodes
                        )
                    );

                    break;

                case 'list':
                default:
                    if (!empty($filters['filtered'])) {
                        $users = Model\User::getAll($filters, $admin_subnode);
                    } else {
                        $users = array();
                    }

                    $status = array(
                                'active' => 'Activo',
                                'inactive' => 'Inactivo'
                            );
                    $interests = Model\User\Interest::getAll();
                    $roles = Model\User::getRolesList();
                    $roles['user'] = 'Solo usuario';
                    $types = array(
                        'creators' => 'Impulsores', // que tienen algun proyecto
                        'investors' => 'Cofinanciadores', // que han aportado a algun proyecto en campaña, financiado, archivado o caso de éxito
                        'supporters' => 'Colaboradores', // que han enviado algun mensaje en respuesta a un mensaje de colaboración
                        'consultants' => 'Asesores'
                        // hay demasiados de estos... 'lurkers' => 'Mirones'
                    );
                    $orders = array(
                        'created' => 'Fecha de alta',
                        'name' => 'Alias',
                        'id' => 'User',
                        'amount' => 'Cantidad',
                        'projects' => 'Proyectos'
                    );
                    // proyectos con aportes válidos
                    $projects = Model\Invest::projects(true, $node);

                    return new View(
                        'admin/index.html.php',
                        array(
                            'folder' => 'users',
                            'file' => 'list',
                            'users'=>$users,
                            'filters' => $filters,
                            'status' => $status,
                            'interests' => $interests,
                            'roles' => $roles,
                            'types' => $types,
                            'nodes' => $nodes,
                            'projects' => $projects,
                            'orders' => $orders
                        )
                    );
                break;
            }

        }

    }

}

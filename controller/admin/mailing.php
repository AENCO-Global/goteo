<?php

namespace Goteo\Controller\Admin {

    use Goteo\Core\View,
        Goteo\Core\Redirection,
        Goteo\Core\Error,
		Goteo\Library\Feed,
        Goteo\Library\Template,
        Goteo\Library\Mail,
        Goteo\Model;

    class Mailing {

        public static function process ($action = 'list', $id = null) {

            $errors = array();

            // Valores de filtro
//            $projects = Model\Project::getAll();
            $interests = Model\User\Interest::getAll();
            $status = Model\Project::status();
            $methods = Model\Invest::methods();
            $types = array(
                'investor' => 'Cofinanciadores',
                'owner' => 'Autores',
                'user' => 'Usuarios'
            );
            $roles = array(
                'admin' => 'Administrador',
                'checker' => 'Revisor',
                'translator' => 'Traductor'
            );

            // una variable de sesion para mantener los datos de todo esto
            if (!isset($_SESSION['mailing'])) {
                $_SESSION['mailing'] = array();
            }

            if (!isset($_SESSION['mailing']['filters']['status']))
                $_SESSION['mailing']['filters']['status'] = -1;

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                switch ($action) {
                    case 'edit':

                        $_SESSION['mailing']['receivers'] = array();

                        $values = array();
                        $sqlFields  = '';
                        $sqlInner  = '';
                        $sqlFilter = '';


                        // Han elegido filtros
                        $filters = array(
                            'project'  => $_POST['project'],
                            'type'     => $_POST['type'],
                            'status'   => $_POST['status'],
                            'method'   => $_POST['method'],
                            'interest' => $_POST['interest'],
                            'role'     => $_POST['role'],
                            'name'     => $_POST['name'],
                            'workshopper' => $_POST['workshopper']
                        );

                        $_SESSION['mailing']['filters'] = $filters;

                        // cargamos los destiantarios
                        //----------------------------
                        // por tipo de usuario
                        switch ($filters['type']) {
                            case 'investor':
                                $sqlInner .= "INNER JOIN invest
                                        ON invest.user = user.id
                                        AND (invest.status = 0 OR invest.status = 1 OR invest.status = 3 OR invest.status = 4)
                                    INNER JOIN project
                                        ON project.id = invest.project
                                        ";
                                $sqlFields .= ", project.name as project";
                                $sqlFields .= ", project.id as projectId";
                                break;
                            case 'owner':
                                $sqlInner .= "INNER JOIN project
                                        ON project.owner = user.id
                                        ";
                                $sqlFields .= ", project.name as project";
                                $sqlFields .= ", project.id as projectId";
                                break;
                            default :
                                break;
                        }
                        $_SESSION['mailing']['filters_txt'] = 'los <strong>' . $types[$filters['type']] . '</strong> ';

                        if (!empty($filters['project']) && !empty($sqlInner)) {
                            $sqlFilter .= " AND project.name LIKE (:project) ";
                            $values[':project'] = '%'.$filters['project'].'%';
                            $_SESSION['mailing']['filters_txt'] .= 'de proyectos que su nombre contenga <strong>\'' . $filters['project'] . '\'</strong> ';
                        } elseif (empty($filters['project']) && !empty($sqlInner)) {
                            $_SESSION['mailing']['filters_txt'] .= 'de cualquier proyecto ';
                        }

                        if (isset($filters['status']) && $filters['status'] > -1 && !empty($sqlInner)) {
                            $sqlFilter .= "AND project.status = :status ";
                            $values[':status'] = $filters['status'];
                            $_SESSION['mailing']['filters_txt'] .= 'en estado <strong>' . $status[$filters['status']] . '</strong> ';
                        } elseif ($filters['status'] < 0 && !empty($sqlInner)) {
                            $_SESSION['mailing']['filters_txt'] .= 'en cualquier estado ';
                        }

                        if ($filters['type'] == 'investor') {
                            if (!empty($filters['method']) && !empty($sqlInner)) {
                                $sqlFilter .= "AND invest.method = :method ";
                                $values[':method'] = $filters['method'];
                                $_SESSION['mailing']['filters_txt'] .= 'mediante <strong>' . $methods[$filters['method']] . '</strong> ';
                            } elseif (empty($filters['method']) && !empty($sqlInner)) {
                                $_SESSION['mailing']['filters_txt'] .= 'mediante cualquier metodo ';
                            }
                        }

                        if (!empty($filters['interest'])) {
                            $sqlInner .= "INNER JOIN user_interest
                                    ON user_interest.user = user.id
                                    AND user_interest.interest = :interest
                                    ";
                            $values[':interest'] = $filters['interest'];
                            $_SESSION['mailing']['filters_txt'] .= 'interesados en fin <strong>' . $interests[$filters['interest']] . '</strong> ';
                        }

                        if (!empty($filters['role'])) {
                            $sqlInner .= "INNER JOIN user_role
                                    ON user_role.user_id = user.id
                                    AND user_role.role_id = :role
                                    ";
                            $values[':role'] = $filters['role'];
                            $_SESSION['mailing']['filters_txt'] .= 'que sean <strong>' . $roles[$filters['role']] . '</strong> ';
                        }

                        if (!empty($filters['name'])) {
                            $sqlFilter .= " AND ( user.name LIKE (:name) OR user.email LIKE (:name) ) ";
                            $values[':name'] = '%'.$filters['name'].'%';
                            $_SESSION['mailing']['filters_txt'] .= 'que su nombre o email contenga <strong>\'' . $filters['name'] . '\'</strong> ';
                        }

                        if (!empty($filters['workshopper'])) {
                            $sqlFilter .= " AND user.password = SHA1(user.email) ";
                            $_SESSION['mailing']['filters_txt'] .= 'que su contraseña sea igual que su email ';
                        }

                        $sql = "SELECT
                                    user.id as id,
                                    user.name as name,
                                    user.email as email
                                    $sqlFields
                                FROM user
                                $sqlInner
                                WHERE user.id != 'root'
                                AND user.active = 1
                                $sqlFilter
                                GROUP BY user.id
                                ORDER BY user.name ASC
                                ";

//                        echo '<pre>'.$sql . '<br />'.print_r($values, 1).'</pre>';

                        if ($query = Model\User::query($sql, $values)) {
                            foreach ($query->fetchAll(\PDO::FETCH_OBJ) as $receiver) {
                                $_SESSION['mailing']['receivers'][$receiver->id] = $receiver;
                            }
                        } else {
                            $_SESSION['mailing']['errors'][] = 'Fallo el SQL!!!!! <br />' . $sql . '<pre>'.print_r($values, 1).'</pre>';
                        }

                        // si no hay destinatarios, salta a la lista con mensaje de error
                        if (empty($_SESSION['mailing']['receivers'])) {
                            $_SESSION['mailing']['errors'][] = 'No se han encontrado destinatarios para ' . $_SESSION['mailing']['filters_txt'];

                            throw new Redirection('/admin/mailing/list');
                        }

                        // si hay, mostramos el formulario de envio
                        return new View(
                            'view/admin/index.html.php',
                            array(
                                'folder'    => 'mailing',
                                'file'      => 'edit',
                                'filters'   => $_SESSION['mailing']['filters'],
//                                'projects'  => $projects,
                                'interests' => $interests,
                                'status'    => $status,
                                'types'     => $types,
                                'roles'     => $roles
                            )
                        );

                        break;
                    case 'send':
                        // Enviando contenido recibido a destinatarios recibidos
                        $users = array();
                        foreach ($_POST as $key=>$value) {
                            $matches = array();
                            \preg_match('#receiver_(\w+)#', $key, $matches);
//                            echo \trace($matches);
                            if (!empty($matches[1]) && !empty($_SESSION['mailing']['receivers'][$matches[1]]->email)) {
                                $users[] = $matches[1];
                            }
                        }

//                        $content = nl2br($_POST['content']);
                        $content = $_POST['content'];
                        $subject = $_POST['subject'];
                        $templateId = !empty($_POST['template']) ? $_POST['template'] : 11;

                        // ahora, envio, el contenido a cada usuario
                        foreach ($users as $usr) {

                            // si es newsletter y el usuario la ha desmarcado en sus preferencias, lo saltamos
                            if ($templateId == 33 && Model\User::mailBlock($usr)) {
                                continue;
                            }

                            $tmpcontent = \str_replace(
                                array('%USERID%', '%USEREMAIL%', '%USERNAME%', '%SITEURL%', '%PROJECTID%', '%PROJECTNAME%', '%PROJECTURL%'),
                                array(
                                    $usr,
                                    $_SESSION['mailing']['receivers'][$usr]->email,
                                    $_SESSION['mailing']['receivers'][$usr]->name,
                                    SITE_URL,
                                    $_SESSION['mailing']['receivers'][$usr]->projectId,
                                    $_SESSION['mailing']['receivers'][$usr]->project,
                                    SITE_URL.'/project/'.$_SESSION['mailing']['receivers'][$usr]->projectId
                                ),
                                $content);


                            $mailHandler = new Mail();

                            $mailHandler->to = $_SESSION['mailing']['receivers'][$usr]->email;
                            $mailHandler->toName = $_SESSION['mailing']['receivers'][$usr]->name;
                            // blind copy a goteo desactivado durante las verificaciones
            //              $mailHandler->bcc = 'comunicaciones@goteo.org';
                            $mailHandler->subject = $subject;
                            $mailHandler->content = '<br />'.$tmpcontent.'<br />';
                            $mailHandler->html = true;
                            $mailHandler->template = $templateId;
                            if ($mailHandler->send($errors)) {
                                $_SESSION['mailing']['receivers'][$usr]->ok = true;
                            } else {
                                $_SESSION['mailing']['receivers'][$usr]->ok = false;
                            }

                            unset($mailHandler);
                        }

                        // Evento Feed
                        $log = new Feed();
                        $log->populate('mailing a usuarios (admin)', '/admin/mailing',
                            \vsprintf("El admin %s ha enviado una %s", array(
                            Feed::item('user', $_SESSION['user']->name, $_SESSION['user']->id),
                            Feed::item('relevant', 'Comunicación masiva')
                        )));
                        $log->doAdmin('admin');
                        unset($log);

                        return new View(
                            'view/admin/index.html.php',
                            array(
                                'folder'    => 'mailing',
                                'file'      => 'send',
                                'content'   => $content,
//                                'projects'  => $projects,
                                'interests' => $interests,
                                'status'    => $status,
                                'methods'   => $methods,
                                'types'     => $types,
                                'roles'     => $roles,
                                'users'     => $users,
                                'errors'    => $errors,
                                'success'   => $success
                            )
                        );

                        break;
                }
			}

            $errors = $_SESSION['mailing']['errors'];
            unset($_SESSION['mailing']['errors']);

            return new View(
                'view/admin/index.html.php',
                array(
                    'folder'    => 'mailing',
                    'file'      => 'list',
//                    'projects'  => $projects,
                    'interests' => $interests,
                    'status'    => $status,
                    'methods'   => $methods,
                    'types'     => $types,
                    'roles'     => $roles,
                    'filters'   => $_SESSION['mailing']['filters'],
                    'errors'    => $errors
                )
            );
            
        }

    }

}

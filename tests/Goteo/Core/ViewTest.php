<?php

namespace Goteo\Tests;

use Goteo\Core\View,
    Goteo\Core\Redirection,
    Goteo\Core\View\Exception,
    Goteo\Model\Project,
    Goteo\Model\Image,
    Goteo\Model\User;

class ViewTest extends \PHPUnit_Framework_TestCase {

    protected static $views = array();

    static function setUpBeforeClass() {
        self::$views = array(
            'admin/blog/list.html.php',
            'admin/commons/list.html.php',
            );

        $path = realpath(GOTEO_PATH . '/view');

        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);
        foreach($objects as $file => $object){
            if(strrpos($file, '.html.php', -9) !== false) {
                $file = substr($file, strlen($path) + 1);
                // print_r("[$file] " . strrpos($file, '.html.php', -9) . "\n");
                self::$views[] = $file;
            }
        }
    }

    public function testInstance() {
        $test = new View(__FILE__);
        $this->assertInstanceOf('\Goteo\Core\View', $test);
        $test = new Exception();
        $this->assertInstanceOf('\Goteo\Core\View\Exception', $test);
        try {
            $test = new View('i-dont-exists.php');
        }
        catch(Exception $e) {
            $this->assertInstanceOf('\Goteo\Core\View\Exception', $e);
        }

    }

    public function testView() {

    }
    public function testGoteoViews() {
        global $_SESSION;
        $project = new Project();
        $project->user = new User();
        $project->user->avatar = new Image();
        $post = new \Goteo\Model\Blog\Post();
        $post->gallery = 'empty';
        $post->image = new Image();
        $call = new \Goteo\Model\Call();
        $call->logo =new Image();
        $vars = array(
            'project' => $project,
            'user' => $project->user,
            'post' => $post,
            'call' => $call
            );

        $_SESSION['user'] = $project->user;
        foreach(self::$views as $view) {
            try {
                $v = new View($view, $vars);
                $this->assertInstanceOf('\Goteo\Core\View', $v);
                $out = $v->render();
                $this->assertInternalType('string', $out);
            }
            catch(\Goteo\Core\Redirection $e) {
                echo "La vista [$view] lanza una exception de redireccion!\nEsto no deberia hacerse aqui!\n";
            }
            // echo $out;
        }
    }
}

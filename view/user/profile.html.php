<?php

use Goteo\Core\View,
    Goteo\Library\Worth,
    Goteo\Model\User\Interest;

$bodyClass = 'user-profile';
include 'view/prologue.html.php';
include 'view/header.html.php';

$user = $this['user'];
$worthcracy = Worth::getAll();
?>

        <div id="sub-header">
            <div>                                
                <h2><img src="/image/<?php echo $user->avatar->id; ?>/75/75" /> Perfil de <br /><em><?php echo $user->name; ?></em></h2>
            </div>
        </div>

        <div id="main">
            
            <div class="center">
            
                <?php echo new View('view/user/widget/worth.html.php', array('worthcracy' => $worthcracy, 'level' => $user->worth)) ?>

                <?php echo new View('view/user/widget/about.html.php', array('user' => $user)) ?>

                <?php echo new View('view/user/widget/social.html.php', array('user' => $user)) ?>                        
                                                
                <div class="widget projects">
                    <h2 class="title">Proyectos que apoyo</h2>
                    <?php foreach ($this['invested'] as $project) : ?>
                        <div>
                            <?php
                            // es instancia del proyecto
                            echo new View('view/project/widget/project.html.php', array(
                                'project'   => $project
                            )); ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="widget projects">
                    <h2 class="title">Mis proyectos</h2>
                    <?php foreach ($this['projects'] as $project) : ?>
                        <div>
                            <?php
                            // es instancia del proyecto
                            echo new View('view/project/widget/project.html.php', array(
                                'project'   => $project
                            )); ?>
                        </div>
                    <?php endforeach; ?>
                    </div>

            </div>
            <div class="side">
                <div class="widget user-supporters">
                        <h3>Mis cofinanciadores</h3>
                        <div>
                        <?php foreach ($this['investors'] as $user=>$investor) {
                            echo new View('view/user/widget/supporter.html.php', array('user' => $investor, 'worthcracy' => $worthcracy));
            //                echo "{$investor->avatar} {$investor->name} De nivel {$investor->worth}  Cofinancia {$investor->projects} proyectos  Me aporta: {$investor->amount} € <br />";
                        } ?>
                        </div>
                        <?php echo new View('view/worth/base.html.php', array('worthcracy' => $worthcracy, 'type' => 'side')); ?>
                    </div>

                    <div class="widget user-mates">
                        <h3>Compartiendo intereses</h3>
                        <?php foreach ($this['shares'] as $share) {
                            echo '<div style="float:left;margin: 10px;"><img src="/image/' . $share->avatar->id . '/50/50" /><br />';
                            echo '<a href="/user/' . $share->user . '">' . $share->name . '</a><br />';
                            echo "Proyectos(" . $share->projects .")<br/>Aportacion(" . $share->invests ." )";
                            echo '</div>';
                        } ?>
                    </div>
            </div>

        </div>
        
    <?php include 'view/footer.html.php' ?>

<?php include 'view/epilogue.html.php' ?>
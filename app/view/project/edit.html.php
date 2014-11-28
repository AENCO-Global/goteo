<?php

use Goteo\Core\View,
    Goteo\Library\Text,
    Goteo\Library\SuperForm;

$bodyClass = 'project-edit';

$project = $this['project'];

$status = View::get('project/edit/status.html.php', array('status' => $project->status, 'progress' => $project->progress));
$steps  = View::get('project/edit/steps.html.php', array('steps' => $this['steps'], 'step' => $this['step'], 'errors' => $project->errors, 'id_project' => $project->id));

if (!empty($this['success'])) {
    Goteo\Library\Message::Info($this['success']);
} elseif ($project->status == 1) {
    Goteo\Library\Message::Info(Text::get('form-ajax-info'));
}

$superform = true;
include __DIR__ . '/../prologue.html.php';

    include __DIR__ . '/../header.html.php'; ?>

    <div id="sub-header">
        <div class="project-header">
            <a href="/user/<?php echo $project->owner; ?>" target="_blank"><img src="<?php echo $project->user->avatar->getLink(50, 50, true); ?>" /></a>
            <h2><span><?php echo htmlspecialchars($project->name) ?></span></h2>
            <div class="project-subtitle"><?php echo htmlspecialchars($project->subtitle) ?></div>
            <div class="project-by"><a href="/user/<?php echo $project->owner; ?>" target="_blank">Por: <?php echo $project->user->name; ?></a></div>
        </div>
    </div>

<?php if(isset($_SESSION['messages'])) { include __DIR__ . '/../header/message.html.php'; } ?>

    <div id="main" class="<?php echo htmlspecialchars($this['step']) ?>">

        <form method="post" id="proj-superform" action="<?php echo "/project/edit/" . $this['project']->id ?>" class="project" enctype="multipart/form-data" >

            <input type="hidden" name="view-step-<?php echo $this['step'] ?>" value="please" />

            <?php echo $status ?>
            <?php if (count($this['steps']) > 1) echo $steps; // si solo se permite un paso no ponemos la navegación ?>

            <?php if($this['step']) echo View::get("project/edit/{$this['step']}.html.php", $this->getArrayCopy() + array('level' => 3)); ?>

            <?php if (count($this['steps']) > 1) echo $steps; // si solo se permite un paso no ponemos la navegación ?>

            <script type="text/javascript">
            $(function () {
                $('div.superform').bind('superform.ajax.done', function (event, html, new_el) {
                    $('li#errors').superform(html);
                });
            });
            </script>

        </form>

    </div>

    <?php include __DIR__ . '/../footer.html.php' ?>

<?php include __DIR__ . '/../epilogue.html.php' ?>

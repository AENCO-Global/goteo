<?php

use Goteo\Core\View,
    Goteo\Library\Text,
    Goteo\Library\SuperForm;

$bodyClass = 'project-edit';

$call = $this['call'];

if (!empty($this['success'])) {
    Goteo\Library\Message::Info($this['success']);
} elseif ($call->status == 1) {
    Goteo\Library\Message::Info(Text::get('call-form-ajax-info'));
}

$steps  = View::get('call/edit/steps.html.php', array('steps' => $this['steps'], 'step' => $this['step'], 'id_call' => $call->id));

// next step
$debug = (isset($_GET['debug']));
if ($debug) var_dump($this['step']);
$keys = array_keys($this['steps']);
if ($debug) var_dump($keys);
$next_step = $keys[ array_search($this['step'], $keys) + 1];
if ($debug) var_dump($next_step);
if ($debug) die;


$superform = true;
include __DIR__ . '/../prologue.html.php';

    include __DIR__ . '/../header.html.php'; ?>

    <div id="sub-header">
        <div class="project-header">
            <a href="/user/<?php echo $call->owner; ?>" target="_blank"><img src="<?php echo SITE_URL ?>/image/<?php echo $call->user->avatar->id; ?>/50/50/1" /></a>
            <h2><span><?php echo htmlspecialchars($call->name) ?></span></h2>
            <div class="project-subtitle"><?php echo htmlspecialchars($call->subtitle) ?></div>
            <div class="project-by"><a href="/user/<?php echo $call->owner; ?>" target="_blank">Por: <?php echo $call->user->name; ?></a></div>
        </div>
    </div>

<?php if(isset($_SESSION['messages'])) { include __DIR__ . '/../header/message.html.php'; } ?>

    <div id="main" class="<?php echo htmlspecialchars($this['step']) ?>">

        <form method="post" action="<?php echo SITE_URL . "/call/edit/" . $call->id ?>" class="project" enctype="multipart/form-data" >

            <input type="hidden" name="view-step-<?php echo $this['step'] ?>" value="please" />

            <?php echo $steps ?>

            <?php if($this['step']) echo View::get("call/edit/{$this['step']}.html.php", $this->getArrayCopy() + array('level' => 3, 'next'=>$next_step)) ?>

            <?php echo $steps ?>

        </form>

    </div>

    <?php include __DIR__ . '/../footer.html.php' ?>

<?php include __DIR__ . '/../epilogue.html.php' ?>

<?php

use Goteo\Core\View,
    Goteo\Library\Text,
    Goteo\Library\Message;

$bodyClass = 'project-edit';

$contract = $this['contract'];

$steps  = View::get('contract/edit/steps.html.php', array('steps' => $this['steps'], 'step' => $this['step'], 'errors' => $contract->errors));

if (!$contract->status->owner)
    Message::Info(Text::get('form-ajax-info'));

$superform = true;
include __DIR__ . '/../prologue.html.php';

    include __DIR__ . '/../header.html.php'; ?>

    <div id="sub-header">
        <div class="project-header">
            <h2><span><?php echo htmlspecialchars($contract->project_name) ?></span></h2>
        </div>
    </div>

<?php if(isset($_SESSION['messages'])) { include __DIR__ . '/../header/message.html.php'; } ?>

    <div id="main" class="<?php echo htmlspecialchars($this['step']) ?>">

        <form method="post" action="<?php echo "/contract/edit/" . $contract->project ?>" class="project" enctype="multipart/form-data" >

            <input type="hidden" name="view-step-<?php echo $this['step'] ?>" value="please" />

            <?php echo $steps ?>

            <?php if($this['step']) echo View::get("contract/edit/{$this['step']}.html.php", $this->getArrayCopy() + array('level' => 3)); ?>

            <?php echo $steps ?>

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
<?php

use Goteo\Library\Text;

$project = $this['project'];
$call = $project->called;
?>
<div class="widget project-called collapsable activable" id="project-called">
    <div class="explain">
        <a href="<?php echo SITE_URL ?>/call/<?php echo $call->id ?>/info" class="expand" target="_blank"></a>
        <dl>
            <dt><?php echo Text::get('call-project-get') ?></dt>
            <dd><?php echo $call->name ?></dd>
        </dl>
        <p><?php echo Text::html('call-project-got_explain', \amount_format($call->project_got), $call->user->name) ?></p>
    </div>
    <div class="amount">
        <a href="<?php echo SITE_URL ?>/call/<?php echo $call->id ?>/info" class="expand" target="_blank"></a>
        <dl>
            <dt><?php echo Text::get('call-splash-whole_budget-header') ?></dt>
            <dd class="light-violet"><span><?php echo \amount_format($call->amount) ?></span></dd>
        </dl>

<?php if (!empty($call->maxproj)) : ?>
        <dl>
            <dt><?php echo Text::get('call-project-limit-header') ?></dt>
            <dd class="violet"><span><?php echo \amount_format($call->maxproj) ?></span></dd>
        </dl>
<?php else : ?>
        <dl>
            <dt><?php echo Text::get('call-splash-remain_budget-header') ?></dt>
            <dd class="violet"><span><?php echo \amount_format($call->rest) ?></span></dd>
        </dl>
<?php endif; ?>
    </div>
</div>
<script type="text/javascript">

    $(function () {
       if ($('div.widget.project-called div.explain').height() > $('div.widget.project-called div.amount').height()) {
           $('div.widget.project-called div.amount').height($('div.widget.project-called div.explain').height());
       } else {
           $('div.widget.project-called div.explain').height($('div.widget.project-called div.amount').height());
       }
    });

</script>

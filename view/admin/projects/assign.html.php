<?php

use Goteo\Library\Text,
    Goteo\Model,
    Goteo\Core\Redirection;

$project = $this['project'];

if (!$project instanceof Model\Project) {
    throw new Redirection('/admin/projects');
}

?>
<div class="widget" >
    <form method="post" action="/admin/projects" >
        <input type="hidden" name="assign-to-call" value="assign-to-call" />
        <input type="hidden" name="id" value="<?php echo $project->id ?>" />

    <p>
        <label for="call-filter">Asignarlo a la convocatoria:</label><br />
        <select id="call-filter" name="call" >
        <?php foreach ($this['available'] as $callId=>$callName) : ?>
            <option value="<?php echo $callId; ?>"><?php echo $callName; ?></option>
        <?php endforeach; ?>
        </select>
    </p>

        <input type="submit" name="save" value="Aplicar" />
    </form>
</div>
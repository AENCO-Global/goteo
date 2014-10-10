<?php

use Goteo\Library\Text,
    Goteo\Model;

$promo = $this['promo'];

$node = isset($_SESSION['admin_node']) ? $_SESSION['admin_node'] : \GOTEO_NODE;

// proyectos disponibles
// si tenemos ya proyecto seleccionado lo incluimos
$projects = Model\Promote::available($promo->project, $node);
$status = Model\Project::status();

//para autocomplete
$items = array();

foreach ($projects as $project) {
    $items[] = '{ value: "'.str_replace('"','\"',$project->name).'", id: "'.$project->id.'" }';
        if($promo->project === $project->id) $preval=$project->name;
}
?>
<form method="post" action="/admin/promote">
    <input type="hidden" name="action" value="<?php echo $this['action'] ?>" />
    <input type="hidden" name="order" value="<?php echo $promo->order ?>" />
    <input type="hidden" name="id" value="<?php echo $promo->id; ?>" />
    <input type="hidden" id="item" name="item" value="<?php echo $promo->project; ?>" />

<!--
<p>
    <label for="promo-project">Proyecto:</label><br />
    <select id="promo-project" name="project">
        <option value="" >Seleccionar el proyecto a destacar</option>
    <?php foreach ($projects as $project) : ?>
        <option value="<?php echo $project->id; ?>"<?php if ($promo->project == $project->id) echo' selected="selected"';?>><?php echo $project->name . ' ('. $status[$project->status] . ')'; ?></option>
    <?php endforeach; ?>
    </select>
</p>
-->

<div>
    <label for="projects-filter">Proyecto: (autocomplete nombre)</label><br />
    <input type="text" name="project" id="projects-filter" value="<?php echo $preval;?>" size="60" />
</div>


<?php if ($node == \GOTEO_NODE) : ?>
<p>
    <label for="promo-name">Título:</label><span style="font-style:italic;">Máximo 24 caracteres</span><br />
    <input type="text" name="title" id="promo-title" value="<?php echo $promo->title; ?>" maxlength="24" style="width:500px;" />
</p>

<p>
    <label for="promo-description">Descripción:</label><span style="font-style:italic;">Máximo 100 caracteres</span><br />
    <input type="text" name="description" id="promo-description" maxlength="100" value="<?php echo $promo->description; ?>" style="width:750px;" />
</p>
<?php endif; ?>

<p>
    <label>Publicado:</label><br />
    <label><input type="radio" name="active" id="promo-active" value="1"<?php if ($promo->active) echo ' checked="checked"'; ?>/> SÍ</label>
    &nbsp;&nbsp;&nbsp;
    <label><input type="radio" name="active" id="promo-inactive" value="0"<?php if (!$promo->active) echo ' checked="checked"'; ?>/> NO</label>
</p>

    <input type="submit" name="save" value="Guardar" />

    <p>
        <label for="mark-pending">Marcar como pendiente de traducir</label>
        <input id="mark-pending" type="checkbox" name="pending" value="1" />
    </p>

</form>
<script type="text/javascript">
$(function () {

    var items = [<?php echo implode(', ', $items); ?>];

    /* Autocomplete para elementos */
    $( "#projects-filter" ).autocomplete({
      source: items,
      minLength: 1,
      autoFocus: true,
      select: function( event, ui) {
                $("#item").val(ui.item.id);
            }
    });

});
</script>
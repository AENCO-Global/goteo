<?php

use Goteo\Library\Text;

// paginacion
require_once 'library/pagination/pagination.php';

$filters = $this['filters'];

$the_filters = '';
foreach ($filters as $key=>$value) {
    $the_filters .= "&{$key}={$value}";
}

$pagedResults = new \Paginated($this['projects'], 10, isset($_GET['page']) ? $_GET['page'] : 1);
?>
<a href="/admin/translates" class="button">Asignar traductores</a>

<div class="widget board">
    <form id="filter-form" action="/admin/projects" method="get">
        <input type="hidden" name="filtered" value="yes" />
        <table>
            <tr>
                <td>
                    <label for="name-filter">Alias/Email del autor:</label><br />
                    <input type="text" id ="name-filter" name="name" value ="<?php echo $filters['name']?>" />
                </td>
                <td>
                    <label for="category-filter">De la categoría:</label><br />
                    <select id="category-filter" name="category" onchange="document.getElementById('filter-form').submit();">
                        <option value="">Cualquier categoría</option>
                    <?php foreach ($this['categories'] as $categoryId=>$categoryName) : ?>
                        <option value="<?php echo $categoryId; ?>"<?php if ($filters['category'] == $categoryId) echo ' selected="selected"';?>><?php echo $categoryName; ?></option>
                    <?php endforeach; ?>
                    </select>
                </td>
                <?php if (!isset($_SESSION['admin_node']) || $_SESSION['admin_node'] == \GOTEO_NODE) : ?>
                <td>
                    <label for="node-filter">Del nodo:</label><br />
                    <select id="node-filter" name="node" onchange="document.getElementById('filter-form').submit();">
                        <option value="">Cualquier nodo</option>
                    <?php foreach ($this['nodes'] as $nodeId=>$nodeName) : ?>
                        <option value="<?php echo $nodeId; ?>"<?php if ($filters['node'] == $nodeId) echo ' selected="selected"';?>><?php echo $nodeName; ?></option>
                    <?php endforeach; ?>
                    </select>
                </td>
                <?php endif; ?>
            </tr>
            <tr>
                <td>
                    <label for="proj_name-filter">Nombre del proyecto:</label><br />
                    <input id="proj_name-filter" name="proj_name" value="<?php echo $filters['proj_name']; ?>" style="width:250px"/>
                </td>
                <td>
                    <label for="status-filter">Mostrar por estado:</label><br />
                    <select id="status-filter" name="status" onchange="document.getElementById('filter-form').submit();">
                        <option value="-1"<?php if ($filters['status'] == -1) echo ' selected="selected"';?>>Todos los estados</option>
                        <option value="-2"<?php if ($filters['status'] == -2) echo ' selected="selected"';?>>En negociacion</option>
                    <?php foreach ($this['status'] as $statusId=>$statusName) : ?>
                        <option value="<?php echo $statusId; ?>"<?php if ($filters['status'] == $statusId) echo ' selected="selected"';?>><?php echo $statusName; ?></option>
                    <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <label for="call-filter">En la convocatoria:</label><br />
                    <select id="call-filter" name="called" onchange="document.getElementById('filter-form').submit();">
                        <option value="">--</option>
                        <option value="all"<?php if ($filters['called'] == 'all') echo ' selected="selected"';?>>En alguna</option>
                        <option value="none"<?php if ($filters['called'] == 'none') echo ' selected="selected"';?>>En ninguna</option>
                    <?php foreach ($this['calls'] as $callId=>$callName) : ?>
                        <option value="<?php echo $callId; ?>"<?php if ($filters['called'] == $callId) echo ' selected="selected"';?>><?php echo $callName; ?></option>
                    <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="proj_id-filter">Id del proyecto:</label><br />
                    <input id="proj_id-filter" name="proj_id" value="<?php echo $filters['proj_id']; ?>" style="width:250px"/>
                </td>
                <td>
                    <label for="order-filter">Ordenar por:</label><br />
                    <select id="order-filter" name="order" onchange="document.getElementById('filter-form').submit();">
                    <?php foreach ($this['orders'] as $orderId=>$orderName) : ?>
                        <option value="<?php echo $orderId; ?>"<?php if ($filters['order'] == $orderId) echo ' selected="selected"';?>><?php echo $orderName; ?></option>
                    <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <label for="consultant-filter">Asesorado por:</label><br />
                    <select id="consultant-filter" name="consultant" onchange="document.getElementById('filter-form').submit();">
                        <option value="-1"<?php if ($filters['consultant'] == -1) echo ' selected="selected"';?>>Cualquier admin</option>
                    <?php foreach ($this['admins'] as $userId=>$userName) : ?>
                        <option value="<?php echo $userId; ?>"<?php if ($filters['consultant'] == $userId) echo ' selected="selected"';?>><?php echo $userName; ?></option>
                    <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" name="filter" value="Buscar">
                </td>
            </tr>
        </table>
    </form>
    <br clear="both" />
    <a href="/admin/projects/?reset=filters">Quitar filtros</a>
<?php if ($filters['filtered'] != 'yes') : ?>
    <p>Es necesario poner algun filtro, hay demasiados registros!</p>
<?php elseif (empty($this['projects'])) : ?>
    <p>No se han encontrado registros</p>
<?php else: ?>
    <p><strong>OJO!</strong> Resultado limitado a 999 registros como máximo.</p>
<?php endif; ?>
</div>

    
<?php if (!empty($this['projects'])) : 
    while ($project = $pagedResults->fetchPagedRow()) : 
?>
<div class="widget board">
    <table>
        <thead>
            <tr>
                <th style="width: 250px;">Proyecto</th> <!-- edit -->
                <th style="min-width: 150px;">Creador</th> <!-- mailto -->
                <th style="min-width: 75px;">Recibido</th> <!-- enviado a revision -->
                <th style="min-width: 80px;">Estado</th>
                <th style="min-width: 50px;">Nodo</th>
                <th style="min-width: 50px;">M&iacute;nimo</th>
                <th style="min-width: 50px;">&Oacute;ptimo</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td><a href="/project/<?php echo $project->id; ?>" target="_blank" title="Preview" style="<?php if (isset($project->called)) echo 'color: blue;'; ?>"><?php echo $project->name; ?></a></td>
                <td><a href="mailto:<?php echo $project->user->email; ?>"><?php echo substr($project->user->email, 0, 100); ?></a></td>
                <td><?php echo date('d-m-Y', strtotime($project->updated)); ?></td>
                <td><?php echo ($project->status == 1 && !$project->draft) ? '<span style="color: green;">En negociación</span>' : $this['status'][$project->status]; ?></td>
                <td style="text-align: center;"><?php echo $project->node; ?></td>
                <td style="text-align: right;"><?php echo \amount_format($project->mincost).'€'; ?></td>
                <td style="text-align: right;"><?php echo \amount_format($project->maxcost).'€'; ?></td>
            </tr>
            <tr>
                <td colspan="7"><?php 
                    if ($project->status < 3) {
                        echo "Información al <strong>{$project->progress}%</strong>";
                    } elseif ($project->status == 3 && $project->round > 0) {
                        echo "Le quedan {$project->days} días de la {$project->round}ª ronda.&nbsp;&nbsp;&nbsp;";
                        echo "<strong>Conseguido:</strong> ".\amount_format($project->invested)."€&nbsp;&nbsp;&nbsp;";
                        echo "<strong>Cofin:</strong> {$project->num_investors}&nbsp;&nbsp;&nbsp;<strong>Colab:</strong> {$project->num_messegers}";

                    }

                    $consultants = array_values($project->consultants);
                    if (!empty($consultants)) {
                        if (($project->status < 3) ||  ($project->status == 3 && $project->round > 0)) {
                            echo " | ";
                        }
                        echo "Asesorado por: " . implode(", ", $consultants);
                    }

                ?></td>
            </tr>
            <tr>
                <td colspan="7">
                    IR A:&nbsp;
                    <a href="/project/edit/<?php echo $project->id; ?>" target="_blank">[Editar]</a>
                    <a href="/admin/users/?id=<?php echo $project->owner; ?>" target="_blank">[Impulsor]</a>
                    <?php if (!isset($_SESSION['admin_node']) 
                            || (isset($_SESSION['admin_node']) && $_SESSION['admin_node'] == \GOTEO_NODE)
                            || (isset($_SESSION['admin_node']) && $user->node == $_SESSION['admin_node'])) : ?>
                    <a href="/admin/accounts/?projects=<?php echo $project->id; ?>" title="Ver sus aportes">[Aportes]</a>
                    <?php else:  ?>
                    <a href="/admin/invests/?projects=<?php echo $project->id; ?>" title="Ver sus aportes">[Aportes]</a>
                    <?php endif; ?>
                    <a href="/admin/users/?project=<?php echo $project->id; ?>" title="Ver sus cofinanciadores">[Cofinanciadores]</a>
                    <a href="/admin/projects/report/<?php echo $project->id; ?>" target="_blank">[Informe Financiacion]</a>
                </td>
            </tr>
            <tr>
                <td colspan="7">
                    CAMBIAR:&nbsp;
                    <a href="<?php echo "/admin/projects/dates/{$project->id}"; ?>">[Fechas]</a>
                    <a href="<?php echo "/admin/projects/accounts/{$project->id}"; ?>">[Cuentas]</a>
                    <a href="<?php echo "/admin/projects/move/{$project->id}"; ?>">[Nodo]</a>
                    <a href="<?php echo "/admin/projects/open_tags/{$project->id}"; ?>">[Agrupación]</a>
                    <?php if ($project->status < 4) : ?><a href="<?php echo "/admin/projects/rebase/{$project->id}"; ?>" onclick="return confirm('Esto es MUY DELICADO, seguimos?');">[Id]</a><?php endif; ?>
                    <a href="<?php echo "/admin/projects/consultants/{$project->id}"; ?>">[Asesor]</a>
                    &nbsp;|&nbsp;
                    <?php if ($project->status < 2) : ?><a href="<?php echo "/admin/projects/review/{$project->id}"; ?>" onclick="return confirm('El creador no podrá editarlo más, ok?');">[A revisión]</a><?php endif; ?>
                    <?php if ($project->status < 3 && $project->status > 0) : ?><a href="<?php echo "/admin/projects/publish/{$project->id}"; ?>" onclick="return confirm('El proyecto va a comenzar los 40 dias de la primera ronda de campaña, ¿comenzamos?');">[Publicar]</a><?php endif; ?>
                    <?php if ($project->status != 1) : ?><a href="<?php echo "/admin/projects/enable/{$project->id}"; ?>" onclick="return confirm('Mucho Ojo! si el proyecto esta en campaña, ¿Reabrimos la edicion?');">[Reabrir edición]</a><?php endif; ?>
                    <?php if ($project->status == 4) : ?><a href="<?php echo "/admin/projects/fulfill/{$project->id}"; ?>" onclick="return confirm('El proyecto pasara a ser un caso de éxito, ok?');">[Retorno Cumplido]</a><?php endif; ?>
                    <?php if ($project->status == 5) : ?><a href="<?php echo "/admin/projects/unfulfill/{$project->id}"; ?>" onclick="return confirm('Lo echamos un paso atras, ok?');">[Retorno Pendiente]</a><?php endif; ?>
                    <?php if ($project->status < 3 && $project->status > 0) : ?><a href="<?php echo "/admin/projects/cancel/{$project->id}"; ?>" onclick="return confirm('El proyecto va a desaparecer del admin, solo se podra recuperar desde la base de datos, Ok?');">[Descartar]</a><?php endif; ?>
                    <?php if ($project->status == 3) : ?><a href="<?php echo "/admin/projects/noinvest/{$project->id}"; ?>" onclick="return confirm('No se podrá aportar más pero sigue técnicamente en campaña hasta final de ronda, ok?');">[Cortar el grifo]</a><?php endif; ?>
                </td>
            </tr>
            <tr>
                <td colspan="5">
                    GESTIONAR:&nbsp;
                    <?php if ($project->status == 1) : ?><a href="<?php echo "/admin/reviews/add/{$project->id}"; ?>" onclick="return confirm('Se va a iniciar revisión de un proyecto en estado Edición, ok?');">[Iniciar revisión]</a><?php endif; ?>
                    <?php if ($project->status == 2) : ?><a href="<?php echo "/admin/reviews/?project=".urlencode($project->id); ?>">[Ir a la revisión]</a><?php endif; ?>
                    <?php if ($project->translate) : ?><a href="<?php echo "/admin/translates/edit/{$project->id}"; ?>">[Ir a la traducción]</a>
                    <?php else : ?><a href="<?php echo "/admin/translates/add/?project={$project->id}"; ?>">[Habilitar traducción]</a><?php endif; ?>
                    <a href="/admin/projects/images/<?php echo $project->id; ?>">[Organizar imágenes]</a>
                    <?php if (in_array($project->status, array('1', '2', '3')) && !isset($project->called)) : ?><a href="<?php echo "/admin/projects/assign/{$project->id}"; ?>">[Asignarlo a una convocatoria]</a><?php endif; ?>
                    <a href="/admin/commons?project=<?php echo $project->id; ?>">[Retornos colectivos]</a>
                    <?php if (isset($this['contracts'][$project->id])) : ?><a href="<?php echo "/admin/contracts/preview/{$project->id}"; ?>">[Contrato]</a><?php endif; ?>
                    <?php if ($project->status < 3) : ?><a href="<?php echo "/admin/projects/reject/{$project->id}"; ?>" onclick="return confirm('Se va a enviar un mail automáticamente pero no cambiará el estado, ok?');">[Rechazo express]</a><?php endif; ?>
                </td>
            </tr>
        </tbody>

    </table>
</div>
<?php endwhile; ?>
<ul id="pagination">
<?php   $pagedResults->setLayout(new DoubleBarLayout());
        echo $pagedResults->fetchPagedNavigation($the_filters); ?>
</ul>
<?php endif; ?>

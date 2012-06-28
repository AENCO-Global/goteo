<?php

use Goteo\Library\Text;

$data = $this['data'];
?>
<div class="widget board">
    <p>Ahora mismo en paypal debería haber <strong><?php echo \amount_format($data->total, 2); ?> &euro;</strong> aproximadamente.</p>
    <p>Preapprovals ejecutados (<?php echo $data->charged->num; ?>):<br />
        Suman <strong><?php echo \amount_format($data->charged->amount, 2) ?> &euro;</strong>, la comisi&oacute;n calculada (3,4% de la cantidad y 0,35 &euro; por cada aporte) es de <strong><?php echo \amount_format($data->charged->fee, 2) ?> &euro;</strong>, da un neto de <strong><?php echo \amount_format($data->charged->net, 2) ?> &euro;</strong> y Goteo tiene el 100% de este neto.</p>
    <p>Aportes pagados al impulsor (<?php echo $data->paid->num; ?>):<br />
        Suman <strong><?php echo \amount_format($data->paid->amount, 2) ?> &euro;</strong>, la comisi&oacute;n calculada (3,4% de la cantidad y 0,35 &euro; por cada aporte) es de <strong><?php echo \amount_format($data->paid->fee, 2) ?> &euro;</strong>, da un neto de <strong><?php echo \amount_format($data->paid->net, 2) ?> &euro;</strong> y Goteo ha mantenido el 8% de este neto que son <strong><?php echo \amount_format($data->paid->goteo, 2) ?> &euro;</strong>.</p>
    <p>Total:<br /><strong><?php echo \amount_format($data->charged->goteo, 2) ?> &euro;</strong> temporales + <strong><?php echo \amount_format($data->paid->goteo, 2) ?> &euro;</strong> de beneficio = <strong><?php echo \amount_format($data->total, 2) ?> &euro;</strong></p>
</div>

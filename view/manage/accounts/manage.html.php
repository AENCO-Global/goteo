<?php

use Goteo\Library\Text,
    Goteo\Library\Paypal,
    Goteo\Library\Tpv;

$invest = $this['invest'];
$project = $this['project'];
$calls = $this['calls'];
$droped = $this['droped'];
$user = $this['user'];

$rewards = $invest->rewards;
array_walk($rewards, function (&$reward) { $reward = $reward->reward; });
?>
<a href="/manage/accounts/update/<?php echo $invest->id ?>" onclick="return confirm('Seguro que deseas cambiarle el estado a este aporte?, esto es delicado')" class="button">Cambiarle el estado</a>
&nbsp;&nbsp;&nbsp;
<a href="/manage/rewards/edit/<?php echo $invest->id ?>" class="button">Gestionar recompensa / dirección</a>
<?php if ($invest->issue) : ?>
&nbsp;&nbsp;&nbsp;
<a href="/manage/accounts/solve/<?php echo $invest->id ?>" onclick="return confirm('Esta incidencia se dará por resuelta: se va a cancelar el preaproval, el aporte pasará a ser de tipo Cash y en estado Cobrado por goteo, seguimos?')" class="button">Nos han hecho la transferencia</a>
<?php endif; ?>
<div class="widget">
    <p>
        <strong>Proyecto:</strong> <?php echo $project->name ?> (<?php echo $this['status'][$project->status] ?>)
        <strong>Usuario: </strong><?php echo $user->name ?> [<?php echo $user->email ?>]
    </p>
    <p>
        <?php if ($invest->status < 1 || ($invest->method == 'tpv' && $invest->status < 2) ||($invest->method == 'cash' && $invest->status < 2)) : ?>
        <a href="/manage/accounts/cancel/<?php echo $invest->id ?>"
            onclick="return confirm('¿Estás seguro de querer cancelar este aporte y su preapproval?');"
            class="button">Cancelar este aporte</a>&nbsp;&nbsp;&nbsp;
        <?php endif; ?>

        <?php if ($invest->method == 'paypal' && $invest->status == 0) : ?>
        <a href="/manage/accounts/execute/<?php echo $invest->id ?>"
            onclick="return confirm('¿Seguro que quieres ejecutar ahora el cargo del preapproval?');"
            class="button">Ejecutar cargo ahora</a>
        <?php endif; ?>

        <?php if (!$invest->resign && $invest->status == 1 && $invest->status == 3) : ?>
        <a href="/manage/accounts/resign/<?php echo $invest->id ?>/?token=<?php echo md5('resign'); ?>" class="button">Es donativo</a>
        <?php endif; ?>
    </p>
    
    <h3>Detalles de la transaccion</h3>
    <dl>
        <dt>Cantidad aportada:</dt>
        <dd><?php echo $invest->amount ?> &euro;
            <?php
                if (!empty($invest->campaign))
                    echo 'Campaña: ' . $campaign->name;
            ?>
        </dd>
    </dl>

    <dl>
        <dt>Estado:</dt>
        <dd><?php echo $this['investStatus'][$invest->status]; if ($invest->status < 0) echo ' <span style="font-weight:bold; color:red;">OJO! que este aporte no fue confirmado.<span>'; if ($invest->issue) echo ' <span style="font-weight:bold; color:red;">INCIDENCIA!<span>'; ?></dd>
    </dl>

    <dl>
        <dt>Fecha del aporte:</dt>
        <dd><?php echo $invest->invested . '  '; ?>
            <?php
                if (!empty($invest->charged))
                    echo 'Cargo ejecutado el: ' . $invest->charged;

                if (!empty($invest->returned))
                    echo 'Dinero devuelto el: ' . $invest->returned;
            ?>
        </dd>
    </dl>

    <dl>
        <dt>Donativo:</dt>
        <dd>
            <?php echo ($invest->resign) ? 'SI' : 'NO'; ?>
        </dd>
    </dl>

    <dl>
        <dt>Método de pago:</dt>
        <dd><?php echo $invest->method . '   '; ?>
            <?php
                if (!empty($invest->campaign))
                    echo '<br />Capital riego';

                if (!empty($invest->anonymous))
                    echo '<br />Aporte anónimo';

                if (!empty($invest->resign))
                    echo "<br />Donativo de: {$invest->address->name} [{$invest->address->nif}]";

                if (!empty($invest->admin))
                    echo '<br />Manual generado por admin: '.$invest->admin;
            ?>
        </dd>
    </dl>

    <dl>
        <dt>Códigos de seguimiento:</dt>
        <dd><?php
                if (!empty($invest->preapproval)) {
                    echo 'Preapproval: '.$invest->preapproval . '   ';
                }

                if (!empty($invest->payment)) {
                    echo 'Cargo: '.$invest->payment . '   ';
                }
            ?>
        </dd>
    </dl>

    <?php if (!empty($invest->rewards)) : ?>
    <dl>
        <dt>Recompensas elegidas:</dt>
        <dd>
            <?php echo implode(', ', $rewards); ?>
        </dd>
    </dl>
    <?php endif; ?>

    <dl>
        <dt>Dirección:</dt>
        <dd>
            <?php echo $invest->address->address; ?>,
            <?php echo $invest->address->location; ?>,
            <?php echo $invest->address->zipcode; ?>
            <?php echo $invest->address->country; ?>
        </dd>
    </dl>

    <?php if ($invest->method == 'paypal') : ?>
        <?php if (!isset($_GET['full'])) : ?>
        <p>
            <a href="/manage/accounts/details/<?php echo $invest->id; ?>/?full=show">Mostrar detalles técnicos</a>
        </p>
        <?php endif; ?>

        <?php if (!empty($invest->transaction)) : ?>
        <dl>
            <dt><strong>Detalles de la devolución:</strong></dt>
            <dd>Hay que ir al panel de paypal para ver los detalles de una devolución</dd>
        </dl>
        <?php endif ?>
    <?php elseif ($invest->method == 'tpv') : ?>
        <p>Hay que ir al panel del banco para ver los detalles de los aportes mediante TPV.</p>
    <?php else : ?>
        <p>No hay nada que hacer con los aportes manuales.</p>
    <?php endif ?>

    <?php if (!empty($droped)) : ?>
    <h3>Capital riego asociado</h3>
    <dl>
        <dt>Convocatoria:</dt>
        <dd><?php echo $calls[$droped->call] ?></dd>
    </dl>
    <a href="/manage/invests/details/<?php echo $droped->id ?>" target="_blank">Ver aporte completo de riego</a>
    <?php endif; ?>

</div>

<div class="widget">
    <h3>Log</h3>
    <?php foreach (\Goteo\Model\Invest::getDetails($invest->id) as $log)  {
        echo "{$log->date} : {$log->log} ({$log->type})<br />";
    } ?>
</div>

<?php if (isset($_GET['full']) && $_GET['full'] == 'show') : ?>
<div class="widget">
    <h3>Detalles técnicos de la transaccion</h3>
    <?php if (!empty($invest->preapproval)) :
        $details = Paypal::preapprovalDetails($invest->preapproval);
        ?>
    <dl>
        <dt><strong>Detalles del preapproval:</strong></dt>
        <dd><?php echo \trace($details); ?></dd>
    </dl>
    <?php endif ?>

    <?php if (!empty($invest->payment)) :
        $details = Paypal::paymentDetails($invest->payment);
        ?>
    <dl>
        <dt><strong>Detalles del cargo:</strong></dt>
        <dd><?php echo \trace($details); ?></dd>
    </dl>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
use Goteo\Library\Text;
?>
<div class="widget">
    <p><?php echo 'Buscábamos comunicarnos con ' . $_SESSION['mailing']['filters_txt']; ?> </p>
    <p>Se ha iniciado un nuevo <a href="/admin/newsletter" target="_blank">mailing masivo</a> a <?php echo count($this['users']);?> usuarios con el asunto "<?php echo $this['subject']; ?>".</p>
</div>


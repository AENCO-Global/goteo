<?php

use Goteo\Library\Text,
    Goteo\Library\Buzz,
    Goteo\Core\View;

$call = $this['call'];
$URL = \SITE_URL;
?>

<div id="title">
	<a href="<?php echo $URL ?>/call/<?php echo $call->id ?>"><img src="<?php echo SRC_URL.'/images/'.$call->logo->name; /*$call->logo->getLink(250, 124, true)*/ ?>" alt="<?php echo $call->user->name ?>" class="logo" /></a>
	<h2 class="title"><?php echo $call->name ?></h2>
</div>

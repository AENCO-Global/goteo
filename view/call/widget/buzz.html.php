<?php
# if ($_SESSION['user']->id == 'root' && !empty($social->buzz_debug)) echo '<p>DEBUG:: '. $social->buzz_debug . '</p>';
# if ($_SESSION['user']->id == 'root' && !empty($social->buzz)) echo \trace($social->buzz);

$social = $this['social'];

foreach ($social->buzz as $item) : ?>
<div style="border-bottom: 1px solid grey; display: block;">
    <div style="float: left; margin-right: 5px;">
        <a href="<?php echo $item->profile ?>" target="_blank">
            <img src="<?php echo $item->avatar ?>" alt="<?php echo $item->author ?>" title="<?php echo $item->user ?>"/>
        </a>
    </div>
    <div>
        <a href="<?php echo $item->profile ?>" target="_blank"><?php echo $item->user ?></a>
        <br />
        <a href="<?php echo 'https://twitter.com/'.$item->twitter_user ?>" target="_blank"><?php echo '@'.$item->twitter_user ?></a>
    </div>
    <br clear="both" />
    <blockquote><?php echo $item->text ?></blockquote>
</div>
<?php endforeach; ?>
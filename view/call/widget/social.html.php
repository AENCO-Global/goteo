<?php

use Goteo\Library\Text,
    Goteo\Library\Buzz,
    Goteo\Core\View;

$call = $this['call'];
$social = $this['social'];

$URL = (NODE_ID != GOTEO_NODE) ? NODE_URL : SITE_URL;
$share_url = $URL . '/call/' . $call->id;
if (LANG != 'es')
    $share_url .= '?lang=' . LANG;

$shate_title = (!empty($social->tweet)) ? $social->tweet : $call->name;
$facebook_url = 'http://facebook.com/sharer.php?u=' . urlencode($share_url) . '&t=' . urlencode($share_title);
?>
<ul>
    <?php if (!empty($call->user->facebook)): ?>
    <li class="facebook"><a href="<?php echo htmlspecialchars($call->user->facebook) ?>"><?php echo Text::get('regular-facebook'); ?></a></li>
    <?php endif ?>
    <?php if (!empty($call->user->google)): ?>
    <li class="google"><a href="<?php echo htmlspecialchars($call->user->google) ?>"><?php echo Text::get('regular-google'); ?></a></li>
    <?php endif ?>
    <?php if (!empty($call->user->twitter)): ?>
    <li class="twitter"><a href="<?php echo htmlspecialchars($call->user->twitter) ?>"><?php echo Text::get('regular-twitter'); ?></a></li>
    <?php endif ?>
    <?php if (!empty($call->user->identica)): ?>
    <li class="identica"><a href="<?php echo htmlspecialchars($call->user->identica) ?>"><?php echo Text::get('regular-identica'); ?></a></li>
    <?php endif ?>
    <?php if (!empty($call->user->linkedin)): ?>
    <li class="linkedin"><a href="<?php echo htmlspecialchars($call->user->linkedin) ?>"><?php echo Text::get('regular-linkedin'); ?></a></li>
    <?php endif ?>
</ul>
<a href="<?php echo $URL ?>/service/resources" id="capital" target="_blank"><?php echo Text::get('footer-service-resources') ?></a>

<!-- texto "difunde esta iniciativa"
y espacio donde irán los botones
-->
<hr />
<span>Difunde esta iniciativa</span>

<a href="https://twitter.com/share" class="twitter-share-button"
   data-url="<?php echo $share_url; ?>"
   data-via="<?php echo $social->author ?>"
   data-text="<?php echo $share_title; ?>"
   data-lang="<?php echo \LANG; ?>"
   data-counturl="<?php echo SITE_URL . '/call/' . $call->id; ?>"
   target="_blank"><?php echo Text::get('regular-twitter'); ?></a>
<script>!function(d,s,id){
    var js,fjs=d.getElementsByTagName(s)[0];
    if(!d.getElementById(id)){
        js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);
    }
}(document,"script","twitter-wjs");</script>

<?php if (!empty($social->fbappid)) : ?>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) {return;}
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/<?php echo \Goteo\Library\Lang::locale(); ?>/all.js#xfbml=1&appId=<?php echo $social->fbappid; ?>";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<?php else: // si no tiene app de facebook ponemos un compartir ?>
<a target="_blank" href="<?php echo htmlentities($facebook_url) ?>"><?php echo Text::get('regular-facebook'); ?></a>
<?php endif; ?>
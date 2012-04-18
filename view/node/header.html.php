<?php 
    use Goteo\Library\Text,
        Goteo\Library\Lang,
        Goteo\Core\NodeSys;

$nodeData = NodeSys::setData(NODE_ID);
?>
<?php include 'view/header/lang.html.php' ?>
<div id="header">
    <h1><?php echo Text::get('regular-main-header'); ?></h1>
    <div id="super-header">
		<div id="goteo-logo">
			<ul>
				<li class="home"><a href="<?php echo SITE_URL ?>">Inicio</a></li>
			</ul>
		</div>

	   <div id="rightside" style="float:right;">
           <div id="about">
                <ul>
                    <li><a href="/about"><?php echo Text::get('regular-header-about'); ?></a></li>
                    <li><a href="/blog"><?php echo Text::get('regular-header-blog'); ?></a></li>
                    <li><a href="/faq"><?php echo Text::get('regular-header-faq'); ?></a></li>
                    <li id="lang"><a href="#" ><?php echo Lang::get(LANG)->short ?></a></li>
                    <script type="text/javascript">
                    jQuery(document).ready(function ($) {
						 $("#lang").hover(function(){
						   //desplegar idiomas
						   try{clearTimeout(TID_LANG)}catch(e){};
						   var pos = $(this).offset().left;
						   $('ul.lang').css({left:pos+'px'});
						   $("ul.lang").fadeIn();
					       $("#lang").css("background","#808285 url('/view/css/bolita.png') 4px 7px no-repeat");

					   },function() {
						   TID_LANG = setTimeout('$("ul.lang").hide()',100);
						});
						$('ul.lang').hover(function(){
							try{clearTimeout(TID_LANG)}catch(e){};
						},function() {
						   TID_LANG = setTimeout('$("ul.lang").hide()',100);
						   $("#lang").css("background","#59595C url('/view/css/bolita.png') 4px 7px no-repeat");
						});


					});
					</script>
                </ul>
            </div>

		</div>

    </div>

    <div id="node-header">
        <div class="logos">
            <div class="node-home"><a href="<?php echo NODE_URL ?>"><?php echo NODE_NAME ?></a></div>
            <div class="node-intro"><?php echo $nodeData->description; ?></div>
            <?php if ($nodeData->logo instanceof \Goteo\Model\Image) : ?>
            <div class="node-logo">
                <span>Con el soporte de:</span>
                <img src="<?php echo $nodeData->logo->getLink(150, 75) ?>" alt="<?php echo $nodeData->subtitle ?>" />
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'view/node/menu.html.php' ?>
</div>

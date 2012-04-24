<?php

use Goteo\Library\Text,
    Goteo\Model\Category,
    Goteo\Model\Post,  // esto son entradas en portada o en footer
    Goteo\Model\Sponsor;

$lang = (LANG != 'es') ? '?lang='.LANG : '';

$posts      = Post::getList('footer');
?>

   <div id="footer">
		<div class="w940">
			<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('.scroll-pane').jScrollPane({showArrows: true});
			});
			</script>
        	<div class="block about" style="border-left:none;">
            	<span class="title"><a href="#">Goteo</a></span>
                <div style="margin-top:-5px" class="scroll-pane">
                <p>Red social para cofinanciar y colaborar
                con proyectos creativos que fomentan el
                procom�n <a href="#">�Tienes un proyecto con adn abierto?</a></p></div>
            </div>

            <div class="block help">
                <span class="title">Necesitas ayuda?</span>
                <div>
                	<ul class="scroll-pane">
                    <li><a href="#">Qu� es el Crowfunding</a></li>
                    <li><a href="#">Porqu� Goteo es diferente</a></li>                    
                    <li><a href="#">FAQ</a></li>                                        
                    <li><a href="#">Glosario</a></li>                                                            
                    </ul>
                </div>
            </div>

            <div class="block creators">
                <span class="title">Impulsores</span>
                <ul class="scroll-pane">
                    <li><a href="#">�Es goteable tu proyecto?</a></li>
                    <li><a href="#">Qu� son los retornos colectivos</a></li>                    
                    <li><a href="#">10 pasos a seguir</a></li>                                        
                    <li><a href="#">Pack de comunicaci�n</a></li>                                                            
                    </ul>
            </div>

            <div class="block investors">
                <span class="title">Cofinanciadores</span>
                <ul class="scroll-pane">
                    <li><a href="#">C�mo apoyar un proyecto</a></li>
                    <li><a href="#">C�mo funcionan los pagos</a></li>                    
                    <li><a href="#">Donaciones y desgravaciones</a></li>                                        
                    </ul>
            </div>

            <div class="block social">
                <span class="title">S&iacute;ganos</span>
                <ul class="scroll-pane">
                    <li class="twitter"><a href="<?php echo Text::get('social-account-twitter') ?>" target="_blank"><?php echo Text::get('regular-twitter') ?></a></li>
                    <li class="facebook"><a href="<?php echo Text::get('social-account-facebook') ?>" target="_blank"><?php echo Text::get('regular-facebook') ?></a></li>
                    <li class="identica"><a href="<?php echo Text::get('social-account-identica') ?>" target="_blank"><?php echo Text::get('regular-identica') ?></a></li>
                    <li class="gplus"><a href="<?php echo Text::get('social-account-google') ?>" target="_blank"><?php echo Text::get('regular-google') ?></a></li>
                    <li class="rss"><a rel="alternate" type="application/rss+xml" title="RSS" href="/rss<?php echo $lang ?>" target="_blank"><?php echo Text::get('regular-share-rss'); ?></a></li>

                </ul>
            </div>

		</div>
    </div>

    <div id="sub-footer">
		<div class="w940">

                <ul>
                    <li><a href="<?php echo SITE_URL ?>/about">Goteo.org</a></li>
                    <li><a href="/user/login"><?php echo Text::get('regular-login'); ?></a></li>
                    <li><a href="/blog"><?php echo Text::get('regular-header-blog'); ?></a></li> 
                    <li><a href="/about/press"><?php echo Text::get('footer-resources-press'); ?></a></li>
                    <li><a href="/legal/privacy"><?php echo Text::get('regular-footer-privacy'); ?></a></li>
                    <li><a href="/contact"><?php echo Text::get('regular-footer-contact'); ?></a></li>
                </ul>

                <div class="platoniq">
                   <span class="text"><a href="#" class="poweredby"><?php echo Text::get('footer-platoniq-iniciative') ?></a></span>
                   <!--<span class="logo"><a href="http://fuentesabiertas.org" target="_blank" class="foundation">FFA</a></span>-->
                   <span class="logo"><a href="http://www.youcoop.org" target="_blank" class="growby">Platoniq</a></span>
                </div>


        </div>

    </div>
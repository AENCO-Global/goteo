<?php
use Goteo\Core\View,
    Goteo\Library\Text;

$stories = $this['stories'];

?>
<div class="widget stories-home" style="padding:0;">

    

	<script type="text/javascript">
    $(function(){
        $('#stories-banners').slides({
            container: 'stories-banners-container',
            paginationClass: 'bannerspage',
            generatePagination: true,
            effect: 'slide',
            play:8000
        });
    });
</script>
<div id="stories-banners" class="rounded-corners-bottom">
    <div class="stories-banners-container rounded-corners-bottom">

        <?php foreach ($stories as $story) : 

        echo new View('view/stories/story.html.php', array('story'=>$story));

        endforeach; ?>

    </div>
    <div id="stories-banners-controler"><ul class="bannerspage"></ul></div>
</div>

</div>

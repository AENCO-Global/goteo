<?php

use Goteo\Core\View,
    Goteo\Library\Text;

$story = $this['story'];

if(!empty($story->post))
    $link_title='/blog/'.$story->post;
else if(!empty($story->url))
    $link_title=$story->url;
else
    $link_title='/project/'.$story->project->id;
if(empty($story->url))
    $link_background='/project/'.$story->project->id;
else
    $link_background=$story->url;

foreach ($story->project->open_tags as $openId => $openData) {
            if($openId!="post") 
                $open_tags=$openData;
            else if($openData) 
                $open_tags='<a href="/blog/'.$openData.'">'.$open_tags.'</a>';
}

?>
<div class="stories-banner<?php if (!empty($story->project)) echo ' activable'; ?>"<?php if ($story->image instanceof \Goteo\Model\Image) : ?> style="background: url('<?php echo $story->image->getLink(940, 385, true); ?>');"<?php endif; ?>>
    <a href="<?php echo $link_background;?>" class="expand" target="_blank"></a>
    <div class="title_story"><strong><?php echo Text::get('home-stories-header').': '?></strong><?php echo $open_tags; ?></div>
        <div class="info">
            <a href="<?php echo $link_title;?>" target="_blank">
                <div id="info_title"><?php echo htmlspecialchars($story->title); ?></div>
            </a>
            <a href="<?php echo $link_title;?>" target="_blank">
                <div id="review"><?php echo htmlspecialchars($story->review); ?></div>
            </a>
            <div id="line"></div>
            <div id="description"><blockquote><?php echo htmlspecialchars($story->description).'</blockquote>. '.'<a href="/user/profile/'.$story->project->user->id.'" target="_blank">'.htmlspecialchars($story->project->user->name).'</a>, del proyecto: '.'<a href="/project/'.$story->project->id.'" target="_blank">'.htmlspecialchars($story->project->name).'</a>'; ?></div>
        </div>
        <div class="info_extra">
            <span id="cofinanciadores"><?php echo  mb_strtoupper(Text::get('project-view-metter-investors'));?></span> <strong id="ncofinanciadores"><?php echo $story->project->num_investors;?></strong>
            <span id="obtenido"><span><?php echo  mb_strtoupper(Text::get('project-view-metter-got'));?></span><strong><?php echo $story->project->amount;?></strong><img src="/view/css/euro/violet/xl.png" width="20"/></span>
        </div>            
    </div>                                   
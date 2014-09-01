<?php

use Goteo\Library\Text,
    Goteo\Model,
    Goteo\Core\Redirection,
    Goteo\Library\NormalForm;

$post = $this['post'];

if (!$post instanceof Model\Info) {
    throw new Redirection('/admin/info');
}

$allow = array(
    array(
        'value'     => 1,
        'label'     => 'Sí'
        ),
    array(
        'value'     => 0,
        'label'     => 'No'
        )
);


$images = array();
foreach ($post->gallery as $image) {
    $images[] = array(
        'type'  => 'html',
        'class' => 'inline gallery-image',
        'html'  => is_object($image) ?
                   $image . '<img src="' . SITE_URL . '/image/' . $image->id . '/128/128" alt="Imagen" /><button class="image-remove weak" type="submit" name="gallery-'.$image->hash.'-remove" title="Quitar imagen" value="remove"></button>' :
                   ''
    );

}

?>
<script type="text/javascript" src="<?php echo SRC_URL; ?>/view/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	// Lanza wysiwyg contenido
	CKEDITOR.replace('text_editor', {
		toolbar: 'Full',
		toolbar_Full: [
				['Source','-'],
				['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
				['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
				'/',
				['Bold','Italic','Underline','Strike'],
				['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
				['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
				['Link','Unlink','Anchor'],
                ['Image','Format','FontSize'],
			  ],
		skin: 'kama',
		language: 'es',
		height: '300px',
		width: '675px'
	});
});
</script>

<form method="post" action="/admin/info/<?php echo $this['action']; ?>/<?php echo $post->id; ?>" class="project" enctype="multipart/form-data" style="margin-bottom: 20px;">

    <?php echo new NormalForm(array(

        'action'        => '',
        'level'         => 3,
        'method'        => 'post',
        'class'         => 'aqua',
        'footer'        => array(
            'view-step-preview' => array(
                'type'  => 'submit',
                'name'  => 'save-post',
                'label' => Text::get('regular-save'),
                'class' => 'next'
            ),
            'pending' => array(
                'type'  => 'checkbox',
                'name'  => 'pending',
                'label' => Text::get('mark-pending'),
            )
        ),
        'elements'      => array(
            'id' => array (
                'type' => 'hidden',
                'value' => $post->id
            ),
            'order' => array (
                'type' => 'hidden',
                'value' => $post->order
            ),
            'node' => array (
                'type' => 'hidden',
                'value' => $post->node
            ),
            'title' => array(
                'type'      => 'textbox',
                'size'      => 20,
                'title'     => 'Idea',
                'value'     => $post->title,
            ),
            'text' => array(
                'type'      => 'textarea',
                'cols'      => 40,
                'rows'      => 4,
                'title'     => 'Explicación de la idea',
                'value'     => $post->text
            ),
            'image' => array(
                'title'     => 'Imagen',
                'type'      => 'group',
                'class'     => 'image',
                'children'  => array(
                    'image_upload'    => array(
                        'type'  => 'file',
                        'class' => 'inline image_upload',
                        'label' => 'Subir',
                        'title' => 'Subir una imagen'
                    )
                )
            ),

            'gallery' => array(
                'type'  => 'group',
                'title' => Text::get('overview-field-image_gallery'),
                'class' => 'inline',
                'children'  => $images
            ),

            'media' => array(
                'type'      => 'textbox',
                'title'     => 'Vídeo',
                'class'     => 'media',
                'value'     => (string) $post->media,
                'children'  => array(
                    'media-preview' => array(
                        'title' => 'Vista previa',
                        'class' => 'media-preview inline',
                        'type'  => 'html',
                        'html'  => !empty($post->media) ? $post->media->getEmbedCode() : ''
                    )
                )
            ),
            'legend' => array(
                'type'      => 'textarea',
                'title'     => Text::get('regular-media_legend'),
                'value'     => $post->legend,
            ),

            'publish' => array(
                'title'     => 'Publicada',
                'type'      => 'slider',
                'options'   => $allow,
                'class'     => 'currently cols_' . count($allow),
                'value'     => (int) $post->publish
            )

        )

    ));
    ?>

</form>
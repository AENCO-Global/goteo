<?php

use Goteo\Library\Text,
    Goteo\Model,
    Goteo\Core\Redirection,
    Goteo\Library\NormalForm;

$node = $this['node'];

if (!$node instanceof Model\Node) {
    throw new Redirection('/admin');
}
?>
<form method="post" action="/admin/node/edit" enctype="multipart/form-data">

    <?php echo new NormalForm(array(

        'action'        => '',
        'level'         => 3,
        'method'        => 'post',
        'title'         => '',
        'class'         => 'aqua',
        'footer'        => array(
            'view-step-preview' => array(
                'type'  => 'submit',
                'name'  => 'save-node',
                'label' => Text::get('regular-save'),
                'class' => 'next'
            )
        ),
        'elements'      => array(
            'name' => array(
                'type'      => 'textbox',
                'size'      => 20,
                'title'     => 'Nombre',
                'value'     => $node->name,
            ),
            'subtitle' => array(
                'type'      => 'textbox',
                'size'      => 20,
                'title'     => 'Título',
                'value'     => $node->subtitle,
            ),

            'description' => array(
                'type'      => 'textarea',
                'cols'      => 40,
                'rows'      => 4,
                'title'     => 'Presentación',
                'value'     => $node->description
            ),

            'logo' => array(
                'type' => 'hidden',
                'value' => $node->logo->id,
            ),
            
            'thelogo' => array(
                'type'      => 'group',
                'title'     => 'Logo',
                'class'     => 'user_avatar',
                'children'  => array(
                    'logo_upload'    => array(
                        'type'  => 'file',
                        'label' => Text::get('form-image_upload-button'),
                        'class' => 'inline avatar_upload'
                    ),
                    'logo-image' => array(
                        'type'  => 'html',
                        'class' => 'inline avatar-image',
                        'html'  => is_object($node->logo) ?
                                   $node->logo . '<img src="'.SRC_URL.'/image/' . $node->logo->id . '/128/128" alt="Avatar" /><button class="image-remove" type="submit" name="logo-'.$node->logo->id.'-remove" title="Quitar este logo" value="remove">X</button>' :
                                   ''
                    )

                )
            ),

            'email' => array(
                'type'      => 'textbox',
                'size'      => 20,
                'title'     => 'Email',
                'value'     => $node->email,
            ),
            
            'location' => array(
                'type'      => 'textbox',
                'size'      => 20,
                'title'     => 'Localización',
                'value'     => $node->location
            )

        )

    ));
    ?>

</form>
<?php

use Goteo\Core\View,
    Goteo\Library\Text,
    Goteo\Library\SuperForm;

$call = $this['call'];
$errors = $call->errors[$this['step']] ?: array();
$okeys  = $call->okeys[$this['step']] ?: array();

$categories = array();

foreach ($this['categories'] as $value => $label) {
    $categories[] =  array(
        'value'     => $value,
        'label'     => $label,
        'checked'   => in_array($value, $call->categories)
        );            
}

// retornos en opcion checkboxes con icono y descripcion
$icons = array();

foreach ($this['icons'] as $id=>$icon) {
    $rewards["icon-{$icon->id}"] =  array(
        'name'  => "icons[]",
        'value' => $icon->id,
        'type'  => 'checkbox',
        'class' => "icon {$icon->id}",
        'label' => $icon->name,
        'hint'  => $icon->description,
        'id'    => "icon-{$icon->id}",
        'checked' => in_array($id, $call->icons)
    );
}


$scope = array();

foreach ($this['scope'] as $value => $label) {
    $scope[] =  array(
        'value'     => $value,
        'label'     => $label
        );
}


/*
<script type="text/javascript" src="/view/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	// wysiwyg para la descripción de la convocatoria
	CKEDITOR.replace('description_editor', {
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
		width: '660px'
	});

	// wysiwyg para los terminos y condiciones
	CKEDITOR.replace('legal_editor', {
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
		width: '640px'
	});
});
</script>
*/

$superform = array(
    'level'         => $this['level'],
    'action'        => '',
    'method'        => 'post',
    'title'         => Text::get('call-overview-main-header'),
    'hint'          => Text::get('guide-call-description'),
    'class'         => 'aqua',        
    'elements'      => array(
        'process_overview' => array (
            'type' => 'hidden',
            'value' => 'overview'
        ),
        
        'name' => array(
            'type'      => 'textbox',
            'title'     => Text::get('call-field-name'),
            'required'  => true,
            'hint'      => Text::get('tooltip-call-name'),
            'value'     => $call->name,
            'errors'    => !empty($errors['name']) ? array($errors['name']) : array(),
            'ok'        => !empty($okeys['name']) ? array($okeys['name']) : array()
        ),
        
        'subtitle' => array(
            'type'      => 'textbox',
            'title'     => Text::get('overview-field-subtitle'),
            'required'  => false,
            'value'     => $call->subtitle,
            'hint'      => Text::get('tooltip-call-subtitle'),
            'errors'    => !empty($errors['subtitle']) ? array($errors['subtitle']) : array(),
            'ok'        => !empty($okeys['subtitle']) ? array($okeys['subtitle']) : array()
        ),

        'logo' => array(
            'type'      => 'group',
            'required'  => true,
            'title'     => Text::get('call-field-logo'),
            'hint'      => Text::get('tooltip-call-logo'),
            'errors'    => !empty($errors['logo']) ? array($errors['logo']) : array(),
            'ok'        => !empty($okeys['logo']) ? array($okeys['logo']) : array(),
            'class'     => 'logo',
            'children'  => array(
                'logo_upload'    => array(
                    'type'  => 'file',
                    'label' => Text::get('form-image_upload-button'),
                    'class' => 'inline image_upload',
                    'hint'  => Text::get('tooltip-call-logo'),
                ),
                'logo-current' => array(
                    'type' => 'hidden',
                    'value' => $call->logo,
                ),
                'logo-image' => array(
                    'type'  => 'html',
                    'class' => 'inline logo-image',
                    'html'  => !empty($call->logo)  ?
                               '<img src="'.SRC_URL.'/image/' . $call->logo . '" alt="Logo" /><button class="image-remove" type="submit" name="logo-'.$call->logo.'-remove" title="Quitar imagen" value="remove">X</button>' :
                               ''
                )

            )
        ),

        'image' => array(
            'type'      => 'group',
            'required'  => true,
            'title'     => Text::get('call-field-image'),
            'hint'      => Text::get('tooltip-call-image'),
            'errors'    => !empty($errors['image']) ? array($errors['image']) : array(),
            'ok'        => !empty($okeys['image']) ? array($okeys['image']) : array(),
            'class'     => 'image',
            'children'  => array(
                'image_upload'    => array(
                    'type'  => 'file',
                    'label' => Text::get('form-image_upload-button'),
                    'class' => 'inline avatar_upload',
                    'hint'  => Text::get('tooltip-call-image'),
                ),
                'image-current' => array(
                    'type' => 'hidden',
                    'value' => $call->image,
                ),
                'image-image' => array(
                    'type'  => 'html',
                    'class' => 'inline image-image',
                    'html'  => !empty($call->image) ?
                               '<img src="'.SRC_URL.'/image/' . $call->image . '" alt="Imagen" /><button class="image-remove" type="submit" name="image-'.$call->image.'-remove" title="Quitar imagen" value="remove">X</button>' :
                               ''
                )

            )
        ),

        'description' => array(            
            'type'      => 'textarea',
            'title'     => Text::get('call-field-description'),
            'required'  => true,
            'hint'      => Text::get('tooltip-call-description'),
            'value'     => $call->description,
            'class'     => 'inline',
            'errors'    => !empty($errors['description']) ? array($errors['description']) : array(),
            'ok'        => !empty($okeys['description']) ? array($okeys['description']) : array()
        ),
        
        'description_group' => array(
            'type'      => 'group',
            'class'     => 'inline',
            'children'  => array(                
                'whom' => array(
                    'type'      => 'textarea',       
                    'title'     => Text::get('call-field-whom'),
                    'required'  => true,
                    'hint'      => Text::get('tooltip-call-whom'),
                    'errors'    => !empty($errors['whom']) ? array($errors['whom']) : array(),
                    'ok'        => !empty($okeys['whom']) ? array($okeys['whom']) : array(),
                    'value'     => $call->whom
                ),
                'apply' => array(
                    'type'      => 'textarea',       
                    'title'     => Text::get('call-field-apply'),
                    'required'  => true,
                    'hint'      => Text::get('tooltip-call-apply'),
                    'errors'    => !empty($errors['apply']) ? array($errors['apply']) : array(),
                    'ok'        => !empty($okeys['apply']) ? array($okeys['apply']) : array(),
                    'value'     => $call->apply
                ),
                'legal' => array(
                    'type'      => 'textarea',
                    'title'     => Text::get('call-field-legal'),
                    'hint'      => Text::get('tooltip-call-legal'),
                    'errors'    => !empty($errors['legal']) ? array($errors['legal']) : array(),
                    'ok'        => !empty($okeys['legal']) ? array($okeys['legal']) : array(),
                    'value'     => $call->legal
                ),
            )
        ),
       
        'dossier' => array(
            'type'      => 'textbox',
            'title'     => Text::get('overview-field-dossier'),
            'required'  => false,
            'value'     => $call->dossier,
            'hint'      => Text::get('tooltip-call-dossier'),
            'errors'    => !empty($errors['dossier']) ? array($errors['dossier']) : array(),
            'ok'        => !empty($okeys['dossier']) ? array($okeys['dossier']) : array()
        ),

        'category' => array(    
            'type'      => 'checkboxes',
            'name'      => 'categories[]',
            'title'     => Text::get('call-field-categories'),
            'required'  => true,
            'class'     => 'cols_3',
            'options'   => $categories,
            'hint'      => Text::get('tooltip-call-category'),
            'errors'    => !empty($errors['categories']) ? array($errors['categories']) : array(),
            'ok'        => !empty($okeys['categories']) ? array($okeys['categories']) : array()
        ),       

        'icons' => array(
            'type'      => 'group',
            'title'     => Text::get('call-field-icons'),
            'required'  => true,
            'class'     => '',
            'children'  => $rewards,
            'hint'      => Text::get('tooltip-call-icons'),
            'errors'    => !empty($errors['icons']) ? array($errors['icons']) : array(),
            'ok'        => !empty($okeys['icons']) ? array($okeys['icons']) : array()
        ),


        'location' => array(
            'type'      => 'textbox',
            'name'      => 'call_location',
            'title'     => Text::get('call-field-call_location'),
            'required'  => true,
            'hint'      => Text::get('tooltip-call-call_location'),
            'errors'    => !empty($errors['call_location']) ? array($errors['call_location']) : array(),
            'ok'        => !empty($okeys['call_location']) ? array($okeys['call_location']) : array(),
            'value'     => $call->call_location
        ),

        'scope' => array(
            'title'     => Text::get('call-field-scope'),
            'type'      => 'slider',
            'required'  => true,
            'options'   => $scope,
            'class'     => 'inline scope cols_' . count($scope),
            'hint'      => Text::get('tooltip-call-scope'),
            'errors'    => !empty($errors['scope']) ? array($errors['scope']) : array(),
            'ok'        => !empty($okeys['scope']) ? array($okeys['scope']) : array(),
            'value'     => $call->scope
        ),

        'amount' => array(
            'type'      => 'textbox',
            'required'  => true,
            'title'     => Text::get('call-field-amount'),
            'size'      => 8,
            'class'     => 'amount',
            'hint'      => Text::get('tooltip-call-amount'),
            'errors'    => !empty($errors['amount']) ? array($errors['amount']) : array(),
            'ok'        => !empty($okeys['amount']) ? array($okeys['amount']) : array(),
            'value'     => $call->amount
        ),

        'resources' => array(
            'type'      => 'textarea',
            'title'     => Text::get('call-field-resources'),
            'required'  => true,
            'hint'      => Text::get('tooltip-call-resources'),
            'value'     => $call->resources,
            'errors'    => !empty($errors['resources']) ? array($errors['resources']) : array(),
            'ok'        => !empty($okeys['resources']) ? array($okeys['resources']) : array()
        ),

        'days' => array(
            'type'      => 'textbox',
            'title'     => Text::get('call-field-days'),
            'size'      => 8,
            'class'     => 'days',
            'hint'      => Text::get('tooltip-call-days'),
            'errors'    => !empty($errors['days']) ? array($errors['days']) : array(),
            'ok'        => !empty($okeys['days']) ? array($okeys['days']) : array(),
            'value'     => $call->days
        ),

        'footer' => array(
            'type'      => 'group',
            'children'  => array(
                'errors' => array(
                    'title' => Text::get('form-footer-errors_title'),
                    'view'  => new View('view/project/edit/errors.html.php', array(
                        'project'   => $call,
                        'step'      => $this['step']
                    ))                    
                ),
                'buttons'  => array(
                    'type'  => 'group',
                    'children' => array(
                        'next' => array(
                            'type'  => 'submit',
                            'name'  => 'view-step-preview',
                            'label' => Text::get('form-next-button'),
                            'class' => 'next'
                        )
                    )
                )
            )
        
        )

    )

);


foreach ($superform['elements'] as $id => &$element) {
    
    if (!empty($this['errors'][$this['step']][$id])) {
        $element['errors'] = arrray();
    }
    
}

echo new SuperForm($superform);
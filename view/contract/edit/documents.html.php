<?php

use Goteo\Core\View,
    Goteo\Library\Text,
    Goteo\Library\SuperForm;

$contract = $this['contract'];
$errors = $contract->errors[$this['step']] ?: array();
$okeys  = $contract->okeys[$this['step']] ?: array();

$docs = array();
foreach ($contract->docs as $doc) {
    $docs[] = array(
        'type'  => 'html',
        'class' => 'inline',
        'html'  => is_object($doc) ?
                   $doc->name . '<button type="submit" name="docs-'.$doc->id.'-remove" title="Quitar este documento" value="remove">[Quitar]</button>' :
                   \trace($doc)
    );

}

// campos de proyecto (descripción, objetivo, retornos)
// ya veremos si son editables por el impulsor o no
$descfields = array(
            'type' => 'group',
            'title'     => 'Proyecto',
            'children'  => array(                
                'project_description' => array(
                    'type'      => 'textarea',       
                    'title'     => 'Descripción del proyecto',
                    'hint'      => Text::get('tooltip-contract-project_description'),
                    'required'  => true,
                    'errors'    => !empty($errors['project_description']) ? array($errors['project_description']) : array(),
                    'ok'        => !empty($okeys['project_description']) ? array($okeys['project_description']) : array(),
                    'value'     => $contract->project_description
                ),
                'project_invest' => array(
                    'type'      => 'textarea',
                    'title'     => 'Objetivos de financiación',
                    'required'  => true,
                    'hint'      => Text::get('tooltip-contract-project_invest'),
                    'errors'    => !empty($errors['project_invest']) ? array($errors['project_invest']) : array(),
                    'ok'        => !empty($okeys['project_invest']) ? array($okeys['project_invest']) : array(),
                    'value'     => $contract->project_invest
                ),
                'project_return' => array(
                    'type'      => 'textarea',
                    'title'     => 'Retornos comprometidos',
                    'hint'      => Text::get('tooltip-contract-project_return'),
                    'required'  => true,
                    'errors'    => !empty($errors['project_return']) ? array($errors['project_return']) : array(),
                    'ok'        => !empty($okeys['project_return']) ? array($okeys['project_return']) : array(),
                    'value'     => $contract->project_return
                )
                
            )
        );



$superform = array(
    'level'         => $this['level'],
    'action'        => '',
    'method'        => 'post',
    'title'         => Text::get('contract-documents-main-header'),
    'hint'          => Text::get('guide-contract-documents'),
    'class'         => 'aqua',        
    'elements'      => array(
        'process_documents' => array (
            'type' => 'hidden',
            'value' => 'documents'
        ),
        
        'docs' => array(        
            'title'     => 'Documentación',
            'type'      => 'group',
            'required'  => true,
            'hint'      => Text::get('tooltip-contract-doc'),
            'errors'    => !empty($errors['doc']) ? array($errors['doc']) : array(),
            'ok'        => !empty($okeys['doc']) ? array($okeys['doc']) : array(),
            'class'     => 'doc',
            'children'  => array(
                'doc_upload'    => array(
                    'type'  => 'file',
                    'label' => 'Subir documento',
                    'class' => 'inline doc_upload',
                    'hint'  => Text::get('tooltip-contract-doc')
                )
            )
        ),        
        'documents' => array(
            'type'  => 'group',
            'title' => 'Documentos subidos',
            'class' => 'inline',
            'children'  => $docs
        ),

        'descfields' => $descfields,
        
        
        'footer' => array(
            'type'      => 'group',
            'children'  => array(
                'errors' => array(
                    'title' => Text::get('form-footer-errors_title'),
                    'view'  => new View('view/contract/edit/errors.html.php', array(
                        'contract'   => $contract,
                        'step'      => $this['step']
                    ))                    
                ),
                'buttons'  => array(
                    'type'  => 'group',
                    'children' => array(
                        'next' => array(
                            'type'  => 'submit',
                            'name'  => 'view-step-additional',
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
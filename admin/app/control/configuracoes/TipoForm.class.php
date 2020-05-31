<?php
/**
 * TipoForm Registration
 *
 * @version    1.0
 * @package    control
 * @subpackage configuracoes
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2019 (https://www.infort.eti.br)
 *
 */
class TipoForm extends TWindow
{
    protected $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        parent::setSize(0.7, null);
        parent::setMinWidth(0.9,500);
        parent::removePadding();
        parent::removeTitleBar();
        //parent::disableEscape();
        
        $this->setDatabase('sistema');              // defines the database
        $this->setActiveRecord('Tipo');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Tipo');
        $this->form->setFormTitle('Formulário de Tipo');
        $this->form->setFieldSizes('100%');
        
        $this->setAfterSaveAction(new TAction(['TipoList','onReload'],['register_state'=>'true']));
        
        // Mudando a cor do cabeçalho
        $this->form->setHeaderProperty('class','header bg-blue-grey');

        // create the form fields
        $id    = new TEntry('id');
        $nome  = new TEntry('nome');
        $ativo = new TRadioGroup('ativo');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Ativo') ], [ $ativo ] );

        $nome->addValidation('Nome', new TRequiredValidator);
        $ativo->addValidation('Ativo', new TRequiredValidator);


        // definindo parâmetros
        $id->setEditable(FALSE);
        $ativo->setSize(80);
        $ativo->addItems(['t'=>'Sim','f'=>'Não']);
        $ativo->setLayout('horizontal');
        $ativo->setUseButton();
        

        // criando os botões do formulário
        $this->addActionButton(_t('Save'), new TAction([$this, 'onSave']), 'far:envelope','btn-primary');
        $this->addActionButton(_t('New'), new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->form->addHeaderActionLink( _t('Close'),  new TAction([__CLASS__, 'onClose'], ['static'=>'1']), 'fa:times red');
        
        parent::add($this->form);
    }
    
    /**
     * Método addActionButton para adicionar um botão com waves-effect
     * @param    $label    text content
     * @param    $action   TAction Object
     * @param    $icon     text icon (fa:user)
     * @param    $class    text class
     */
    private function addActionButton($label, TAction $action, $icon = null, $class = 'btn-default')
    {
        $btn = $this->form->addAction($label, $action, $icon);
        $btn->class = 'btn btn-sm '.$class.' waves-effect';

        return $btn;
    }
    
    /**
     * Closes window
     */
    public static function onClose()
    {
        parent::closeWindow();
    }
    
}

<?php
/**
 * MidiaForm Registration
 *
 * @version    1.0
 * @package    control
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 */
class MidiaForm extends TWindow
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
        parent::setSize(0.8, null);
        parent::setMinWidth(0.9,800);
        parent::removePadding();
        parent::removeTitleBar();
        //parent::disableEscape();
        
        $this->setDatabase('sistema');              // defines the database
        $this->setActiveRecord('Midia');     // defines the active record
        
        $this->setAfterSaveAction(new TAction(['MidiaList','onReload'],['register_state'=>'true']));
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Midia');
        $this->form->setFormTitle('Formulário de Mídias Sociais');
        $this->form->setFieldSizes('100%');
        
        // Mudando a cor do cabeçalho
        $this->form->setHeaderProperty('class','header bg-blue-grey');

        // create the form fields
        $id    = new TEntry('id');
        $nome  = new TEntry('nome');
        $url   = new TEntry('url');
        $icone = new TIcon('icone');
        $ativo = new TRadioGroup('ativo');
        
        // definindo parametros dos campos
        $id->setEditable(FALSE);
        $ativo->addItems(['t'=>_t('Yes'),'f'=>_t('No')]);
        $ativo->setLayout('horizontal');
        $ativo->setSize(80);


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] , [ new TLabel('Icone') ], [ $icone ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] , [ new TLabel('Url') ], [ $url ] );
        $this->form->addFields( [ new TLabel('Ativo') ], [ $ativo ] );

        // defininda as validações
        $nome->addValidation('Nome', new TRequiredValidator);
        $url->addValidation('Url', new TRequiredValidator);
        $icone->addValidation('Icone', new TRequiredValidator);
        $ativo->addValidation('Ativo', new TRequiredValidator);

        
        // criando os botões do formulário
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->form->addHeaderActionLink( _t('Close'),  new TAction([__CLASS__, 'onClose'], ['static'=>'1']), 'fa:times red');
        
        parent::add($this->form);
    }
    
    /**
     * Closes window
     */
    public static function onClose()
    {
        parent::closeWindow();
    }
    
    
}

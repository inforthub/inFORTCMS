<?php
/**
 * LinkForm Registration
 *
 * @version     1.0
 * @package     control
 * @subpackage  configuracoes
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class LinkForm extends TWindow
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
        $this->setActiveRecord('Link');     // defines the active record
        
        $this->setAfterSaveAction(new TAction(['LinkList','onReload'],['register_state'=>'true']));
        
        // criando um formulário
        $this->form = new BootstrapFormBuilder('form_Link');
        $this->form->setFormTitle('Formulário de Link');
        $this->form->setFieldSizes('100%');
        
        // Mudando a cor do cabeçalho
        $this->form->setHeaderProperty('class','header bg-blue-grey');

        // criando os campos
        $id                = new TEntry('id');
        $url               = new TEntry('url');
        //$system_modules_id = new TDBUniqueSearch('system_modules_id', 'sistema', 'SystemModules', 'id', 'nome');
        $lastmod           = new TDateTime('lastmod');
        $changefreq        = new TCombo('changefreq');
        $priority          = new TEntry('priority');
        $artigo_id         = new THidden('artigo_id');


        // adicionando os campos
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] , [$artigo_id]);
        $this->form->addFields( [ new TLabel('Url') ], [ $url ]  , [ new TLabel('Lastmod') ], [ $lastmod ] );
        //$this->form->addFields( [ new TLabel('Tipo') ], [ $system_modules_id ] , [ new TLabel('Lastmod') ], [ $lastmod ] );
        $this->form->addFields( [ new TLabel('Changefreq') ], [ $changefreq ] , [ new TLabel('Priority') ], [ $priority ] );

        // criando validações
        $url->addValidation('Url', new TRequiredValidator);
        //$system_modules_id->addValidation('Tipo', new TRequiredValidator);
        $changefreq->addValidation('Changefreq', new TRequiredValidator);
        $priority->addValidation('Priority', new TRequiredValidator);


        // definindo parâmetros
        $id->setEditable(FALSE);
        $url->setEditable(FALSE);
        //$system_modules_id->setEditable(FALSE);
        $lastmod->setMask('dd/mm/yyyy hh:ii');
        $lastmod->setDatabaseMask('yyyy-mm-dd hh:ii');
        $changefreq->addItems(['never'=>'never', 'yearly'=>'yearly', 'monthly'=>'monthly', 'weekly'=>'weekly', 'daily'=>'daily', 'hourly'=>'hourly', 'always'=>'always']);
        $priority->setMask('9,99');
        
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

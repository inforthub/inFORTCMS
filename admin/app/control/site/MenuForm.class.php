<?php
/**
 * MenuForm Form
 *
 * @version     1.0
 * @package     control
 * @subpackage  site
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class MenuForm extends TWindow
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        //parent::setTitle('Formulário de Menu');
        parent::setSize(0.7, null);
        parent::setMinWidth(0.9,800);
        parent::removePadding();
        parent::removeTitleBar();
        //parent::disableEscape();
        
         // creates the form
        $this->form = new BootstrapFormBuilder('form_Menu');
        $this->form->setFormTitle('Formulário de Menu');
        $this->form->setFieldSizes('100%');
        
        // Mudando a cor do cabeçalho
        $this->form->setHeaderProperty('class','header bg-blue-grey');

        $criteria = TCriteria::create(['ativo'=>'t']);
        $criteria2 = TCriteria::create(['ativo'=>'t','menu_pai_id'=>0]);


        // create the form fields
        $id           = new TEntry('id');
        $titulo       = new TEntry('titulo');
        $url          = new TEntry('url');
        $inicial      = new TRadioGroup('inicial');
        $header_class = new TEntry('header_class');
        $icone        = new TIcon('icone');
        $ativo        = new TRadioGroup('ativo');
        $ordem        = new THidden('ordem');
        $menu_pai_id  = new TDBCombo('menu_pai_id','sistema','Menu','id','titulo','ordem',$criteria2); 
        $tipo         = new TDBRadioGroup('tipo','sistema','Tipo','id','nome','id',$criteria);
        //
        $artigo_id    = new TDBUniqueSearch('artigo_id','sistema','Artigo','id','titulo','titulo',$criteria);

        
        // definindo parâmetros dos campos
        $yesno = ['t'=>_t('Yes'),'f'=>_t('No')];
        $inicial->addItems($yesno);
        $inicial->setLayout('horizontal');
        $inicial->setUseButton();
        $ativo->addItems($yesno);
        $ativo->setLayout('horizontal');
        $ativo->setUseButton();
        $tipo->setLayout('horizontal');
        $menu_pai_id->enableSearch();
        $id->setEditable(FALSE);
        
        // definindo o tamanho dos campos
        $inicial->setSize(80);
        $ativo->setSize(80);
        
        // criando eventos
        $artigo_id->setChangeAction( new TAction([$this,'onChangeArtigo']) );
        
        // defininda as validações
        $titulo->addValidation('Título', new TRequiredValidator);
        $url->addValidation('Url', new TRequiredValidator);
        //$pagina_id->addValidation('Página', new TRequiredValidator);
        $inicial->addValidation('Inicial', new TRequiredValidator);
        $ativo->addValidation('Ativo', new TRequiredValidator);
        

        // adicionando os campos ao formulário
        $this->form->addFields( [new TLabel('ID')], [$id], [new TLabel('Submenu de')], [$menu_pai_id] );
        $this->form->addFields( [new TLabel('Título')], [$titulo], [new TLabel('Url')], [$url] );
        //$this->form->addFields( [new TLabel('Tipo de Link')], [$tipo] );
        $this->form->addFields( [new TFormSeparator('')] );
        $this->form->addFields( [new TLabel('Página')], [$artigo_id] );
        $this->form->addFields( [new TFormSeparator('')] );
        $this->form->addFields( [new TLabel(_t('Icon'))], [$icone], [new TLabel('Classe Header')], [$header_class] );
        $this->form->addFields( [new TLabel('Inicial')], [$inicial], [new TLabel('Ativo')], [$ativo] );
        $this->form->addFields( [$ordem] );
         
        // create the form actions
        $this->addActionButton(_t('Save'), new TAction([$this, 'onSave']), 'fa:save','btn-primary');
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
     * Preenche o campo URL com a URL do Artigo
     * @param $param Request
     */
    public static function onChangeArtigo( $param )
    {
        TTransaction::open('sistema');
        $artigo = Artigo::find($param['artigo_id']);
        TTransaction::close();
        
        if($artigo)
        {
            $obj = new StdClass;
            $obj->url = $artigo->url;
            
            TForm::sendData('form_Menu',$obj);
        }
    }

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open('sistema'); // open a transaction

            $this->form->validate(); // validate form data
            
            $object = new Menu;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
            
            $count = Menu::countObjects();

            // definindo a ordem do menu
            if ( empty($object->ordem) )
            { 
                $object->ordem = $count+1;
            }

            if ( $object->inicial == 't' )
            {
                if ( $count > 0 )
                    Menu::clear_inicial();
            }

            // verificando se é submenu
            if ( empty($object->menu_pai_id) )
            {
                $object->menu_pai_id = 0;
            }
            
            // verifica e atualiza a URL, se necessário
            $artigo = Artigo::find($object->artigo_id);
            
            if($artigo)
            {
                $object->url = $artigo->url;
            }
            
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'), new TAction(['MenuList','onClear']));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('sistema'); // open a transaction
                $object = new Menu($key); // instantiates the Active Record

                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Closes window
     */
    public static function onClose()
    {
        parent::closeWindow();
    }
    
}

<?php
/**
 * CategoriaForm Registration
 *
 * @version     1.0
 * @package     control
 * @subpackage  site
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 (https://www.infort.eti.br)
 */
class CategoriaForm extends TWindow
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
        parent::setMinWidth(0.9,900);
        parent::removePadding();
        parent::removeTitleBar();
        //parent::disableEscape();
        
        $this->setDatabase('sistema');              // defines the database
        $this->setActiveRecord('Artigo');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Categoria');
        $this->form->setFormTitle('Categoria');
        $this->form->setFieldSizes('100%');
        
        //$this->setAfterSaveAction(new TAction(['CategoriaList','onReload'],['register_state'=>'true']));
        
        // Mudando a cor do cabeçalho
        $this->form->setHeaderProperty('class','header bg-blue-grey');
        
        $criteria  = TCriteria::create(['tipo_id'=>1,'modo'=>'c','ativo'=>'t']); //pegamos somente as categorias ativas do tipo site
        //$criteria2 = TCriteria::create(['active'=>'Y']);

        // create the form fields
        $id           = new TEntry('id');
        $titulo       = new TEntry('titulo');
        $url          = new TEntry('url');
        $artigo       = new TText('artigo');
        $metadesc     = new TText('metadesc');
        $metakey      = new TMultiEntry('metakey');
        $dt_post      = new TDateTime('dt_post');
        $dt_edicao    = new TDateTime('dt_edicao');
        $visitas      = new TEntry('visitas');
        //$usuario_id  = new TDBCombo('usuario_id','sistema','SystemUser','id','name','name',$criteria2);
        $ativo        = new TRadioGroup('ativo');
        $categoria_id = new TDBCombo('categoria_id', 'sistema', 'Artigo', 'id', 'titulo', 'titulo', $criteria);


        // adicionando os campos ao formulário
        $this->form->addFields( [ new TLabel('ID')], [$id], [ new TLabel('Categoria Pai') ], [ $categoria_id ] );
        $this->form->addFields( [ new TLabel('Nome')], [$titulo] , [ new TLabel('URL')], [$url]);
        $this->form->addFields( [ new TLabel('Descrição') ], [ $artigo ] );
        $this->form->addFields( [ new TLabel('Meta Descrição')], [$metadesc] );
        $this->form->addFields( [ new TLabel('Palavras Chave')], [$metakey] );
        $this->form->addFields( [ new TLabel('Data Post') ], [ $dt_post ] , [ new TLabel('Data Edição') ], [ $dt_edicao ] );
        $this->form->addFields( [ new TLabel('Ativo')], [$ativo] , [ new TLabel('Visitas') ], [ $visitas ] );
        

        // defininda as validações
        $titulo->addValidation('Nome', new TRequiredValidator);
        $url->addValidation('Url', new TRequiredValidator);
        $artigo->addValidation('Descrição', new TRequiredValidator);
        //$metadesc->addValidation('Metadesc', new TRequiredValidator);
        //$metakey->addValidation('Metakey', new TRequiredValidator);

        
        // criando eventos
        $titulo->setExitAction(new TAction([$this,'onNomeChange']));


        // definindo parâmetros dos campos
        $id->setEditable(FALSE);
        //$usuario_id->setEditable(FALSE);
        $metadesc->setSize('100%',100);
        $metakey->setSize('100%',60);
        $url->forceLowerCase();
        $dt_post->setEditable(FALSE);
        $dt_post->setMask('dd/mm/yyyy hh:ii');
        $dt_post->setDatabaseMask('yyyy-mm-dd hh:ii');
        $dt_edicao->setEditable(FALSE);
        $dt_edicao->setMask('dd/mm/yyyy hh:ii');
        $dt_edicao->setDatabaseMask('yyyy-mm-dd hh:ii');
        $visitas->setEditable(FALSE);
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
     * Preenche o campo URL com o Nome
     * @param $param Request
     */
    public static function onNomeChange( $param )
    {
        $obj = new StdClass;
        $obj->url = THelper::urlAmigavel( $param['titulo'] );
        
        TForm::sendData('form_Categoria',$obj);
    }
    
    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open($this->database); // open a transaction

            // validando os campos
            $this->form->validate();
            
            $object = new Artigo;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
            
            // verificando campos de data
            if (empty($object->dt_post))
            {
                $object->dt_post = date('Y-m-d H:i:s');
            }
            else
            {
                $object->dt_edicao = date('Y-m-d H:i:s');
            }
                        
            if (empty($object->visitas))
            {
                $object->visitas = 0;
            }
            
            // preparando as palavras chave
            $object->metakey = implode(',',$data->metakey);
            
            // garantindo outras informações
            $object->tipo_id    = Tipo::getIdByNome('Site');
            $object->modo       = 'c'; // categoria
            $object->usuario_id = TSession::getValue('userid');

            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'), new TAction(['CategoriaList','onClear']));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
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
                TTransaction::open($this->database); // open a transaction
                $object = new Artigo($key); // instantiates the Active Record
                
                // preparando as palavras chave
                $object->metakey = explode(',',$object->metakey);
                
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

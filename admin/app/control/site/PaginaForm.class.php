<?php
/**
 * PaginaForm Form
 *
 * @version     1.1
 * @package     control
 * @subpackage  site
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 (https://www.infort.eti.br)
 *
 */
class PaginaForm extends TWindow
{
    protected $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        //parent::setTitle('Formulário de Página');
        parent::setSize(0.9,null);
        parent::setPosition(null,10);
        parent::removePadding();
        parent::removeTitleBar();
        //parent::disableEscape();
        
        $this->setDatabase('sistema');              // defines the database
        $this->setActiveRecord('Artigo');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Pagina');
        $this->form->setFormTitle('Formulário de Página');
        $this->form->setFieldSizes('100%');
        
        // Mudando a cor do cabeçalho
        $this->form->setHeaderProperty('class','header bg-blue-grey');
        
        $criteria  = TCriteria::create(['tipo_id'=>1,'modo'=>'c','ativo'=>'t']); //pegamos somente as categorias ativas do tipo site
        $criteria2 = TCriteria::create(['active'=>'Y']);

        // create the form fields
        $id          = new TEntry('id');
        $titulo      = new TEntry('titulo');
        $url         = new TEntry('url');
        //$resumo      = new TText('resumo');
        $artigo      = new TTextSourceCode('artigo');
        $metadesc    = new TText('metadesc');
        $metakey     = new TMultiEntry('metakey');
        $dt_post     = new TDateTime('dt_post');
        $dt_edicao   = new TDateTime('dt_edicao');
        $visitas     = new TEntry('visitas');
        $usuario_id  = new TDBCombo('usuario_id','sistema','SystemUser','id','name','name',$criteria2);
        $ativo       = new TRadioGroup('ativo');
        // nesse caso, devemos buscar por categorias
        $categoria_id   = new TDBCombo('categoria_id', 'sistema', 'Artigo', 'id', 'titulo', 'titulo', $criteria);


        // adicionando os campos ao formulário
        $this->form->addFields( [ new TLabel('ID')], [$id], [ new TLabel('Usuário') ], [ $usuario_id ] );
        $this->form->addFields( [ new TLabel('Título')], [$titulo] , [ new TLabel('URL')], [$url]);
        //$this->form->addFields( [ new TLabel('Resumo')], [$resumo] );
        $this->form->addFields( [ new TLabel('HTML') ], [ $artigo ] );
        //$this->form->addFields( [new TLabel('Módulos Disponíveis')], [$this->list1], [new TLabel('Sequência de Módulos Ativos')], [$this->list2] );
        $this->form->addFields( [ new TLabel('Meta Descrição')], [$metadesc] );
        $this->form->addFields( [ new TLabel('Palavras Chave')], [$metakey] );
        
        
        $this->form->addFields( [ new TLabel('Categoria') ], [ $categoria_id ] , [] );
        $this->form->addFields( [ new TLabel('Data Post') ], [ $dt_post ] , [ new TLabel('Data Edição') ], [ $dt_edicao ] );
        $this->form->addFields( [ new TLabel('Ativo')], [$ativo] , [ new TLabel('Visitas') ], [ $visitas ] );

        
        // defininda as validações
        $titulo->addValidation('Titulo', new TRequiredValidator);
        $url->addValidation('Url', new TRequiredValidator);
        $artigo->addValidation('HTML', new TRequiredValidator);
        $metadesc->addValidation('Metadesc', new TRequiredValidator);
        $metakey->addValidation('Metakey', new TRequiredValidator);
        
        
        // criando eventos
        $titulo->setExitAction(new TAction([$this,'onTituloChange']));


        // definindo parâmetros dos campos
        $id->setEditable(FALSE);
        $usuario_id->setEditable(FALSE);
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
                
        
        // create the form actions
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
     * Preenche o campo URL com o título
     * @param $param Request
     */
    public static function onTituloChange( $param )
    {
        $obj = new StdClass;
        $obj->url = THelper::urlAmigavel( $param['titulo'] );
        
        TForm::sendData('form_Pagina',$obj);
    }
    
    
    /**
     * Retorna um array com a listagem dos módulos ativos
     *
    private function getList()
    {
        try
        {
            TTransaction::open($this->database); // open a transaction
            
            $modulos = Modulo::where('ativo','=','t')->load();
            $arr = [];
                    
            if ($modulos)
            {
                foreach ($modulos as $modulo)
                {
                    $arr[$modulo->id] = $modulo->nome;
                }
            }
            
            TTransaction::close();
            
            return $arr;
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     *
     *
    public static function onListChange( $param )
    {
        $obj = new StdClass;
        $obj->list1 = new TDBSortList('list1','sistema','Modulo','id','nome','nome',$criteria);
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
            
            /*
            // adicionando os módulos a página
            if ( is_array($data->list2) AND !empty($data->list2) )
            {
                foreach ($data->list2 as $key => $value)
                {
                    $modulo = Modulo::find($value);
                    $modulo->ordem = $key;
                    $object->addModulo( $modulo );
                }
            }
            */
            
            if (empty($object->visitas))
            {
                $object->visitas = 0;
            }
            
            // preparando as palavras chave
            $object->metakey = implode(',',$data->metakey);
            
            // garantindo outras informações
            $object->tipo_id    = Tipo::getIdByNome('Site');
            $object->modo       = 'a'; // artigo
            $object->usuario_id = TSession::getValue('userid');

            $object->store(); // save the object

            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'), new TAction(['PaginaList','onClear']));
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
     *
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
        //$this->list1->addItems($this->getList());
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
                
                
                /*
                $modulos = $object->getModulos();
                
                // pegando os modulos
                if ( $modulos )
                {
                    $list1 = $this->getList();
                    $list2 = [];
                    
                    //$this->list1->clearField();
                    
                    foreach ($modulos as $modulo)
                    {
                        unset($list1[$modulo->id]); // remove o item da list1
                        $list2[$modulo->id] = $modulo->nome;
                        //$this->list2->addItems([$modulo->id=>$modulo->nome]);
                    }
                    
                    $this->list1->addItems($list1);
                    $this->list2->addItems($list2);
                }
                else
                {
                    $this->list1->addItems($this->getList());
                }
                */
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
                //$this->list1->addItems($this->getList());
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

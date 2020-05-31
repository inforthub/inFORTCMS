<?php
/**
 * ArquivoFormList Registration
 *
 * @version     1.1
 * @package     control
 * @subpackage  site
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 (https://www.infort.eti.br)
 *
 */
class ArquivoFormList extends TPage //TWindow
{
    protected $form; // form
    protected $datagrid; // datagrid
    protected $pageNavigation;
    
    use Adianti\Base\AdiantiStandardFormListTrait; // standard form/list methods
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    public function __construct( $param )
    {
        parent::__construct();/*
        //parent::setTitle('Formulário de Fotos');
        parent::setSize(0.8, null);
        parent::setMinWidth(0.9,900);
        parent::removePadding();
        parent::removeTitleBar();
        //parent::disableEscape();
        */
        //parent::setTargetContainer('adianti_right_panel');
        
        $this->setDatabase('sistema');            // defines the database
        $this->setActiveRecord('Arquivo');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        //$this->setCriteria($criteria) // define a standard filter
        
        // verificamos se foi recebido o ID do artigo
        if (!isset($param['artigo_id']) )
        {
            new TMessage('error','Artigo não identificado.',new TAction(['Dashboard','onClear']));
        }

        // criando filtros para a listagem
        $this->setCriteria( TCriteria::create(['artigo_id'=>$param['artigo_id'],'formato'=>'F']) ); // exibe somente fotos
        
        // criando um formulário
        $this->form = new BootstrapFormBuilder('form_Arquivo');
        $this->form->setFormTitle('Galeria de Fotos');
        $this->form->setFieldSizes('100%');
        
        // Mudando a cor do cabeçalho
        //$this->form->setHeaderProperty('class','header bg-blue-grey');
        

        // criando campos
        $id        = new TEntry('id');
        //$nome      = new TSlim('nome'); //TFile('nome');
        $nome_web  = new TEntry('nome_web');
        $descricao = new TEntry('descricao');
        //$formato   = new TCombo('formato');
        $artigo_id = new THidden('artigo_id');
        
        $btn = TButton::create('buscar_imagem',[$this,'onBuscaImagem'],'Procurar Imagem','fa:image');

        // criando o frame da logo
        $this->frame = new TElement('div');
        $this->frame->id = 'photo_frame';
        $this->frame->style = 'width:100%;height:auto;border:1px solid gray;padding:4px;';
        
        // adicionando os campos ao formulário
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] , [ $artigo_id ]);
        //$this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addContent( [$this->frame] );
        $this->form->addFields( [ new TLabel('Caminho') ], [ $nome_web , $btn] );
        $this->form->addFields( [ new TLabel('Descrição') ], [ $descricao ] );
        //$this->form->addFields( [ new TLabel('Formato') ], [ $formato ] );

        // criando validações
        //$nome->addValidation('Nome', new TRequiredValidator);
        $nome_web->addValidation('Nome', new TRequiredValidator);
        $descricao->addValidation('Descrição', new TRequiredValidator);


        // configurando parâmetros dos campos
        $id->setEditable(FALSE);
        //$artigo_id->setEditable(FALSE);
        $artigo_id->setValue($param['artigo_id']);
        
        /*
        $nome->setDataProperties(['label'=>'Upload imagem']);//aqui eu seto o nome do label
        //tamanho final no máximo 1500x1500 e proporção de 4:3 na janela de visualização
        $nome->setDataProperties(['size'=>'1400,1200','ratio'=>'16:9','download'=>'true']);
        $nome->setWatermark(THelper::getPreferences('pref_site_nome')); //'Jornal Folha de Araçoiaba');
        //$nome->setImageWatermark('app/images/logo-infort.svg');
        */
        
        // criando os botões do formulário
        $this->addActionButton(_t('Save'), new TAction([$this, 'onSave'],['artigo_id'=>$param['artigo_id']]), 'fa:save','btn-primary');
        $this->addActionButton(_t('New'), new TAction([$this, 'onEdit'],['artigo_id'=>$param['artigo_id']]), 'fa:eraser red');
        $this->addActionButton(_t('Back'),  new TAction([$this, 'onClose']), 'far:arrow-alt-circle-left blue');
        
        $btn = $this->form->addHeaderAction(_t('Back'), new TAction([$this, 'onClose']), 'far:arrow-alt-circle-left blue');
        $btn->class = 'btn btn-sm btn-default waves-effect';
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', '#', 'right', 30);
        $column_nome_web = new TDataGridColumn('nome_web', 'Foto', 'left', 100);
        $column_descricao = new TDataGridColumn('descricao', 'Descrição', 'left');
        $column_destaque = new TDataGridColumn('destaque', '<i class="far fa-star gray"></i>', 'center');
        //$column_formato = new TDataGridColumn('formato', 'Formato', 'left');
        //$column_artigo_id = new TDataGridColumn('artigo_id', 'Artigo Id', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_destaque);
        $this->datagrid->addColumn($column_nome_web);
        $this->datagrid->addColumn($column_descricao);
        //$this->datagrid->addColumn($column_formato);
        //$this->datagrid->addColumn($column_artigo_id);
        
        
        //função para mostrar a imagem no grid
        $column_nome_web->setTransformer( function($image) { 
            $imagem= new TImage($image); 
            $imagem->style='width:100px';//tamanho da imagem no grid
            return $imagem; 
        });
        $column_destaque->setTransformer( function($value)
        {
                $icone = ($value=='f') ? 'fa:star-o #bbb' : 'fa:star #ffb200';
                $imagem = new TImage($icone);
                return $imagem;
        });
        
        $action1 = new TDataGridAction([$this, 'onEdit'], ['id'=>'{id}','register_state' => 'false']);
        $action1->setParameter('artigo_id',$param['artigo_id']);
        
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}','register_state' => 'false']);
        $action2->setParameter('artigo_id',$param['artigo_id']);
        
        $action3 = new TDataGridAction([$this, 'onTurnDestaque'], ['id'=>'{id}','register_state' => 'false']);
        $action3->setParameter('artigo_id',$param['artigo_id']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue fa-fw');
        $this->datagrid->addAction($action2, _t('Delete'),   'far:trash-alt red');
        $this->datagrid->addAction($action3, 'Alternar Destaque',   'far:star gray');

        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        //$this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        parent::add($container);
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
     * Turn Destaque
     */
    public function onTurnDestaque($param)
    {
        try
        {
            TTransaction::open($this->database);
            $obj = Arquivo::find($param['id']);
            if ($obj instanceof Arquivo)
            {
                $obj->destaque = $obj->destaque == 't' ? 'f' : 't';
                $obj->store();
            }
            
            TTransaction::close();
            
            $this->onReload($param);
            
            new TMessage('info','O Destaque foi alterado com sucesso!');
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    
    public function onBuscaImagem($param)
    {
        TSession::setValue('Classe_Retorno_Busca_Imagem','form_Arquivo');
        AdiantiCoreApplication::loadPage('SelecaoImagem');
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

            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $object = new Arquivo;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            
            /*
            $object2 = Arquivo::find($data->id); //objeto criado para testar se imagem foi repetida
            
            $images = Slim::getImages();
            
            // No image found under the supplied input name
            if ($images)
            {            
                $image = $images[0];
                // save output data if set
                if (isset($image['output']['data']))
                {
                    $arquivo = pathinfo($image['output']['name']);
                    
                    // geramos um hash com o nome do arquivo concatenado com o tempo
                    $name = time().'-'.md5($arquivo['filename']).'.'.$arquivo['extension'];
                    
                    // We'll use the output crop data
                    $output_data = $image['output']['data'];
                    
                    // prepara a zona (sempre UTF-8)
                    setlocale( LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'pt_BR.utf-8', 'portuguese' );
                    date_default_timezone_set( 'America/Sao_Paulo' );
                    
                    $artigo = Artigo::find($data->artigo_id);
                    $categoria = $artigo->getURLCategoriaPai();
                    
                    // definindo o path com a categoria pai'
                    $target_path = '..'.CMS_IMAGE_PATH.'/'.$artigo->getURLCategoriaPai().'/';
                    
                    // salva o arquivo
                    $output = Slim::saveFile($output_data, $name, $target_path, false);
                    
                    if ($output)
                    {
                        if( file_exists ($output['path']) )// se existir apaga o anterior
                        {
                            // pegamos o arquivo salvo e convertemos para webp
                            $webp_file = str_replace('.'.$arquivo['extension'],'.webp',$output['path']);
                            THelper::toWebP($output['path'],$webp_file);
                            
                            $object->nome_web = $webp_file;
                        }
                    }
                    
                    if( $object2 )//teste de imagem repetida
                    {
                        if( file_exists ($object2->nome))// se existir apaga o anterior
                        {
                            unlink( $object2->nome ); //apaga
                        }
                        if( file_exists ($object2->nome_web))// se existir apaga o anterior
                        {
                            unlink( $object2->nome_web ); //apaga
                        }
                    }
                    $object->nome = $output['path'];//recebe o caminho para salvar
                    
                }
            }
            else
            { 
               //$object2 = new Arquivo($data->id); 
               $object->nome =  $object2->nome;
            }
            */
            $object->formato = 'F';
            
            $object->store(); // save the object

            TTransaction::close(); // close the transaction

            $this->form->clear(TRUE);
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved')); // success message
            $this->onReload(); // reload the listing
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
                $object = new Arquivo($key); // instantiates the Active Record
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
                
                // refresh photo_frame
                TScript::create("$('#photo_frame').html('')");
                TScript::create("$('#photo_frame').append(\"<img style='width:100%' src='{$object->nome_web}'>\");");
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
    
    public function onLoad($param)
    {
        TSession::setValue(__CLASS__.'_retorno',$param['pagina']);
    }
    
    /**
     * Closes window
     */
    public static function onClose()
    {
        //parent::closeWindow();
        $ret = TSession::getValue(__CLASS__.'_retorno');
        THelper::clearSession();
        
        TApplication::loadPage($ret);
    }
    
    
}

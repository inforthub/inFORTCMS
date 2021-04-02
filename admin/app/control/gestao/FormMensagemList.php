<?php
/**
 * FormMensagemList Listing
 *
 * @version     1.0
 * @package     control
 * @subpackage  gestao
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class FormMensagemList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('sistema');            // defines the database
        $this->setActiveRecord('FormMensagem');   // defines the active record
        $this->setDefaultOrder('dt_mensagem', 'desc');         // defines the default order
        $this->setLimit(20);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('formulario_id', '=', 'formulario_id'); // filterField, operator, formField
        $this->addFilterField('assunto', 'like', 'assunto'); // filterField, operator, formField
        $this->addFilterField('email_origem', 'like', 'email_origem'); // filterField, operator, formField
        $this->addFilterField('email_destino', 'like', 'email_destino'); // filterField, operator, formField
        $this->addFilterField('enviada', '=', 'enviada'); // filterField, operator, formField
        $this->addFilterField('dt_mensagem', '>=', 'data_de', function($value) {
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); // filterField, operator, formField, transformFunction
        $this->addFilterField('dt_mensagem', '<=', 'data_ate', function($value) {
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); // filterField, operator, formField, transformFunction
        
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_FormMensagem');
        $this->form->setFormTitle('Mensagens Recebidas');
        $this->form->setFieldSizes('100%');
        

        // create the form fields
        $formulario_id = new TDBUniqueSearch('formulario_id', 'sistema', 'Formulario', 'id', 'nome');
        $assunto       = new TEntry('assunto');
        $email_origem  = new TEntry('email_origem');
        $email_destino = new TEntry('email_destino');
        $enviada       = new TCombo('enviada');
        $data_de        = new TDate('data_de');
        $data_ate       = new TDate('data_ate');


        // add the fields
        $this->form->addFields( [ new TLabel('Formulário') ], [ $formulario_id ] , [ new TLabel('Assunto') ], [ $assunto ] );
        $this->form->addFields( [ new TLabel('Email Origem') ], [ $email_origem ] , [ new TLabel('Email Destino') ], [ $email_destino ] );
        $this->form->addFields( [ new TLabel('Data (de)') ], [ $data_de ] , [ new TLabel('Data (até)') ], [ $data_ate ] );
        $this->form->addFields( [ new TLabel('Enviada') ], [ $enviada ], [] );


        // definindo parâmetros
        $formulario_id->setMinLength(1);
        $data_de->setMask( 'dd/mm/yyyy' );
        //$data_de->setOption('triggerEvent', 'dblclick'); // exibe o popup do calendáio somente dando 2 cliques
        $data_ate->setMask( 'dd/mm/yyyy' );
        //$data_ate->setOption('triggerEvent', 'dblclick'); // exibe o popup do calendáio somente dando 2 cliques

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $this->addActionButton(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search','btn-primary');
        //$this->addActionButton(_t('New'),  new TAction(array('FormMensagemForm', 'onEdit')), 'fa:plus green');
        $this->addActionButton(_t('Clear'), new TAction(array($this, 'onClear')), 'fa:eraser red');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', '#', 'right');
        $column_formulario_id = new TDataGridColumn('{formulario->nome}', 'Formulário', 'left');
        $column_assunto = new TDataGridColumn('assunto', 'Assunto', 'left');
        $column_email_origem = new TDataGridColumn('email_origem', 'Email Origem', 'left');
        $column_email_destino = new TDataGridColumn('email_destino', 'Email Destino', 'left');
        $column_dt_mensagem = new TDataGridColumn('dt_mensagem', 'Data de Envio', 'left');
        $column_enviada = new TDataGridColumn('enviada', 'Enviada', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_formulario_id);
        $this->datagrid->addColumn($column_assunto);
        $this->datagrid->addColumn($column_email_origem);
        $this->datagrid->addColumn($column_email_destino);
        $this->datagrid->addColumn($column_dt_mensagem);
        $this->datagrid->addColumn($column_enviada);
        
        // definindo transformações
        $column_dt_mensagem->setTransformer(['TTransformers','formataDataHoraBR']);
        $column_enviada->setTransformer(['TTransformers','formataSimNao']);


        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_formulario_id->setAction(new TAction([$this, 'onReload']), ['order' => 'formulario_id']);
        $column_assunto->setAction(new TAction([$this, 'onReload']), ['order' => 'assunto']);
        $column_email_origem->setAction(new TAction([$this, 'onReload']), ['order' => 'email_origem']);
        $column_email_destino->setAction(new TAction([$this, 'onReload']), ['order' => 'email_destino']);
        $column_dt_mensagem->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_mensagem']);
        $column_enviada->setAction(new TAction([$this, 'onReload']), ['order' => 'enviada']);

        
        //$action1 = new TDataGridAction(['FormMensagemForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        $action3 = new TDataGridAction(['FormMensagemFormView', 'onEdit'], ['id'=>'{id}']);
        $action4 = new TDataGridAction([$this, 'onReenviarEmail'], ['id'=>'{id}','register_state' => 'false']);
        
        $this->datagrid->addAction($action3 ,_t('View'), 'fas:eye gray');
        //$this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        $this->datagrid->addAction($action4 ,'Reenviar E-mail', 'fas:paper-plane green');
        
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        /*
        $panel = new TPanelGroup('', 'white');
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        // header actions
        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table blue' );
        $dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf red' );
        $panel->addHeaderWidget( $dropdown );
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        */
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
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
     * Pergunta antes de tentar enviar o e-mail
     */
    public static function onReenviarEmail($param)
    {
        $action = new TAction([__CLASS__,'Reenvia'], $param);
        
        new TQuestion('Deseja reenviar o e-mail dessa mensagem ?', $action);
    }
    
    /**
     * Tenta reenviar o e-mail
     */
    public function Reenvia($param)
    {
        try
        {
            TTransaction::open('sistema');
            
            $obj = FormMensagem::find($param['id']);
            if ($obj instanceof FormMensagem)
            {
                // enviamos o email
                MailService::send( $obj->email_destino, $obj->assunto, $obj->mensagem, 'html' );
                
                // atualizamos o status
                $obj->enviada = 't';
                $obj->store();
            }
            else
            {
                throw new Exception('A Mensagem não exisite ou foi deletada!');
            }
            
            TTransaction::close();
            
            new TMessage('info','E-mail enviado com sucesso!', new TAction([__CLASS__,'onReload']));
        }
        catch (Exception $e)
        {
            TTransaction::rollback();
            new TMessage('error',$e->getMessage());
        }
    }
    
    
    /**
     * Método para limpar os campos da pesquisa
     */
    public function onClear()
    {
        $this->clearFilters();
        $this->onReload();
    }
    
    
    
}

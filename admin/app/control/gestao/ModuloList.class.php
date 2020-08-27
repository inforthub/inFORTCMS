<?php
/**
 * ModuloList Listing
 *
 * @version     1.0
 * @package     control
 * @subpackage  gestao
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class ModuloList extends TPage
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
        $this->setActiveRecord('Modulo');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
        $this->addFilterField('posicao', '=', 'posicao'); // filterField, operator, formField
        $this->addFilterField('modelo_html_id', '=', 'modelo_html_id'); // filterField, operator, formField
        $this->addFilterField('ativo', '=', 'ativo'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Modulo');
        $this->form->setFormTitle('Gestão de Modulos');
        $this->form->setFieldSizes('100%');
        
        // expand button
        $this->form->addExpandButton('','fas:expand',false);

        // create the form fields
        $nome           = new TEntry('nome');
        $posicao        = new TDBUniqueSearch('posicao', 'sistema', 'Posicao', 'id', 'nome', 'nome asc');
        $modelo_html_id = new TDBCombo('modelo_html_id', 'sistema', 'ModeloHTML', 'id', 'nome', 'nome asc');
        $ativo          = new TCombo('ativo');


        // add the fields
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] , [ new TLabel('Ativo') ], [ $ativo ] );
        $this->form->addFields( [ new TLabel('Posicao') ], [ $posicao ] , [ new TLabel('Modelo Html') ], [ $modelo_html_id ] );


        // definindo parâmetros
        $ativo->addItems(['t'=>'Sim','f'=>'Não']);
        $posicao->setMinLength(1);
        $posicao->setMask('<b>{template->nome}</b> - {nome}');
        
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $this->addActionButton(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search','btn-primary');
        $this->addActionButton(_t('New'),  new TAction(array('ModuloForm', 'onEdit')), 'fa:plus green');
        $this->addActionButton(_t('Clear'), new TAction(array($this, 'onClear')), 'fa:eraser red');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id             = new TDataGridColumn('id', '#', 'right');
        $column_nome           = new TDataGridColumn('nome', 'Nome', 'left');
        $column_ordem          = new TDataGridColumn('ordem', 'Ordem', 'center');
        $column_posicao        = new TDataGridColumn('posicao', 'Posicao', 'left');
        $column_modelo_html_id = new TDataGridColumn('{modelo_html->nome}', 'Modelo Html', 'left');
        $column_ativo          = new TDataGridColumn('ativo', 'Ativo', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_ordem);
        $this->datagrid->addColumn($column_posicao);
        $this->datagrid->addColumn($column_modelo_html_id);
        $this->datagrid->addColumn($column_ativo);
        
        
        // definindo método transformador
        $column_ativo->setTransformer(['TTransformers','formataSimNao']);


        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_nome->setAction(new TAction([$this, 'onReload']), ['order' => 'nome']);
        $column_ordem->setAction(new TAction([$this, 'onReload']), ['order' => 'ordem']);
        $column_posicao->setAction(new TAction([$this, 'onReload']), ['order' => 'posicao']);
        $column_ativo->setAction(new TAction([$this, 'onReload']), ['order' => 'ativo']);

        
        $action1 = new TDataGridAction(['ModuloForm', 'onEdit'], ['id'=>'{id}','register_state' => 'false']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}','register_state' => 'false']);
        $action3 = new TDataGridAction([$this, 'onTurnOnOff'], ['id'=>'{id}','register_state' => 'false']);
        $action4 = new TDataGridAction([$this, 'onOrdemUP'], ['id'=>'{id}','register_state' => 'false']);
        $action5 = new TDataGridAction([$this, 'onOrdemDown'], ['id'=>'{id}','register_state' => 'false']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue fa-fw');
        $this->datagrid->addAction($action2, _t('Delete'), 'far:trash-alt red');
        $this->datagrid->addAction($action3 ,_t('Activate/Deactivate'), 'fas:power-off orange');
        $this->datagrid->addAction($action4, _t('Move up'),   'fas:sort-up');
        $this->datagrid->addAction($action5, _t('Move down'), 'fas:sort-down');
        
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
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
     * Turn on/off
     */
    public function onTurnOnOff($param)
    {
        try
        {
            TTransaction::open('sistema');
            $obj = Modulo::find($param['id']);
            if ($obj instanceof Modulo)
            {
                $obj->ativo = $obj->ativo == 't' ? 'f' : 't';
                $obj->store();
            }
            
            TTransaction::close();
            
            $this->onReload($param);
            
            new TMessage('info','O status foi alterado com sucesso!');
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Reordena o menu para cima
     */
    public function onOrdemUP( $param )
    {
        $this->reordena($param['id'],'up');
    }
    
    /**
     * Reordena o menu para baixo
     */
    public function onOrdemDown( $param )
    {
        $this->reordena($param['id'],'down');
    }
    
    /**
     * Método privado para realizar a ordenação do menu
     * @param $id       id do menu
     * @param $ordem    string
     */
    private function reordena( $id, $ordem )
    {
        try
        {
            TTransaction::open($this->database);
            
            $modulo = Modulo::find($id);
            $total  = Modulo::where('posicao', '=', $modulo->posicao)->count();
            
            if ( $modulo )
            {
                if ( $ordem == 'up' )
                {
                    if ( $modulo->ordem > 1 )
                    {
                        $ant = Modulo::where('ordem','=',$modulo->ordem-1)->where('posicao', '=', $modulo->posicao)->first();
                        if ($ant)
                        {
                            $tmp = $ant->ordem;
                            $ant->ordem = $modulo->ordem;
                            $ant->store();
                            $modulo->ordem = $tmp;
                            $modulo->store();
                        }
                    }
                }
                else
                {
                    if ( $modulo->ordem < $total )
                    {
                        $ant = Modulo::where('ordem','=',$modulo->ordem+1)->where('posicao', '=', $modulo->posicao)->first();
                        if ($ant)
                        {
                            $tmp = $ant->ordem;
                            $ant->ordem = $modulo->ordem;
                            $ant->store();
                            $modulo->ordem = $tmp;
                            $modulo->store();
                        }
                    }
                }
            }
            
            TTransaction::close();
            
            $this->onClear();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Método para limpar os campos da pesquisa
     */
    public function onClear()
    {
        $this->form->clear();
        
        // limpando dados da sessão
        THelper::clearSession();
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
}

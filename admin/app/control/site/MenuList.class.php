<?php
/**
 * MenuList Listing
 *
 * @version     1.1
 * @package     control
 * @subpackage  site
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 (https://www.infort.eti.br)
 *
 */
class MenuList extends TPage
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
        $this->setActiveRecord('Menu');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('titulo', 'like', 'titulo'); // filterField, operator, formField
        $this->addFilterField('menu_pai_id', '=', 'menu_pai_id'); // filterField, operator, formField
        $this->addFilterField('ativo', '=', 'ativo'); // filterField, operator, formField
        
        $this->setDefaultOrder('ordem');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Menu');
        $this->form->setFormTitle('Menu');
        $this->form->setFieldSizes('100%');
        
        
        // create the form fields
        $titulo = new TEntry('titulo');
        $menu_pai_id = new TDBCombo('menu_pai_id','sistema','Menu','id','titulo','titulo', TCriteria::create(['ativo'=>'t','menu_pai_id'=>0]));
        $ativo = new TCombo('ativo');
        $ativo->addItems(['t'=>'Sim','f'=>'Não']);


        // add the fields
        $this->form->addFields( [ new TLabel('Titulo') ], [ $titulo ] , [ new TLabel('Menu Pai') ], [ $menu_pai_id ] );
        $this->form->addFields( [ new TLabel('Ativo') ], [ $ativo ] , [] , [] );

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Menu_filter_data') );
        
        // add the search form actions
        $this->addActionButton(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search','btn-primary');
        $this->addActionButton(_t('New'),  new TAction(array('MenuForm', 'onEdit')), 'fa:plus green');
        $this->addActionButton(_t('Clear'), new TAction(array($this, 'onClear')), 'fa:eraser red');

        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_titulo = new TDataGridColumn('titulo', 'Titulo', 'left');
        $column_ordem = new TDataGridColumn('ordem', 'Ordem', 'center');
        $column_menu_pai_id = new TDataGridColumn('menu_pai_id', 'Menu Pai', 'center');
        $column_inicial = new TDataGridColumn('inicial', 'Inicial', 'left');
        $column_ativo = new TDataGridColumn('ativo', 'Ativo', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_titulo);
        $this->datagrid->addColumn($column_ordem);
        $this->datagrid->addColumn($column_menu_pai_id);
        $this->datagrid->addColumn($column_inicial);
        $this->datagrid->addColumn($column_ativo);
        
        // definindo método transformador
        $column_inicial->setTransformer(['TTransformers','formataSimNao']);
        $column_ativo->setTransformer(['TTransformers','formataSimNao']);
        $column_menu_pai_id->setTransformer(['TTransformers','showMenuTitulo']);
        


        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_titulo->setAction(new TAction([$this, 'onReload']), ['order' => 'titulo']);
        $column_ordem->setAction(new TAction([$this, 'onReload']), ['order' => 'ordem']);
        $column_menu_pai_id->setAction(new TAction([$this, 'onReload']), ['order' => 'menu_pai_id']);
        $column_ativo->setAction(new TAction([$this, 'onReload']), ['order' => 'ativo']);

        
        $action1 = new TDataGridAction(['MenuForm', 'onEdit'], ['id'=>'{id}','register_state' => 'false']);
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
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        parent::add($container);
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
            TTransaction::open('sistema');
            
            $menu = Menu::find($id);
            $total = Menu::countObjects();

            if ($menu)
            {
                if ( $ordem == 'up' )
                {
                    if ( $menu->ordem > 1 )
                    {
                        $ant = Menu::where('ordem','=',$menu->ordem-1)->load();
                        $tmp = $ant[0]->ordem;
                        $ant[0]->ordem = $menu->ordem;
                        $ant[0]->store();
                        $menu->ordem = $tmp;
                        $menu->store();
                    }
                }
                else
                {
                    if ( $menu->ordem < $total )
                    {
                        $ant = Menu::where('ordem','=',$menu->ordem+1)->load();
                        $tmp = $ant[0]->ordem;
                        $ant[0]->ordem = $menu->ordem;
                        $ant[0]->store();
                        $menu->ordem = $tmp;
                        $menu->store();
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
            $obj = Menu::find($param['id']);
            if ($obj instanceof Menu)
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
     * Método para limpar os campos da pesquisa
     */
    public function onClear()
    {
        // limpando dados da sessão
        //THelper::clearSession();
        $this->clearFilters();
        $this->onReload();
    }
    

}

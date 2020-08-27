<?php
/**
 * CategoriaList Listing
 *
 * @version     1.0
 * @package     control
 * @subpackage  site
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class CategoriaList extends TPage
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
        $this->setActiveRecord('Artigo');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('titulo', 'like', 'titulo'); // filterField, operator, formField
        $this->addFilterField('url', 'like', 'url'); // filterField, operator, formField
        $this->addFilterField('ativo', '=', 'ativo'); // filterField, operator, formField
        
        /*****************************
         * 1- Site
         * 2- Blog
         * 3- News
         *****************************/
        $this->setCriteria( TCriteria::create(['tipo_id'=>1,'modo'=>'c']) ); // define a standard filter
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Categoria');
        $this->form->setFormTitle('Categoria');
        $this->form->setFieldSizes('100%');
        
        // expand button
        $this->form->addExpandButton('','fas:expand',false);

        // create the form fields
        $titulo    = new TEntry('titulo');
        $url       = new TEntry('url');
        $ativo     = new TCombo('ativo');
        $ativo->addItems(['t'=>'Sim','f'=>'Não']);


        // add the fields
        $this->form->addFields( [ new TLabel('Nome') ], [ $titulo ] , [ new TLabel('Url') ], [ $url ] );
        $this->form->addFields( [ new TLabel('Ativo') ], [ $ativo ] , [] );

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $this->addActionButton(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search', 'btn-primary');
        $this->addActionButton(_t('New'),  new TAction(['CategoriaForm', 'onEdit']), 'fa:plus green');
        $this->addActionButton(_t('Clear'), new TAction([$this, 'onClear']), 'fa:eraser red');

        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        
        
        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', '#', 'right');
        $column_titulo = new TDataGridColumn('titulo', 'Nome', 'left');
        $column_url = new TDataGridColumn('url', 'Url', 'left');
        $column_dt_post = new TDataGridColumn('dt_post', 'Data Post', 'center');
        $column_dt_edicao = new TDataGridColumn('dt_edicao', 'Data Edição', 'center');
        $column_visitas = new TDataGridColumn('visitas', 'Visitas', 'right');
        $column_ativo = new TDataGridColumn('ativo', 'Ativo', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_titulo);
        $this->datagrid->addColumn($column_url);
        $this->datagrid->addColumn($column_dt_post);
        $this->datagrid->addColumn($column_dt_edicao);
        $this->datagrid->addColumn($column_visitas);
        $this->datagrid->addColumn($column_ativo);
        
        // definindo método transformador
        $column_ativo->setTransformer(['TTransformers','formataSimNao']);


        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_titulo->setAction(new TAction([$this, 'onReload']), ['order' => 'titulo']);
        $column_url->setAction(new TAction([$this, 'onReload']), ['order' => 'url']);
        $column_dt_post->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_post']);
        $column_dt_edicao->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_edicao']);
        $column_visitas->setAction(new TAction([$this, 'onReload']), ['order' => 'visitas']);
        $column_ativo->setAction(new TAction([$this, 'onReload']), ['order' => 'ativo']);

        
        $action1 = new TDataGridAction(['CategoriaForm', 'onEdit'], ['id'=>'{id}','register_state' => 'false']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}','register_state' => 'false']);
        $action3 = new TDataGridAction([$this, 'onTurnOnOff'], ['id'=>'{id}','register_state' => 'false']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue fa-fw');
        $this->datagrid->addAction($action2, _t('Delete'), 'far:trash-alt red');
        $this->datagrid->addAction($action3 ,_t('Activate/Deactivate'), 'fas:power-off orange');

        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        $panel = new TPanelGroup('', 'white');
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        /*
        // header actions
        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table blue' );
        $dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf red' );
        $panel->addHeaderWidget( $dropdown );
        */
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
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
            TTransaction::open($this->database);
            $obj = Artigo::find($param['id']);
            if ($obj instanceof Artigo)
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
        $this->clearFilters();
        $this->onReload();
    }
}

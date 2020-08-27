<?php
/**
 * LinkList Listing
 *
 * @version     1.0
 * @package     control
 * @subpackage  configuracoes
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class LinkList extends TPage
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
        $this->setActiveRecord('Link');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('url', 'like', 'url'); // filterField, operator, formField
        $this->addFilterField('tipo_id', '=', 'tipo_id'); // filterField, operator, formField
        $this->addFilterField('lastmod', 'like', 'lastmod'); // filterField, operator, formField
        $this->addFilterField('changefreq', '=', 'changefreq'); // filterField, operator, formField
        $this->addFilterField('priority', '=', 'priority'); // filterField, operator, formField

        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Link');
        $this->form->setFormTitle('Gestão de Links');
        $this->form->setFieldSizes('100%');
        
        // expand button
        $this->form->addExpandButton('','fas:expand',false);


        // create the form fields
        $url        = new TEntry('url');
        $tipo_id    = new TDBUniqueSearch('tipo_id', 'sistema', 'Tipo', 'id', 'nome');
        $lastmod    = new TDateTime('lastmod');
        $changefreq = new TCombo('changefreq');
        $priority   = new TEntry('priority');


        // add the fields
        $this->form->addFields( [ new TLabel('URL') ], [ $url ] );
        $this->form->addFields( [ new TLabel('Tipo') ], [ $tipo_id ] , [ new TLabel('Lastmod') ], [ $lastmod ] );
        $this->form->addFields( [ new TLabel('Changefreq') ], [ $changefreq ] , [ new TLabel('Priority') ], [ $priority ] );


        // definindo parâmetros
        $lastmod->setMask('dd/mm/yyyy hh:ii');
        $lastmod->setDatabaseMask('yyyy-mm-dd hh:ii');
        $changefreq->addItems(['never'=>'never', 'yearly'=>'yearly', 'monthly'=>'monthly', 'weekly'=>'weekly', 'daily'=>'daily', 'hourly'=>'hourly', 'always'=>'always']);
        $priority->setMask('9,99');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Link_filter_data') );
        
        // add the search form actions
        $this->addActionButton(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search','btn-primary');
        //$this->addActionButton(_t('New'),  new TAction(array('LinkForm', 'onEdit')), 'fa:plus green');
        $this->addActionButton(_t('Clear'), new TAction(array($this, 'onClear')), 'fa:eraser red');
        $this->addActionButton('Refazer Links', new TAction(array($this, 'onMakeLinks')), 'fa:list');

        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', '#', 'right');
        $column_url = new TDataGridColumn('url', 'URL', 'left');
        $column_tipo_id = new TDataGridColumn('tipo_id', 'Tipo', 'center');
        $column_lastmod = new TDataGridColumn('lastmod', 'Lastmod', 'center');
        $column_changefreq = new TDataGridColumn('changefreq', 'Changefreq', 'center');
        $column_priority = new TDataGridColumn('priority', 'Priority', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_url);
        $this->datagrid->addColumn($column_tipo_id);
        $this->datagrid->addColumn($column_lastmod);
        $this->datagrid->addColumn($column_changefreq);
        $this->datagrid->addColumn($column_priority);


        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_url->setAction(new TAction([$this, 'onReload']), ['order' => 'url']);
        $column_tipo_id->setAction(new TAction([$this, 'onReload']), ['order' => 'tipo_id']);
        $column_lastmod->setAction(new TAction([$this, 'onReload']), ['order' => 'lastmod']);
        $column_changefreq->setAction(new TAction([$this, 'onReload']), ['order' => 'changefreq']);
        $column_priority->setAction(new TAction([$this, 'onReload']), ['order' => 'priority']);

        // define the transformer method over image
        $column_lastmod->setTransformer(['TTransformers','formataDataHoraBR']);
        $column_tipo_id->setTransformer(['TTransformers','showTipo']);

        
        $action1 = new TDataGridAction(['LinkForm', 'onEdit'], ['id'=>'{id}']);
        //$action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        //$this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');

        
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
     * Método para limpar os campos da pesquisa
     */
    public function onClear()
    {
        $this->clearFilters();
        $this->onReload();
    }
    
    /**
     * 
     */
    public function onMakeLinks()
    {
        try
        {
            TTransaction::open($this->database);

            Link::updateLinks();
            
            new TMessage('info', 'Links reconstruídos com sucesso!', new TAction([$this,'onClear']));
            
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    

}

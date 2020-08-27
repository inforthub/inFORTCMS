<?php
/**
 * MidiaList Listing
 *
 * @version     1.0
 * @package     control
 * @subpackage  gestao
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class MidiaList extends TPage
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
        $this->setActiveRecord('Midia');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
        $this->addFilterField('url', 'like', 'url'); // filterField, operator, formField
        $this->addFilterField('ativo', '=', 'ativo'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Midia');
        $this->form->setFormTitle('Mídias Sociais');
        $this->form->setFieldSizes('100%');
        
        // expand button
        $this->form->addExpandButton('','fas:expand',false);

        // create the form fields
        $nome = new TEntry('nome');
        $url  = new TEntry('url');
        $ativo = new TCombo('ativo');
        $ativo->addItems(['t'=>'Sim','f'=>'Não']);


        // add the fields
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] , [ new TLabel('Url') ], [ $url ] );
        $this->form->addFields( [ new TLabel('Ativo') ], [ $ativo ] , [] , [] );


        // set sizes
        $nome->setSize('100%');
        $url->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Midia_filter_data') );
        
        // add the search form actions
        $this->addActionButton(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search','btn-primary');
        $this->addActionButton(_t('New'),  new TAction(array('MidiaForm', 'onEdit')), 'fa:plus green');
        $this->addActionButton(_t('Clear'), new TAction(array($this, 'onClear')), 'fa:eraser red');

        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', '#', 'right');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_url = new TDataGridColumn('url', 'Url', 'left');
        $column_icone = new TDataGridColumn('icone', 'Ícone', 'left');
        $column_icon = new TDataGridColumn('icone', '', 'center');
        $column_ativo = new TDataGridColumn('ativo', 'Ativo', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_url);
        $this->datagrid->addColumn($column_icon);
        $this->datagrid->addColumn($column_icone);
        $this->datagrid->addColumn($column_ativo);
        
        $column_icon->setTransformer(['TTransformers','showIcone']);
        $column_ativo->setTransformer(['TTransformers','formataSimNao']);


        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_nome->setAction(new TAction([$this, 'onReload']), ['order' => 'nome']);
        $column_url->setAction(new TAction([$this, 'onReload']), ['order' => 'url']);
        $column_icone->setAction(new TAction([$this, 'onReload']), ['order' => 'icone']);
        $column_ativo->setAction(new TAction([$this, 'onReload']), ['order' => 'ativo']);

        
        $action1 = new TDataGridAction(['MidiaForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        $action3 = new TDataGridAction([$this, 'onTurnOnOff'], ['id'=>'{id}']);
        $action4 = new TDataGridAction(['MidiaFormView', 'onEdit'], ['id'=>'{id}']);
        
        $this->datagrid->addAction($action4 ,_t('Clicks Stats'), 'fas:chart-bar green');
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        $this->datagrid->addAction($action3 ,_t('Activate/Deactivate'), 'fas:power-off orange');
        
        
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
     * Turn on/off
     */
    public function onTurnOnOff($param)
    {
        try
        {
            TTransaction::open('sistema');
            $obj = Midia::find($param['id']);
            if ($obj instanceof Midia)
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

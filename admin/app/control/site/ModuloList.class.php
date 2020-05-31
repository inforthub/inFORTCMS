<?php
/**
 * ModuloList Listing
 *
 * @version     1.0
 * @package     control
 * @subpackage  site
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2019 (https://www.infort.eti.br)
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
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
        $this->addFilterField('ativo', '=', 'ativo'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Modulo');
        $this->form->setFormTitle('Modulo');
        $this->form->setFieldSizes('100%');
        

        // create the form fields
        $nome = new TEntry('nome');
        $ativo = new TCombo('ativo');
        $ativo->addItems(['t'=>'Sim','f'=>'Não']);


        // add the fields
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ], [ new TLabel('Ativo') ], [ $ativo ] );

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Modulo_filter_data') );
        
        // add the search form actions
        $this->addActionButton(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search','btn-primary');
        $this->addActionButton(_t('New'),  new TAction(array('ModuloForm', 'onClear')), 'fa:plus green');
        $this->addActionButton(_t('Clear'), new TAction(array($this, 'onClear')), 'fa:eraser red');

        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_modelo_modulo_id = new TDataGridColumn('modelo_modulo_id', 'Modelo de Modulo', 'left');
        $column_ativo = new TDataGridColumn('ativo', 'Ativo', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_modelo_modulo_id);
        $this->datagrid->addColumn($column_ativo);
        
        // definindo método transformador
        $column_ativo->setTransformer(['TTransformers','formataSimNao']);
        $column_modelo_modulo_id->setTransformer(['TTransformers','showModeloModulo']);


        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_nome->setAction(new TAction([$this, 'onReload']), ['order' => 'nome']);
        $column_modelo_modulo_id->setAction(new TAction([$this, 'onReload']), ['order' => 'modelo_modulo_id']);
        $column_ativo->setAction(new TAction([$this, 'onReload']), ['order' => 'ativo']);

        
        // create EDIT action
        $action_edit = new TDataGridAction(['ModuloForm', 'onEdit']);
        //$action_edit->setUseButton(TRUE);
        //$action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('fa:pencil-square-o blue fa-lg');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        //$action_del->setUseButton(TRUE);
        //$action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('fa:trash-o red fa-lg');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);
        
        // create ONOFF action
        $action_onoff = new TDataGridAction(array($this, 'onTurnOnOff'));
        $action_onoff->setButtonClass('btn btn-default');
        $action_onoff->setLabel(_t('Activate/Deactivate'));
        $action_onoff->setImage('fa:power-off fa-lg orange');
        $action_onoff->setField('id');
        $this->datagrid->addAction($action_onoff);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        


        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
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

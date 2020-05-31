<?php
/**
 * SystemChangeLogView
 *
 * @version    1.0
 * @package    control
 * @subpackage log
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemChangeLogView extends TStandardList
{
    protected $form;      // formulário de cadastro
    protected $datagrid;  // listagem
    protected $loaded;
    protected $pageNavigation;  // pagination component
    protected $activeRecord;
    protected $formgrid;
    protected $formfields;
    protected $delAction;
    
    /*
     * método construtor
     * Cria a página, o formulário e a listagem
     */
    public function __construct()
    {
        parent::__construct();
        
        parent::setDatabase('log');
        parent::setActiveRecord('SystemChangeLog');
        parent::setDefaultOrder('id', 'asc');         // defines the default order
        parent::addFilterField('tablename');
        parent::addFilterField('login');
        parent::addFilterField('class_name', 'like'); // add a filter field
        parent::addFilterField('session_id', 'like'); // add a filter field
        parent::setLimit(20);

        $this->form = new BootstrapFormBuilder('form_table_logger');
        $this->form->setFormTitle('Table change log');
        
        // cria os campos do formulário
        $tablename   = new TEntry('tablename');
        $login       = new TEntry('login');
        $class_name  = new TEntry('class_name');
        $session_id  = new TEntry('session_id');
        
        $this->form->addFields( [new TLabel(_t('Table'))], [$tablename], [new TLabel(_t('Program'))], [$class_name] );
        $this->form->addFields( [new TLabel('Login')], [$login], [new TLabel(_t('Session'))], [$session_id]);
        
        $this->form->setData( TSession::getValue('SystemChangeLogView_filter_data') );
        
        $btn = $this->form->addAction(_t('Search'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->formgrid = new TForm;
        
        // instancia objeto DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        $this->datagrid->setGroupColumn('transaction_id', 'Transaction: <b>{transaction_id}</b>');
        $this->datagrid->enablePopover(_t('Execution trace'), '{log_trace_formatted}');
        
        parent::setTransformer(array($this, 'onBeforeLoad'));
        
        // datagrid inside form
        $this->formgrid->add($this->datagrid);
        
        // instancia as colunas da DataGrid
        $id         = new TDataGridColumn('pkvalue',    'PK',      'center');
        $date       = new TDataGridColumn('logdate',    _t('Date'),   'center');
        $login      = new TDataGridColumn('login',      'Login',   'center');
        $name       = new TDataGridColumn('tablename',  _t('Table'),  'center');
        $column     = new TDataGridColumn('columnname', _t('Column'), 'center');
        $operation  = new TDataGridColumn('operation',  _t('Operation'), 'center');
        $oldvalue   = new TDataGridColumn('oldvalue',   _t('Old value'), 'left');
        $newvalue   = new TDataGridColumn('newvalue',   _t('New value'), 'left');
        $class_name = new TDataGridColumn('class_name',  _t('Program'), 'center');
        $php_sapi   = new TDataGridColumn('php_sapi',   'SAPI', 'center');
        $access_ip  = new TDataGridColumn('access_ip',  'IP', 'center');
        
        $operation->setTransformer( function($value, $object, $row) {
            $div = new TElement('span');
            $div->style="text-shadow:none; font-size:12px";
            if ($value == 'created')
            {
                $div->class="label label-success";
            }
            else if ($value == 'deleted')
            {
                $div->class="label label-danger";
            }
            else if ($value == 'changed')
            {
                $div->class="label label-info";
            }
            $div->add($value);
            return $div;
        });
        
        $order1= new TAction(array($this, 'onReload'));
        $order2= new TAction(array($this, 'onReload'));
        $order3= new TAction(array($this, 'onReload'));
        $order4= new TAction(array($this, 'onReload'));
        $order5= new TAction(array($this, 'onReload'));
        
        $order1->setParameter('order', 'pkvalue');
        $order2->setParameter('order', 'logdate');
        $order3->setParameter('order', 'login');
        $order4->setParameter('order', 'tablename');
        $order5->setParameter('order', 'columnname');
        
        $id->setAction($order1);
        $date->setAction($order2);
        $login->setAction($order3);
        $name->setAction($order4);
        $column->setAction($order5);
        
        // adiciona as colunas à DataGrid
        $this->datagrid->addColumn($date);
        $this->datagrid->addColumn($login);
        $this->datagrid->addColumn($name);
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($column);
        $this->datagrid->addColumn($operation);
        $this->datagrid->addColumn($oldvalue);
        $this->datagrid->addColumn($newvalue);
        $this->datagrid->addColumn($class_name);
        $this->datagrid->addColumn($php_sapi);
        $this->datagrid->addColumn($access_ip);
        
        // cria o modelo da DataGrid, montando sua estrutura
        $this->datagrid->createModel();
        
        // cria o paginador
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid)->style='overflow-x:auto';
        $panel->addFooter($this->pageNavigation);
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
    
    /**
     *
     */
    public function filterSession($param)
    {
        parent::clearFilters();
        
        $data = new stdClass;
        $data->session_id = $param['session_id'];
        $this->form->setData($data);
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter('session_id', 'like', $param['session_id']));
        parent::setCriteria($criteria);
        
        $this->onReload($param);
    }
}

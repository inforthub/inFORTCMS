<?php
/**
 * SystemAccessLogList
 *
 * @version    1.0
 * @package    control
 * @subpackage log
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemAccessLogList extends TStandardList
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        parent::setDatabase('log');            // defines the database
        parent::setActiveRecord('SystemAccessLog');   // defines the active record
        parent::setDefaultOrder('id', 'desc');         // defines the default order
        parent::addFilterField('login', 'like'); // add a filter field
        parent::setLimit(20);
        
        // creates the form, with a table inside
        $this->form = new BootstrapFormBuilder('form_search_SystemAccessLog');
        $this->form->setFormTitle('Access Log');
        
        // create the form fields
        $login = new TEntry('login');

        // add the fields
        $this->form->addFields( [new TLabel(_t('Login'))], [$login] );
        $login->setSize('70%');
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('SystemAccessLog_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        $this->datagrid->setHeight(320);
        

        // creates the datagrid columns
        $id = $this->datagrid->addQuickColumn('id', 'id', 'center');
        $sessionid = $this->datagrid->addQuickColumn('sessionid', 'sessionid', 'left');
        $login = $this->datagrid->addQuickColumn(_t('Login'), 'login', 'center');
        $login_time = $this->datagrid->addQuickColumn('login_time', 'login_time', 'center');
        $logout_time = $this->datagrid->addQuickColumn('logout_time', 'logout_time', 'center');
        $access_ip = $this->datagrid->addQuickColumn('IP', 'access_ip', 'center');
        
        $action = new TDataGridAction(['SystemSqlLogList', 'filterSession'], ['session_id' => '{sessionid}']);
        $action2 = new TDataGridAction(['SystemChangeLogView', 'filterSession'], ['session_id' => '{sessionid}']);
        $action3 = new TDataGridAction(['SystemRequestLogList', 'filterSession'], ['session_id' => '{sessionid}']);
        
        $action->setImage('fa:database blue');
        $action2->setImage('fa:film green');
        $action3->setImage('fa:globe orange');
        $action->setLabel(_t('SQL Log'));
        $action2->setLabel(_t('Change Log'));
        $action3->setLabel(_t('Request Log'));
        $action->setUseButton(true);
        $action2->setUseButton(true);
        $action3->setUseButton(true);
        $this->datagrid->addAction($action); 
        $this->datagrid->addAction($action2);
        $this->datagrid->addAction($action3);
        
        $login->setTransformer( function($value, $object, $row) {
            if ($object->impersonated == 'Y')
            {
                $div = new TElement('span');
                $div->class = "label label-info";
                $div->style = "text-shadow:none; font-size:12px";
                $div->add(_t('Impersonated'));
                
                return $value . ' ' . $div;
            }
            return $value;
        });
        
        $order_id = new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $id->setAction($order_id);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
}

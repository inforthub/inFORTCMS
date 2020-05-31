<?php
/**
 * SystemSqlLogList
 *
 * @version    1.0
 * @package    control
 * @subpackage log
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemSqlLogList extends TStandardList
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
        parent::setActiveRecord('SystemSqlLog');   // defines the active record
        parent::setDefaultOrder('id', 'asc');         // defines the default order
        parent::addFilterField('login', 'like'); // add a filter field
        parent::addFilterField('database_name', 'like'); // add a filter field
        parent::addFilterField('sql_command', 'like'); // add a filter field
        parent::addFilterField('class_name', 'like'); // add a filter field
        parent::addFilterField('session_id', 'like'); // add a filter field
        parent::addFilterField('request_id', '='); // add a filter field
        parent::setLimit(20);
        
        // creates the form, with a table inside
        $this->form = new BootstrapFormBuilder('form_search_SystemSqlLog');
        $this->form->setFormTitle('SQL Log');
        
        // create the form fields
        $login       = new TEntry('login');
        $database    = new TEntry('database_name');
        $sql         = new TEntry('sql_command');
        $class_name  = new TEntry('class_name');
        $session_id  = new TEntry('session_id');
        $request_id  = new TEntry('request_id');


        // add the fields
        $this->form->addFields( [new TLabel(_t('Login'))], [$login], [new TLabel(_t('Program'))], [$class_name] );
        $this->form->addFields( [new TLabel(_t('Database'))], [$database], [new TLabel(_t('Session'))], [$session_id] );
        $this->form->addFields( [new TLabel('SQL')], [$sql], [new TLabel(_t('Request'))], [$request_id] );
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('SystemSqlLog_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        $this->datagrid->disableDefaultClick();
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setGroupColumn('transaction_id', 'Transaction: <b>{transaction_id}</b>');
        $this->datagrid->enablePopover(_t('Execution trace'), '{log_trace_formatted}');
        
        // creates the datagrid columns
        //$transaction_id = $this->datagrid->addQuickColumn('Uniqid', 'transaction_id', 'center');
        $id = $this->datagrid->addQuickColumn('ID', 'id', 'center', 50, new TAction(array($this, 'onReload')), array('order', 'id'));
        $logdate = $this->datagrid->addQuickColumn(_t('Date'), 'logdate', 'center', NULL, new TAction(array($this, 'onReload')), array('order', 'logdate'));
        $login = $this->datagrid->addQuickColumn(_t('Login'), 'login', 'center', NULL, new TAction(array($this, 'onReload')), array('order', 'login'));
        $database = $this->datagrid->addQuickColumn(_t('Database'), 'database_name', 'center', NULL, new TAction(array($this, 'onReload')), array('order', 'database_name'));
        $sql = $this->datagrid->addQuickColumn('SQL', 'sql_command', 'left', NULL);
        $class_name = $this->datagrid->addQuickColumn(_t('Program'), 'class_name', 'center');
        $php_sapi = $this->datagrid->addQuickColumn('SAPI', 'php_sapi', 'center');
        $access_ip  = $this->datagrid->addQuickColumn('IP', 'access_ip', 'center');
        
        $sql->setTransformer(function($sql_string) {
            $original_sql = $sql_string;
            $m = [];
            preg_match_all("/'([^']+)'/", $sql_string, $matches);
            
            if (count($matches[0]) > 0)
            {
                foreach ($matches[0] as $found_string)
                {
                    $sql_string = str_replace($found_string, '<b class="orange">'.$found_string.'</b>', $sql_string);
                }
            }
            
            $sql_string = str_replace('INSERT INTO ', '<b class="blue">INSERT INTO </b>', $sql_string);
            $sql_string = str_replace('DELETE FROM ', '<b class="blue">DELETE FROM </b>', $sql_string);
            $sql_string = str_replace('UPDATE ',  '<b class="blue">UPDATE </b>',  $sql_string);
            $sql_string = str_replace(' FROM ',   '<b class="blue"> FROM </b>',   $sql_string);
            $sql_string = str_replace(' WHERE ',  '<b class="blue"> WHERE </b>',  $sql_string);
            $sql_string = str_replace(' SET ',    '<b class="blue"> SET </b>',    $sql_string);
            $sql_string = str_replace(' VALUES ', '<b class="blue"> VALUES </b>', $sql_string);
            
            $div = new TElement('span');
            $div->style="text-shadow:none; font-size:12px";
            if (substr($original_sql, 0, 11) == 'INSERT INTO')
            {
                $div->class="label label-success";
                $div->add('INSERT');
            }
            else if (substr($original_sql, 0, 11) == 'DELETE FROM')
            {
                $div->class="label label-danger";
                $div->add('DELETE');
            }
            if (substr($original_sql, 0, 6) == 'UPDATE')
            {
                $div->class="label label-info";
                $div->add('UPDATE');
            }
            
            return $div . $sql_string;
        });
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
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
    
    /**
     *
     */
    public function filterRequest($param)
    {
        parent::clearFilters();
        
        $data = new stdClass;
        $data->request_id = $param['request_id'];
        $this->form->setData($data);
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter('request_id', '=', $param['request_id']));
        parent::setCriteria($criteria);
        
        $this->onReload($param);
    }
}

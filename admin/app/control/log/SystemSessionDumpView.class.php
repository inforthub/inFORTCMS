<?php
/**
 * SystemSessionDumpView
 *
 * @version    1.0
 * @package    control
 * @subpackage log
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemSessionDumpView extends TPage
{
    private $datagrid;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        
        $name  = new TDataGridColumn('name',    _t('Name'),    'left',   '20%');
        $value = new TDataGridColumn('value',   _t('Value'),   'left',   '80%');
        
        $value->setTransformer(function($value) {
            return '<pre style="border:none;background:none">'.print_r($value,true).'</pre>';
        });
        
        $this->datagrid->addColumn($name);
        $this->datagrid->addColumn($value);
        
        $action1 = new TDataGridAction([$this, 'onDeleteSessionVar'],   ['name'=>'{name}' ] );
        $action1->setUseButton(TRUE);
        $this->datagrid->addAction($action1, _t('Delete'), 'fas:trash-alt red');
        
        $input_search = new TEntry('input_search');
        $input_search->placeholder = _t('Search');
        $input_search->setSize('100%');
        
        $this->datagrid->enableSearch($input_search, 'name');
        $this->datagrid->createModel();
        
        if ($_SESSION[APPLICATION_NAME])
        {
            foreach ($_SESSION[APPLICATION_NAME] as $name => $value)
            {
                $data = new stdClass;
                $data->name = $name;
                $data->value = $value;
                
                $this->datagrid->addItem($data);
            }
        }
        
        $panel = new TPanelGroup(_t('Session'));
        $panel->addHeaderWidget($input_search);
        $panel->add($this->datagrid)->style = 'overflow-x:auto';
        
        $vbox = new TVBox;
        $vbox->style = 'width:100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($panel);
        
        parent::add($panel);
    }
    
    /**
     * Ask before deletion
     */
    public static function onDeleteSessionVar($param)
    {
        $action1 = new TAction(array(__CLASS__, 'deleteSessionVar'));
        $action1->setParameters($param);
        new TQuestion('Do you really want to delete ?', $action1);
    }
    
    /**
     * Delete session var
     */
    public static function deleteSessionVar($param)
    {
        TSession::delValue($param['name']);
        AdiantiCoreApplication::gotoPage('SystemSessionDumpView');
    }
}

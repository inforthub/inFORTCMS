<?php
/**
 * SystemMessageList
 *
 * @version    1.0
 * @package    control
 * @subpackage communication
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemMessageList extends TStandardList
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    protected $transformCallback;
    protected $folders;
    
    /**
     * Page constructor
     */
    public function __construct($param)
    {
        parent::__construct();
        
        parent::setDatabase('communication');            // defines the database
        parent::setActiveRecord('SystemMessage');   // defines the active record
        parent::setDefaultOrder('id', 'desc');         // defines the default order
        parent::addFilterField('checked', 'like', 'checked'); // filterField, operator, formField
        parent::addFilterField('subject', 'like', 'subject'); // filterField, operator, formField
        parent::addFilterField('message', 'like', 'message'); // filterField, operator, formField
        
        parent::setCriteria( TSession::getValue('inbox_criteria') ); // define a standard filter
        
        // creates the form
        $this->form = new TForm('form_search_SystemMessage');
        
        // create the form fields
        $subject = new TEntry('subject');
        $message = new TEntry('message');
        $button  = TButton::create( 'search', array($this, 'onSearch'), _t('Find'), 'fa:search');
        
        $subject->placeholder = _t('Subject');
        $message->placeholder = _t('Message');
        
        $table = new TTable;
        $table->style = 'width: 100%';
        $table->addRowSet( $subject, $message, $button );

        $subject->setSize('100%');
        $message->setSize('100%');
        
        $this->form->add($table);
        
        // define logic form fields
        $this->form->setFields( [$subject, $message, $button] );
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('SystemMessage_filter_data') );
        
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';

        // creates the datagrid columns
        $column_message = new TDataGridColumn('message', _t('Message'), 'left', '60%');
        $column_from    = new TDataGridColumn('user_mixed->name', _t('User'), 'center', '20%');
        $column_date    = new TDataGridColumn('dt_message', _t('Date'), 'center', '20%');
        
        $column_from->setTransformer( function($value, $object, $row) {
            if ($object->checked == 'Y')
            {
                $row->style = "color:gray";
            }
            return $value;
        });
        
        $column_message->setTransformer( function($value, $object, $row) {
            return '<b>'.$object->subject.'</b> - ' . $value;
        });
        
        $column_date->setTransformer( function($value, $object, $row) {
            return '<i class="fas fa-calendar-alt red"/> '.substr($value,0,10);
        });
        
        $action = new TDataGridAction(array('SystemMessageFormView', 'onView'));
        $action->setField('id');
        $action->setImage('far:folder-open');
        $this->datagrid->addAction($action);
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_message);
        $this->datagrid->addColumn($column_from);
        $this->datagrid->addColumn($column_date);

        $order = new TAction(array($this, 'onReload'));
        $order->setParameter('order', 'dt_message');
        $column_message->setAction($order);
        
        parent::setTransformer( array($this, 'onBeforeLoad') );
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup('&nbsp;');
        $panel->addHeaderWidget($this->form);
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        $this->folders = new THtmlRenderer('app/resources/system_message_folders.html');
        $this->folders->enableSection('main', ['class_inbox'    => (TSession::getValue('inbox_criteria_type')== 'inbox') ? 'active' : '']);
        $this->folders->enableSection('main', ['class_sent'     => (TSession::getValue('inbox_criteria_type')== 'sent') ? 'active' : '']);
        $this->folders->enableSection('main', ['class_archived' => (TSession::getValue('inbox_criteria_type')== 'archived') ? 'active' : '']);
        $this->folders->enableTranslation();
        
        $hbox = new THBox;
        $hbox->style = 'width:100%';
        $hbox->add($this->folders, '')->class = 'left-mailbox';
        $hbox->add($panel, '')->class = 'right-mailbox';
        
        $vbox = new TVBox;
        $vbox->style = 'width:100%';
        $vbox->add(TBreadCrumb::create( [_t('Messages'), _t('List')] ) );
        $vbox->add($hbox);
        
        parent::add($vbox);
    }
    
    /**
     * show inbox folder
     */
    public function filterInbox($param)
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('system_user_to_id', '=', TSession::getValue('userid') ) );
        $criteria->add(new TFilter('checked', '<>', 'Y' ) );
        TSession::setValue('inbox_criteria', $criteria);
        TSession::setValue('inbox_criteria_type', 'inbox');
        parent::setCriteria( $criteria ); // define a standard filter
        
        $this->folders->enableSection('main', ['class_inbox'    => 'active',
                                               'class_sent'     => '',
                                               'class_archived' => '']);
        
        $this->onReload($param);
    }
    
    /**
     * show archived folder
     */
    public function filterArchived($param)
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('system_user_to_id', '=', TSession::getValue('userid') ) );
        $criteria->add(new TFilter('checked', '=', 'Y' ) );
        TSession::setValue('inbox_criteria', $criteria);
        TSession::setValue('inbox_criteria_type', 'archived');
        parent::setCriteria( $criteria ); // define a standard filter
        
        $this->folders->enableSection('main', ['class_inbox'    => '',
                                               'class_sent'     => '',
                                               'class_archived' => 'active']);
        
        $this->onReload($param);
    }
    
    /**
     * show sent folder
     */
    public function filterSent($param)
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('system_user_id', '=', TSession::getValue('userid') ) );
        TSession::setValue('inbox_criteria', $criteria);
        TSession::setValue('inbox_criteria_type', 'sent');
        parent::setCriteria( $criteria ); // define a standard filter
        
        $this->folders->enableSection('main', ['class_inbox'    => '',
                                               'class_sent'     => 'active',
                                               'class_archived' => '']);
        
        $this->onReload($param);
    }
}

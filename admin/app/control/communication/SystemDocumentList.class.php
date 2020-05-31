<?php
/**
 * SystemDocumentList
 *
 * @version    1.0
 * @package    control
 * @subpackage communication
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemDocumentList extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_SystemDocument');
        $this->form->setFormTitle(_t('Documents'));
        $this->form->setFieldSizes('100%');
        
        // create the form fields
        $title       = new TEntry('title');
        $category_id = new TDBCombo('category_id', 'communication', 'SystemDocumentCategory', 'id', 'name');
        $filename    = new TEntry('filename');
        
        $this->form->addFields( [new TLabel(_t('Title'))], [$title] , [new TLabel(_t('Category'))], [$category_id] );
        $this->form->addFields( [new TLabel(_t('File'))], [$filename] , [] );
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('SystemDocument_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction(array('SystemDocumentUploadForm', 'onNew')), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->datatable = 'true';
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'center', 50);
        $column_title = new TDataGridColumn('title', _t('Title'), 'left');
        $column_category_id = new TDataGridColumn('category->name', _t('Category'), 'center');
        $column_submission_date = new TDataGridColumn('submission_date', _t('Date'), 'center', 100);

        $column_id->setTransformer(function($value, $object, $row) {
            if ($object->archive_date)
            {
                $row->style= 'text-shadow:none; color:#8c8484';
            }
            return $value;
        });
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_title);
        $this->datagrid->addColumn($column_category_id);
        $this->datagrid->addColumn($column_submission_date);
        
        if (TSession::getValue('login') == 'admin')
        {
            $column_user = new TDataGridColumn('system_user->name', _t('User'), 'left');
            $this->datagrid->addColumn($column_user);
        }
        
        // creates the datagrid column actions
        $order_id = new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $column_id->setAction($order_id);
        
        $order_title = new TAction(array($this, 'onReload'));
        $order_title->setParameter('order', 'title');
        $column_title->setAction($order_title);
        
        $order_category_id = new TAction(array($this, 'onReload'));
        $order_category_id->setParameter('order', 'category_id');
        $column_category_id->setAction($order_category_id);
        
        $order_submission = new TAction(array($this, 'onReload'));
        $order_submission->setParameter('order', 'submission_date');
        $column_submission_date->setAction($order_submission);
        
        // create DOWNLOAD action
        $action_download = new TDataGridAction(array($this, 'onDownload'));
        //$action_edit->setUseButton(TRUE);
        $action_download->setButtonClass('btn btn-default');
        $action_download->setLabel(_t('Download'));
        $action_download->setImage('fas:cloud-download-alt green');
        $action_download->setField('id');
        $this->datagrid->addAction($action_download);

        // create UPLOAD action
        $action_upload = new TDataGridAction(array('SystemDocumentUploadForm', 'onEdit'));
        //$action_edit->setUseButton(TRUE);
        $action_upload->setButtonClass('btn btn-default');
        $action_upload->setLabel(_t('Upload'));
        $action_upload->setImage('fas:cloud-upload-alt orange');
        $action_upload->setField('id');
        $this->datagrid->addAction($action_upload);
        
        // create EDIT action
        $action_edit = new TDataGridAction(array('SystemDocumentForm', 'onEdit'));
        //$action_edit->setUseButton(TRUE);
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('far:edit blue');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        //$action_del->setUseButton(TRUE);
        $action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('far:trash-alt red');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
    
    /**
     * Download file
     */
    public function onDownload($param)
    {
        try
        {
            if (isset($param['id']))
            {
                $id = $param['id'];  // get the parameter $key
                TTransaction::open('communication'); // open a transaction
                $object = new SystemDocument($id); // instantiates the Active Record
                
                if ($object->system_user_id == TSession::getValue('userid') OR TSession::getValue('login') === 'admin')
                {
                    if (strtolower(substr($object->filename, -4)) == 'html')
                    {
                        $win = TWindow::create( $object->filename, 0.8, 0.8 );
                        $win->add( file_get_contents( "files/documents/{$id}/".$object->filename ) );
                        $win->show();
                    }
                    else
                    {
                        TPage::openFile("files/documents/{$id}/".$object->filename);
                    }
                }
                else
                {
                    new TMessage('error', _t('Permission denied'));
                }
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue('SystemDocumentList_filter_title',   NULL);
        TSession::setValue('SystemDocumentList_filter_category_id',   NULL);
        TSession::setValue('SystemDocumentList_filter_filename',   NULL);

        if (isset($data->title) AND ($data->title)) {
            $filter = new TFilter('title', 'like', "%{$data->title}%"); // create the filter
            TSession::setValue('SystemDocumentList_filter_title',   $filter); // stores the filter in the session
        }


        if (isset($data->category_id) AND ($data->category_id)) {
            $filter = new TFilter('category_id', '=', $data->category_id); // create the filter
            TSession::setValue('SystemDocumentList_filter_category_id',   $filter); // stores the filter in the session
        }


        if (isset($data->filename) AND ($data->filename)) {
            $filter = new TFilter('filename', 'like', "%{$data->filename}%"); // create the filter
            TSession::setValue('SystemDocumentList_filter_filename',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('SystemDocument_filter_data', $data);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'communication'
            TTransaction::open('communication');
            
            // creates a repository for SystemDocument
            $repository = new TRepository('SystemDocument');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            if (TSession::getValue('login') !== 'admin')
            {
                $criteria->add(new TFilter('system_user_id', '=', TSession::getValue('userid')));
            }
            
            if (TSession::getValue('SystemDocumentList_filter_title')) {
                $criteria->add(TSession::getValue('SystemDocumentList_filter_title')); // add the session filter
            }

            if (TSession::getValue('SystemDocumentList_filter_category_id')) {
                $criteria->add(TSession::getValue('SystemDocumentList_filter_category_id')); // add the session filter
            }

            if (TSession::getValue('SystemDocumentList_filter_filename')) {
                $criteria->add(TSession::getValue('SystemDocumentList_filter_filename')); // add the session filter
            }

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * Ask before deletion
     */
    public function onDelete($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'Delete'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }
    
    /**
     * Delete a record
     */
    public function Delete($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            TTransaction::open('communication'); // open a transaction with database
            $object = new SystemDocument($key, FALSE); // instantiates the Active Record
            if ($object->system_user_id == TSession::getValue('userid') OR TSession::getValue('login') === 'admin')
            {
                $object->delete(); // deletes the object from the database
            }
            else
            {
                throw new Exception(_t('Permission denied'));
            }
            TTransaction::close(); // close the transaction
            $this->onReload( $param ); // reload the listing
            new TMessage('info', AdiantiCoreTranslator::translate('Record deleted')); // success message
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload', 'onSearch')))) )
        {
            if (func_num_args() > 0)
            {
                $this->onReload( func_get_arg(0) );
            }
            else
            {
                $this->onReload();
            }
        }
        parent::show();
    }
}

<?php
/**
 * SystemSharedDocumentList
 *
 * @version    1.0
 * @package    control
 * @subpackage communication
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemSharedDocumentList extends TPage
{
    private $form; // form
    private $card; // listing
    private $pageNavigation;
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
        $this->form->setFormTitle(_t('Shared with me'));
        
        // create the form fields
        $title       = new TEntry('title');
        $category_id = new TDBCombo('category_id', 'communication', 'SystemDocumentCategory', 'id', 'name');

        $this->form->addFields( [new TLabel(_t('Title'))], [$title] );
        $this->form->addFields( [new TLabel(_t('Category'))], [$category_id] );
        
        $title->setSize('70%');
        $category_id->setSize('70%');
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('SystemDocument_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates the cards
        $this->card = new TCardView;
        $this->card->addAction(new TAction([$this, 'onDownload'], ['id' => '{id}']), _t('Download'), 'fas:cloud-download-alt bg-green');
        
        $item_tpl = '<table width="100%">
                        <tr>
                            <td style="vertical-align:top">
                                {description}
                            </td>
                            <td align="right" style="min-width: 100px">
                                {category->name} <i class="fa fa-th fa-fw" aria-hidden="true"></i> <br>
                                {submission_date} <i class="fas fa-calendar-alt fa-fw" aria-hidden="true"></i>
                            </td>
                        </tr>
                     </table>';
        
        if (TSession::getValue('login') == 'admin')
        {
            $item_tpl = str_replace('{description}', '{description}' . ' <br> <i class="far fa-user" aria-hidden="true"></i> {system_user->name}', $item_tpl);
        }
        
        $this->card->setTitleAttribute('title');
        $this->card->setItemTemplate($item_tpl);
        $this->card->setItemDatabase('communication');
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        
        $panel = new TPanelGroup;
        $panel->add($this->card);
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
                
                //system_user_id
                if ($object->hasUserAccess( TSession::getValue('userid') ) OR $object->hasGroupAccess( TSession::getValue('usergroupids') ))
                {
                    if (strtolower(substr($object->filename, -4)) == 'html')
                    {
                        $win = TWindow::create( $object->filename, 0.8, 0.8 );
                        $win->add( file_get_contents( "files/documents/{$id}/".$object->filename ) );
                        $win->show();
                    }
                    else if (strtolower(substr($object->filename, -3)) == 'pdf')
                    {
                        $embed = new TElement('object');
                        $embed->data  = "download.php?file=files/documents/{$id}/".$object->filename;
                        $embed->type  = 'application/pdf';
                        $embed->style = "width: 100%; height:calc(100% - 10px)";
                        
                        $win = TWindow::create( $object->filename, 0.8, 0.8 );
                        $win->add( $embed );
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

        if (isset($data->title) AND ($data->title)) {
            $filter = new TFilter('title', 'like', "%{$data->title}%"); // create the filter
            TSession::setValue('SystemDocumentList_filter_title',   $filter); // stores the filter in the session
        }


        if (isset($data->category_id) AND ($data->category_id)) {
            $filter = new TFilter('category_id', '=', "$data->category_id"); // create the filter
            TSession::setValue('SystemDocumentList_filter_category_id',   $filter); // stores the filter in the session
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
     * Load the card with data
     */
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'communication'
            TTransaction::open('communication');
            
            // creates a repository for SystemDocument
            $repository = new TRepository('SystemDocument');
            $limit = 100;
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
            
            $criteria->add(new TFilter('archive_date', 'is', null));
            
            // shared sub-criteria
            $userid = TSession::getValue('userid');
            $usergroups = implode(',', TSession::getValue('usergroupids'));
            $shared_criteria = new TCriteria;
            $shared_criteria->add(new TFilter('id', 'IN', "(SELECT document_id FROM system_document_user WHERE system_user_id='$userid')"), TExpression::OR_OPERATOR);
            $shared_criteria->add(new TFilter('id', 'IN', "(SELECT document_id FROM system_document_group WHERE system_group_id IN ($usergroups))"), TExpression::OR_OPERATOR);
            $criteria->add($shared_criteria);
            
            
            if (TSession::getValue('SystemDocumentList_filter_title')) {
                $criteria->add(TSession::getValue('SystemDocumentList_filter_title')); // add the session filter
            }
            
            if (TSession::getValue('SystemDocumentList_filter_category_id')) {
                $criteria->add(TSession::getValue('SystemDocumentList_filter_category_id')); // add the session filter
            }
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $this->card->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the card
                    $this->card->addItem($object);
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
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the card is already loaded
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

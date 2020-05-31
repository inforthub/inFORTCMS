<?php
/**
 * TemplateList Listing
 *
 * @version    1.0
 * @package    control
 * @subpackage configuracoes
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 */
class TemplateList extends TPage
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
        $this->setActiveRecord('Template');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
        $this->addFilterField('nome_fisico', 'like', 'nome_fisico'); // filterField, operator, formField
        $this->addFilterField('padrao', '=', 'padrao'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Template');
        $this->form->setFormTitle('Templates');
        $this->form->setFieldSizes('100%');
        

        // create the form fields
        $nome        = new TEntry('nome');
        $nome_fisico = new TEntry('nome_fisico');
        $padrao      = new TCombo('padrao');
        
        
        // parametros dos campos
        $padrao->addItems(['t'=>'Sim','f'=>'Não']);


        // add the fields
        $this->form->addFields( [ new TLabel('Template') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Nome Fisico') ], [ $nome_fisico ] );
        $this->form->addFields( [ new TLabel('Padrão') ], [ $padrao ] );

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Template_filter_data') );
                
        // add the search form actions
        $this->addActionButton(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search','btn-primary');
        $this->addActionButton(_t('New'),  new TAction(array('TemplateForm', 'onEdit')), 'fa:plus green');
        $this->addActionButton(_t('Clear'), new TAction(array($this, 'onClear')), 'fa:eraser red');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_nome = new TDataGridColumn('nome', 'Template', 'left');
        $column_nome_fisico = new TDataGridColumn('nome_fisico', 'Nome Fisico', 'left');
        $column_dt_cadastro = new TDataGridColumn('dt_cadastro', 'Data Cadastro', 'center');
        $column_padrao = new TDataGridColumn('padrao', 'Padrão', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_nome_fisico);
        $this->datagrid->addColumn($column_dt_cadastro);
        $this->datagrid->addColumn($column_padrao);
        
        // definindo método transformador
        $column_padrao->setTransformer(['TTransformers','formataSimNao']);
        $column_dt_cadastro->setTransformer(['TTransformers','formataDataBR']);


        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_nome->setAction(new TAction([$this, 'onReload']), ['order' => 'nome']);
        $column_nome_fisico->setAction(new TAction([$this, 'onReload']), ['order' => 'nome_fisico']);
        $column_dt_cadastro->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_cadastro']);
        $column_padrao->setAction(new TAction([$this, 'onReload']), ['order' => 'padrao']);

        
        $action1 = new TDataGridAction(['TemplateForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        $action3 = new TDataGridAction(['TemplateFile', 'onReload'], ['id'=>'{id}']);
        $action4 = new TDataGridAction([$this, 'onClone'], ['id'=>'{id}']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        $this->datagrid->addAction($action3 ,_t('Directory and files verification'), 'far:folder-open orange');
        $this->datagrid->addAction($action4 ,_t('Clone'), 'far:clone dark');
                
        
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
     * Abre uma janela para inserção do nome do Clone
     */
    public function onClone($param)
    {
        $form = new BootstrapFormBuilder('input_form_clone');
        $form->setFieldSizes('100%');
        
        $nome        = new TEntry('nome');
        $nome_fisico = new TEntry('nome_fisico');
        $id_clone    = new THidden('id_clone');
        
        $nome_fisico->forceLowerCase();
        $id_clone->setValue($param['id']);
        
        $form->addFields( [ new TLabel('Novo Nome da Template') ] );
        $form->addFields( [ $nome ] );
        $form->addFields( [ new TLabel('Novo Nome da Pasta') ] );
        $form->addFields( [ $nome_fisico ] );
        $form->addFields( [ $id_clone ] );
        
        $nome->addValidation('Nome', new TRequiredValidator);
        $nome_fisico->addValidation('Nome Fisico', new TRequiredValidator);

        // create the form actions
        $btn = $form->addAction(_t('Save'), new TAction(array($this, 'onSaveClone')), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        $form->addAction(_t('Cancel'), new TAction(array($this, 'onClear')), 'fa:eraser red');
        
        // show the input dialog
        new TInputDialog('Clonar Template', $form);
    }
    
    /**
     * Salva o clone
     */
    public static function onSaveClone($param)
    {
        try
        {
            TTransaction::open('sistema'); // open a transaction
            
            if (empty($param['nome']))
            {
                throw new Exception(_t('The field ^1 is required', 'Novo Nome da Template'));
            }
            
            if (empty($param['nome_fisico']))
            {
                throw new Exception(_t('The field ^1 is required', 'Novo Nome da Pasta'));
            }
            
            // verifica e clona os arquivos
            $template_orig = new Template($param['id_clone']);
            
            if ($template_orig->nome_fisico != $param['nome_fisico'])
            {
                // verificando sa já existe uma pasta com esse nome
                if (is_dir('../templates/'.$param['nome_fisico']))
                    throw new Exception(_t("A directory with this name already exists."));
                
                // clona os arquivos
                Template::CopiaDir('../templates/'.$template_orig->nome_fisico, '../templates/'.$param['nome_fisico']);
            }

            // cria um novo registro no banco de dados com os dados clonados
            $obj = new Template;
            $obj->nome = $param['nome'];
            $obj->nome_fisico = THelper::urlAmigavel($param['nome_fisico']);
            $obj->script_head = $template_orig->script_head;
            $obj->script_body = $template_orig->script_body;
            $obj->store();
            
            TTransaction::close(); // close the transaction
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'), new TAction(['TemplateList','onClear']));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            //$this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Método para limpar os campos da pesquisa
     */
    public function onClear()
    {
        // limpando dados da sessão
        THelper::clearSession();
        $this->clearFilters();
        $this->onReload();
    }
    

}

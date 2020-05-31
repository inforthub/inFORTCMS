<?php
/**
 * ClickList Listing
 *
 * @version    1.0
 * @package    control
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 */
class ClickList extends TPage
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
    public function __construct($param)
    {
        parent::__construct();
        
        $this->setDatabase('sistema');            // defines the database
        $this->setActiveRecord('Click');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        
        $this->addFilterField('dt_clique', 'like', 'dt_clique'); // filterField, operator, formField
        $this->addFilterField('pagina', '=', 'pagina'); // filterField, operator, formField
        $this->addFilterField('ip', 'like', 'ip'); // filterField, operator, formField
        $this->addFilterField('cidade', 'like', 'cidade'); // filterField, operator, formField
        $this->addFilterField('regiao', 'like', 'regiao'); // filterField, operator, formField
        $this->addFilterField('pais', '=', 'pais'); // filterField, operator, formField
        $this->addFilterField('navegador', 'like', 'navegador'); // filterField, operator, formField
        $this->addFilterField('plataforma', 'like', 'plataforma'); // filterField, operator, formField
        $this->addFilterField('midia_id', '=', 'midia_id'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Click');
        $this->form->setFormTitle('Registros de Cliques');
        $this->form->setFieldSizes('100%');
        

        // create the form fields
        $dt_clique  = new TEntry('dt_clique');
        $pagina     = new TDBUniqueSearch('pagina', 'sistema', 'Pagina', 'id', 'titulo');
        $ip         = new TEntry('ip');
        $cidade     = new TEntry('cidade');
        $regiao     = new TEntry('regiao');
        $pais       = new TDBUniqueSearch('pais', 'sistema', 'Pais', 'id', 'iso');
        $navegador  = new TEntry('navegador');
        $plataforma = new TEntry('plataforma');
        $midia_id   = new TDBCombo('midia_id', 'sistema', 'Midia', 'id', 'nome', 'nome');

        
        $pais->setMinLength(1);
        $pagina->setMinLength(1);

        // add the fields
        $this->form->addFields( [ new TLabel('Data') ], [ $dt_clique ] , [ new TLabel('Pagina') ], [ $pagina ] );
        $this->form->addFields( [ new TLabel('Ip') ], [ $ip ] , [ new TLabel('Cidade') ], [ $cidade ] );
        $this->form->addFields( [ new TLabel('Regiao') ], [ $regiao ] , [ new TLabel('Pais') ], [ $pais ] );
        $this->form->addFields( [ new TLabel('Navegador') ], [ $navegador ] , [ new TLabel('Plataforma') ], [ $plataforma ] );
        $this->form->addFields( [ new TLabel('Mídia') ], [ $midia_id ] , [],[] );


        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Click_filter_data') );
        
        // add the search form actions
        $this->addActionButton(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search','btn-primary');
        $this->addActionButton(_t('Clear'), new TAction(array($this, 'onClear')), 'fa:eraser red');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        $this->datagrid->enablePopover('Detalhes', 'Cidade: <b>{cidade}</b><br>Região: <b>{regiao}</b><br>País: <b>{pais}</b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', '#', 'right');
        $column_dt_clique = new TDataGridColumn('dt_clique', 'Data', 'center');
        $column_pagina = new TDataGridColumn('pagina', 'Página', 'left');
        $column_ip = new TDataGridColumn('ip', 'IP', 'center');
        //$column_cidade = new TDataGridColumn('cidade', 'Cidade', 'left');
        //$column_regiao = new TDataGridColumn('regiao', 'Região', 'left');
        //$column_pais = new TDataGridColumn('pais', 'País', 'left');
        $column_navegador = new TDataGridColumn('navegador', 'Navegador', 'center');
        $column_plataforma = new TDataGridColumn('plataforma', 'Plataforma', 'center');
        $column_midia_id = new TDataGridColumn('midia_id', 'Mídia', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_dt_clique);
        $this->datagrid->addColumn($column_pagina);
        $this->datagrid->addColumn($column_ip);
        //$this->datagrid->addColumn($column_cidade);
        //$this->datagrid->addColumn($column_regiao);
        //$this->datagrid->addColumn($column_pais);
        $this->datagrid->addColumn($column_navegador);
        $this->datagrid->addColumn($column_plataforma);
        $this->datagrid->addColumn($column_midia_id);


        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_dt_clique->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_clique']);
        $column_pagina->setAction(new TAction([$this, 'onReload']), ['order' => 'pagina']);
        $column_ip->setAction(new TAction([$this, 'onReload']), ['order' => 'ip']);
        //$column_cidade->setAction(new TAction([$this, 'onReload']), ['order' => 'cidade']);
        //$column_regiao->setAction(new TAction([$this, 'onReload']), ['order' => 'regiao']);
        //$column_pais->setAction(new TAction([$this, 'onReload']), ['order' => 'pais']);
        $column_navegador->setAction(new TAction([$this, 'onReload']), ['order' => 'navegador']);
        $column_plataforma->setAction(new TAction([$this, 'onReload']), ['order' => 'plataforma']);
        $column_midia_id->setAction(new TAction([$this, 'onReload']), ['order' => 'midia_id']);

        // define the transformer method over image
        $column_dt_clique->setTransformer(['TTransformers','formataDataHoraBR']);
        $column_midia_id->setTransformer(['TTransformers','showMidia']);


        /*
        $action1 = new TDataGridAction(['ClickForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        */

        
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
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
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
     * Método para limpar os campos da pesquisa
     */
    public function onClear()
    {
        $this->clearFilters();
        $this->onReload();
    }
    
    

}

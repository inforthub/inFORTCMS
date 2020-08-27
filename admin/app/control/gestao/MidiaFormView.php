<?php
/**
 * MidiaFormView Form
 *
 * @version     1.0
 * @package     control
 * @subpackage  gestao
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class MidiaFormView extends TPage
{
    protected $form;
    protected $detail_list;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');

        $this->form = new BootstrapFormBuilder('form_Midia_View');
        
        $this->form->setFormTitle('Estatísticas da Mídia');
        $this->form->setColumnClasses(2, ['col-sm-3', 'col-sm-9']);
        
        $dropdown = new TDropDown('Opções','fa:th');
        $dropdown->addAction(_t('Print'), new TAction([$this, 'onPrint'], ['key'=>$param['key'], 'static' => '1']), 'far:file-pdf red');
        $dropdown->addAction(_t('Edit'), new TAction(['MidiaForm', 'onEdit'], ['key'=>$param['key'], 'register_state'=>'true']), 'far:edit blue');
        $dropdown->addAction(_t('Close'),  new TAction([__CLASS__, 'onClose'], ['static'=>'1']), 'fa:times red');
        
        $this->form->addHeaderWidget($dropdown);
        
        $btn = $this->form->addHeaderActionLink( '',  new TAction([__CLASS__, 'onClose'], ['static'=>'1']), 'fa:times');
        $btn->{'class'} = 'btn btn-sm btn-danger';
        $this->form->addAction(_t('Close'),  new TAction([__CLASS__, 'onClose'], ['static'=>'1']), 'fa:times red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($this->form);
        parent::add($container);
    }
    
    /**
     * Show data
     */
    public function onEdit( $param )
    {
        try
        {
            TTransaction::open('sistema');
        
            $object = new Midia($param['key']);
            
            $stats = Click::getTotais($param['key']);
            
            //$label_id = new TLabel('#:', '#333333', '', 'B');
            $label_nome       = new TLabel('Mídia:', '#333333', '', 'B');
            $label_url        = new TLabel('Url:', '#333333', '', 'B');
            $label_u_24_horas = new TLabel('Últimas 24 horas:', '#333333', '12px', 'B');
            $label_u_7_dias   = new TLabel('Últimos 7 dias:', '#333333', '12px', 'B');
            $label_u_30_dias  = new TLabel('Últimos 30 dias:', '#333333', '12px', 'B');
            $label_u_365_dias = new TLabel('Últimos 365 dias:', '#333333', '12px', 'B');
            $label_total      = new TLabel('Total:', '#333333', '12px', 'B');


            //$text_id  = new TTextDisplay($object->id, '#333333', '', '');
            $text_nome       = new TTextDisplay($object->nome, '#333333', '', '');
            $text_url        = new TTextDisplay($object->url, '#333333', '', '');
            $text_u_24_horas = new TTextDisplay($stats['u_24_horas'], '#333333', '12px', '');
            $text_u_7_dias   = new TTextDisplay($stats['u_7_dias'], '#333333', '12px', '');
            $text_u_30_dias  = new TTextDisplay($stats['u_30_dias'], '#333333', '12px', '');
            $text_u_365_dias = new TTextDisplay($stats['u_365_dias'], '#333333', '12px', '');
            $text_total      = new TTextDisplay($stats['total'], '#333333', '12px', '');

            //$this->form->addFields([$label_id],[$text_id]);
            $this->form->addFields([$label_nome],[$text_nome]);
            $this->form->addFields([$label_url],[$text_url]);
            $this->form->addContent( [''] );
            $this->form->addContent( [new TFormSeparator('Cliques')] );
            $this->form->addFields([$label_u_24_horas],[$text_u_24_horas], [$label_u_30_dias],[$text_u_7_dias])->layout = ['col-sm-4 control-label', 'col-sm-2', 'col-sm-4 control-label', 'col-sm-2' ];
            $this->form->addFields([$label_u_365_dias],[$text_u_30_dias], [$label_u_7_dias],[$text_u_365_dias])->layout = ['col-sm-4 control-label', 'col-sm-2', 'col-sm-4 control-label', 'col-sm-2' ];
            $this->form->addFields([$label_total],[$text_total])->layout = ['col-sm-4 control-label', 'col-sm-8'];

            
            //
            $this->detail_list = new BootstrapDatagridWrapper( new TDataGrid );
            $this->detail_list->style = 'width:100%';
            $this->detail_list->disableDefaultClick();
            
            $this->detail_list->addColumn( new TDataGridColumn('pagina', 'Página', 'left') );
            $this->detail_list->addColumn( $t=new TDataGridColumn('views', 'Cliques', 'right') );
            
            $t->setTransformer( function($value) {
                return number_format($value,0,',','.');
            });
            
            $this->detail_list->createModel();
            
            $items = Click::getTotaisPagina($param['key']);
            $this->detail_list->addItems($items);

            $panel = new TPanelGroup('Cliques por Página', '#f5f5f5');
            $panel->add($this->detail_list);
            
            $this->form->addContent([$panel]);

            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Print view
     */
    public function onPrint($param)
    {
        try
        {
            $this->onEdit($param);
            
            // string with HTML contents
            $html = clone $this->form;
            $contents = file_get_contents('app/resources/styles-print.html') . $html->getContents();
            
            // converts the HTML template into PDF
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($contents);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $file = 'app/output/Midia-export.pdf';
            
            // write and open file
            file_put_contents($file, $dompdf->output());
            
            $window = TWindow::create('Export', 0.8, 0.8);
            $object = new TElement('object');
            $object->data  = $file.'?rndval='.uniqid();
            $object->type  = 'application/pdf';
            $object->style = "width: 100%; height:calc(100% - 10px)";
            $window->add($object);
            $window->show();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * on close
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}

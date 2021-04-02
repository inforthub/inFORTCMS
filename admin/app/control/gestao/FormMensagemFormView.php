<?php
/**
 * FormMensagemFormView Form
 *
 * @version     1.0
 * @package     control
 * @subpackage  gestao
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class FormMensagemFormView extends TPage
{
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');

        $this->form = new BootstrapFormBuilder('form_FormMensagem_View');
        $this->form->setFormTitle('Mensagem');
        $this->form->setColumnClasses(2, ['col-sm-3', 'col-sm-9']);
        /*
        $this->form->addHeaderActionLink( _t('Print'), new TAction([$this, 'onPrint'], ['key'=>$param['key'], 'static' => '1']), 'far:file-pdf red');
        $this->form->addHeaderActionLink( _t('Edit'), new TAction(['FormMensagemForm', 'onEdit'], ['key'=>$param['key'], 'register_state'=>'true']), 'far:edit blue');
        */
        $btn = $this->form->addHeaderActionLink( '',  new TAction([__CLASS__, 'onClose'], ['static'=>'1']), 'fa:times');
        $btn->{'class'} = 'btn btn-sm btn-danger';
        $this->form->addAction(_t('Close'),  new TAction([__CLASS__, 'onClose'], ['static'=>'1']), 'fa:times red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
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
        
            $object = new FormMensagem($param['key']);
            
            $label_id = new TLabel('#:', '#333333', '', 'B');
            $label_formulario_id = new TLabel('Formulário:', '#333333', '', 'B');
            $label_email_origem = new TLabel('Email Origem:', '#333333', '', 'B');
            $label_email_destino = new TLabel('Email Destino:', '#333333', '', 'B');
            $label_dt_mensagem = new TLabel('Data da Mensagem:', '#333333', '', 'B');
            $label_enviada = new TLabel('Enviada:', '#333333', '', 'B');
            $label_assunto = new TLabel('Assunto:', '#333333', '', 'B');
            $label_mensagem = new TLabel('Mensagem:', '#333333', '', 'B');

            $text_id  = new TTextDisplay($object->id, '#333333', '', '');
            $text_formulario_id  = new TTextDisplay($object->formulario->nome, '#333333', '', '');
            $text_email_origem  = new TTextDisplay($object->email_origem, '#333333', '', '');
            $text_email_destino  = new TTextDisplay($object->email_destino, '#333333', '', '');
            $text_dt_mensagem  = new TTextDisplay( TDateTime::convertToMask($object->dt_mensagem ,'yyyy-mm-dd hh:ii','dd/mm/yyyy hh:ii'), '#333333', '', '');
            $text_enviada  = new TTextDisplay( TTransformers::formataSimNao($object->enviada,null,null), '#333333', '', '');
            $text_assunto  = new TTextDisplay($object->assunto, '#333333', '', '');
            $text_mensagem  = new TTextDisplay($object->mensagem, '#333333', '', '');

            $this->form->addFields([$label_id],[$text_id]);
            $this->form->addFields([$label_formulario_id],[$text_formulario_id]);
            $this->form->addFields([$label_email_origem],[$text_email_origem]);
            $this->form->addFields([$label_email_destino],[$text_email_destino]);
            $this->form->addFields([$label_dt_mensagem],[$text_dt_mensagem]);
            $this->form->addFields([$label_enviada],[$text_enviada]);
            $this->form->addFields([$label_assunto],[$text_assunto]);
            $this->form->addFields([$label_mensagem],[$text_mensagem]);

            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Print view
     *
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
            
            $file = 'app/output/FormMensagem-export.pdf';
            
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

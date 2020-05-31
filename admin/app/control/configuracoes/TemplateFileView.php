<?php
/**
 * TemplateFileView Form
 *
 * @version    1.0
 * @package    control
 * @subpackage configuracoes
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 */
class TemplateFileView extends TPage
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

        $this->form = new BootstrapFormBuilder('form_TemplateFile_View');
        $this->form->setFormTitle('Detalhes do Arquivo');
        $this->form->setColumnClasses(2, ['col-sm-3', 'col-sm-9']);
        /*
        $dropdown = new TDropDown('Opções','fa:th');
        $dropdown->addAction(_t('Print'), new TAction([$this, 'onPrint'], $param), 'far:file-pdf red');
        $dropdown->addAction(_t('Close'),  new TAction([__CLASS__, 'onClose'], ['static'=>'1']), 'fa:times red');
        
        $this->form->addHeaderWidget($dropdown);
        */
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
    public function onView( $param )
    {
        try
        {
            $image = new TImage($param['path'].'/'.$param['name']);
            $image->setProperty('class','img-fluid img-thumbnail');
            $image->setProperty('style','background:#e8e8e8;');
            $painel = TElement::tag('div',$image,['class'=>'text-center']);
            
            $label_nome   = new TLabel('Nome:', '#333333', '', 'B');
            $label_url    = new TLabel('Local:', '#333333', '', 'B');
            
            $text_nome    = new TTextDisplay($param['name'], '#333333', '', '');
            $text_url     = new TTextDisplay($param['path'], '#333333', '', '');

            // Adicionando os campos no formulário
            $this->form->addFields([$painel]);
            $this->form->addFields([$label_nome],[$text_nome]);
            $this->form->addFields([$label_url],[$text_url]);
            $this->form->addContent( [''] );
            $this->form->addContent( [new TFormSeparator('Propriedades')] );
            $this->form->addFields([]); //->layout = ['col-sm-4 control-label', 'col-sm-2', 'col-sm-4 control-label', 'col-sm-2' ];

            
            // Pegando os parâmetros do arquivo
            $parametros = stat($param['path'].'/'.$param['name']);
            $parametros['image'] = getimagesize($param['path'].'/'.$param['name']);
            $parametros['last_modified'] = date ("d/m/Y H:i:s", filemtime($param['path'].'/'.$param['name']));
            
            // Criando um datagrid
            $this->detail_list = new BootstrapDatagridWrapper( new TDataGrid );
            $this->detail_list->style = 'width:100%';
            $this->detail_list->disableDefaultClick();
            
            $this->detail_list->addColumn( new TDataGridColumn('pro', '', 'left') );
            $this->detail_list->addColumn( new TDataGridColumn('val', '', 'right') );
            
            $this->detail_list->createModel();
            
            // Adicionando as propriedades no datagrid
            
            // tamanho
            $item = new StdClass;
            $item->pro = 'Tamanho';
            $item->val = THelper::fileSize($parametros['size']);
            $this->detail_list->addItem($item);
            
            // largura
            $item = new StdClass;
            $item->pro = 'Largura';
            $item->val = $parametros['image'][0] . ' px';
            $this->detail_list->addItem($item);
            
            // altura
            $item = new StdClass;
            $item->pro = 'Altura';
            $item->val = $parametros['image'][1] . ' px';
            $this->detail_list->addItem($item);
            
            // bits
            $item = new StdClass;
            $item->pro = 'Bits';
            $item->val = $parametros['image']['bits'];
            $this->detail_list->addItem($item);
            
            // mime
            $item = new StdClass;
            $item->pro = 'Mime type';
            $item->val = $parametros['image']['mime'];
            $this->detail_list->addItem($item);
            
            // última modificação
            $item = new StdClass;
            $item->pro = 'Última modificação';
            $item->val = $parametros['last_modified'];
            $this->detail_list->addItem($item);
            

            $panel = new TPanelGroup('', '#f5f5f5');
            $panel->add($this->detail_list);
            
            $this->form->addContent([$this->detail_list]);
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
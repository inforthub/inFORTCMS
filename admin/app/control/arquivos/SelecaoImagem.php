<?php
/**
 * SelecaoImagem
 *
 * @version     1.0
 * @package     control
 * @subpackage  arquivos
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 (https://www.infort.eti.br)
 */
class SelecaoImagem extends TWindow
{
    private $iconview;
    private $loaded;
    
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
        parent::setTitle('Seleção de Imagem');
        parent::setSize(0.7, null);
        parent::setMinWidth(0.9,1000);
        //parent::removePadding();
        //parent::removeTitleBar();

        // criando campo de ícones
        $this->iconview = new TIconView;
        
        $opendir = '..' . CMS_IMAGE_PATH;
        if (!empty(TSession::getValue(__CLASS__.'_opendir')))
            $opendir = TSession::getValue(__CLASS__.'_opendir');
        else
            TSession::setValue(__CLASS__.'_opendir',$opendir);
        
        $dir = new DirectoryIterator( $opendir );
        $arr = [];
        
        foreach ($dir as $fileinfo)
        {
            if (!$fileinfo->isDot())
            {
                $item = new stdClass;
                if ($fileinfo->isDir())
                {
                    $item->type = 'folder';
                    $item->icon = 'fas:folder blue fa-4x';
                    $item->path = $fileinfo->getPath();
                    $item->name = $fileinfo->getFilename();
                    
                    $arr['d'.$fileinfo->getFilename()] = $item;
                }
                else
                {
                    $nome = $fileinfo->getFilename();
                    if ( !in_array($nome,['.htaccess']) )
                    {
                        $item->type = 'file';
                        $item->icon = $fileinfo->getPath().'/'.$fileinfo->getFilename(); //'far:file orange fa-4x';
                        $item->path = $fileinfo->getPath();
                        $item->name = $fileinfo->getFilename();
                        
                        $arr['f'.$fileinfo->getFilename()] = $item;
                    }
                }
            }
        }
        
        $arr_dir = explode('/',$opendir);
        
        if ( count($arr_dir) > 2 )
        {
            array_pop($arr_dir);
            
            $item = new stdClass;
            $item->type = 'folder';
            $item->icon = 'far:arrow-alt-circle-up dark fa-4x';
            $item->path = implode('/',$arr_dir);
            $item->name = 'Voltar';
            
            $arr['d'.$fileinfo->getFilename()] = $item;
        }
        
        ksort($arr);
        
        // iterando os objetos
        foreach ($arr as $item)
        {
            $this->iconview->addItem($item);
        }
        
        //$this->iconview->enablePopover('', '<b>Name:</b> {name}');
        
        $this->iconview->setIconAttribute('icon');
        $this->iconview->setLabelAttribute('name');
        $this->iconview->setInfoAttributes(['name', 'path']);
        
        $display_condition = function($object) {
            return ($object->type == 'file');
        };
        
        $this->iconview->addContextMenuOption('Ações');
        $this->iconview->addContextMenuOption('');
        $this->iconview->addContextMenuOption('Selecionar',   new TAction([$this, 'onOpen']),   'far:folder-open blue');
        //$this->iconview->addContextMenuOption(_t('Rename'), new TAction([$this, 'onRename']), 'far:edit green');
        //$this->iconview->addContextMenuOption(_t('Delete'), new TAction([$this, 'onDelete']), 'far:trash-alt red'); //, $display_condition);
        
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add(TPanelGroup::pack(new TImage('fa:folder') . ' : ' . $opendir, $this->iconview));
        
        parent::add($container);
    }
    
    
    /**
     * Open action
     */
    public static function onOpen($param)
    {
        if (is_dir( $param['path'] . DIRECTORY_SEPARATOR . $param['name']) || $param['name'] == 'Voltar') 
        {
            $dir = ($param['name'] == 'Voltar') ? $param['path'] : $param['path'] . DIRECTORY_SEPARATOR . $param['name'];
            TSession::setValue(__CLASS__.'_opendir',$dir);
            TApplication::loadPage(__CLASS__);
        }
        else 
        {
            unset($param['static']);
            $param['register_state'] = 'false';
            
            $object = new StdClass;
            $object->nome_web = $param['path'] . DIRECTORY_SEPARATOR . $param['name'];
            
            TForm::sendData(TSession::getValue('Classe_Retorno_Busca_Imagem'), $object);
            // refresh photo_frame
            TScript::create("$('#photo_frame').html('')");
            TScript::create("$('#photo_frame').append(\"<img style='width:100%' src='{$object->nome_web}'>\");");
            
            parent::closeWindow(); // closes the window
            //TApplication::loadPage(TSession::getValue('Classe_Retorno_Busca_Imagem'),'onView',$param);
            //AdiantiCoreApplication::loadPage('ArquivosFormView','onView',$param);
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
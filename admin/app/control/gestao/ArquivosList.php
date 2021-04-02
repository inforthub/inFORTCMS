<?php
/**
 * ArquivosList
 *
 * @version     1.0
 * @package     control
 * @subpackage  gestao
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class ArquivosList extends TPage
{
    private $iconview;
    private $form;
    private $loaded;
    //private $tamanho;
    
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
        
        // criando formulário de filtragem
        $this->form = new BootstrapFormBuilder('form_search_ArquivosList');
        $this->form->setFormTitle('Gestão de Arquivos');
        $this->form->setFieldSizes('100%');
        
        // criando os campos do formulário
        $arquivo = new TEntry('arquivo');

        // adicionando os campos
        $this->form->addFields( [ new TLabel('Arquivo') ], [ $arquivo ] );
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // adicionando botões de ação
        $this->addActionButton(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search','btn-primary');
        $this->addActionButton(_t('Clear'), new TAction(array($this, 'onClear')), 'fa:eraser red');
        $this->addActionButton('Criar Pasta', new TAction(array($this, 'onCreateFolder')), 'fas:folder-plus white', 'btn-warning white');
        $this->addActionButton(_t('Upload'), new TAction(array($this, 'onUpload')), 'fas:cloud-upload-alt', 'btn-success');
        $this->addActionButton(_t('Upload Multiple Files'), new TAction(array($this, 'onUploadMulti')), 'fas:upload', 'btn-info');
        
        // criando campo de ícones
        $this->iconview = new TIconView;
        
        $opendir = '..' . CMS_IMAGE_PATH;
        if (!empty(TSession::getValue(__CLASS__.'_opendir')))
            $opendir = TSession::getValue(__CLASS__.'_opendir');
        else
            TSession::setValue(__CLASS__.'_opendir',$opendir);
            
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack(new TImage('fa:folder') . ' : ' . $opendir, $this->iconview));
        
        parent::add($container);
    }
    
    
    /**
     * Cria formulário para upload de multiplos arquivos
     */
    public function onUploadMulti($param)
    {
        $form = new BootstrapFormBuilder('input_file_form');
        $form->setFieldSizes('100%');
        
        // campos
        $file = new TMultiFile('file');
        $convert = new TRadioGroup('convert');
        
        // parametros
        $file->enableFileHandling();
        $file->setAllowedExtensions(['jpg','jpeg','png','gif','webp','svg']);
        $convert->setSize(60);
        $convert->addItems(['t'=>'Sim','f'=>'Não']);
        $convert->setLayout('horizontal');
        $convert->setUseButton();
        $convert->setValue('f');
        
        $form->addFields( [ $file] );
        $form->addFields( [ new TLabel('<b>Converter todas as imagens para o formato ".webp"?</b>') ],[ $convert ] )->layout = ['col-sm-12 col-lg-8', 'col-sm-12 col-lg-4'];
        
        $btn = $form->addAction(_t('Save'), new TAction([__CLASS__, 'UploadMulti']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary waves-effect';
        $form->addAction(_t('Cancel'), new TAction([__CLASS__, 'onReload']), 'fa:times red');
        
        // show the input dialog
        new TInputDialog('Upload de Arquivo', $form);
    }
    
    /**
     * Faz o upload do(s) arquivo(s)
     */
    public static function UploadMulti( $param )
    {
        try
        {
            $target_path = TSession::getValue(__CLASS__.'_opendir') . DIRECTORY_SEPARATOR;
            
            foreach ($param['file'] as $arq)
            {
                $dados_file = json_decode(urldecode($arq));
                
                if (isset($dados_file->fileName))
                {
                    $source_file   = $dados_file->fileName;
                    $target_file   = $target_path . pathinfo($dados_file->fileName, PATHINFO_BASENAME); //current(array_reverse(explode('/', $dados_file->fileName)));
                    $extension     = pathinfo($dados_file->fileName, PATHINFO_EXTENSION);
                    
                    if (file_exists($source_file))
                    {
                        rename($source_file,$target_file);
                        
                        $file = pathinfo($target_file);
                        if ( isset($param['convert']) && $param['convert'] == 't' && in_array($file['extension'], ['jpg','jpeg','png','gif']) )
                        {
                            if( file_exists ($target_file) )// se existir apaga o anterior
                            {
                                $webp_file = $target_path.THelper::urlAmigavel($file['filename']).'.webp';
                                
                                // pegamos o arquivo salvo e convertemos para webp
                                THelper::toWebP($target_file,$webp_file);
                                
                                // apagando o arquivo original
                                unlink( $target_file ); //apaga
                            }
                        }
                    }
                }
            }
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'), new TAction([__CLASS__,'onReload']));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
        }
    }
    
    
    
    
    /**
     * Cria formulário para upload de arquivo
     */
    public function onUpload($param)
    {
        $form = new BootstrapFormBuilder('input_file_form');
        $form->setFieldSizes('100%');
        //$form->setColumnClasses(2, ['col-sm-6 col-lg-6', 'col-sm-6 col-lg-6']);
        
        // criando campos
        $file    = new TSlim('file');
        $formato = new TCombo('size');
        //$width   = new TNumeric('width',0,'','',false);
        //$height  = new TNumeric('height',0,'','',false);
        $convert = new TRadioGroup('convert');
        
        // definindo parâmetros dos campos
        $file->setId('file_slim');
        //$width->setMaxLength(4);
        //$width->setValue(725);
        //$height->setMaxLength(4);
        //$height->setValue(725);
        $convert->setSize(60);
        $convert->addItems(['t'=>'Sim','f'=>'Não']);
        $convert->setLayout('horizontal');
        $convert->setUseButton();
        $convert->setValue('f');
        /*
        $arr = ['720,480,4:3'=>'4:3|VGA - 720x480px',
                '800,600,4:3'=>'4:3|SVGA - 800x600',
                '1024,768,4:3'=>'4:3|XGA - 1024x768',
                '1280,720,16:9'=>'16:9|HD - 1280x720px',
                '1440,900,16:10'=>'16:10|WXGA+ - 1440x900px',
                '1600,900,16:9'=>'16:9|UXGA - 1920x1080px',
                '1920,1080,16:9'=>'16:9|Full HD - 1920x1080px'];
        */
        $arr = ['1:1'=>'1:1 - Avatar',
                '2:3'=>'2:3 - Retrato',
                '3:2'=>'3:2 - Paisagem',
                '3:4'=>'3:4 - Retrato',
                '4:3'=>'4:3 - Paisagem',
                '9:16'=>'9:16 - Retrato',
                '16:9'=>'16:9 - Paisagem',
                '16:10'=>'16:10 - Paisagem'];
        
        $formato->addItems($arr);
        $formato->setValue('4:3');
        $ratio = '4:3';
        $size  = THelper::getPreferences('pref_site_imglargura').','.THelper::getPreferences('pref_site_imgaltura'); // '725,725';
        
        // colocando os campos no formulário
        $form->addFields( [ new TLabel('Formato') ], [ $formato ] );
        $form->addFields( [ $file ] );
        $form->addFields( [ new TLabel('<b>Tamanho máximo (largura, altura)</b>')], [$size.' px'] )->layout = ['col-sm-12 col-lg-8', 'col-sm-12 col-lg-4'];
        $form->addFields( [ new TLabel('<b>Converter para o formato ".webp"?</b>') ],[ $convert ] )->layout = ['col-sm-12 col-lg-8', 'col-sm-12 col-lg-4'];
        

        
        $file->setDataProperties(['label'=>'Upload de imagem']);//aqui eu seto o nome do label
        //tamanho final no máximo 1500x1500 e proporção de 4:3 na janela de visualização
        $file->setDataProperties(['size'=>$size,'ratio'=>$ratio,'download'=>'true']); //'size'=>'1200,1200',
        //$file->setWatermark(THelper::getPreferences('pref_site_nome'));
        //$file->setImageWatermark('app/images/logo-infort.svg');
        $file->setInitRatioSelect($formato->getId());
        
        $btn = $form->addAction(_t('Save'), new TAction([__CLASS__, 'Upload']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary waves-effect';
        $form->addAction(_t('Cancel'), new TAction([__CLASS__, 'onReload']), 'fa:times red');
        
        // show the input dialog
        new TInputDialog('Upload de Arquivo de Imagem', $form);
    }

    
    /**
     * Faz o upload do arquivo
     */
    public static function Upload( $param )
    {
        try
        {
            $images = Slim::getImages();
            
            // No image found under the supplied input name
            if ($images)
            {            
                $image = $images[0];
                // save output data if set
                if (isset($image['output']['data']))
                {
                    $arquivo = pathinfo($image['output']['name']);
                    
                    // geramos um hash com o nome do arquivo concatenado com o tempo
                    //$name = time().'-'.md5($arquivo['filename']).'.'.$arquivo['extension'];
                    $name = THelper::urlAmigavel($arquivo['filename']).'.'.$arquivo['extension'];
                    
                    // We'll use the output crop data
                    $output_data = $image['output']['data'];
                    
                    // definindo o path com a categoria pai'
                    $target_path = TSession::getValue(__CLASS__.'_opendir') . DIRECTORY_SEPARATOR;
                    
                    // salva o arquivo
                    $output = Slim::saveFile($output_data, $name, $target_path, false);
                    
                    if ( $output && (isset($param['convert']) && $param['convert'] == 't') )
                    {
                        if( file_exists ($output['path']) )// se existir apaga o anterior
                        {
                            // pegamos o arquivo salvo e convertemos para webp
                            $webp_file = str_replace('.'.$arquivo['extension'],'.webp',$output['path']);
                            THelper::toWebP($output['path'],$webp_file);
                            
                            // apagando o arquivo original
                            unlink( $output['path'] ); //apaga
                        }
                    }
                    
                }
            }

            //new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'), new TAction([__CLASS__,'onReload']));
            TToast::show('success', _t('File saved'), 'bottom right', 'far:check-circle' );
            TApplication::loadPage(__CLASS__);
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
        }
    }
    
    /**
     * Cria formulário para criação da pasta
     */
    public static function onCreateFolder($param)
    {
        $form = new BootstrapFormBuilder('input_folder_form');
        $form->setFieldSizes('100%');
        
        $nome = new TEntry('nome');
        
        $form->addFields( [ new TLabel('Nome da Pasta') , $nome] );
        
        $btn = $form->addAction(_t('Create'), new TAction([__CLASS__, 'newFolder']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary waves-effect';
        $form->addAction(_t('Cancel'), new TAction([__CLASS__, 'onReload']), 'fa:times red');
        
        // show the input dialog
        new TInputDialog('Nova Pasta', $form);
    }
    
    /**
     * Salva a nova pasta no diretório atual
     */
    public static function newFolder( $param )
    {
        try
        {
            $nome = THelper::urlAmigavel( $param['nome'] );

            // validando os campos
            if ( empty($nome) )
            {
                throw new Exception('Ouve um erro ao tentar criar a pasta!');
            }
            
            mkdir(TSession::getValue(__CLASS__.'_opendir') . DIRECTORY_SEPARATOR . $nome, 0755);

            //new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'), new TAction([__CLASS__,'onReload']));
            TToast::show('success', 'Pasta criada com sucesso!', 'bottom right', 'far:check-circle' );
            TApplication::loadPage(__CLASS__);
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
        }
    }
    
    
    /**
     * Cria formulário para converter um arquivo em "webp"
     */
    public static function onConverter($param)
    {
        $file = pathinfo($param['name']);
        
        if (in_array($file['extension'], ['jpg','jpeg','png','gif']))
        {
            $form = new BootstrapFormBuilder('converter_form');
            $form->setFieldSizes('100%');
            
            $atual = new TEntry('atual');
            $novo  = new TEntry('novo');
            //$type  = new THidden('type');
            
            $form->addFields( [ new TLabel('Arquivo atual') , $atual ] );
            //$form->addFields( [ $type ] );
            
            $atual->setEditable(false);
            $atual->setValue($param['name']);
            //$type->setValue($param['type']);
            
            $btn = $form->addAction(_t('Save'), new TAction([__CLASS__, 'Converte']), 'fa:save');
            $btn->class = 'btn btn-sm btn-primary waves-effect';
            $form->addAction(_t('Cancel'), new TAction([__CLASS__, 'onReload']), 'fa:times red');
            
            // show the input dialog
            new TInputDialog('Converter Arquivo para ".webp"', $form);
        }
        else
        {
            TToast::show('warning', 'Este arquivo não pode ser convertido para "webp"', 'bottom right', 'fas:exclamation-triangle' );
        }
    }
    
    /**
     * Converte o arquivo
     */
    public static function Converte($param)
    {
        try
        {
            // validando os campos
            if ( empty($param['atual']) )
            {
                throw new Exception('Ouve um erro ao tentar converter o arquivo!');
            }
            
            $atual = pathinfo($param['atual']);
            $dir   = TSession::getValue(__CLASS__.'_opendir') . DIRECTORY_SEPARATOR;
            
            if( file_exists ($dir.$param['atual']) )// se existir apaga o anterior
            {
                $webp_file = $dir.THelper::urlAmigavel($atual['filename']).'.webp';
                
                // pegamos o arquivo salvo e convertemos para webp
                THelper::toWebP($dir.$param['atual'],$webp_file);
                
                // apagando o arquivo original
                unlink( $dir.$param['atual'] ); //apaga
            
                //new TMessage('info', _t('File saved'), new TAction([__CLASS__,'onReload']));
                TToast::show('success', _t('File saved'), 'bottom right', 'far:check-circle' );
                TApplication::loadPage(__CLASS__);
            }
            
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
        }
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
            TApplication::loadPage('ArquivosFormView','onView',$param);
            //AdiantiCoreApplication::loadPage('ArquivosFormView','onView',$param);
        }
    }
    
    
    /**
     * Cria formulário para renomear um arquivo / pasta
     */
    public static function onRename($param)
    {
        $form = new BootstrapFormBuilder('rename_form');
        $form->setFieldSizes('100%');
        
        $atual = new TEntry('atual');
        $novo  = new TEntry('novo');
        //$type  = new THidden('type');
        
        $form->addFields( [ new TLabel('Nome atual') , $atual ] );
        $form->addFields( [ new TLabel('Novo nome') , $novo ] );
        //$form->addFields( [ $type ] );
        
        $atual->setEditable(false);
        $atual->setValue($param['name']);
        //$type->setValue($param['type']);
        
        $btn = $form->addAction(_t('Save'), new TAction([__CLASS__, 'Rename']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary waves-effect';
        $form->addAction(_t('Cancel'), new TAction([__CLASS__, 'onReload']), 'fa:times red');
        
        // show the input dialog
        new TInputDialog('Renomear Arquivo/Pasta', $form);
    }
    
    /**
     * Renomeia o arquivo / pasta
     */
    public static function Rename( $param )
    {
        try
        {
            // validando os campos
            if ( empty($param['novo']) || empty($param['atual']) )
            {
                throw new Exception('Ouve um erro ao tentar criar a pasta!');
            }
            
            $arquivo = pathinfo($param['novo']);
            $atual   = pathinfo($param['atual']);

            // preparando o novo nome e garantindo a mesma extenção
            $novo = THelper::urlAmigavel( $arquivo['filename'] ) . (isset($atual['extension']) ? ('.' . $atual['extension']) : '');

            $dir = TSession::getValue(__CLASS__.'_opendir') . DIRECTORY_SEPARATOR;
            
            rename($dir.$param['atual'], $dir.$novo);
        
            //new TMessage('info', _t('File saved'), new TAction([__CLASS__,'onReload']));
            TToast::show('success', _t('File saved'), 'bottom right', 'far:check-circle' );
            TApplication::loadPage(__CLASS__);
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
        }
    }
    
    
    /**
     * Delete action
     */
    public static function onDelete($param)
    {
        // define the delete action
        $action = new TAction(array(__CLASS__, 'Delete'));
        $action->setParameters($param); // pass the key parameter ahead
        
        if (is_file($param['path'].'/'.$param['name']))
        {
            // shows a dialog to the user
            new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
        }
        else
        {
            new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?').'<br>Esta ação apagará todos os arquivos contidos nessa pasta.', $action);
        }
    }
    
    /**
     * Deleta o arquivo / pasta
     */
    public static function Delete($param)
    {
        try
        {
            $caminho = $param['path'].'/'.$param['name'];
            
            if (is_file($caminho))
            {
                unlink($caminho);
                
                //new TMessage('info', _t('File deleted'), new TAction([__CLASS__,'onReload'])); // success message
                TToast::show('success', _t('File deleted'), 'bottom right', 'far:check-circle' );
            }
            else
            {
                THelper::apagarTudo($caminho); // apagando todo o conteúdo da pasta
                rmdir($caminho); // apagando a pasta
                
                //new TMessage('info', _t('Folder deleted'), new TAction([__CLASS__,'onReload'])); // success message
                TToast::show('success', _t('Folder deleted'), 'bottom right', 'far:check-circle' );
            }
            TApplication::loadPage(__CLASS__);
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
        }
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
     * Register the filter in the session
     */
    public function onSearch($param)
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue(__CLASS__.'_filter_arquivo',  NULL);

        if (isset($data->arquivo) AND ($data->arquivo)) {
            TSession::setValue(__CLASS__.'_filter_arquivo',  $data->arquivo); // stores the filter in the session
        }
        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue(__CLASS__ . '_filter_data', $data);
        
        $this->onReload($param);
    }
    
    /**
     * Carregamos a listagem
     */
    public function onReload($param = NULL)
    {
        $filter = [];

        if (TSession::getValue(__CLASS__.'_filter_arquivo')) {
            $filter[] = TSession::getValue(__CLASS__.'_filter_arquivo'); // add the session filter
        }
        
        //***
        
        $opendir = '..' . CMS_IMAGE_PATH;

        if (!empty(TSession::getValue(__CLASS__.'_opendir')))
        {
            $opendir = TSession::getValue(__CLASS__.'_opendir');
        }
        
        $dir = new DirectoryIterator( $opendir );
        $arr = [];
        
        foreach ($dir as $fileinfo)
        {
            
            if (!$fileinfo->isDot())
            {
                $item = new stdClass;
                $n = 'd';
                if ($fileinfo->isDir())
                {
                    $item->type = 'folder';
                    $item->icon = 'fas:folder blue fa-4x';
                    $item->path = $fileinfo->getPath();
                    $item->name = $fileinfo->getFilename();
                    
                    $arr[$n.$fileinfo->getFilename()] = $item;
                }
                else
                {
                    $nome = $fileinfo->getFilename();
                    if ( str_replace($filter, '', $nome) != $nome || empty($filter) && !in_array($nome,['.htaccess']) )
                    {
                        $item->type = 'file';
                        $item->icon = $fileinfo->getPath().'/'.$fileinfo->getFilename(); //'far:file orange fa-4x';
                        $n = 'f';
                        $item->path = $fileinfo->getPath();
                        $item->name = $fileinfo->getFilename();
                        
                        $arr[$n.$fileinfo->getFilename()] = $item;
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
        $this->iconview->addContextMenuOption(_t('Open'),   new TAction([$this, 'onOpen']),   'far:folder-open blue');
        $this->iconview->addContextMenuOption(_t('Convert'), new TAction([$this, 'onConverter']), 'fas:recycle gray');
        $this->iconview->addContextMenuOption(_t('Rename'), new TAction([$this, 'onRename']), 'far:edit green');
        $this->iconview->addContextMenuOption(_t('Delete'), new TAction([$this, 'onDelete']), 'far:trash-alt red'); //, $display_condition);
        //***
        
        $this->loaded = true;
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
    
    /**
     * Método para limpar os campos da pesquisa
     */
    public function onClear()
    {
        // limpando dados da sessão
        THelper::clearSession();

        $this->form->clear();
        TApplication::loadPage(__CLASS__);
    }
    
}

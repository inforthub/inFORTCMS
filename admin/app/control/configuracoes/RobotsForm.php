<?php
/**
 * RobotsForm
 *
 * @version     1.0
 * @package     control
 * @subpackage  configuracoes
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 */
class RobotsForm extends TPage
{
    protected $form; // form
    
    use App\Base\AppFileSaveTrait;
    
    /**
     * Class constructor
     * Creates the page
     */
    public function __construct()
    {
        parent::__construct();
        
        // criando um formulário
        $this->form = new BootstrapFormBuilder('form_Link');
        $this->form->setFormTitle('Arquivo Robots.TXT');
        $this->form->setFieldSizes('100%');
        $this->form->setColumnClasses(2, ['col-sm-4', 'col-sm-8']);
        
        // criando os botões do formulário
        $this->addActionButton(_t('Save'), new TAction([$this, 'onSave']), 'fa:save','btn-primary');
        $this->addActionButton(_t('Remake').' '._t('File'), new TAction([$this, 'onRemake']), 'fa:redo red');
        $this->form->addHeaderActionLink( _t('Save'),  new TAction([__CLASS__, 'onSave'], ['static'=>'1']), 'fa:save blue');
        //$this->form->addHeaderActionLink( _t('Close'),  new TAction([__CLASS__, 'onClose'], ['static'=>'1']), 'fa:times red');
        
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
     * Recria o arquiovo robots.txt
     */
    public static function onRemake()
    {
        try
        {
            TTransaction::open('sistema');
            
            $string  = 'User-Agent: *'.PHP_EOL;
            $string .= 'Disallow: /admin'.PHP_EOL;
            $string .= 'Disallow: /cache'.PHP_EOL;
            $string .= 'Disallow: /lib'.PHP_EOL;

            $sitemap = Link::getSitemap();
            $root    = THelper::getPreferences('pref_site_dominio');
            
            if (!empty($sitemap))
            {
                $n = 0;
                foreach ($sitemap as $xml)
                {
                    $string .= 'Sitemap: ' . $root.'/sitemap'.$n.PHP_EOL;
                    $n++;
                }
            }

            $path = str_replace('/admin','',PATH);
            
            // criando o arquivo
            $fp = fopen($path.'/robots.txt', 'w+');
            $fw = fwrite($fp, $string);
            
            if( !$fw )
            {
                throw new Exception ('Falha ao gerar o arquivo');
            }
            
            TTransaction::close();
            
            // fechando o arquivo
            fclose($fp);
            
            new TMessage('info','Arquivo gerado com sucesso!', new TAction(['RobotsForm','onEdit']));
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    
    /**
     * Show data
     */
    public function onEdit()
    {
        try
        {
            TTransaction::open('sistema');
            
            // abrindo o arquivo
            $root = THelper::getPreferences('pref_site_dominio');
            $path = str_replace('/admin','',PATH);
            
            if (!file_exists($path.'/robots.txt'))
            {
                $this->onRemake();
            }
            else
            {
                // criando os campos
                $caminho = new TTextDisplay($root.'/robots.txt');
                $robots  = new TTextSourceCode('robots');
                

                // carrega o arquivo
                if ( !$robots->loadFile($path.'/robots.txt') )
                {
                    // criamos o arquivo
                    $robots->loadString('');
                }
                
                // adicionando os campos
                $this->form->addContent( [ new TLabel('Caminho do arquivo') ] , [ $caminho ] );
                $this->form->addFields( [ $robots ] );
                
                // vertical box container
                $container = new TVBox;
                $container->style = 'width: 100%';
                $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
                $container->add($this->form);
                
                parent::add($container);
            }
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Save form data
     * @param $param Request
     */
    public function onSave($param)
    {
        try
        {
            // validando os campos
            if (empty($param['robots']))
            {
                throw new Exception (_t(translate('The field ^1 is required', $label)));
            }

            $path = str_replace('/admin','',PATH);
            
            // abrindo o arquivo
            $fp = fopen($path.'/robots.txt' , "w");
            $fw = fwrite($fp, $param['robots']);
            
            if( !$fw )
            {
                throw new Exception (_t('Failed to save file'));
            }
            
            new TMessage('info','Arquivo salvo com sucesso!', new TAction(['RobotsForm','onEdit']));
            
            // fechando o arquivo
            fclose($fp);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}

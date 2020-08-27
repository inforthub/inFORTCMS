<?php
/**
 * TemplateFileForm Registration
 *
 * @version     1.0
 * @package     control
 * @subpackage  gestao
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class TemplateFileForm extends TWindow
{
    private $form;
    
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        parent::setSize(0.9,null);
        parent::setPosition(null,10);
        parent::removePadding();
        parent::removeTitleBar();
        //parent::disableEscape();
        
        // criando o formulário
        $this->form = new BootstrapFormBuilder('form_EditFile');
        $this->form->setFormTitle('Editor de Arquivo');
        
        // Mudando a cor do cabeçalho
        $this->form->setHeaderProperty('class','header bg-blue-grey');

        // criando ações
        $btn = $this->form->addHeaderAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addHeaderActionLink( _t('Close'),  new TAction([__CLASS__, 'onClose'], ['static'=>'1']), 'fa:times red');
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('Close'),  new TAction([$this, 'onClose'], ['static'=>'1']), 'fa:times red');
    }
    
    /**
     * Carrega o arquivo e exibe na tela
     */
    public function onEdit($param)
    {
        $this->form->setFormTitle('Editando arquivo: '.$param['name']);
        
        // criando os campos
        $file = new TTextSourceCode('file');
        $arquivo = new THidden('arquivo');
        
        // carrega os campos
        $file->loadFile($param['path'].'/'.$param['name']);
        $arquivo->setValue($param['path'].'/'.$param['name']);
        
        // adicionando os campos ao formulário
        $this->form->addFields( [$file] );
        $this->form->addFields( [$arquivo] );

        parent::add($this->form);
    }
    
    /**
     * Salva o arquivo
     */
    public function onSave($param)
    {
        try
        {
            // abrindo o arquivo
            $fp = fopen($param['arquivo'] , "w");
            $fw = fwrite($fp, $param['file']);
            
            // verificar se o arquivo foi salvo.
            if( !$fw )
            {
                throw new Exception ('Falha ao salvar o arquivo');
            }
            
            new TMessage('info','Arquivo salvo com sucesso!');
            
            // fechando o arquivo
            fclose($fp);
            
            parent::closeWindow();
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
        }
    }

    /**
     * Closes window
     */
    public static function onClose()
    {
        parent::closeWindow();
    }
    
    
    /**
     * Load a file
     * @param $file Path to the file
     *
    public function loadFile($file)
    {
        if (!file_exists($file))
        {
            return '';
        }
        
        $ret = file_get_contents($file);
        if (utf8_encode(utf8_decode($ret)) !== $ret ) // NOT UTF
        {
            $ret = utf8_encode($this->value);
        }
        return $ret;
    }
    */
    
}

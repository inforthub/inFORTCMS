<?php

/**
 * TTextSourceCode View
 *
 * @version     1.0
 * @package     widget
 * @subpackage  lib
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class TTextSourceCode extends TText
{
    private $mode;
    
    /**
     * Class Constructor
     * @param $name Widet's name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        
        // defines the text default height
        $this->height= 'auto';
        $this->width = '100%';
        
        $this->mode = 'text/html';
    }
    
    /**
     * Seta o parametro "mode" do CodeMirror
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }
    
    /**
     * 
     */
    private function detectMode($file)
    {
        $mode = '';
        
        // pegamos os ultimos caracteres da string
        $arr = explode('.',$file);
        $code = end($arr);
        
        switch ($code)
        {
            case 'html':
                $mode = 'text/html';
                break;
            case 'js':
                $mode = 'text/javascript';
                break;
            case 'css':
            case 'scss':
                $mode = 'text/css';
                break;
            case 'php':
                $mode = 'text/x-php';
                break;
        }
        
        $this->setMode($mode);
    }
    
    /**
     * Load a file
     * @param $file Path to the file
     */
    public function loadFile($file)
    {
        if (!file_exists($file))
        {
            return FALSE;
        }
        
        $this->detectMode($file);
        
        $this->value = file_get_contents($file);
        if (utf8_encode(utf8_decode($this->value)) !== $this->value ) // NOT UTF
        {
            $this->value = utf8_encode($this->value);
        }
        return TRUE;
    }
    
    /**
     * Load from string
     */
    public function loadString($content)
    {
        $this->value = $content;
        
        if (utf8_encode(utf8_decode($content)) !== $content ) // NOT UTF
        {
            $this->value = utf8_encode($content);
        }
    }
    
    /**
     * Define the field's value
     * @param $value A string containing the field's value
     */
    public function setValue($value)
    {
        $this->loadString($value);
    }
    
    /**
     * Show the highlighted source code
     */
    public function show()
    {
        parent::show();
        
        TScript::create("
            $(document).ready(function(){
                var editor = CodeMirror.fromTextArea(document.getElementById('".$this->id."'), {
                    mode: '".$this->mode."',
                    extraKeys: {'Ctrl-Space': 'autocomplete'},
                    lineNumbers: true,
                    lineWrapping: true,
                    tabMode: 'indent',
                    styleActiveLine: true,
                    matchBrackets: true,
                    theme: 'monokai'
                }).on('change', editor => {
                    $('#".$this->id."').val(editor.getValue());
                });
            });
        ");
    }
}

<?php
/**
 * Modulo Active Record
 *
 * @version    1.0
 * @package    model
 * @subpackage gestao
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 */
class Modulo extends TRecord
{
    const TABLENAME = 'modulo';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('html');
        parent::addAttribute('parametros');
        parent::addAttribute('posicao');
        parent::addAttribute('ordem');
        parent::addAttribute('ativo');
        parent::addAttribute('modelo_html_id');
    }
    
    /**
     * Method get_modelo_html
     * Sample of usage: $artigo->modelo_html->attribute;
     * @returns ModeloHTML instance
     */
    public function get_modelo_html()
    {
        return ModeloHTML::find($this->modelo_html_id);
    }
    
    /**
     * Retorna todo o HTML de uma posição
     */
    public static function getHTMLbyPosition($pos)
    {
        $modulos = self::where('posicao','=',$pos)->where('ativo','=','t')->orderBy('ordem')->load();
        $html = null;
        
        if ($modulos)
        {
            $html = '';
            foreach( $modulos as $mod )
            {
                $html .= $mod->html;
            }
        }
        
        return $html;
    } 
    
    
}

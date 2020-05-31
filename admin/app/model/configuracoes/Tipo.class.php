<?php
/**
 * Tipo Active Record
 *
 * @version    1.0
 * @package    model
 * @subpackage configuracoes
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 */
class Tipo extends TRecord
{
    const TABLENAME = 'tipo';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('parametros');
        parent::addAttribute('ativo');
    }
    
    /**
     * Método que retorna os artigos de um tipo
     */
    public function get_posts()
    {
        return Artigo::where('tipo_id','=',$this->id)->where('ativo','=','t')->orderBy('dt_post')->load();
    }
    
    /**
     * 
     */
    public static function getIdByNome($nome)
    {
        $tipo = self::where('nome','=',$nome)->first();
        if ($tipo)
        {
            return $tipo->id;
        }
        return FALSE;
    }


}

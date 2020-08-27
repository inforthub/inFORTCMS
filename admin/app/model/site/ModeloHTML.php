<?php
/**
 * ModeloHTML Active Record
 *
 * @version    1.0
 * @package    model
 * @subpackage site
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 */
class ModeloHTML extends TRecord
{
    const TABLENAME = 'modelo_html';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $formulario;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('parametros');
        parent::addAttribute('html');
    }
    
    /**
     * Atualizamos todos os artigos relacionados
     */
    public function onAfterStore($object)
    {
        $artigos = Artigo::where('modelo_html_id','=',$object->id)->load();
        
        if ($artigos)
        {
            foreach($artigos as $artigo)
            {
                $parametros = json_decode(str_replace('..','{root}', $artigo->parametros), true);
                $arr_parse = [];
                
                foreach($parametros as $key=>$val)
                {
                    if ($key == 'campos')
                        $arr_parse = $val;
                    else
                        $arr_parse[$key] = $val;
                }
                
                // aplicando o parse no html e salvamos o artigo
                $parse = new TParser;
                $artigo->artigo = $parse->parse_string($object->html, $arr_parse, TRUE);
                $artigo->store();
            }
        }
    }

}

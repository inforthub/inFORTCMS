<?php
/**
 * Midia Active Record
 *
 * @version    1.0
 * @package    model
 * @subpackage gestao
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 */
class Midia extends TRecord
{
    const TABLENAME = 'midia';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('url');
        parent::addAttribute('icone');
        parent::addAttribute('ativo');
    }
    
    /**
     * Retorna um array com as midias
     */
    public static function getArrayMidias()
    {
        $midias = Midia::select('nome','url','icone')->where('ativo','=','t')->load();
        $arr = [];
        foreach ($midias as $midia)
        {
            $m = $midia->toArray();
            $m['nome_min'] = strtolower($m['nome']);
            $arr[] = $m;
        }
        return $arr;
    }
    
    /**
     * Localiza uma mídia pela URL e retorna seus dados
     * @link $link string
     */
    public static function findURL($link)
    {
        return Midia::where('url','=',$link)->first();
    }
    
    /**
     * Localiza uma mídia do whatsapp e atualiza seus dados
     * @link $link string
     */
    public static function setWhatsAppURL($link)
    {
        if( !empty($link) )
        {
            $midia = Midia::where('nome','=','WhatsApp')->first();
            
            if ($midia)
            {
                // atualiza
                $midia->url = $link;
                $midia->store();
            }
            else
            {
                // criamos uma midia
                $midia = new Midia;
                $midia->nome  = 'WhatsApp';
                $midia->icone = 'fab fa-whatsapp';
                $midia->url   = $link;
                $midia->store();
            }
        }
    }
    


}

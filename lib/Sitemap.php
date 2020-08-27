<?php
/**
 * Sitemap
 *
 * @version     1.0
 * @package     lib
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 (https://www.infort.eti.br)
 *
 */
class Sitemap
{
    private $_xml;

    public function __construct()
    {
        // gera o sitemap
        $this->_xml = Link::getSitemap();
    }
    
    /**
     * Retorna o sitemap com base no id
     */
    public function get_sitemap($id=null)
    {
        // verifica se ele existe
        if (is_null($id))
            return $this->_xml;
        
        if (array_key_exists($id, $this->_xml))
        {
            // retornamos o xml
            return $this->_xml[$id];
        }
        else
        {
            throw new Exception("Página não encontrada!");
        }
    }
    
    /**
     * Verifica se existe o sitemap com base na URL
     */
    public static function getByURL($url)
    {
        $arr = explode('/',$url);
        if( empty(end($arr)) )
            array_pop($arr); //deleta o último índice do array
        
        if( empty(reset($arr)) )
            array_shift($arr); //deleta o primeiro índice do array
        
        if ( substr($arr[0], 0, 7) == 'sitemap' )
        {
            $num = mb_substr($arr[0],7,3); //intval($arr[0]);
            $sitemap = new self();
            return $sitemap->get_sitemap($num);
        }
        
        return false;
    }
    

    
}

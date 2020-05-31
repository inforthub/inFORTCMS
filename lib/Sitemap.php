<?php
/**
 * Sitemap
 *
 * @version     1.1
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
        $this->set_sitemap();
    }
    
    /**
     * Monta o xml do sitemap
     */ 
    private function set_sitemap()
    {
        // pegamos todos os links
        $links = Link::all();
        $root  = THelper::getPreferences('pref_site_dominio');

        if ( $links )
        {
            $n = 1;
            $arr = [];
            
            // montamos o xml
            $xml  = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

            // percorrendo os links
            foreach ($links as $link)
            {
                $xml .= '  <url>';
                $xml .= '      <loc>'.$root.$link->url.'</loc>';
                $xml .= '      <lastmod>'.substr($link->lastmod,0,10).'</lastmod>';
                $xml .= '      <changefreq>'.$link->changefreq.'</changefreq>';
                $xml .= '      <priority>'.$link->priority.'</priority>';
                $xml .= '  </url>';
                
                $n++;
                
                // limitamos o xml a 50.000 links
                if ($n > 50000)
                {
                    $xml  .= '</urlset>';
                    $arr[] = $xml;
                    $n     = 1;
                    
                    // iniciamos novamente o xml
                    $xml  = '<?xml version="1.0" encoding="UTF-8"?>';
                    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
                }
            }
            $xml  .= '</urlset>';
            $arr[] = $xml;

            $this->_xml = $arr; // retornamos o xml pronto
        }
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
            $sitemap = new Sitemap;
            return $sitemap->get_sitemap($num);
        }
        
        return false;
    }
    
    
    
    
}


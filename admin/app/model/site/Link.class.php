<?php
/**
 * Link Active Record
 *
 * @version     1.0
 * @package     model
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 (https://www.infort.eti.br)
 */
class Link extends TRecord
{
    const TABLENAME = 'link';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $artigo;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('url');
        parent::addAttribute('lastmod');
        parent::addAttribute('changefreq');
        parent::addAttribute('priority');
        parent::addAttribute('artigo_id');
        parent::addAttribute('tipo_id');
    }

    
    /**
     * Method set_artigo
     * Sample of usage: $link->artigo = $object;
     * @param $object Instance of Artigo
     */
    public function set_artigo(Artigo $object)
    {
        $this->artigo = $object;
        $this->artigo_id = $object->id;
    }
    
    /**
     * Method get_artigo
     * Sample of usage: $link->artigo->attribute;
     * @returns Artigo instance
     */
    public function get_artigo()
    {
        // loads the associated object
        if (empty($this->artigo))
            $this->artigo = new Artigo($this->artigo_id);
    
        // returns the associated object
        return $this->artigo;
    }
    
    /**
     * Contabiliza o acesso ao artigo
     */
    public function updateVisita()
    {
        $obj = $this->get_artigo();
        $obj->updateVisita();
    }
    
    
    /**
     * Localiza uma URL e retorna seus dados
     * @link $link string
     */
    public static function findURL($link)
    {
        $url = false;
        if( !empty($link) )
        {
            $url = Link::where('url','=',$link)->first();
        }
        return $url;
    }
    /*
    public static function isCategoria($id)
    {
        $cat = new Tipo($id);
        if ($cat && $cat->nome == 'Categoria')
        {
            return true;
        }
        return false;
    }
    */
    /**
     * Atualiza todos os links do sistema
     */
    public static function updateLinks()
    {
        // iniciando rotina para gerar todas as URLs do sistema
        
        //$dominio = THelper::getPreferences('pref_site_dominio');
        $root    = '/';
        
        // limpando a tabela
        $repository = new TRepository('Link');
        $repository->delete();
        
        $tipo = Tipo::where('ativo','=','t')->load();
        $id = 1; // iniciando indice
        
        
        // pegando a homepage
        $homepage = Menu::where('inicial','=','t')->where('ativo','=','t')->first();
        
        if ( $homepage )
        {
            //$destino = explode(':',$homepage->destino);
            
            $link = new Link;
            $link->id         = $id;
            $link->url        = $root;
            //$link->lastmod  = 
            $link->changefreq = 'weekly';
            $link->priority   = '1';
            $link->tipo_id    = $homepage->tipo_id;
            $link->artigo_id  = $homepage->artigo_id;
            $link->store();
            
            $id++;
        }
        
        /********
         * MENU
         ********/
        
        // pegando o menu (menos a home)
        $menu = Menu::where('inicial','<>','t')->where('ativo','=','t')->where('artigo_id','>',0)->load();
        
        if ( $menu )
        {
            foreach ( $menu as $m )
            {
                $link = new Link;
                $link->id         = $id;
                $link->url        = $root.$m->url;
                //$link->lastmod  = 
                $link->changefreq = 'monthly';
                $link->priority   = '0.90';
                $link->tipo_id    = $m->tipo_id;
                $link->artigo_id  = $m->artigo_id;
                $link->store();
                
                $id++;
            }
        }
        
        // percorrendo os artigos
        $paginas = Artigo::where('ativo','=','t')->load();
        
        if ($paginas)
        {
            foreach ( $paginas as $artigo )
            {
                $url = $artigo->get_url();
                if ( !self::findURL($url) )
                {
                    $link = new Link;
                    $link->id         = $id;
                    $link->url        = $url;
                    //$link->lastmod  = 
                    $link->changefreq = 'monthly';
                    $link->priority   = '0.80';
                    $link->tipo_id    = $artigo->tipo_id;
                    $link->artigo_id  = $artigo->artigo_id;
                    $link->store();
                    
                    $id++;
                    /*
                    if ($artigo->tipo->nome == 'Categoria')
                    {
                        // adicionando 'filhos' e retorna o próximo id
                        $id = self::setLink($url,$artigo->tipo->get_posts(),$id);
                    }
                    */
                }
            }
        }

    }
    
    /**
     * Método recursivo para inserção des links filhos
     *
     *
    private static function setLink($url,$object,$id)
    {
        if ( is_array($object) and count($object) > 0 )
        {
            $url .= '/';
            foreach ($object as $artigo)
            {
                $link = new Link;
                $link->id         = $id;
                $link->url        = $url.$artigo->url;
                //$link->lastmod    = 
                //$link->changefreq = 'monthly';
                $link->priority   = '0.70';
                $link->tipo_id    = $artigo->tipo_id;
                $link->artigo_id  = $artigo->artigo_id;
                $link->store();
                
                $id++;
                //$last = $id;
                // adicionando 'filhos' e retorna o próximo id
                if ($artigo->tipo->nome == 'Categoria')
                {
                    // adicionando 'filhos' e retorna o próximo id
                    $id = self::setLink($url,$artigo->tipo->get_posts(),$id);
                }
            } // fim foreach
        }
        return $id;
    }
    */


}

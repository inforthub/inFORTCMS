<?php
/**
 * Menu Active Record
 *
 * @version    1.0
 * @package    model
 * @subpackage site
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class Menu extends TRecord
{
    const TABLENAME = 'menu';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $obj_destino;
    private $artigo;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('titulo');
        parent::addAttribute('url');
        parent::addAttribute('ordem');
        parent::addAttribute('inicial');
        parent::addAttribute('icone');
        parent::addAttribute('header_class');
        parent::addAttribute('menu_pai_id');
        parent::addAttribute('ativo');
        parent::addAttribute('artigo_id');
    }
    
    
    /**
     * Method set_artigo
     * Sample of usage: $menu->artigo = $object;
     * @param $object Instance of Artigo
     *
    public function set_artigo(Artigo $object)
    {
        $this->artigo = $object;
        $this->artigo_id = $object->id;
    }
    
    /**
     * Method get_artigo
     * Sample of usage: $menu->artigo->attribute;
     * @returns Artigo instance
     */
    public function get_artigo()
    {
        // loads the associated object
        if (empty($this->artigo))
            $this->artigo = new Pagina($this->artigo_id);
    
        // returns the associated object
        return $this->artigo;
    }
    
    /**
     * Método que retorna todos os itens do submenu caso existam
     */
    public function get_submenu()
    {
        return Menu::where('menu_pai_id','=',$this->id)->where('ativo','=','t')->orderBy('ordem')->load();
    }
    
    /**
     * Método que retorna o menu principal sem submenus
     */
    public function get_menu()
    {
        return Menu::where('menu_pai_id','=','')->where('ativo','=','t')->orderBy('ordem')->load();
    }
    
    /**
     * Método que atualiza a coluna 'inicial' de todos os registros para 'FALSE'
     */
    public static function clear_inicial()
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('titulo', '!=', ''));
        
        $repos = new TRepository('Menu');
        $repos->update(['inicial'=>'f'],$criteria);
    }
    
    /**
     * Retorna o menu pai
     */
    public function get_menu_pai()
    {
        $pai = new Menu($this->menu_pai_id);
        // returns the associated object
        return $pai;
    }
    
    /**
     * Método Hook para atualizar a tabela de Link
     */
    public function onAfterStore($obj)
    {
        // atualizando link da página inicial
        if ( $this->inicial == 't' && !empty($this->artigo_id) )
        {
            // atualizamos a tabela de Link
            $link = Link::findURL('/');
            
            if (!$link)
            {
                $link = new Link;
            }
            
            // atualizando os campos
            $link->url        = '/';
            $link->lastmod    = date('Y-m-d H:i:s');
            $link->artigo_id  = $this->artigo_id;
            $link->tipo_id    = ($this->tipo_id) ?: 1; // site
            $link->changefreq = 'hourly';
            $link->priority   = '1,00';
            
            $link->store();
        }
    }


}

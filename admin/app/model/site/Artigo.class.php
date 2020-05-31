<?php
/**
 * Artigo Active Record
 *
 * @version     1.0
 * @package     model
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2019 (https://www.infort.eti.br)
 */
class Artigo extends TRecord
{
    const TABLENAME = 'artigo';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}

    private $tipo;
    private $modulos;
    private $arquivos;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('titulo');
        parent::addAttribute('url');
        parent::addAttribute('resumo');
        parent::addAttribute('artigo');
        parent::addAttribute('metadesc');
        parent::addAttribute('metakey');
        parent::addAttribute('midias');
        parent::addAttribute('dt_cadastro');
        parent::addAttribute('dt_post');
        parent::addAttribute('dt_edicao');
        parent::addAttribute('visitas');
        parent::addAttribute('usuario_id');
        parent::addAttribute('destaque');
        parent::addAttribute('ativo');
        parent::addAttribute('modo');
        parent::addAttribute('tipo_id');
        parent::addAttribute('categoria_id');
    }
    
    /**
     * Method get_categoria
     * Sample of usage: $artigo->categoria->attribute;
     * @returns Artigo instance
     */
    public function get_categoria()
    {
        return Artigo::find($this->categoria_id);
    }
    
    
    /**
     * Method set_tipo
     * Sample of usage: $artigo->tipo = $object;
     * @param $object Instance of Tipo
     */
    public function set_tipo(Tipo $object)
    {
        $this->tipo = $object;
        $this->tipo_id = $object->id;
    }
    
    /**
     * Method get_tipo
     * Sample of usage: $artigo->tipo->attribute;
     * @returns Tipo instance
     */
    public function get_tipo()
    {
        // loads the associated object
        if (empty($this->tipo))
            $this->tipo = new Tipo($this->tipo_id);
    
        // returns the associated object
        return $this->tipo;
    }
    
    
    /**
     * Method addModulo
     * Add a Modulo to the Artigo
     * @param $object Instance of Modulo
     */
    public function addModulo(Modulo $object)
    {
        $this->modulos[] = $object;
    }
    
    /**
     * Method getModulos
     * Return the Artigo' Modulo's
     * @return Collection of Modulo
     */
    public function getModulos()
    {
        return $this->modulos;
    }
    
    /**
     * Method addArquivo
     * Add a Arquivo to the Artigo
     * @param $object Instance of Arquivo
     */
    public function addArquivo(Arquivo $object)
    {
        $this->arquivos[] = $object;
    }
    
    /**
     * Method getArquivos
     * Return the Artigo' Arquivo's
     * @return Collection of Arquivo
     */
    public function getArquivos()
    {
        return $this->arquivos;
    }

    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        $this->modulos = array();
        $this->arquivos = array();
    }

    /**
     * Load the object and its aggregates
     * @param $id object ID
     */
    public function load($id)
    {
        $this->modulos = parent::loadComposite('Modulo', 'artigo_id', $id);
        $this->arquivos = parent::loadComposite('Arquivo', 'artigo_id', $id);
    
        // load the object itself
        return parent::load($id);
    }

    /**
     * Store the object and its aggregates
     */
    public function store()
    {
        // store the object itself
        parent::store();
    
        parent::saveComposite('Modulo', 'artigo_id', $this->id, $this->modulos);
        //parent::saveComposite('Arquivo', 'artigo_id', $this->id, $this->arquivos);
    }
    
    /**
     * Contabiliza uma visita ao artigo
     */
    public function updateVisita()
    {
        $this->visitas ++;
        parent::store();
    }

    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        $id = isset($id) ? $id : $this->id;
        parent::deleteComposite('Modulo', 'artigo_id', $id);
        parent::deleteComposite('Arquivo', 'artigo_id', $id);
        
        parent::deleteComposite('Comentario', 'artigo_id', $id);
        parent::deleteComposite('Link', 'artigo_id', $id);
    
        // delete the object itself
        parent::delete($id);
    }
    
    /**
     * Método Hook para atualizar a tabela de Link
     */
    public function onAfterStore($obj)
    {
        $link = Link::where('artigo_id','=',$obj->id)->first();
        
        if (!$link)
        {
            $link = new Link;
        }
        
        $url = '/'.$obj->url;
        
        // verificando a categoria e ajustando a URL
        if (!empty($obj->categoria_id))
        {
            //$cat = Artigo::find($obj->categoria_id);
            //$url = $cat->url.'/'.$url;
            $url = self::getURL($obj->categoria_id).$url;
        }
        
        // atualizando os campos
        $link->url        = $url;
        $link->lastmod    = date('Y-m-d H:i:s');
        $link->artigo_id  = $obj->id;
        $link->tipo_id    = $obj->tipo_id;
        
        if ($obj->tipo_id == 1)
        {
            $link->changefreq = 'monthly';
            $link->priority   = '0,80';
        }
        
        $link->store();
    }
    
    /**
     * Retorna a URL completa
     */
    public function get_fullurl()
    {
        return self::pegaURL($this->categoria_id,'/'.$this->url);
    }
    
    /**
     * Método que retorna todos os itens 'filhos' de um artigo, caso existam
     */
    public function get_filhos()
    {
        return Artigo::where('categoria_id','=',$this->id)->where('ativo','=','t')->where('modo','=','a')->orderBy('dt_cadastro')->load();
    }
    
    /**
     * Retorna todos os posts em ordem de postagem
     * @param $skip            integer 
     */
    public static function getAllPosts($skip=0)
    {
        // listamos os ultimos 10 artigos
        return Artigo::where('ativo','=','t')->where('modo','=','a')->orderBy('dt_post','desc')->take(10)->skip($skip)->load();
    }
    
    /**
     * Busca por posts e retorna em ordem de postagem
     * @param $busca    string
     * @param $skip     integer 
     */
    public static function buscaPosts($busca,$skip=0)
    {
        // listamos os ultimos 10 artigos
        return Artigo::where('ativo','=','t')->where('titulo','like','%'.$busca.'%')->where('modo','=','a')->orderBy('dt_post','desc')->take(10)->skip($skip)->load();
    }
    
    /**
     * Retorna todos os posts de uma categoria em ordem de postagem
     * @param $categoria_id    integer
     * @param $skip            integer 
     */
    public static function getPostCategoria($categoria_id,$skip=0)
    {
        // listamos os ultimos 10 artigos
        return Artigo::where('categoria_id','=',$categoria_id)->where('modo','=','a')->where('ativo','=','t')->orderBy('dt_post','desc')->take(10)->skip($skip)->load();
    }
    
    /**
     * Retorna a url formatada
     * @param $id    integer
     */
    public static function getURL($id)
    {
        return self::pegaURL($id,'');
    }
    private static function pegaURL($id,$url)
    {
        $obj = self::find($id);
        $url = '/'.$obj->url . $url;
        if (!empty($obj->categoria_id))
            $url = self::pegaURL($obj->id,$url);
            
        return $url;
    }
    
    /**
     * Retorna a primeira URL de uma categoria 
     * Ex: se a url for /blog/viagem/, retornará "blog"
     */
    public function getURLCategoriaPai()
    {
        if ($this->modo = 'c' && $this->categoria_id == 0)
            return $this->url;
            
        return self::pegaURLCategoriaPai($this->categoria_id);
    }
    private static function pegaURLCategoriaPai($id)
    {
        $obj = self::find($id);
        $ret = $obj->url;
        if (!empty($obj->categoria_id))
            $ret = self::pegaURLCategoriaPai($obj->categoria_id);
            
        return $ret;
    }
    
    /**
     * Pega todos os Comentários de um Post
     */
    public function getComentarios()
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('artigo_id', '=', $this->id));
        return Comentario::getObjects( $criteria );
    }
    
    /**
     * Método loadFromURL
     * Retorna a página a partir da URL
     */
    public static function loadFromURL( $url=NULL )
    {
        $page = NULL;
        if ( !is_null($url) )
        {
            if ($url == 'index')
            {
                //chamamos a homepage
                $menu = Menu::where('inicial','=','t')->load();
                if ($menu)
                {
                    $page = $menu[0]->get_artigo();
                }
            }
            else
            {
                $instance = self::where('apelido','=',$url)->load();
                if (isset($instance[0]))
                {
                    $page = $instance[0];
                }
            }
        }
        
        return $page;
    }
    
    /**
     * Lista todas as Categorias
     * @param $obj boolean
     */
    public static function listCategorias($arr=false)
    {
        $repos = new TRepository('Artigo');
        $criteria = TCriteria::create(['modo'=>'c', 'ativo'=>'t']);
        if ($arr)
        {
            $arr = [];
            $obj = $repos->load($criteria,false);
            foreach($obj as $cat)
            {
                $arr[] = $cat->toArray();
            }
            return $arr;
        }
        else
            return $repos->load($criteria);
    }
    
    /**
     * Retorna uma imagem da galeria associada ao artigo
     */
    public function getImagemDestaque()
    {
        $img = Arquivo::where('artigo_id','=',$this->id)->where('destaque','=','t')->where('formato','=','F')->first();
        if ($img)
        {
            return $img;
        }
        
        // caso não encontre, retornamos qualuqer imagem
        $img = Arquivo::where('artigo_id','=',$this->id)->where('formato','=','F')->first();
        if ($img)
        {
            return $img;
        }
        
        return false;
    }
    


}

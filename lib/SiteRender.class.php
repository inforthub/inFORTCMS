<?php
/**
 * SiteRender
 *
 * @version    1.1
 * @package    lib
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2021 (https://www.infort.eti.br)
 *
 */
require_once 'HtmlBase.php';

class SiteRender extends HtmlBase
{
    public function __construct($url=null)
    {
        $this->setReplaces($url);
    }
    
    /**
     * Recebe o id da Pagina e retorna o HTML pronto
     */
    public function render($artigo)
    {
        // iniciando parser
        $parse = new TParser;

        // carregando replaces
        $this->_replaces['title']           = $artigo->titulo . ' | ' . $this->_replaces['title'];
        $this->_replaces['titulo']          = $artigo->titulo;
        $this->_replaces['resumo']          = $artigo->resumo;
        $this->_replaces['mensagem']        = '';
        $this->_replaces['titulo_listagem'] = 'Últimos Artigos';
        // carregando scripts da página
        $this->_replaces['scripts_head']   .= $parse->parse_string( $artigo->script_head, $this->_replaces );
        $this->_replaces['scripts_body']   .= $parse->parse_string( $artigo->script_body, $this->_replaces );
        
        // verifico se é um artigo ou uma categoria
        if ($artigo->modo === 'c')
        {
            // pegamos o resumo do campo 'artigo'
            $this->_replaces['resumo'] = $artigo->artigo;
            
            // renderizamos uma listagem dos artigos da categoria "blog", "news", menos os sites
            // Ex: procuramos por blog_listagem.html, ou listagem.html
            
            $file = ROOT.'/templates/'.$this->_replaces['theme'].'/partials/listagem.html';
            if ( file_exists(ROOT.'/templates/'.$this->_replaces['theme'].'/partials/'.$artigo->getURLCategoriaPai().'_listagem.html') )
            {
                // pegamos o modelo html de listagem correspondente
        	    $file = ROOT.'/templates/'.$this->_replaces['theme'].'/partials/'.$artigo->getURLCategoriaPai().'_listagem.html';
            }
            $listagem = new THtmlRenderer($file);
            $listagem->disableHtmlConversion();
    	    
    	    // pode ser uma busca...
            // se for, buscar os ultimos 10 artigos filtrados pelo valor vindo do post
            // então, verificamos se existe algum POST ...
            
            if (!empty($_POST["query"]))
            {
                $posts = Artigo::buscaPosts($_POST["query"],0);
                $this->_replaces['titulo_listagem'] = 'Resultado da Busca por: "'.$_POST["query"].'"';
            }
            else
            {
                // pegamos os artigos da Categoria
                $posts = Artigo::getPostCategoria($artigo->id,0);
            }
            
            if ($posts)
            {
                $arr_posts = [];
                foreach ($posts as $post)
                {
                    // pegar a imagem da tabela 'arquivo'
                    $img = $post->getImagemDestaque();
                    
                    if ($img)
                    {
                        $link['imagem']        = $this->_pref['pref_site_dominio'].str_replace('..','', $img->nome_web);
        				$link['imagem_alt']    = $img->descricao;
                    }
                    else
                    {
                        // colocamos uma imagem padrão
                        //$link['imagem']        = $this->_pref['pref_site_dominio'].str_replace('..','', $post->imagem);
        				$link['imagem_alt']    = $post->titulo;
    				}
    				$data_post = new DateTime($post->dt_post);
    				
    				$link['titulo']    = $post->titulo;
    				$link['autor']     = THelper::showUserName($post->usuario_id);
    				$link['data_ex']   = THelper::dataPorExtenso($post->dt_post);
        			$link['data_d']    = $data_post->format('d');
        			$link['data_m']    = $data_post->format('m');
        			$link['data_Y']    = $data_post->format('Y');
        			$link['data_m_ex'] = $data_post->format('M');
        			$link['categoria'] = $post->categoria->titulo;
    				$link['resumo']    = !empty($post->resumo) ? $post->resumo : $post->metadesc;
    				$link['btn_link']  = $this->_pref['pref_site_dominio'].$post->get_fullurl();
                    $link['visitas']   = $post->visitas;

    				$arr_posts[] = $link;
                }
                $listagem->enableSection('posts', $arr_posts, TRUE);
                
                // montando meta tags
                $this->_replaces['meta_tags'] = $this->setMetaTags($artigo);
                
                // pegar listagem de artigos relacionados
                
            }
            else
            {
                $this->_replaces['mensagem'] = 'Nenhum Post encontrado!';
            }

            // listagem de categorias
            $categorias = Artigo::listCategorias();
            if ($categorias)
            {
                $lista = [];
                foreach($categorias as $cat)
                {
                    $arr = $cat->toArray();
                    $arr['link'] = $this->_pref['pref_site_dominio'].$cat->get_fullurl();
                    $lista[] = $arr;
                }
                $listagem->enableSection('categorias', $lista, TRUE);
            }
            
            // posts aleatórios
            $posts = $this->get_posts($artigo->id,null,'RAND()');
            if (is_array($posts))
            {
                $listagem->enableSection('ultimos_artigos', $posts, TRUE);
            }
            
            // ativano html principal
            $listagem->enableSection('main', $this->_replaces);
            
            // atualizamos a variavel "_replaces['pagina']" com a string da página pronta
            $this->_replaces['pagina'] = $listagem->getContents();
        }
        else
        {
            // pegar a imagem da tabela 'arquivo'
            $img = $artigo->getImagemDestaque();
            
            if ($img)
            {
                $this->_replaces['imagem']        = $this->_pref['pref_site_dominio'].str_replace('..','', $img->nome_web);
				$this->_replaces['imagem_alt']    = $img->descricao;
            }
            else
            {
                // colocamos uma imagem padrão
                //$this->_replaces['imagem']        = $this->_pref['pref_site_dominio'].str_replace('..','', $post->imagem);
				$this->_replaces['imagem_alt']    = $artigo->titulo;
			}
			
            if( $artigo->tipo->nome <> 'Site' )
            {
                
    		    $data_post = new DateTime($artigo->dt_post);
    		    
    			$this->_replaces['titulo']    = $artigo->titulo;
    			$this->_replaces['autor']     = THelper::showUserName($artigo->usuario_id);
    			$this->_replaces['data_ex']   = THelper::dataPorExtenso($artigo->dt_post);
    			$this->_replaces['data_d']    = $data_post->format('d');
        		$this->_replaces['data_m']    = $data_post->format('m');
        		$this->_replaces['data_Y']    = $data_post->format('Y');
        		$this->_replaces['data_m_ex'] = $data_post->format('M');
    			$this->_replaces['categoria'] = $artigo->categoria->titulo;
    			$this->_replaces['artigo']    = $artigo->artigo;
    			$this->_replaces['visitas']   = $artigo->visitas;

                // montando meta tags
                $this->_replaces['meta_tags'] = $this->setMetaTags($artigo);
                
                // pegar listagem de artigos relacionados

                // pegamos o modelo html da postagem
			    $file = ROOT.'/templates/'.$this->_replaces['theme'].'/partials/post.html';
                if ( file_exists(ROOT.'/templates/'.$this->_replaces['theme'].'/partials/'.$artigo->getURLCategoriaPai().'_post.html') )
                {
                    // pegamos o modelo html de listagem correspondente
            	    $file = ROOT.'/templates/'.$this->_replaces['theme'].'/partials/'.$artigo->getURLCategoriaPai().'_post.html';
                }
                $postagem = new THtmlRenderer($file);
                $postagem->disableHtmlConversion();
                
                
                // carregano galeria de imagens
                $galeria = $this->getGaleria($artigo);
                if ($galeria)
                {
                    $postagem->enableSection('galeria');
                    $postagem->enableSection('fotos',$galeria, TRUE);
                }

                // posts aleatórios do mesmo tipo
                $posts = $this->get_posts($artigo->id,null,'RAND()');
                if (is_array($posts))
                {
                    $listagem->enableSection('ultimos_artigos', $posts, TRUE);
                }
                
                // ativano html principal
                $postagem->enableSection('main', $this->_replaces);
                
                // atualizamos a variavel "_replaces['pagina']" com a string da página pronta
                $this->_replaces['pagina'] = $postagem->getContents();
            }
            else
            {
                // carregano galeria de imagens
                $galeria = $this->getGaleria($artigo);
                if ($galeria)
                {
                    $this->_replaces['galeria'] = $galeria;
                }
                
                // renderizamos o artigo em si
                $this->_replaces['pagina'] .= $artigo->artigo;
            }
        }
        
        
        $blog_id = Tipo::getIdByNome('Blog');
        
        // últimos 5 blogs
        $posts = $this->get_posts(null,$blog_id);
        if (is_array($posts))
        {
            $this->_replaces['ultimos_blogs'] = $posts;
        }
        
        // renderiando as posições da template
        $this->renderPosicoes($this->_template->getPosicoes());
        
        // montando meta tags
        $this->_replaces['meta_tags'] = $this->setMetaTags($artigo);
        
        // aplicando o parse na página pronta
        $this->_replaces['pagina'] = $parse->parse_string($this->_replaces['pagina'], $this->_replaces, TRUE);
        
        return $this->_replaces;
    }
    
    /**
     * Retorna uma lista de posts
     * @param $categoria_id  integer
     * @param $tipo_id       integer
     * @param $order_by      string 'RAND()', 'dt_post', 'id', etc
     * @param $direction     string 'asc' ou 'desc'
     * @param $take          integer '5'
     */
    private function get_posts($categoria_id=null, $tipo_id=null, $order_by='dt_post',$direction='desc', $take='5')
    {
        if (empty($categoria_id) && !empty($tipo_id))
        {
            // posts do mesmo tipo
            $posts = Artigo::where('ativo','=','t')->where('modo','=','a')->where('tipo_id','=',$tipo_id)->orderBy($order_by,$direction)->take( (int) $take )->load();
        }
        else if (!empty($categoria_id) && empty($tipo_id))
        {
            // posts da mesma categoria
            $posts = Artigo::where('ativo','=','t')->where('modo','=','a')->where('categoria_id','=',$categoria_id)->orderBy($order_by)->take( (int) $take )->load();
        }
        
        if ($posts)
        {
            $arr_posts = [];
            foreach ($posts as $post)
            {
				// pegar a imagem da tabela 'arquivo'
                $img = $post->getImagemDestaque();
                
                if ($img)
                {
                    $link['imagem']        = $this->_pref['pref_site_dominio'].str_replace('..','', $img->nome_web);
    				$link['imagem_alt']    = $img->descricao;
                }
                else
                {
                    // colocamos uma imagem padrão
                    //$link['imagem']        = $this->_pref['pref_site_dominio'].str_replace('..','', $post->imagem);
    				$link['imagem_alt']    = $post->titulo;
				}
				$data_post = new DateTime($post->dt_post);
				
				$link['titulo']    = $post->titulo;
				$link['autor']     = THelper::showUserName($post->usuario_id);
				$link['data_ex']   = THelper::dataPorExtenso($post->dt_post);
    			$link['data_d']    = $data_post->format('d');
    			$link['data_m']    = $data_post->format('m');
    			$link['data_Y']    = $data_post->format('Y');
    			$link['data_m_ex'] = $data_post->format('M');
    			$link['categoria'] = $post->categoria->titulo;
				$link['resumo']    = $post->resumo;
				$link['btn_link']  = $this->_pref['pref_site_dominio'].'/'.$post->get_fullurl();
                $link['visitas']   = $post->visitas;

				$arr_posts[] = $link;
            }
            //$postagem->enableSection('ultimos_artigos', $arr_posts, TRUE);
            return $arr_posts;
        }
        return FALSE;
    }
    


 }

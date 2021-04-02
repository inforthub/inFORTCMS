<?php
/**
 * SiteRender
 *
 * @version    1.0
 * @package    lib
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 (https://www.infort.eti.br)
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
        
        // verifico se é um artigo ou uma categoria
        if ($artigo->modo === 'c')
        {
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
        				$link['imagem_title']  = $img->descricao;
                    }
                    else
                    {
                        // colocamos uma imagem padrão
                        //$link['imagem']        = $this->_pref['pref_site_dominio'].str_replace('..','', $post->imagem);
        				//$link['imagem_alt']    = $post->imagem_alt;
        				//$link['imagem_title']  = $post->imagem_title;
    				}
    				$data_post = new DateTime($post->dt_post);
    				
    				$link['titulo']    = $post->titulo;
    				$link['autor']     = THelper::showUserName($post->usuario_id);
    				$link['data_ex']   = THelper::dataPorExtenso($post->dt_post);
        			$link['data_d']    = $data_post->format('d');
        			$link['data_m']    = $data_post->format('m');
        			$link['data_Y']    = $data_post->format('Y');
        			$link['categoria'] = $post->categoria->titulo;
    				$link['resumo']    = $post->resumo;
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
            $posts = $this->get_posts($artigo->id);
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
            if( $artigo->tipo->nome <> 'Site' )
            {
                // pegar a imagem da tabela 'arquivo'
                $img = $artigo->getImagemDestaque();
                
                if ($img)
                {
                    $this->_replaces['imagem']        = $this->_pref['pref_site_dominio'].str_replace('..','', $img->nome_web);
    				$this->_replaces['imagem_alt']    = $img->descricao;
    				$this->_replaces['imagem_title']  = $img->descricao;
                }
                else
                {
                    // colocamos uma imagem padrão
                    //$this->_replaces['imagem']        = $this->_pref['pref_site_dominio'].str_replace('..','', $post->imagem);
    				//$this->_replaces['imagem_alt']    = $post->imagem_alt;
    				//$this->_replaces['imagem_title']  = $post->imagem_title;
				}
    		    $data_post = new DateTime($artigo->dt_post);
    		    
    			$this->_replaces['titulo']    = $artigo->titulo;
    			$this->_replaces['autor']     = THelper::showUserName($artigo->usuario_id);
    			$this->_replaces['data_ex']   = THelper::dataPorExtenso($artigo->dt_post);
    			$this->_replaces['data_d']    = $data_post->format('d');
        		$this->_replaces['data_m']    = $data_post->format('m');
        		$this->_replaces['data_Y']    = $data_post->format('Y');
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
                $arquivos = $artigo->getArquivos();
                if ($arquivos)
                {
                    $arr_galeria = [];
                    foreach ($arquivos as $img)
                    {
                        if ($img->formato == 'F')
                        {
                            $link['imagem_url'] = $this->_pref['pref_site_dominio'].str_replace('..','', $img->nome_web);
                            $link['imagem_alt'] = $img->descricao;
                        
                            $arr_galeria[] = $link;
                        }
                    }
                    
                    $postagem->enableSection('galeria');
                    $postagem->enableSection('fotos',$arr_galeria, TRUE);
                }


                // posts aleatórios do mesmo tipo
                $posts = $this->get_posts($artigo->categoria_id);
                if (is_array($posts))
                {
                    $postagem->enableSection('ultimos_artigos', $posts, TRUE);
                }
                /*
                $posts = Artigo::where('ativo','=','t')->where('tipo_id','=',$artigo->tipo_id)->orderBy('RAND()')->take(3)->load();
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
            				$link['imagem_title']  = $img->descricao;
                        }
                        else
                        {
                            // colocamos uma imagem padrão
                            //$link['imagem']        = $this->_pref['pref_site_dominio'].str_replace('..','', $post->imagem);
            				//$link['imagem_alt']    = $post->imagem_alt;
            				//$link['imagem_title']  = $post->imagem_title;
        				}
        				$data_post = new DateTime($post->dt_post);
        				
        				$link['titulo']    = $post->titulo;
        				$link['autor']     = THelper::showUserName($post->usuario_id);
        				$link['data_ex']   = THelper::dataPorExtenso($post->dt_post);
            			$link['data_d']    = $data_post->format('d');
            			$link['data_m']    = $data_post->format('m');
            			$link['data_Y']    = $data_post->format('Y');
            			$link['categoria'] = $post->categoria->titulo;
        				$link['btn_link']  = $this->_pref['pref_site_dominio'].'/'.$post->get_fullurl();
                        $link['visitas']   = $post->visitas;
        
        				$arr_posts[] = $link;
                    }
                    $postagem->enableSection('ultimos_artigos', $arr_posts, TRUE);
                }
                */
                // ativano html principal
                $postagem->enableSection('main', $this->_replaces);
                
                // atualizamos a variavel "_replaces['pagina']" com a string da página pronta
                $this->_replaces['pagina'] = $postagem->getContents();
            }
            else
            {
                // renderizamos o artigo em si
                $this->_replaces['pagina'] .= $artigo->artigo;
            }
        }
        
        // montando meta tags
        $this->_replaces['meta_tags'] = $this->setMetaTags($artigo);
        
        // aplicando o parse na página pronta
        $this->_replaces['pagina'] = $parse->parse_string($this->_replaces['pagina'], $this->_replaces, TRUE);
        
        return $this->_replaces;
    }
    
    
    private function get_posts($categoria_id)
    {
        // posts aleatórios do mesmo tipo
        $posts = Artigo::where('ativo','=','t')->where('categoria_id','=',$categoria_id)->orderBy('RAND()')->take(3)->load();
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
    				$link['imagem_title']  = $img->descricao;
                }
                else
                {
                    // colocamos uma imagem padrão
                    //$link['imagem']        = $this->_pref['pref_site_dominio'].str_replace('..','', $post->imagem);
    				//$link['imagem_alt']    = $post->imagem_alt;
    				//$link['imagem_title']  = $post->imagem_title;
				}
				$data_post = new DateTime($post->dt_post);
				
				$link['titulo']    = $post->titulo;
				$link['autor']     = THelper::showUserName($post->usuario_id);
				$link['data_ex']   = THelper::dataPorExtenso($post->dt_post);
    			$link['data_d']    = $data_post->format('d');
    			$link['data_m']    = $data_post->format('m');
    			$link['data_Y']    = $data_post->format('Y');
    			$link['categoria'] = $post->categoria->titulo;
				$link['btn_link']  = $this->_pref['pref_site_dominio'].'/'.$post->get_fullurl();
                $link['visitas']   = $post->visitas;

				$arr_posts[] = $link;
            }
            //$postagem->enableSection('ultimos_artigos', $arr_posts, TRUE);
            return $arr_posts;
        }
        return FALSE;
    }
    
    
    
    //********************************************//
    
    /**
     * Recebe o id da Pagina e retorna o HTML pronto
     */
    public function render_old($destino)
    {
        $id = $destino[2];
        
        TTransaction::open('sistema');
        $pagina = Pagina::find($id);
        
        if ( $pagina )
        {
            // iniciando parser
            $parse = new TParser;
            /*
            // verificando o método
            if ( $this->_method == 'post' )
            {
                // chamamos a classe padrão para envio de emails e pegamos o seu retorno
                $this->_replaces['alerta'] = Contato::sendMail($_POST);
            }
            */
            // verificamos se a página é do menu principal
            $menu = Menu::where('tipo','=',1)->where('destino','=','Pagina:pagina:'.$pagina->id)->first();
            if ($menu)
            {
                $this->_replaces['header_class'] = $menu->header_class;
            }
            
            $this->_replaces['title']        = $pagina->titulo . ' | ' . $this->_replaces['title'];
            $this->_replaces['titulo']       = $pagina->titulo;
            $this->_replaces['titulotexto']  = $pagina->subtitulo;
            $this->_replaces['icone_menu']   = $pagina->icone;
            
            // montando meta tags
            $this->_replaces['meta_tags'] = $this->setMetaTags($pagina);
            
            // carregando os módulos da página
            $modulos = Modulo::where('artigo_id','=',$pagina->id)->orderBy('ordem')->load();
            
            // montando array dos módulos
            foreach ($modulos as $modulo)
            {
                $mod = Modulo::find($modulo->modulo_id);
                $param = unserialize($mod->parametros);
                $arr['root'] = $this->_pref['pref_site_dominio'];
                
                // verificamos se existem campos com conteúdo
                if ( isset($param['campos']) && is_array($param['campos']) )
                {
                    // carregamos o conteúdo dos campos
                    foreach ($param['campos'] as $key => $value)
                    {
                        //$arr[$key] = nl2br($value); // $campos_parse[$key] = nl2br($value);
                        $arr[$key] = $value;
                    }
                }

                $html = $mod->modelo_modulo->html;

                // renderizamos uma string com o html pronto e concatenamos na página
                $this->_replaces['pagina'] .= $parse->parse_string($html, $arr, TRUE);
            }
            
            $this->_replaces['pagina'] .= $pagina->html;
            
            // aplicando o parse na página pronta
            $this->_replaces['pagina'] = $parse->parse_string($this->_replaces['pagina'], $this->_replaces, TRUE);
            
            return $this->_replaces;
        }
        else
        {
            // Criamos uma exceção
            throw new Exception("Página não encontrada!");
        }
        TTransaction::close();
    }

    
 }

<?php
/**
 * Route
 *
 * @version     1.0
 * @package     lib
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 (https://www.infort.eti.br)
 *
 */
class Route
{
    private $_url;
    private $_explode;
    //private $_class;
    private $_pref;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        $this->_pref = THelper::getPreferences();
        if (!$this->_pref OR empty($this->_pref['pref_site_dominio']))
        {
            echo '<h1>Parâmetros do site não configurados!</h1><p>Acesse o painel de controle e configure os parâmentros do site.<br><a href="./admin">Painel de Controle</a></p>';
            exit();
        }
        
        $this->setUrl();
        $this->setExplode();
        //$this->setClass();
    }
    
    
    /**
     * Capturando o que vem da URL
     */
    private function setUrl()
    {
        $_GET['url'] = (isset($_GET['url']) ? $_GET['url'] : '/');
        $this->_url = $_GET['url'];
    }
    public function getURL()
    {
        return $this->_url;
    }
    
    
    /**
     * Cria um array com os parametros da url
     */
    private function setExplode()
    {
        $this->_explode = $this->_url == '' ? null : explode('/', $this->_url);
        
        if ( !is_null($this->_explode) )
        {
            $url = $this->_explode;
            if( end($url) == null )
            {
                array_pop($url); //deleta o último índice do array
                if ( count($url)>1)
                    $this->_url = implode('/',$url);
                else
                    $this->_url = $url[0];
            }
        }
    }
    public function getExplode()
    {
        return $this->_explode;
    }
    
    
    /**
     * Determina a classe a ser usada
     *
    private function setClass()
    {
        $this->_class = is_null($this->_explode[0]) ? 'index' : $this->_explode[0];
    }
    
    
    /**
     * Carrega a página correspondente
     */
    public function run()
    {
        $tema = THelper::getTheme();
        
        try
        {
            TTransaction::open('sistema');
            //TTransaction::dump();
            
            $parse = new TParser;

            // verificamos se o site está em manutenção
            if ( $this->_pref['pref_site_manutencao'] === '1' )
            {
                require_once 'HtmlBase.php';
                $replaces = HtmlBase::getStaticReplaces($this->_url);
                
                $layout_content = file_get_contents(ROOT.'/templates/'.$tema.'/manutencao.html');
                $replaces['pagina'] = $this->_pref['pref_site_mensagem'];
                // rederiza a página
                echo $parse->parse_string($layout_content, $replaces);
            }
            else
            {
                $this->_url  = empty($this->_url) ? '/' : '/'.$this->_url;
                // consultando a tabela de links
                $link = Link::findURL($this->_url);
                
                if ( $link ) //|| $this->_url == 'blog/buscar' )
                {
                    //$destino = explode(':',$link->destino);
                    //$classe  = $link->system_modules->nome . 'Render';
                    
                    $pagina = new SiteRender($this->_url);
                    $replaces = $pagina->render( $link->get_artigo() );
                    
                    $tema = empty($link->template_id) ? $tema : $link->template->nome_fisico;
                    $layout_content = file_get_contents(ROOT.'/templates/'.$tema.'/layout.html');
                    
                    // verificamos se existem formulários na página à serem substituidos
                    $formularios = Formulario::getFormulários();
                    foreach ($formularios as $form)
                    {
                        $variavel = '{'.$form->url.'}';
                        if ( strpos($layout_content, $variavel) !== false )
                        {
                            $layout_content = str_replace($variavel, $form->html_site, $layout_content);
                            
                            // pegamos o modal
                            $modal = file_get_contents(ROOT.'/templates/'.$replaces['theme'].'/partials/modal.html');
                            
                            //$this->_replaces['scripts_head'] .= '';
                            $replaces['scripts_body'] .= $form->script . $modal;
                        }
                    }
                    
                    // rederiza a página
                    echo $parse->parse_string($layout_content, $replaces);
                }
                else
                {
                    require_once 'Sitemap.php';
                    
                    // pode ser um sitemap
                    $sitemap = Sitemap::getByURL($this->_url);
                    if ( $sitemap )
                    {
                        // preparando cabeçalho
                        header("Content-Type: text/xml;charset=iso-8859-1");
                        echo $sitemap;
                    }
                    else
                    {
                        
                        switch ($this->_explode[0])
                        {
                            case 'click':
                                // registra um clique
                                Click::registrar();
                                break;
                            case 'busca':
                                $pagina = new SiteRender($this->_url);
                                $replaces = $pagina->renderBusca();
                                
                                $tema = empty($link->template_id) ? $tema : $link->template->nome_fisico;
                                $layout_content = file_get_contents(ROOT.'/templates/'.$tema.'/layout.html');
                                
                                // rederiza a página
                                echo $parse->parse_string($layout_content, $replaces);
                                break;
                            case 'enviar':
                                if (isset($_POST))
                                {
                                    header("content-type:application/json");
                                    
                                    // faz o 'post' de um formulário e envia o e-mail
                                    //$ret = FormMensagem::Enviar($this->_explode[1]);
                                    //var_dump($ret);
                                    // retorna a resposta ao site
                                    echo json_encode(FormMensagem::Enviar($this->_explode[1])); //['titulo'=>'Esse é o Título', 'mensagem'=>'Essa é a mensagem!']);
                                }
                                break;
                            default :
                                // Criamos uma exceção
                                throw new Exception("Página não encontrada!");
                                break;
                        }
                    }
                }
            }

            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            //new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
            
            require_once 'HtmlBase.php';
            $replaces = HtmlBase::getStaticReplaces($this->_url);
            
            // carrega uma página de erro 404
            $layout_content = file_get_contents(ROOT.'/templates/'.$tema.'/404.html');
            $replaces['message_erro'] = $e->getMessage();
            
            // rederiza a página de erro
            header("HTTP/1.0 404 Not Found");
            //http_response_code(404);
            echo $parse->parse_string($layout_content, $replaces);
        }
    }
    
    
}

<?php
/**
 * HtmlBase
 *
 * @version     1.0
 * @package     lib
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 (https://www.infort.eti.br)
 *
 */
class HtmlBase
{
    protected $_pref;
    protected $_replaces;
    protected $_url;
    
    /**
     * Monta o array de substituições
     * @param $url    string com a url da página
     */
    public function setReplaces($url=null)
    {
        $this->_pref = THelper::getPreferences();
        $this->_url  = empty($url) ? '/' : $url;

        try
        {
            TTransaction::open('sistema');
            
            $template = Template::where('padrao','=','t')->first();
            
            // site
            $replaces['root']     = $this->_pref['pref_site_dominio'];
            $replaces['url']      = $this->_url;
            $replaces['theme']    = $template->nome_fisico; //$template[0]->nome_fisico;
            $replaces['nome']     = $this->_pref['pref_site_nome'];
            $replaces['language'] = $this->_pref['pref_site_language'];
            
            // social
            $replaces['social']         = Midia::getArrayMidias();
            $replaces['social_buttons'] = file_get_contents(ROOT.'/templates/'.$replaces['theme'].'/partials/social_buttons.html');
            
            $replaces['instagram_token']  = $this->_pref['pref_instagram_token'];
            $replaces['instagram_userid'] = $this->_pref['pref_instagram_userid'];
            
            
            // dados de contato
            $replaces['contato_nome']     = $this->_pref['pref_emp_nome'];
            $replaces['contato_email']    = $this->_pref['pref_emp_email'];
            $replaces['contato_telefone'] = $this->_pref['pref_emp_fone'];
            $replaces['contato_celular']  = $this->_pref['pref_emp_celular'];
            $replaces['contato_whatsapp'] = $this->_pref['pref_emp_whatsapp'];
            $replaces['contato_endereco'] = $this->_pref['pref_emp_endereco'];
            $replaces['contato_cidade']   = $this->_pref['pref_emp_cidade'];
            $replaces['contato_estado']   = $this->_pref['pref_emp_estado'];
            $replaces['contato_pais']     = $this->_pref['pref_emp_pais'];
            $replaces['contato_postal']   = $this->_pref['pref_emp_postal'];
            $replaces['contato_cnpj']     = $this->_pref['pref_emp_cnpj'];
            
            // página
            $replaces['alerta']       = '';
            $replaces['title']        = $this->_pref['pref_site_nome'];
            $replaces['titulo']       = $this->_pref['pref_site_nome'];
            $replaces['pagina']       = '';
            $replaces['meta_tags']    = '';
            $replaces['header_class'] = '';
            $replaces['buscar-action']  = $this->_pref['pref_site_dominio'].$this->_url;
            
            
            // parseando os scripts da template
            $parse = new TParser;
            $replaces['social_buttons'] = $parse->parse_string( $replaces['social_buttons'], $replaces );
            $replaces['scripts_head']   = $parse->parse_string( $template->script_head, $replaces );
            $replaces['scripts_body']   = $parse->parse_string( $template->script_body, $replaces );
            
            // injetando script para captura dos clicks
            $replaces['scripts_body']   .= "<script type=\"text/javascript\">window.onload = function() {jQuery('body').on('click','a', function(){var atual = window.location.href; jQuery.post('".$this->_pref['pref_site_dominio']."/click',{ref:this.href,url:atual});});};</script>";
            
            // outros
            $replaces['&nbsp;'] = ' ';
            
            $this->_replaces = $replaces;
            
            // criando o menu do site
            $this->renderMenu();
            
            // renderiando as posições da template
            $this->renderPosicoes($template->getPosicoes());
            
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            return FALSE;
            // shows the exception error message
            //new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * Retorna o array de substituições
     */
    public function getReplaces()
    {
        if ( empty($this->_replaces) )
        {
            $this->setReplaces();
        }
        return $this->_replaces;
    }
    
    /**
     * Retorna o array de substituições estaticamente
     */
    public static function getStaticReplaces($url)
    {
        $base = new HtmlBase;
        $base->setReplaces($url);
        return $base->getReplaces();
    }
    
    /**
     * Retorna um array com a galeria de imagens do um artigo
     */
    public function getGaleria()
    {
        //$obj = 
    }
    
    /**
     * Renderiza o menu
     */
    public function renderMenu()
    {
        // carregando a classe de funções da template
        require_once '../templates/'.$this->_replaces['theme'].'/TemplateFunctions.php';
        $ret = TemplateFunctions::renderMenu($this->_pref['pref_site_dominio'].'/',$this->_url);
        
        if (is_array($ret))
        {
            $this->_replaces['menu']        = $ret[0];
            $this->_replaces['menu_mobile'] = $ret[1];
        }
        else
        {
            $this->_replaces['menu'] = $ret;
        }
    }
    
    /**
     * Renderiza todas as posições da template
     */
    public function renderPosicoes($posicoes)
    {
        if (is_array($posicoes) && !empty($posicoes))
        {
            $parse = new TParser;
            foreach( $posicoes as $posicao )
            {
                $html = ($posicao->ativo == 't') ? Modulo::getHTMLbyPosition($posicao->nome) : null;
                $this->_replaces['$'.$posicao->nome] = $parse->parse_string( $html, $this->_replaces );
            }
        }
    }
    
    /**
     * Monta as meta-tags da página
     */
    public function setMetaTags($object)
    {
        // preparando dados do geo
        if (empty($object->country_iso))
        {
            // pegamos os dados da região baseado no endereço da empresa
            $object->country_iso = 'BR';
            $object->region_iso  = empty($this->_pref['pref_emp_estado']) ? '' : $this->_pref['pref_emp_estado'];
            $object->placename   = empty($this->_pref['pref_emp_cidade']) ? '' : $this->_pref['pref_emp_cidade'];
            $object->geo_lat     = empty($this->_pref['pref_emp_geolat']) ? '' : $this->_pref['pref_emp_geolat'];
            $object->geo_lng     = empty($this->_pref['pref_emp_geolong']) ? '' : $this->_pref['pref_emp_geolong'];
        }
        
        $url = ($this->_url != '/') ? $this->_url : '';

        $tags  = '<meta name="language" content="'.$this->_pref['pref_site_language'].'" />';
        $tags .= '<meta name="author" content="'.$this->_pref['pref_site_nome'].'" />';
        $tags .= '<meta name="creator" content="'.$this->_pref['pref_site_nome'].'" />';
		$tags .= '<meta name="url" content="'.$this->_pref['pref_site_dominio'].$url.'" />';
        
        // SEO
        $tags .= '<meta name="description" content="'.$object->metadesc.'" />';
		$tags .= '<meta name="keywords" content="'.$this->_pref['pref_site_keywords'].','.$object->metakey.'" />';
		$tags .= '<meta name="robots" content="index, follow" />';
		$tags .= '<meta name="revisit-after" content="7 days">';
		$tags .= '<meta name="distribution" content="web">';
		
		$geo_region = ($object->region_iso == '') ? $object->country_iso.'-'.$object->region_iso : $object->country_iso;
		
		// Meta Tags geo localização
        $tags .= '<meta name="geo.region" content="'.$object->country_iso.'-'.$object->region_iso.'" />';
		$tags .= '<meta name="geo.placename" content="'.$object->placename.'" />';
		$tags .= '<meta name="geo.position" content="'.$object->geo_lat.';'.$object->geo_lng.'" />';
		$tags .= '<meta name="ICBM" content="'.$object->geo_lat.';'.$object->geo_lng.'" />';
		
		// Outras
		$tags .= '<meta name="classification" content="'.$object->metadesc.'" />';
		$tags .= '<meta name="rating" content="general" />';
		$tags .= '<meta name="fone" content="'.$this->_pref['pref_emp_fone'].'"/>';
		$tags .= '<meta name="city" content="'.$this->_pref['pref_emp_cidade'].'"/>';
		$tags .= '<meta name="country" content="'.$this->_pref['pref_emp_pais'].'"/>';
		$tags .= '<meta name="keyphrases" content="'.$this->_pref['pref_site_nome'].'" />';
		$tags .= '<meta name="copyright" content="Copyright &copy; '.$this->_pref['pref_site_nome'].'" />';
		$tags .= '<meta http-equiv="imagetoolbar" content="no" />';
		$tags .= '<meta name="MSSmartTagsPreventParsing" content="true" />';
		
		//<!-- Open Graph data -->
		$tags .= '<meta name="og:locale" content="'.$this->_pref['pref_site_language'].'" />';
		$tags .= '<meta name="og:region" content="'.$this->_pref['pref_emp_pais'].'" />';
		$tags .= '<meta name="og:title" content="'.$object->titulo.'" />';
		$tags .= '<meta name="og:type" content="article" />';
		$tags .= '<meta name="og:image" content="'.$this->_pref['pref_site_dominio'].'/images/favicon.png" />';
		$tags .= '<meta name="og:url" content="'.$this->_pref['pref_site_dominio'].$url.'" />';
		$tags .= '<meta name="og:description" content="'.$object->metadesc.'" />';
		$tags .= '<meta name="og:site_name" content="'.$this->_pref['pref_site_nome'].'" />';
		
		//<!-- Twitter Card data -->
		$tags .= '<meta name="twitter:card" content="summary" />';
		//$tags .= '<meta name="twitter:site" content="@creativetim">';
		$tags .= '<meta name="twitter:title" content="'.$object->titulo.'" />';
		$tags .= '<meta name="twitter:description" content="'.$object->metadesc.'" />';
		//$tags .= '<meta name="twitter:creator" content="@creativetim">';
		$tags .= '<meta name="twitter:image" content="'.$this->_pref['pref_site_dominio'].'/images/favicon.png">';
		$tags .= '<meta name="twitter:url" content="'.$this->_pref['pref_site_dominio'].$url.'"/>';
		
		// Meta Tags para cache ( tags descontinuadas - sem efeito )
		//$tags .= '<meta http-equiv="cache-control" content="public" />';
		//$tags .= '<meta http-equiv="pragma" content="public" />';
		
		// canonical
		$tags .= '<link rel="canonical" href="'.$this->_pref['pref_site_dominio'].$url.'" />';
		
		/*
		$tags .= '<meta http-equiv="content-language" content="pt-br" />';
		$tags .= '<meta name="designer" content="inFORT" />';
		$tags .= '<meta name="publisher" content="inFORT" />';
		$tags .= '<meta http-equiv="refresh" content="30">';
		*/
		
        return $tags;
    }
    
    
    
}

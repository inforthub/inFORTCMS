<?php
/**
 * Dashboard
 *
 * @version    1.0
 * @package    control
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 */
class Dashboard extends TPage
{
    /**
     * Class constructor
     * Creates the page
     */
    function __construct()
    {
        parent::__construct();
        
        // pegando as estatísticas do site
        $stats = new TStats;
        
        $titulo = TElement::tag('div','<h2>DASHBOARD</h2>',['class'=>'block-header']);
        
        // Criando InfoBox
        $info1 = $this->TInfoBox('Site',$stats->get_stats('Site'),'fas:tv','info-box-3 bg-red hover-zoom-effect');
        $info2 = $this->TInfoBox('Blog',$stats->get_stats('Blog'),'fas:book','info-box-3 bg-teal hover-zoom-effect');
        $info3 = $this->TInfoBox('Artigos',$stats->get_stats('News'),'far:file-alt','info-box-3 bg-light-blue hover-zoom-effect');
        $info4 = $this->TInfoBox('Total de Páginas',$stats->get_stats('All'),'fas:globe-americas','info-box-3 bg-cyan hover-zoom-effect');
        $tag1 = TElement::tag('div', $info1, ['class'=>'col-lg-3 col-md-3 col-sm-6 col-xs-12']);
        $tag2 = TElement::tag('div', $info2, ['class'=>'col-lg-3 col-md-3 col-sm-6 col-xs-12']);
        $tag3 = TElement::tag('div', $info3, ['class'=>'col-lg-3 col-md-3 col-sm-6 col-xs-12']);
        $tag4 = TElement::tag('div', $info4, ['class'=>'col-lg-3 col-md-3 col-sm-6 col-xs-12']);
        $infobox  = TElement::tag('div',[$tag1,$tag2,$tag3,$tag4],['class'=>'row']);
        
        // criando um container com os botões
        $vbox = new TElement('div');
        $vbox->class = 'row clearfix';
        
        
        // pegamos os nível de acesso do usuário e filtramos os botões
        $acesso = TSession::getValue('usergroupids');
        
        /*****************************
         * 1- Site
         * 2- Blog
         * 3- News
         *****************************/
        
        if ( in_array('1',$acesso) )
        {
            // botões do Site
            $site = TElement::tag('div', TElement::tag('div','<h2>SITE</h2>',['class'=>'header']), ['class'=>'card']);
            $body = new TElement('div');
            $body->class = 'body text-center';
            $body->add( $this->btnAPP('Menu',['MenuList','onReload'], 'fas:bars') );
            $body->add( $this->btnAPP('Páginas',['PaginaList','onReload'], 'far:file-code') );
            $body->add( $this->btnAPP('Módulos',['ModuloList','onReload'], 'fas:cubes') );
            $body->add( $this->btnAPP('Limpar Cache',[$this,'limparCache'], 'fas:database') );
            $site->add($body);
            
            $vbox->add( TElement::tag('div',$site,['class'=>'col-xs-12 col-sm-6 col-md-6 col-lg-6']) );
        }
        
        if ( in_array('2',$acesso) )
        {
            // botões do Blog
            $blog = TElement::tag('div', TElement::tag('div','<h2>BLOG</h2>',['class'=>'header']), ['class'=>'card']);
            $body = new TElement('div');
            $body->class = 'body text-center';
            $body->add( $this->btnAPP('Categorias',['BlogCategoriaList','onReload'], 'far:bookmark') );
            $body->add( $this->btnAPP('Posts',['BlogPostList','onReload'], 'far:file-alt') );
            $blog->add($body);
            
            $vbox->add( TElement::tag('div',$blog,['class'=>'col-xs-12 col-sm-6 col-md-6 col-lg-6']) );
        }
        /*
        if ( in_array('3',$acesso) )
        {
            // botões do News
            $blog = TElement::tag('div', TElement::tag('div','<h2>Notícias</h2>',['class'=>'header']), ['class'=>'card']);
            $body = new TElement('div');
            $body->class = 'body text-center';
            $body->add( $this->btnAPP('Artigos',['NewsList','onReload'], 'far:file-alt') );
            $body->add( $this->btnAPP('Categorias',['NewsCategoriaList','onReload'], 'far:bookmark') );
            $body->add( $this->btnAPP('Região',['CidadeList','onReload'], 'fas:city') );
            $blog->add($body);
            
            $vbox->add( TElement::tag('div',$blog,['class'=>'col-xs-12 col-sm-6 col-md-6 col-lg-6']) );
        }
        */
        
        // limpando toda a sessão
        THelper::clearSession();
        
        // adicionando os paineis
        parent::add($titulo);
        parent::add($infobox);
        parent::add($vbox);
    }
    
    /**
     * Método para criar um Infobox (ADMINBSB - Material Design)
     * @icon    $icon string (fa: bs: mi:)
     * @class   $efect string
     */
    private function TInfoBox($text,$val,$icon,$class = 'info-box-3 bg-red',$number=false)
    {
        $div = new TElement('div');
        $div->class = $class;
        
        $icon = TElement::tag('div',new TImage($icon),['class'=>'icon']);
        
        $number = TElement::tag('div',$val,['class'=>'number']);
        if ($number)
        {
            $number->class                   = 'number count-to';
            $number->{'data-from'}           = '0';
            $number->{'data-to'}             = $val;
            $number->{'data-speed'}          = '1000';
            $number->{'data-fresh-interval'} = '5';
        }
        
        $content = new TElement('div');
        $content->class = 'content';
        $content->add(TElement::tag('div',$text,['class'=>'text']));
        $content->add($number);
        
        $div->add($icon);
        $div->add($content);
        
        return $div;
    }
    
    /**
     * Cria um botão estilo APP
     */
    private function btnAPP($value, $action, $icon)
    {
        return THelper::TAppLink($value,  new TAction( $action ), $icon, 'btn btn-app waves-effect');
    }
    
    /**
     * Método para limpar o Cache do site
     */
    public function limparCache()
    {
        try
        {
            THelper::apagarTudo('../cache');
            new TMessage('info', 'Cache limpo com sucesso!');
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
        }
    }
    
    public function onClear(){}
    
}

<?php
/**
 * Dashboard
 *
 * @version    1.0
 * @package    control
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class Dashboard extends TPage
{
    /**
     * Class constructor
     * Creates the page
     */
    public function __construct($param)
    {
        parent::__construct();
        
        // pegando as estatísticas do site
        $stats = new TStats;
        
        $titulo = TElement::tag('div','<h2>DASHBOARD</h2>',['class'=>'block-header']);
        
        // Criando InfoBox
        $info1 = $this->TInfoBox('Páginas de Site',$stats->get_stats('Site'),'fas:tv','info-box-3 bg-red hover-zoom-effect');
        $info2 = $this->TInfoBox('Páginas de Blog',$stats->get_stats('Blog'),'fas:book','info-box-3 bg-teal hover-zoom-effect');
        $info3 = $this->TInfoBox('Midias Ativas',$stats->get_stats('Midias'),'fas:share-alt','info-box-3 bg-light-blue hover-zoom-effect');
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
         *****************************/
        
        if ( in_array('1',$acesso) )
        {
            // botões do Site
            $site = TElement::tag('div', TElement::tag('div','<h2>SITE</h2>',['class'=>'header']), ['class'=>'card']);
            $body = new TElement('div');
            $body->class = 'body text-center';
            $body->add( $this->btnAPP('Menu',['MenuList','onReload'], 'fas:bars') );
            $body->add( $this->btnAPP('Páginas',['PaginaList','onReload'], 'far:file-code') );
            $body->add( $this->btnAPP('Categorias',['CategoriaList','onReload'], 'far:bookmark') );
            $body->add( $this->btnAPP('Limpar Cache',[$this,'limparCache'], 'fas:database') );
            $site->add($body);
            
            $vbox->add( TElement::tag('div',$site,['class'=>'col-sm-6']) );
        }
        
        if ( in_array('2',$acesso) )
        {
            // botões do Blog
            $blog = TElement::tag('div', TElement::tag('div','<h2>BLOG</h2>',['class'=>'header']), ['class'=>'card']);
            $body = new TElement('div');
            $body->class = 'body text-center';
            $body->add( $this->btnAPP('Posts',['BlogPostList','onReload'], 'far:file-alt') );
            $body->add( $this->btnAPP('Categorias',['BlogCategoriaList','onReload'], 'far:bookmark') );
            $blog->add($body);
            
            $vbox->add( TElement::tag('div',$blog,['class'=>'col-sm-6']) );
        }
        
        /*
            $param['periodo'] = '-30 days';
            $param['limit']   = '20';
        */
        
        // carregando estatísticas gráficas
        
        $graficos = new TElement('div');
        $graficos->class = 'row clearfix';
        
        // visualizações diárias (30 dias)
        $div = TElement::tag('div', TElement::tag('div','<h2>Visualizações diárias (30 dias)</h2>',['class'=>'header']), ['class'=>'card']);
        $div->add( new TrafegoDiaView($param) );
        $graficos->add(TElement::tag('div',$div,['class'=>'col-sm-12']));
        
        // visualizações por hora (30 dias)
        $div = TElement::tag('div', TElement::tag('div','<h2>Visualizações por hora (30 dias)</h2>',['class'=>'header']), ['class'=>'card']);
        $div->add( new TrafegoHoraView($param) );
        $graficos->add(TElement::tag('div',$div,['class'=>'col-sm-12']));
        
        // totais
        $graficos->add( TElement::tag('div', new TrafegoTotaisView(false),['class'=>'col-sm-12']) );
        
        // tráfego por plataformas
        $div = TElement::tag('div', TElement::tag('div','<h2>Tráfego por Plataformas (30 dias)</h2>',['class'=>'header']), ['class'=>'card']);
        $div->add( new TrafegoPlataformaView($param) );
        $graficos->add(TElement::tag('div',$div,['class'=>'col-sm-6']));
        
        // tráfego por navegadores
        $div = TElement::tag('div', TElement::tag('div','<h2>Tráfego por Navegadores (30 dias)</h2>',['class'=>'header']), ['class'=>'card']);
        $div->add( new TrafegoNavegadorView($param) );
        $graficos->add(TElement::tag('div',$div,['class'=>'col-sm-6']));
        
        
        
        
        // carregando listagens
        
        $listagem = new TElement('div');
        $listagem->class = 'row clearfix';
        
        $listagem->add( TElement::tag('div', new TrafegoPaginaView($param),['class'=>'col-md-6']) );
        $listagem->add( TElement::tag('div', new TrafegoReferenciaView($param),['class'=>'col-md-6']) );
        
        if ( !empty(THelper::getPreferences('pref_site_trafego')) )
        {
            $listagem->add( TElement::tag('div', new TrafegoCidadeView($param),['class'=>'col-md-6 col-lg-4']) );
            $listagem->add( TElement::tag('div', new TrafegoRegiaoView($param),['class'=>'col-md-6 col-lg-4']) );
            $listagem->add( TElement::tag('div', new TrafegoPaisView($param),['class'=>'col-md-6 col-lg-4']) );
        }
        
        // limpando toda a sessão
        THelper::clearSession();
        
        // adicionando os paineis
        parent::add($titulo);
        parent::add($infobox);
        parent::add($vbox);
        parent::add($graficos);
        parent::add($listagem);
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
            THelper::apagarTudo('../cache',['.htaccess']);
            new TMessage('info', 'Cache limpo com sucesso!');
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
        }
    }
    
    public function onClear(){}
    
}

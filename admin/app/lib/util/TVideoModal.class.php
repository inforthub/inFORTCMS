<?php

/**
 * Class TVideoModal - Cria um modal para exibição de Vídeos
 *
 * @version    1.0
 * @package    util
 * @subpackage lib
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class TVideoModal extends TElement
{
    private $id;
    
    /**
     * Class Constructor
     * @param $id_video ID do vídeo a ser exibido
     */
    public function __construct($id_video)
    {
        // criando iframe com o ID do video
        $iframe = new TElement('iframe');
        $iframe->id = "iframe_external";
        $iframe->src = "https://www.youtube.com/embed/{$id_video}"; //"https://www.youtube.com/embed/Y2ofxrCo6QA";
        $iframe->frameborder = "0";
        //$iframe->scrolling = "yes";
        //$iframe->width = "560";
        //$iframe->height = "315";
        $iframe->allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture";
        $iframe->allowfullscreen;
        
        // criando o modal
        $modal_body = TElement::tag('div', $iframe, ['class'=>'modal-body']);
        
        $close = TElement::tag('button','x',['type'=>'button','class'=>'close','data-dismiss'=>'modal','aria-hidden'=>'true']);
        $modal_header = TElement::tag('div',$close,['class'=>'modal-header']);
        
        $modal_content = TElement::tag('div',[$modal_header,$modal_body],['class'=>'modal-content']);
        $modal_content->style = 'background: none; border: 0; -moz-border-radius: 0; -webkit-border-radius: 0; border-radius: 0; -moz-box-shadow: none; -webkit-box-shadow: none; box-shadow: none !important;'; 'background:none; box-shadow:none !important;';

        $modal_dialog = TElement::tag('div',$modal_content,['class'=>'modal-dialog']);
        
        $this->id = 'tvideo_'.mt_rand(1000000000, 1999999999);
        
        $modal_wrapper = new TElement('div');
        $modal_wrapper->{'class'} = 'modal video-modal';
        $modal_wrapper->{'id'} = $this->id;
        $modal_wrapper->{'tabindex'} = '-1';
        $modal_wrapper->add($modal_dialog);
        
        // renderizando modal em tela
        $modal_wrapper->show();
        TScript::create( "tdialog_start( '#{$this->id}');");
    }
}

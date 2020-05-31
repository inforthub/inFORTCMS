<?php

use Adianti\Core\AdiantiCoreTranslator;
use Adianti\Control\TAction;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Util\TImage;

/**
 * Modal de conteúdo livre
 *
 * @version    1.0
 * @package    util
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2014-2019 inFORT Ltd. (https://www.infort.eti.br)
 */
class TModal extends TElement
{
    private $id;
    private $action;
    
    /**
     * Class Constructor
     * @param $type    Type of the message (default, primary, info, warning, success, danger)
     * @param $message Message to be shown
     * @param $action  Action to be processed when closing the dialog
     * @param $title_msg  Dialog Title
     * @param $cor_header
     * @param $cor_footer
     * @param $modal_lg
     */
    public function __construct($type, $message, TAction $action = NULL, $title_msg = '', $cor_header = '', $cor_footer = '', $modal_lg = FALSE)
    {
        $this->id = 'tmessage_'.mt_rand(1000000000, 1999999999);
        
        $modal_wrapper = new TElement('div');
        $class_buttons = 'btn ';
        switch ($type)
        {
            case 'primary':
                $modal_wrapper->{'class'} = 'modal modal-primary'; $class_buttons .= 'btn-primary'; break;
            case 'info':
                $modal_wrapper->{'class'} = 'modal modal-info'; $class_buttons .= 'btn-info'; break;
            case 'warning':
                $modal_wrapper->{'class'} = 'modal modal-warning'; $class_buttons .= 'btn-warning'; break;
            case 'success':
                $modal_wrapper->{'class'} = 'modal modal-success'; $class_buttons .= 'btn-success'; break;
            case 'danger':
                $modal_wrapper->{'class'} = 'modal modal-danger'; $class_buttons .= 'btn-danger'; break;
            default:
                $modal_wrapper->{'class'} = 'modal'; $class_buttons .= 'btn-default'; break;
        }
        
        //$modal_wrapper->{'class'} = 'modal';
        $modal_wrapper->{'id'}    = $this->id;
        $modal_wrapper->{'tabindex'} = '-1';
        
        $modal_dialog = new TElement('div');
        $modal_dialog->{'class'} = ($modal_lg) ? 'modal-dialog modal-lg' : 'modal-dialog';
        
        $modal_content = new TElement('div');
        $modal_content->{'class'} = 'modal-content';
        
        $modal_header = new TElement('div');
        $modal_header->{'class'} = 'modal-header '.$cor_header;
        
        $close = new TElement('button');
        $close->{'type'} = 'button';
        $close->{'class'} = 'close';
        $close->{'data-dismiss'} = 'modal';
        $close->{'aria-hidden'} = 'true';
        $close->add('×');
        
        $title = new TElement('h4');
        $title->{'class'} = 'modal-title';
        $title->{'style'} = 'display:inline';
        $title->add( $title_msg );
        
        $body = new TElement('div');
        $body->{'style'} = 'text-align:left';
        $body->{'class'} = 'modal-body';
        
        $span = new TElement('span');
        $span->add($message);
        
        $body->add($span);
        $button = new TElement('button');
        $button->{'class'} = $class_buttons;
        $button->{'data-dismiss'} = 'modal';
        $button->{'onclick'} = "\$( '.modal-backdrop' ).last().remove(); \$('#{$this->id}').modal('hide'); \$('body').removeClass('modal-open');";
        $button->add('<i class="fa fa-times fa-fw"></i> Fechar');
        
        if ($action)
        {
            $button->{'onclick'} .= "__adianti_load_page('{$action->serialize()}');";
            $button->{'data-toggle'} = "modal";
        }
        
        $footer = new TElement('div');
        $footer->{'class'} = 'modal-footer '.$cor_footer;
        
        $modal_wrapper->add($modal_dialog);
        $modal_dialog->add($modal_content);
        $modal_content->add($modal_header);
        $modal_header->add($close);
        $modal_header->add($title);
        
        $modal_content->add($body);
        $modal_content->add($footer);
        
        $footer->add($button);
        
        $modal_wrapper->show();
        $callback = 'function () {' . $button->{'onclick'} .'}';
        TScript::create( "tdialog_start( '#{$this->id}', $callback );");
    }
}

<?php
/**
 * Posicao Active Record
 *
 * @version    1.0
 * @package    model
 * @subpackage configuracoes
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 */
class Posicao extends TRecord
{
    const TABLENAME = 'posicao';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('ativo');
        parent::addAttribute('template_id');
    }
    
    /**
     * Method get_template
     * Sample of usage: $link->template->attribute;
     * @returns Template instance
     */
    public function get_template()
    {
        // returns the associated object
        return Template::find($this->template_id);
    }


}

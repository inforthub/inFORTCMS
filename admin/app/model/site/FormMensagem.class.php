<?php
/**
 * FormMensagem Active Record
 *
 * @version    1.0
 * @package    model
 * @subpackage site
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class FormMensagem extends TRecord
{
    const TABLENAME = 'form_mensagem';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $formulario;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('assunto');
        parent::addAttribute('mensagem');
        parent::addAttribute('email_origem');
        parent::addAttribute('email_destino');
        parent::addAttribute('dt_mensagem');
        parent::addAttribute('enviada');
        parent::addAttribute('formulario_id');
    }


    /**
     * Method set_formulario
     * Sample of usage: $form_mensagem->formulario = $object;
     * @param $object Instance of Formulario
     */
    public function set_formulario(Formulario $object)
    {
        $this->formulario = $object;
        $this->formulario_id = $object->id;
    }
    
    /**
     * Method get_formulario
     * Sample of usage: $form_mensagem->formulario->attribute;
     * @returns Formulario instance
     */
    public function get_formulario()
    {
        // loads the associated object
        if (empty($this->formulario))
            $this->formulario = new Formulario($this->formulario_id);
    
        // returns the associated object
        return $this->formulario;
    }



}

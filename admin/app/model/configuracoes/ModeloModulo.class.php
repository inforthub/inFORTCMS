<?php
/**
 * ModeloModulo Active Record
 * @author  <your-name-here>
 */
class ModeloModulo extends TRecord
{
    const TABLENAME = 'modelo_modulo';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $formulario;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('parametros');
        parent::addAttribute('html');
    }
    


}

<?php
/**
 * SystemModules Active Record
 * @author  <your-name-here>
 */
class SystemModules extends TRecord
{
    const TABLENAME = 'system_modules';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        //parent::addAttribute('metodo_render');
        parent::addAttribute('ativo');
        //parent::addAttribute('sql');
    }
    

}

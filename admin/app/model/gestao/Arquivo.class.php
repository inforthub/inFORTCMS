<?php
/**
 * Arquivos Active Record
 *
 * @version    1.0
 * @package    model
 * @subpackage gestao
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 */
class Arquivo extends TRecord
{
    const TABLENAME = 'arquivo';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('nome_web');
        parent::addAttribute('descricao');
        parent::addAttribute('formato');
        parent::addAttribute('dt_cadastro');
        parent::addAttribute('destaque');
        parent::addAttribute('artigo_id');
    }
    
    /** 
     * Método executado após uma ação de Delete
     *
    public function onAfterDelete($object)
    {
        if( file_exists ($object->nome))// se existir
        {
            unlink( $object->nome ); //apaga
        }
        
        if( file_exists ($object->nome_web))// se existir
        {
            unlink( $object->nome_web ); //apaga
        }
    }
    */

}

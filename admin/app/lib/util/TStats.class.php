<?php
/**
 * TStats
 *
 * @version    1.0
 * @package    util
 * @subpackage lib
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class TStats
{
    private $stats;
    
    /**
     * Método construtor
     */
    public function __construct()
    {
        try
        {
            TTransaction::open('sistema');

            // contando os links baseados nos módulos ativos
            $tipo = Tipo::where('ativo','=','t')->load();
            foreach ($tipo as $module)
            {
                $this->stats[$module->nome] = Link::where('tipo_id','=',$module->id)->count();
            }
            
            // pegando tudo
            $this->stats['All'] = Link::countObjects();
            $this->stats['Midias'] = Midia::where('ativo','=','t')->count();
            
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            return FALSE;
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * Método que retorna todo o Status
     */
    public function get_stats($nome=null)
    {
        if (!is_null($nome))
            return isset($this->stats[$nome]) ? $this->stats[$nome] : 0;
        
        return $this->stats;
    }
    


    
}

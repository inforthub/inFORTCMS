<?php
/**
 * TStats
 *
 * @version    1.0
 * @package    util
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT Ltd. (https://www.infort.eti.br)
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
            //TTransaction::setLogger(new TLoggerSTD); // standard output
            /*
            $this->stats['site']   = Link::where('tipo','=',1)->count();
            $this->stats['blog']   = Link::where('tipo','=',2)->count();
            $this->stats['artigo'] = Link::where('tipo','=',3)->where('destino','like','GeoConteudo%')->count();
            $this->stats['geo']    = Link::where('tipo','=',3)->count(); 
                                   //Link::where('tipo','=',3)->where('destino','like','GeoLocal%')->count();
            */
            // contando os links baseados nos módulos ativos
            $tipo = Tipo::where('ativo','=','t')->load();
            foreach ($tipo as $module)
            {
                $this->stats[$module->nome] = Link::where('tipo_id','=',$module->id)->count();
            }
            
            // pegando tudo
            $this->stats['All'] = Link::countObjects();
            
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

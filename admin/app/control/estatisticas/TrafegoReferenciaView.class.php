<?php
/**
 * TrafegoReferenciaView Chart
 *
 * @version     1.0
 * @package     control
 * @subpackage  estatisticas
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class TrafegoReferenciaView extends TPage
{
    private $paginas_list;
    
    /**
     * Class constructor
     * Creates the page
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        try
        {
            TTransaction::open('sistema');
            
            // recebemos os dados
            $periodo = empty($param['periodo']) ? '-30 days' : $param['periodo'];
            $limit   = empty($param['limit']) ? '10' : $param['limit'];
            
            // carregamos os dados
            $paginas = Trafego::porCampo('referencia',$periodo,$limit);

            // criando listagem
            $paginas_list = new BootstrapDatagridWrapper( new TDataGrid );
            $paginas_list->style = 'width:100%';
            $paginas_list->disableDefaultClick();
            
            $paginas_list->addColumn( new TDataGridColumn('referencia', 'Referência', 'left') );
            $paginas_list->addColumn( $t=new TDataGridColumn('views', 'Acessos', 'right') );
            
            $t->setTransformer( function($value) {
                return number_format($value,0,',','.');
            });
            
            $paginas_list->createModel();
            
            
            $paginas_list->addItems($paginas);

            $panel = new TPanelGroup('Referências - TOP '.$limit, '#f5f5f5');
            $panel->add($paginas_list);
            
            
            TTransaction::close();

            parent::add($panel);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}
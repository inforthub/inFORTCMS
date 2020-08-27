<?php
/**
 * TrafegoNavegadorView Chart
 *
 * @version     1.0
 * @package     control
 * @subpackage  estatisticas
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class TrafegoNavegadorView extends TPage
{
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
            
            $html = new THtmlRenderer('app/resources/google_bar_chart.html');
            
            // recebemos os dados
            $dias    = empty($param['periodo']) ? '-30 days' : $param['periodo'];
            $periodo = date("Y-m-d H:i:s", strtotime($dias));
            
            // carregamos os dados
            $obj   = Trafego::porCampo('navegador',$periodo,'3');

            // total do período sem o limit
            $total = Trafego::where('dt_acesso','>=',$periodo)->count();

            $data   = array();
            $data[0][] = 'Navegador';
            $data[1][] = '';
            
            $resto = $total;
            foreach ( $obj as $trafego )
            {
                $data[0][] = $trafego->navegador;
                $data[1][] = intval($trafego->views);

                $resto  = $resto - $trafego->views;
            }
            
            $data[0][] = 'Outros';
            $data[1][] = intval($resto);
            
            // replace the main section variables
            $html->enableSection('main', array('data'   => json_encode($data),
                                               'width'  => '100%',
                                               'height' => '300px',
                                               'poslegend' => 'top',
                                               'title'  => '',
                                               'ytitle' => 'Navegadores', 
                                               'xtitle' => 'Acessos',
                                               'uniqid' => uniqid()));
            
            TTransaction::close();

            parent::add($html);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}

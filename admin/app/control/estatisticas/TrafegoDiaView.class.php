<?php
/**
 * TrafegoDiaView Chart
 *
 * @version     1.0
 * @package     control
 * @subpackage  estatisticas
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class TrafegoDiaView extends TPage
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
            
            $html = new THtmlRenderer('app/resources/google_area_chart.html');
            
            // recebemos os dados
            $dias    = empty($param['periodo']) ? '-30 days' : $param['periodo'];
            $periodo = date("Y-m-d H:i:s", strtotime($dias));
            
            $conn  = TTransaction::get(); // obtém a conexão
            
            $obj   = $conn->query("SELECT DATE_FORMAT(dt_acesso,'%d/%m') as dia, COUNT(id) as views FROM trafego WHERE (dt_acesso >= '".$periodo."') GROUP BY dia ORDER BY dia asc");
            $users = $conn->query("SELECT DATE_FORMAT(dt_acesso,'%d/%m') as dia, COUNT(DISTINCT ip) as views FROM trafego WHERE (dt_acesso >= '".$periodo."') GROUP BY dia ORDER BY dia asc");

            $users = $users->fetchALL();
            
            $data   = array();
            $data[] = [ 'Data', 'Acessos', 'Usuários' ];
            
            foreach ( $obj as $key=>$trafego )
            {
                $data[] = [ $trafego['dia'], intval($trafego['views']), intval($users[$key]['views']) ];
            }

            // replace the main section variables
            $html->enableSection('main', array('data'   => json_encode($data),
                                               'width'  => '100%',
                                               'height' => '300px',
                                               'poslegend' => 'top',
                                               'stacked' => 'default',
                                               'title'  => '',
                                               'ytitle' => 'Acessos', 
                                               'xtitle' => '',
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

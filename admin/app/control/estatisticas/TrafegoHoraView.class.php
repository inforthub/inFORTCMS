<?php
/**
 * TrafegoHoraView Chart
 *
 * @version     1.0
 * @package     control
 * @subpackage  estatisticas
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class TrafegoHoraView extends TPage
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
            
            $conn   = TTransaction::get(); // obtém a conexão
            $query  = $conn->query("SELECT HOUR(dt_acesso) as hora, COUNT(id) as views FROM trafego WHERE (dt_acesso >= '".$periodo."') GROUP BY hora ORDER BY hora asc");
            $result = $query->fetchALL(PDO::FETCH_OBJ);

            $dados = [];
            foreach($result as $res):
			    $dados[$res->hora] = $res->views;
    		endforeach;
    
    		$horas = [];
    		for($i = 0; $i < 24; $i++):
    			array_push($horas, '0');
    		endfor;
            
            $final = array_replace($horas, $dados);
            
            
            $data   = array();
            $data[] = [ 'Hora', 'Acessos' ];
            foreach ( $final as $hora => $view )
            {
                $data[] = [ $hora, intval($view) ];
            }
            
            /*
            foreach ( $obj as $trafego )
            {
                $data[] = [ $trafego['hora'], intval($trafego['views']) ];
            }
*/
            
            // replace the main section variables
            $html->enableSection('main', array('data'   => json_encode($data),
                                               'width'  => '100%',
                                               'height' => '300px',
                                               'poslegend' => 'top',
                                               'stacked' => 'default',
                                               'title'  => '',
                                               'ytitle' => 'Acessos', 
                                               'xtitle' => 'Hora',
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

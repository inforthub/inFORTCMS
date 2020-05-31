<?php
/**
 * Chart
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class NavegadorView extends TPage
{
    /**
     * Class constructor
     * Creates the page
     */
    function __construct( $param )
    {
        parent::__construct();
        
        // recebemos os dados
        if (!empty($param))
        {
        
            $html = new THtmlRenderer('app/resources/google_pie_chart.html');
            
            try
            {
                TTransaction::open('sistema');
                
                // pegamos os dados do banco
                $obj = Click::where()->load();
                
                
                $data = array();
                $data[] = [ 'Pessoa', 'Value' ];
                $data[] = [ 'Pedro',   40 ];
                $data[] = [ 'Maria',   30 ];
                $data[] = [ 'João',    30 ];
                
                # PS: If you use values from database ($row['total'), 
                # cast to float. Ex: (float) $row['total']
                
                $panel = new TPanelGroup('Navegadores');
                $panel->style = 'width: 100%';
                $panel->add($html);
                
                // replace the main section variables
                $html->enableSection('main', array('data'   => json_encode($data),
                                                   'width'  => '100%',
                                                   'height'  => '300px',
                                                   'title'  => '',
                                                   'ytitle' => 'Accesses', 
                                                   'xtitle' => 'Day',
                                                   'uniqid' => uniqid()));
                
                TTransation::close();
            }
            catch (Exception $e)
            {
                new TMessage('error', $e->getMessage());
            }
        }
        
        $container = new TVBox;
        $container->style = 'width: 100%';

        $container->add($panel);
        parent::add($container);
    }
}

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
class TrafegoTotaisView extends TPage
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

            $div = new TElement('div');
            $div->class = 'row';
            
            $info1 = new THtmlRenderer('app/resources/info-box.html');
            $info2 = new THtmlRenderer('app/resources/info-box.html');
            
            $total_acesso = Trafego::countObjects();
            $acesso_hoje = Trafego::where('DATE(dt_acesso)','=',date("Y-m-d"))->count();


            $info1->enableSection('main', [ 'title'      => 'Total de Visualizações',
                                            'icon'       => 'eye',
                                            'background' => 'green',
                                            'value'      => $total_acesso ] );
            
            $info2->enableSection('main', [ 'title'      => 'Visualizações de Hoje',
                                            'icon'       => 'calendar-day',
                                            'background' => 'orange',
                                            'value'      => $acesso_hoje ] );
            
            $div->add( TElement::tag('div', $info1, ['class'=>'col-sm-6']) );
            $div->add( TElement::tag('div', $info2, ['class'=>'col-sm-6']) );
            
            TTransaction::close();

            parent::add($div);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}
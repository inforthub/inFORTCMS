<?php
/**
 * SystemLogDashboard
 *
 * @version    1.0
 * @package    control
 * @subpackage log
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemLogDashboard extends TPage
{
    /**
     * Class constructor
     * Creates the page
     */
    function __construct()
    {
        parent::__construct();
        
        try
        {
            $html = new THtmlRenderer('app/resources/system_log_dashboard.html');
            
            TTransaction::open('log');
            $indicator1 = new THtmlRenderer('app/resources/info-box.html');
            $indicator2 = new THtmlRenderer('app/resources/info-box.html');
            $indicator3 = new THtmlRenderer('app/resources/info-box.html');
            
            $accesses = SystemAccessLog::where('login_year','=',date('Y'))
                                       ->where('login_month','=',date('m'))
                                       ->where('login_day','=',date('d'))
                                       ->count();
            $sqllogs = SystemSqlLog::where('log_year','=',date('Y'))
                                   ->where('log_month','=',date('m'))
                                   ->where('log_day','=',date('d'))
                                   ->count();
            $reqlogs = SystemRequestLog::where('log_year','=',date('Y'))
                                       ->where('log_month','=',date('m'))
                                       ->where('log_day','=',date('d'))
                                       ->count();
                                   
            $indicator1->enableSection('main', ['title' => _t('Accesses'),    'icon' => 'sign-in',  'background' => 'orange', 'value' => $accesses]);
            $indicator2->enableSection('main', ['title' => _t('SQL Log'),     'icon' => 'database', 'background' => 'blue',   'value' => $sqllogs]);
            $indicator3->enableSection('main', ['title' => _t('Request Log'), 'icon' => 'globe',    'background' => 'purple', 'value' => $reqlogs]);
            
            $chart1 = new THtmlRenderer('app/resources/google_column_chart.html');
            $data1 = [];
            $data1[] = [ _t('Day'), _t('Count') ];
            
            $stats1 = SystemAccessLog::groupBy('login_day')
                                     ->where('login_year', '=', date('Y'))
                                     ->where('login_month', '=', date('m'))
                                     ->orderBy('login_day')
                                     ->countBy('id', 'count');
            if ($stats1)
            {
                foreach ($stats1 as $row)
                {
                    $data1[] = [ $row->login_day, (int) $row->count];
                }
            }
            
            // replace the main section variables
            $chart1->enableSection('main', ['data'   => json_encode($data1),
                                            'width'  => '100%',          'height'  => '300px',
                                            'title'  => _t('Accesses'),  'uniqid' => uniqid(),
                                            'ytitle' => _t('Accesses'),  'xtitle' => _t('Count'),
                                            ]);
            
            $chart2 = new THtmlRenderer('app/resources/google_column_chart.html');
            $data2 = [];
            $data2[] = [ _t('Day'), _t('Count') ];
            
            $stats2 = SystemSqlLog::groupBy('log_day')
                                  ->where('log_year', '=', date('Y'))
                                  ->where('log_month', '=', date('m'))
                                  ->orderBy('log_day')
                                  ->countBy('id', 'count');
            
            if ($stats2)
            {
                foreach ($stats2 as $row)
                {
                    $data2[] = [ $row->log_day, (int) $row->count];
                }
            }
            
            // replace the main section variables
            $chart2->enableSection('main', ['data'   => json_encode($data2),
                                            'width'  => '100%',         'height'  => '300px',
                                            'title'  => _t('SQL Log'),  'uniqid' => uniqid(),
                                            'ytitle' => _t('SQL Log'),   'xtitle' => _t('Count'),
                                            ]);
                                            
            $chart3 = new THtmlRenderer('app/resources/google_column_chart.html');
            $data3 = [];
            $data3[] = [ _t('Day'), _t('Count') ];
            
            $stats3 = SystemRequestLog::groupBy('log_day')
                                      ->where('log_year', '=', date('Y'))
                                      ->where('log_month', '=', date('m'))
                                      ->orderBy('log_day')
                                      ->countBy('id', 'count');
            
            if ($stats3)
            {
                foreach ($stats3 as $row)
                {
                    $data3[] = [ $row->log_day, (int) $row->count];
                }
            }
            
            // replace the main section variables
            $chart3->enableSection('main', ['data'   => json_encode($data3),
                                            'width'  => '100%',         'height'  => '300px',
                                            'title'  => _t('Request Log'),  'uniqid' => uniqid(),
                                            'ytitle' => _t('Request Log'),   'xtitle' => _t('Count'),
                                            ]);
            
            $html->enableSection('main', ['indicator1' => $indicator1,
                                          'indicator2' => $indicator2,
                                          'indicator3' => $indicator3,
                                          'chart1'     => $stats1 ? $chart1 : TPanelGroup::pack('','No logs'),
                                          'chart2'     => $stats2 ? $chart2 : TPanelGroup::pack('SQL/day','No enough data to render chart'),
                                          'chart3'     => $stats3 ? $chart3 : TPanelGroup::pack('Requests/day','No enough data to render chart')] );
                                          
            $container = new TVBox;
            $container->style = 'width: 100%';
            $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
            $container->add($html);
            
            parent::add($container);
            TTransaction::close();
        }
        catch (Exception $e)
        {
            parent::add($e->getMessage());
        }
    }
}

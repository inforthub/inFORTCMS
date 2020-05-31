<?php
/**
 * SystemAdministrationDashboard
 *
 * @version    1.0
 * @package    control
 * @subpackage log
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemAdministrationDashboard extends TPage
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
            $html = new THtmlRenderer('app/resources/system_admin_dashboard.html');
            
            TTransaction::open('permission');
            $indicator1 = new THtmlRenderer('app/resources/info-box.html');
            $indicator2 = new THtmlRenderer('app/resources/info-box.html');
            $indicator3 = new THtmlRenderer('app/resources/info-box.html');
            $indicator4 = new THtmlRenderer('app/resources/info-box.html');
            
            $indicator1->enableSection('main', ['title' => _t('Users'),    'icon' => 'user',       'background' => 'orange', 'value' => SystemUser::count()]);
            $indicator2->enableSection('main', ['title' => _t('Groups'),   'icon' => 'users',      'background' => 'blue',   'value' => SystemGroup::count()]);
            $indicator3->enableSection('main', ['title' => _t('Units'),    'icon' => 'university', 'background' => 'purple', 'value' => SystemUnit::count()]);
            $indicator4->enableSection('main', ['title' => _t('Programs'), 'icon' => 'code',       'background' => 'green',  'value' => SystemProgram::count()]);
            
            $chart1 = new THtmlRenderer('app/resources/google_bar_chart.html');
            $data1 = [];
            $data1[] = [ 'Group', 'Users' ];
            
            $stats1 = SystemUserGroup::groupBy('system_group_id')->countBy('system_user_id', 'count');
            if ($stats1)
            {
                foreach ($stats1 as $row)
                {
                    $data1[] = [ SystemGroup::find($row->system_group_id)->name, (int) $row->count];
                }
            }
            
            // replace the main section variables
            $chart1->enableSection('main', ['data'   => json_encode($data1),
                                            'width'  => '100%',
                                            'height'  => '500px',
                                            'title'  => _t('Users by group'),
                                            'ytitle' => _t('Users'), 
                                            'xtitle' => _t('Count'),
                                            'uniqid' => uniqid()]);
            
            $chart2 = new THtmlRenderer('app/resources/google_pie_chart.html');
            $data2 = [];
            $data2[] = [ 'Unit', 'Users' ];
            
            $stats2 = SystemUserUnit::groupBy('system_unit_id')->countBy('system_user_id', 'count');
            
            if ($stats2)
            {
                foreach ($stats2 as $row)
                {
                    $data2[] = [ SystemUnit::find($row->system_unit_id)->name, (int) $row->count];
                }
            }
            // replace the main section variables
            $chart2->enableSection('main', ['data'   => json_encode($data2),
                                            'width'  => '100%',
                                            'height'  => '500px',
                                            'title'  => _t('Users by unit'),
                                            'ytitle' => _t('Users'), 
                                            'xtitle' => _t('Count'),
                                            'uniqid' => uniqid()]);
            
            $html->enableSection('main', ['indicator1' => $indicator1,
                                          'indicator2' => $indicator2,
                                          'indicator3' => $indicator3,
                                          'indicator4' => $indicator4,
                                          'chart1'     => $chart1,
                                          'chart2'     => $chart2] );
            
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

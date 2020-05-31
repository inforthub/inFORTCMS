<?php
/**
 * SystemPHPErrorLogView
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemPHPErrorLogView extends TPage
{
    protected $form; // formulário
    
    /**
     * método construtor
     * Cria a página e o formulário de cadastro
     */
    function __construct()
    {
        parent::__construct();
        
        $div = new TElement('div');
        
        $ini = ini_get_all();
        $log_errors      = $ini['log_errors']['local_value'];
        $error_log       = $ini['error_log']['local_value'];
        $display_errors  = $ini['display_errors']['local_value'];
        
        if (empty($log_errors))
        {
            new TMessage('error', _t('Errors are not being logged. Please turn <b>log_errors = On</b> at php.ini') );
            return;
        }
        
        if (!file_exists($error_log))
        {
            new TMessage('error', _t('Error log is empty or has not been configured correctly. Define the error log file, setting <b>error_log</b> at php.ini') );
            return;
        }
        
        if (!is_readable($error_log))
        {
            new TMessage('error', _t('Failed to read error log (^1)', $error_log) );
            return;
        }
        
        $lines = [];
        $data = array_slice(file($error_log), -200);
        $count = 0;
        foreach ($data as $line)
        {
            if (substr($line,0,1) == '[')
            {
                $count++;
            }
            if (!isset($lines[$count]))
            {
                $lines[$count] = '';
            }
            $lines[$count] .= $line . '<br>';
        }
        $lines = array_reverse($lines);
        
        $datagrid = new BootstrapDatagridWrapper( new TQuickGrid );
        $datagrid->disableHtmlConversion();
        $datagrid->addQuickColumn(_t('Date'), 'date', 'left', 100);
        $datagrid->addQuickColumn(_t('Time'), 'time', 'left', 80);
        $datagrid->addQuickColumn(_t('Type'), 'type', 'left', 120);
        $datagrid->addQuickColumn(_t('Message'),  'message',  'left', null);
        
        /*
        $a > 5;      // Notice
        $a->x = 5;   // Warning
        asort(null); // Fatal error
        */
        
        $datagrid->createModel();
        
        foreach ($lines as $line)
        {
            preg_match('~^\[(.*?)\]~', $line, $matches);
            if (count($matches)==2)
            {
                $properties = explode(' ', $matches[1]);
                if (count($properties) == 3)
                {
                    $date = $properties[0];
                    $time = $properties[1];
                    $zone = $properties[2];
                    
                    $line = str_replace($matches[0], '', $line);
                    $pieces = explode(':', $line, 2);
                    
                    $type = $pieces[0];
                    
                    if (stripos($type, 'error') !== false)
                    {
                        $type = "<font color='red'>{$type}</font>";
                    }
                    else if (stripos($type, 'warning') !== false)
                    {
                        $type = "<font color='orange'>{$type}</font>";
                    }
                    
                    $type = "<b>{$type}</b>";
                    $message = $pieces[1];
                    
                    $object = new stdClass;
                    $object->date = $date;
                    $object->time = $time;
                    $object->type = $type;
                    $object->message  = $message;
                    
                    $datagrid->addItem($object);
                }
            }
        }
        
        $panel = new TPanelGroup('PHP Error');
        
        $panel->add(new TAlert('info', _t('The error log current location is <b>^1</b>', $error_log)));
        
        if (!is_writable($error_log))
        {
            $panel->add(new TAlert('warning', _t('Error log (^1) is not writable by web server user, so the messages may be incomplete', $error_log). '<br>'.
                                              _t('Check the owner of the log file. He must be the same as the web user (usually www-data, www, etc)') ));
        }
        
        if (empty($display_errors))
        {
            $panel->add(new TAlert('warning', _t('Errors are not currently being displayd because the <b>display_errors</b> is set to Off in php.ini'). '<br>'.
                                              _t('This configuration is usually recommended for production, not development purposes') ));
        }
        
        $panel->add($datagrid)->style='overflow-x:auto';
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($panel);
        
        parent::add($container);
    }
}

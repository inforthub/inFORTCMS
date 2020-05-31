<?php
/**
 * SystemRequestLogView
 *
 * @version    1.0
 * @package    control
 * @subpackage log
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemRequestLogView extends TPage
{
    
    public function onLoad($param)
    {
        parent::setTargetContainer('adianti_right_panel');
        
        try
        {
            TTransaction::open('log');
            $log = SystemRequestLog::find($param['id']);
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
        
        $form = new BootstrapFormBuilder;
        $form->setFormTitle(_t('Request Log'));
        $form->addFields( ['<b>Endpoint</b>'], [strtoupper($log->endpoint)] );
        $form->addFields( ['<b>Time</b>'], [$log->logdate] );
        $form->addFields( ['<b>Session</b>'], [$log->session_id] );
        $form->addFields( ['<b>Login</b>'], [$log->login] );
        $form->addFields( ['<b>IP</b>'], [$log->access_ip] );
        $form->addFields( ['<b>Program</b>'], [$log->class_name] );
        $form->addFields( ['<b>Host</b>'], [$log->http_host] );
        $form->addFields( ['<b>Port</b>'], [$log->server_port] );
        $form->addFields( ['<b>Request URI</b>'], [$log->request_uri] );
        $form->addFields( ['<b>Request Method</b>'], [$log->request_method] );
        $form->addFields( ['<b>Query String</b>'], [$log->query_string] );
        $form->addFields( ['<b>Request headers</b>'], ['<pre>'.json_encode(json_decode($log->request_headers), JSON_PRETTY_PRINT).'</pre>'] );
        $form->addFields( ['<b>Request body</b>'], ['<pre>'.json_encode(json_decode($log->request_body), JSON_PRETTY_PRINT).'</pre>'] );
        
        $form->addHeaderAction(_t('Close'), new TAction(array($this, 'onClose')), 'fa:times red');
        parent::add($form);
    }
    
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}

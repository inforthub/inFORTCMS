<?php
header('Content-Type: application/json; charset=utf-8');

// initialization script
require_once 'init.php';

class AdiantiRestServer
{
    public static function run($request)
    {
        $input    = json_decode(file_get_contents("php://input"));
        $request  = array_merge($request, (array) $input);
        $class    = isset($request['class']) ? $request['class']   : '';
        $method   = isset($request['method']) ? $request['method'] : '';
        $response = NULL;
        
        // aqui implementar mecanismo de controle !!
        if (get_parent_class($class) !== 'Adianti\Service\AdiantiRecordService')
        {
            return json_encode( array('status' => 'error',
                                      'data'   => _t('Permission denied')));
        }
        
        try
        {
            $response = AdiantiCoreApplication::execute($class, $method, $request, 'rest');
            return json_encode( array('status' => 'success', 'data' => $response));
        }
        catch (Exception $e)
        {
            return json_encode( array('status' => 'error', 'data' => $e->getMessage()));
        }
    }
}

print AdiantiRestServer::run($_REQUEST);

<?php
header('Content-Type: application/json; charset=utf-8');

// initialization script
require_once 'init.php';

class AdiantiRestServer
{
    public static function run($request)
    {
        $ini      = AdiantiApplicationConfig::get();
        $input    = json_decode(file_get_contents("php://input"));
        $request  = array_merge($request, (array) $input);
        $class    = isset($request['class']) ? $request['class']   : '';
        $method   = isset($request['method']) ? $request['method'] : '';
        $headers  = AdiantiCoreApplication::getHeaders();
        $response = NULL;
        
        try
        {
            if (empty($headers['Authorization']))
            {
                throw new Exception( _t('Authorization error') );
            }
            else
            {
                if (substr($headers['Authorization'], 0, 5) == 'Basic')
                {
                    if (empty($ini['general']['rest_key']))
                    {
                        throw new Exception( _t('REST key not defined') );
                    }
					
                    if ($ini['general']['rest_key'] !== substr($headers['Authorization'], 6))
                    {
                        return json_encode( array('status' => 'error', 'data' => _t('Authorization error')));
                    }
                }
                else if (substr($headers['Authorization'], 0, 6) == 'Bearer')
                {
                    ApplicationAuthenticationService::fromToken( substr($headers['Authorization'], 7) );
                }
                else
                {
                    throw new Exception( _t('Authorization error') );
                }
            }
            
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

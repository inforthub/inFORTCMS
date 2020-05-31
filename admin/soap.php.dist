<?php
// initialization script
require_once 'init.php';

class AdiantiSoapServer
{
    public function __call($method, $parameters)
    {
        $class    = isset($_REQUEST['class']) ? $_REQUEST['class']   : '';
        $response = NULL;
        
        // aqui implementar mecanismo de controle
        if (!in_array($class, array('CustomerService')))
        {
            throw new SoapFault('server', _t('Permission denied'));
        }
        
        try
        {
            if (class_exists($class))
            {
                if (method_exists($class, $method))
                {
                    $rf = new ReflectionMethod($class, $method);
                    if ($rf->isStatic())
                    {
                        $response = call_user_func_array(array($class, $method),$parameters);
                    }
                    else
                    {
                        $response = call_user_func_array(array(new $class($_GET), $method),$parameters);
                    }
                    return $response;
                }
                else
                {
                    throw new SoapFault('server', TAdiantiCoreTranslator::translate('Method ^1 not found', "$class::$method"));
                }
            }
            else
            {
                throw new SoapFault('server', TAdiantiCoreTranslator::translate('Class ^1 not found', $class));
            }
        }
        catch (Exception $e)
        {
            throw new SoapFault('server', $e->getMessage());
        }
    }
}

$server = new SoapServer(NULL, array('encoding' => 'UTF-8', 'uri' => 'http://test-uri/'));
$server->setClass('AdiantiSoapServer');
$server->handle();

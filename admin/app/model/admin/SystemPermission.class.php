<?php
/**
 * SystemPermission
 *
 * @version    1.0
 * @package    model
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemPermission
{
    public static function checkPermission($action)
    {
        $ini    = AdiantiApplicationConfig::get();
        
        $programs = TSession::getValue('programs');
        $public_classes = $ini['permission']['public_classes'];
        $public_classes[] = 'LoginForm';
        
        return ( (isset($programs[$action]) AND $programs[$action]) OR
                 in_array($action, $public_classes) );
    } 
}

<?php
/**
 * SystemAccessLog
 *
 * @version    1.0
 * @package    model
 * @subpackage log
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemAccessLog extends TRecord
{
    const TABLENAME = 'system_access_log';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('sessionid');
        parent::addAttribute('login');
        parent::addAttribute('login_time');
        parent::addAttribute('login_year');
        parent::addAttribute('login_month');
        parent::addAttribute('login_day');
        parent::addAttribute('logout_time');
        parent::addAttribute('impersonated');
        parent::addAttribute('access_ip');
    }
}

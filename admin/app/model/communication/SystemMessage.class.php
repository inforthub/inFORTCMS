<?php
/**
 * SystemMessage
 *
 * @version    1.0
 * @package    model
 * @subpackage communication
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemMessage extends TRecord
{
    const TABLENAME = 'system_message';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('system_user_id');
        parent::addAttribute('system_user_to_id');
        parent::addAttribute('subject');
        parent::addAttribute('message');
        parent::addAttribute('dt_message');
        parent::addAttribute('checked');
    }
    
    public function get_user_from()
    {
        return SystemUser::findInTransaction('permission', $this->system_user_id);
    }
    
    public function get_user_to()
    {
        return SystemUser::findInTransaction('permission', $this->system_user_to_id);
    }
    
    public function get_user_mixed()
    {
        if ($this->system_user_id == TSession::getValue('userid'))
        {
            return $this->get_user_to();
        }
        else
        {
            return $this->get_user_from();
        }
    }
}

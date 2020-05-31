<?php
/**
 * SystemNotificationFormView
 *
 * @version    1.0
 * @package    control
 * @subpackage communication
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemNotificationFormView extends TPage
{
    /**
     * Show data
     */
    public function onView( $param )
    {
        try
        {
            // convert parameter to object
            $data = (object) $param;
            
            // load the html template
            $html = new THtmlRenderer('app/resources/system_notification_view.html');
            $html->enableTranslation(TRUE);
            
            TTransaction::open('communication');
            if (isset($data->id))
            {
                // load customer identified in the form
                $object = SystemNotification::find( $data->id );
                if ($object)
                {
                    if ($object->system_user_to_id == TSession::getValue('userid'))
                    {
                        // create one array with the customer data
                        $array_object = $object->toArray();
                        $array_object['checked_string'] = ($array_object['checked'] == 'Y' ? _t('Yes') : _t('No'));
                        $array_object['action_encoded'] = base64_encode($array_object['action_url']);
                        
                        TTransaction::open('permission');
                        $user = SystemUser::find($array_object['system_user_id']);
                        if ($user instanceof SystemUser)
                        {
                            $array_object['user'] = $user->name . ' (' . $array_object['system_user_id'] . ')';
                        }
                        TTransaction::close();
                        
                        // replace variables from the main section with the object data
                        $html->enableSection('main',  $array_object);
                        
                        if ($object->checked == 'N')
                        {
                            $html->enableSection('check', $array_object);
                        }
                    }
                    else
                    {
                        throw new Exception(_t('Permission denied'));
                    }
                }
                else
                {
                    throw new Exception(_t('Object ^1 not found in ^2', $data->id, 'SystemNotification'));
                }
            }
            
            TTransaction::close();
            
            $vbox = new TVBox;
            $vbox->style = 'width:100%';
            $vbox->add(TBreadCrumb::create( [_t('Notifications'), _t('View')] ) );
            $vbox->add($html);
            
            parent::add($vbox);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Check message as read
     */
    public function onExecuteAction($param)
    {
        try
        {
            TTransaction::open('communication');
            
            $notification = SystemNotification::find($param['id']);
            if ($notification)
            {
                if ($notification->system_user_to_id == TSession::getValue('userid'))
                {
                    $notification->checked = 'Y';
                    $notification->store();
            
                    $query_string = $notification->action_url;
                    parse_str($query_string, $query_params);
                    $class  = $query_params['class'];
                    $method = isset($query_params['method']) ? $query_params['method'] : null;
                    unset($query_params['class']);
                    unset($query_params['method']);
                    AdiantiCoreApplication::loadPage( $class, $method, $query_params);
                    TScript::create('update_notifications_menu()');
                }
                else
                {
                    throw new Exception(_t('Permission denied'));
                }
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}

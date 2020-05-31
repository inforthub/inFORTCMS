<?php
require_once 'init.php';

// AdiantiCoreApplication::setRouter(array('AdiantiRouteTranslator', 'translate'));

class TApplication extends AdiantiCoreApplication
{
    public static function run($debug = null)
    {
        new TSession;
        ApplicationTranslator::setLanguage( TSession::getValue('user_language'), true ); // multi-lang
        
        if ($_REQUEST)
        {
            $ini = AdiantiApplicationConfig::get();
            
            $class  = isset($_REQUEST['class']) ? $_REQUEST['class'] : '';
            $public = in_array($class, $ini['permission']['public_classes']);
            $debug  = is_null($debug)? $ini['general']['debug'] : $debug;
            if (TSession::getValue('logged')) // logged
            {
                $programs = (array) TSession::getValue('programs'); // programs with permission
                $programs = array_merge($programs, self::getDefaultPermissions());
                
                if( isset($programs[$class]) OR $public )
                {
                    parent::run($debug);
                }
                else
                {
                    new TMessage('error', _t('Permission denied') );
                }
            }
            else if ($class == 'LoginForm' OR $public )
            {
                parent::run($debug);
            }
            else
            {
                new TMessage('error', _t('Permission denied'), new TAction(array('LoginForm','onLogout')) );
            }
        }
    }
    
    /**
     * Return default programs for logged users
     */
    public static function getDefaultPermissions()
    {
        return ['Adianti\Base\TStandardSeek' => TRUE,
                'LoginForm' => TRUE,
                'AdiantiMultiSearchService' => TRUE,
                'AdiantiUploaderService' => TRUE,
                'AdiantiAutocompleteService' => TRUE,
                'SystemDocumentUploaderService' => TRUE,
                'EmptyPage' => TRUE,
                'MessageList' => TRUE,
                'NotificationList' => TRUE,
                'SearchBox' => TRUE,
                'SearchInputBox' => TRUE];
    } 
}

TApplication::run();

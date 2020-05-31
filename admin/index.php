<?php
require_once 'init.php';
$theme  = $ini['general']['theme'];
$class  = isset($_REQUEST['class']) ? $_REQUEST['class'] : '';
$public = in_array($class, $ini['permission']['public_classes']);

// AdiantiCoreApplication::setRouter(array('AdiantiRouteTranslator', 'translate'));

new TSession;
ApplicationTranslator::setLanguage( TSession::getValue('user_language'), true );

if ( TSession::getValue('logged') )
{
    $content = file_get_contents("app/templates/{$theme}/layout.html");
    $menu    = AdiantiMenuBuilder::parse('menu.xml', $theme);
    $content = str_replace('{MENU}', $menu, $content);
}
else
{
    if (isset($ini['general']['public_view']) && $ini['general']['public_view'] == '1')
    {
        $content = file_get_contents("app/templates/{$theme}/public.html");
        $menu    = AdiantiMenuBuilder::parse('menu-public.xml', $theme);
        $content = str_replace('{MENU}', $menu, $content);
    }
    else
    {
        $content = file_get_contents("app/templates/{$theme}/login.html");
    }
}

$content = ApplicationTranslator::translateTemplate($content);
$content = AdiantiTemplateParser::parse($content);

//
$skin    = $ini['cms']['skin']; // black, blue, green, purple, red, yellow -- blue-light ...
$content = str_replace('{skin}', $skin, $content);
$content = str_replace('{cms_version}', $ini['cms']['version'], $content);

echo $content;

if (TSession::getValue('logged') OR $public)
{
    if ($class)
    {
        $method = isset($_REQUEST['method']) ? $_REQUEST['method'] : NULL;
        AdiantiCoreApplication::loadPage($class, $method, $_REQUEST);
    }
}
else
{
    if (isset($ini['general']['public_view']) && $ini['general']['public_view'] == '1')
    {
        if (!empty($ini['general']['public_entry']))
        {
            AdiantiCoreApplication::loadPage($ini['general']['public_entry'], '', $_REQUEST);
        }
    }
    else
    {
        AdiantiCoreApplication::loadPage('LoginForm', '', $_REQUEST);
    }
}

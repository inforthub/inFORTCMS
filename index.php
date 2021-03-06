<?php

// carregando classes
require_once 'lib/Loader.php';

define('ROOT', getcwd());
chdir('admin');
//ini_set('error_log', 'tmp/php_errors.log');

// iniciando o core
spl_autoload_register(array('Infort\Core\Loader', 'autoload'));
Infort\Core\Loader::loadClassMap();

// Iniciando o cache
$cache = new Cache;
if ( $cache->start() )
{
    new TSession;
    
    $start = new Route;
    $start->run();
}
// Terminando o cache
$cache->end();

chdir('..');

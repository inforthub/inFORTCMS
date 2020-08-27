<?php
namespace Infort\Core;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Classe loader, baseado no Framework class autoloader do Adianti v5.7
 *
 * @version     1.0
 * @package     lib
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 (https://www.infort.eti.br)
 *
 */
class Loader
{
    static private $classMap;
    
    /**
     * Load the class map
     */
    public static function loadClassMap()
    {
        self::$classMap = self::getMap();
    }
    
    /**
     * Define the class path
     * @param $class Class name
     * @param $path  Class path
     */
    public static function setClassPath($class, $path)
    {
        self::$classMap[$class] = $path;
    }
    
    /**
     * Core autloader
     * @param $className Class name
     */
    public static function autoload($className)
    {
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if (strrpos($className, '\\') !== FALSE)
        {
            $pieces    = explode('\\', $className);
            $className = array_pop($pieces);
            $namespace = implode('\\', $pieces);
        }
        $fileName = 'lib'.'\\'.strtolower($namespace).'\\'.$className.'.php';
        $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $fileName);
        
        if (file_exists($fileName))
        {
            //echo "PSR: $className <br>";
            require_once $fileName;
            self::globalScope($className);
        }
        else
        {
            if (!self::legacyAutoload($className))
            {
                self::AppLoader($className);
            }
        }
    }
    
    /**
     * autoloader
     * @param $class classname
     */
    public static function legacyAutoload($class)
    {
        if (isset(self::$classMap[$class]))
        {
            if (file_exists(self::$classMap[$class]))
            {
                //echo 'Classmap '.self::$classMap[$class] . '<br>';
                require_once self::$classMap[$class];
                
                self::globalScope($class);
                return TRUE;
            }
        }
    }
    
    /**
     * make a class global
     */
    public static function globalScope($class)
    {
        if (isset(self::$classMap[$class]) AND self::$classMap[$class])
        {
            if (!class_exists($class, FALSE))
            {
                $ns = self::$classMap[$class];
                $ns = str_replace('/', '\\', $ns);
                $ns = str_replace('lib\\adianti', 'Adianti', $ns);
                $ns = str_replace('.class.php', '', $ns);
                $ns = str_replace('.php', '', $ns);
                
                //echo "&nbsp;&nbsp;&nbsp;&nbsp;Mapping: $ns, $class<br>";
                if (class_exists($ns) OR interface_exists($ns))
                {
                    class_alias($ns, $class, FALSE);
                }
            }
        }
    }
    
    
    /***************************************************
                  AdiantiApplicationLoader
    ***************************************************/
    
    public static function AppLoader($class)
    {
        $folders = array();
        $folders[] = 'app/model';
        $folders[] = 'app/lib/util';
        $folders[] = '../lib';

        foreach ($folders as $folder)
        {
            if (file_exists("{$folder}/{$class}.class.php"))
            {
                require_once "{$folder}/{$class}.class.php";
                return TRUE;
            }
            else
            {
                try
                {
                    if (file_exists($folder))
                    {
                        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder),
                                                               RecursiveIteratorIterator::SELF_FIRST) as $entry)
                        {
                            if (is_dir($entry))
                            {
                                if (file_exists("{$entry}/{$class}.class.php"))
                                {
                                    require_once "{$entry}/{$class}.class.php";
                                    return TRUE;
                                }
                            }
                        }
                    }
                }
                catch(Exception $e)
                {
                    new TMessage('error', $e->getMessage());
                }
            }
        }
    }
    
    
    /***************************************************
                       AdiantiClassMap
    ***************************************************/
    
    
    public static function getMap()
    {
        $classPath = array();
        $classPath['AdiantiCoreTranslator']      = 'lib/adianti/core/AdiantiCoreTranslator.php';
        $classPath['AdiantiTemplateParser']      = 'lib/adianti/core/AdiantiTemplateParser.php';
        $classPath['TConnection']                = 'lib/adianti/database/TConnection.php';
        $classPath['TCriteria']                  = 'lib/adianti/database/TCriteria.php';
        $classPath['TFilter']                    = 'lib/adianti/database/TFilter.php';
        $classPath['TDatabase']                  = 'lib/adianti/database/TDatabase.php';
        $classPath['TRecord']                    = 'lib/adianti/database/TRecord.php';
        $classPath['TRepository']                = 'lib/adianti/database/TRepository.php';
        $classPath['TSqlSelect']                 = 'lib/adianti/database/TSqlSelect.php';
        $classPath['TSqlUpdate']                 = 'lib/adianti/database/TSqlUpdate.php';
        $classPath['TTransaction']               = 'lib/adianti/database/TTransaction.php';
        $classPath['TSession']                   = 'lib/adianti/registry/TSession.php';
        $classPath['TElement']                   = 'lib/adianti/widget/base/TElement.php';
        $classPath['THtmlRenderer']              = 'lib/adianti/widget/template/THtmlRenderer.php';
        $classPath['THyperLink']                 = 'lib/adianti/widget/util/THyperLink.php';
               
        return $classPath;
    }
    
}

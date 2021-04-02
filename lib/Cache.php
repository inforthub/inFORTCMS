<?php
/**
 * Cache Class
 *
 * @version     1.0
 * @package     lib
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 (https://www.infort.eti.br)
 *
 */
require 'TinyHtmlMinifier.php';

class Cache
{
    // Pages you do not want to Cache:
    var $doNotCache = array("admin","click");

    // General Config Vars
    var $cacheDir = "../cache";
    var $cacheTime = 86400;
    var $caching = false;
    var $cacheFile;
    var $cacheFileName;
    var $cacheLogFile;
    var $cacheLog;
    
    var $cacheControl;
    
    /*
     * Time
     * 3600    = 1 hora
     * 10800   = 3 horas
     * 21600   = 6 horas
     * 43200   = 12 horas
     * 86400   = 24 horas / 1 dia
     * 604800  = 7 dias
     * 1296000 = 15 dias
     * 2592000 = 30 dias
     */

    function __construct()
    {
        $this->cacheControl = THelper::getPreferences('pref_cache_control');
        if (!empty($this->cacheControl))
        {
            // colocar urls dos formulários ativos para impedir o cache
            $formularios = [];
            
            $sitemap = THelper::countSitemap();
            $this->doNotCache = array_merge(['admin','click'],$sitemap['links'],$formularios);
            
            $this->cacheFile = base64_encode($_SERVER['REQUEST_URI']);
            $this->cacheFileName = $this->cacheDir.'/'.$this->cacheFile.'.txt';
            $this->cacheLogFile = $this->cacheDir."/log.txt";
            if(!is_dir($this->cacheDir)) mkdir($this->cacheDir, 0755);
            if(file_exists($this->cacheLogFile))
                $this->cacheLog = unserialize(file_get_contents($this->cacheLogFile));
            else
                $this->cacheLog = array();
        }
    }

    function start()
    {
        if (!empty($this->cacheControl))
        {
            //$location = array_slice(explode('/',$_SERVER['REQUEST_URI']), 2);
            $location = explode('/',$_SERVER['REQUEST_URI']);
            if(!in_array($location[0],$this->doNotCache))
            {
                if(file_exists($this->cacheFileName) && (time() - filemtime($this->cacheFileName)) < $this->cacheTime && $this->cacheLog[$this->cacheFile] == 1)
                {
                    $this->caching = false;
                    echo file_get_contents($this->cacheFileName);
                    //exit();
                    return false;
                }else{
                    $this->caching = true;
                    ob_start();
                }
            }
        }
        return true;
    }

    function end()
    {
        // registramos o acesso com ou sem cache
        THelper::setTrafego();
        
        if (!empty($this->cacheControl))
        {
            if($this->caching)
            {
                file_put_contents( $this->cacheFileName,TinyMinify::html(ob_get_contents()) );
                ob_end_flush();
                $this->cacheLog[$this->cacheFile] = 1;
                if(file_put_contents($this->cacheLogFile,serialize($this->cacheLog)))
                    return true;
            }
        }
    }

    function purge($location)
    {
        $location = base64_encode($location);
        $this->cacheLog[$location] = 0;
        if(file_put_contents($this->cacheLogFile,serialize($this->cacheLog)))
            return true;
        else
            return false;
    }

    function purge_all()
    {
        if(file_exists($this->cacheLogFile))
        {
            foreach($this->cacheLog as $key=>$value) $this->cacheLog[$key] = 0;
            if(file_put_contents($this->cacheLogFile,serialize($this->cacheLog)))
                return true;
            else
                return false;
        }
    }

}

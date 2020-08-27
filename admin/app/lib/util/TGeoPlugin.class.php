<?php
/**
 * TGeoPlugin Class
 *
 * @version    1.0
 * @package    util
 * @subpackage lib
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class TGeoPlugin
{
	
	private $hosts;

    /**
     * Método construtor
     */
	public function __construct()
	{
		// configurando os servidores de busca
		$this->hosts[] = "http://ip-api.com/json/{IP}";              // limite de 45 requisições por minuto
		$this->hosts[] = "https://ip.seeip.org/geoip/{IP}";          // uso livre, dados pouco confiáveis
		$this->hosts[] = "http://www.geoplugin.net/json.gp?ip={IP}"; // limite de 120 requisições por minuto
	}
	
	/**
	 * Busca os dados pelo IP
	 */
	public function locate($ip)
	{
		// faz a busca efetivamente
		$data = $this->fetch($ip);
		
		if ($data)
		{
			$geo = json_decode($data);
			
			// padronizando o campos
			if (isset($geo->geoplugin_region))
			{
			    $geo->region  = $geo->geoplugin_region;
			    $geo->city    = $geo->geoplugin_city;
			    $geo->country = $geo->geoplugin_countryName;
			}
			else if (isset($geo->regionName))
			{
			    $geo->region = $geo->regionName;
			}
			
			//$geo->region = (isset($geo->regionName)) ? $geo->regionName : $geo->region;
		}
		else
		{
			$geo = null;
		}
		
		return $geo;
	}
	
	/**
	 * Executa a busca
	 */
	private function fetch($ip)
	{
		$opts = array('https'=>['method'=>"GET",'timeout'=>2,'ignore_errors'=> true]);  
		$context = stream_context_create($opts);
		
		// escolhe um host randomicamente faz o parse no IP
		$host = str_replace( '{IP}', $ip, $this->hosts[mt_rand(0, count($this->hosts)-1)] );
		
		if ( function_exists('curl_init') )
		{
			//usando cURL para consultar
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $host);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'geoPlugin PHP Class v1.1');
			$response = curl_exec($ch);
			curl_close ($ch);
		}
		else if ( ini_get('allow_url_fopen') )
		{
			//fall back to fopen()
			$response = @file_get_contents($host,false,$context); //file_get_contents($host, 'r');
		}
		else
		{
			trigger_error ('geoPlugin class Error: Cannot retrieve data. Either compile PHP with cURL support or enable allow_url_fopen in php.ini ', E_USER_ERROR);
			return;
		}
		
		return $response;
	}
	
	
}

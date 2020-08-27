<?php
/**
 * Trafego Active Record
 *
 * @version     1.0
 * @package     model
 * @subpackage  estatisticas
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class Trafego extends TRecord
{
    const TABLENAME = 'trafego';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('dt_acesso');
        parent::addAttribute('pagina');
        parent::addAttribute('ip');
        parent::addAttribute('cidade');
        parent::addAttribute('regiao');
        parent::addAttribute('pais');
        parent::addAttribute('navegador');
        parent::addAttribute('referencia');
        parent::addAttribute('plataforma');
    }
    
    /**
     * Pegamos o tráfego filtrado pelo campo e pelo período
     * @param    $campo - pagina, cidade, regiao, pais, navegador, referencia, plataforma
     */
    public static function porCampo($campo, $param = '-30 days', $limit = '10')
    {
        $periodo = date("Y-m-d H:i:s", strtotime($param));
        
        $conn  = TTransaction::get(); // obtém a conexão
        $query = $conn->query("SELECT ".$campo.", COUNT(id) as views FROM trafego WHERE (dt_acesso >= '".$periodo."') GROUP BY ".$campo." ORDER BY views DESC LIMIT ".$limit);
		$result = $query->fetchALL(PDO::FETCH_OBJ);
		
		return $result;
    }
    
    
    /**
     * Método estático que registra um acesso
     */
    public static function registrar()
    {
        $uri        = filter_input(INPUT_SERVER,'REQUEST_URI',FILTER_DEFAULT);
		$ip         = THelper::get_ip_address();
		$user_agent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
		/*
		$geturl     = $_GET['url'];
		
		$sitemap      = THelper::countSitemap();
        $naoRegistrar = array_merge(['admin','click'],$sitemap['links']);
        
        $explode = explode('/',$uri);
        */
        if ( $ip <> '::1' && $ip <> '127.0.0.1' )
        {
            // verifica se não é um robô antes de registrar os dados
    		$botRegexPattern = "(googlebot\/|Googlebot\-Mobile|Googlebot\-Image|Google favicon|Mediapartners\-Google|bingbot|slurp|java|wget|curl|Commons\-HttpClient|Python\-urllib|libwww|httpunit|nutch|phpcrawl|msnbot|jyxobot|FAST\-WebCrawler|FAST Enterprise Crawler|biglotron|teoma|convera|seekbot|gigablast|exabot|ngbot|ia_archiver|GingerCrawler|webmon |httrack|webcrawler|grub\.org|UsineNouvelleCrawler|antibot|netresearchserver|speedy|fluffy|bibnum\.bnf|findlink|msrbot|panscient|yacybot|AISearchBot|IOI|ips\-agent|tagoobot|MJ12bot|dotbot|woriobot|yanga|buzzbot|mlbot|yandexbot|purebot|Linguee Bot|Voyager|CyberPatrol|voilabot|baiduspider|citeseerxbot|spbot|twengabot|postrank|turnitinbot|scribdbot|page2rss|sitebot|linkdex|Adidxbot|blekkobot|ezooms|dotbot|Mail\.RU_Bot|discobot|heritrix|findthatfile|europarchive\.org|NerdByNature\.Bot|sistrix crawler|ahrefsbot|Aboundex|domaincrawler|wbsearchbot|summify|ccbot|edisterbot|seznambot|ec2linkfinder|gslfbot|aihitbot|intelium_bot|facebookexternalhit|yeti|RetrevoPageAnalyzer|lb\-spider|sogou|lssbot|careerbot|wotbox|wocbot|ichiro|DuckDuckBot|lssrocketcrawler|drupact|webcompanycrawler|acoonbot|openindexspider|gnam gnam spider|web\-archive\-net\.com\.bot|backlinkcrawler|coccoc|integromedb|content crawler spider|toplistbot|seokicks\-robot|it2media\-domain\-crawler|ip\-web\-crawler\.com|siteexplorer\.info|elisabot|proximic|changedetection|blexbot|arabot|WeSEE:Search|niki\-bot|CrystalSemanticsBot|rogerbot|360Spider|psbot|InterfaxScanBot|Lipperhey SEO Service|CC Metadata Scaper|g00g1e\.net|GrapeshotCrawler|urlappendbot|brainobot|fr\-crawler|binlar|SimpleCrawler|Livelapbot|Twitterbot|cXensebot|smtbot|bnf\.fr_bot|A6\-Indexer|ADmantX|Facebot|Twitterbot|OrangeBot|memorybot|AdvBot|MegaIndex|SemanticScholarBot|ltx71|nerdybot|xovibot|BUbiNG|Qwantify|archive\.org_bot|Applebot|TweetmemeBot|crawler4j|findxbot|SemrushBot|yoozBot|lipperhey|y!j\-asr|Domain Re\-Animator Bot|AddThis|YisouSpider|BLEXBot|YandexBot|SurdotlyBot|AwarioRssBot|FeedlyBot|Barkrowler|Gluten Free Crawler|Cliqzbot)";
    
    		//return preg_match("/{$botRegexPattern}/", $user_agent);
    
    		if (!preg_match("/{$botRegexPattern}/", $user_agent))
    		{
                $robot = array();

                if(preg_match('/google/i',$_SERVER['HTTP_USER_AGENT']))
                {
                    $robot['buscador'] = 'Google';
                };
                
                if(preg_match('/slurp/i',$_SERVER['HTTP_USER_AGENT']))
                {
                    $robot['buscador'] = 'Yahoo';
                };
                
                if(preg_match('/msn/i',$_SERVER['HTTP_USER_AGENT']))
                {
                    $robot['buscador'] = 'MSN';
                };
                
                if(preg_match('/ask/i',$_SERVER['HTTP_USER_AGENT']))
                {
                    $robot['buscador'] = 'ASK';
                };
                
                if(preg_match('/alexa/i',$_SERVER['HTTP_USER_AGENT']))
                {
                    $robot['buscador'] = 'Alexa';
                };
                
                if(preg_match('/UOL/i',$_SERVER['HTTP_USER_AGENT']))
                {
                    $robot['buscador'] = 'UOL';
                };
                
                if(!isset($robot['buscador']))
                {
                    $geo = null;
                    
                    if ( !empty(THelper::getPreferences('pref_site_trafego')) )
                    {
                        // consultamos os dados no GEO
                        $geoplugin = new TGeoPlugin();
                		$geo = $geoplugin->locate($ip);
            		}
            		
            		// prepara a zona (sempre UTF-8)
                    setlocale( LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'pt_BR.utf-8', 'portuguese' );
                    date_default_timezone_set( 'America/Sao_Paulo' );
        
            		$trafego = new Trafego;
            		$trafego->dt_acesso  = date('Y-m-d H:i:s');
            		$trafego->pagina     = $uri;
            		$trafego->ip         = $ip;
            		$trafego->cidade     = (isset($geo->city)) ? $geo->city : 'Desconhecida';
            		$trafego->regiao     = (isset($geo->region)) ? $geo->region : 'Desconhecida';
            		$trafego->pais       = (isset($geo->country)) ? $geo->country : 'Desconhecido';
            		$trafego->navegador  = THelper::get_browser($user_agent);
            		$trafego->plataforma = THelper::get_platform($user_agent);
            		$trafego->referencia = self::get_referer();
            		// salvando os dados no banco
            		$trafego->store();
                }
            }
        }
    }
    
    /**
	 * Detecta se é um robô
	 */
	private function is_bot($user_agent)
	{
		$botRegexPattern = "(googlebot\/|Googlebot\-Mobile|Googlebot\-Image|Google favicon|Mediapartners\-Google|bingbot|slurp|java|wget|curl|Commons\-HttpClient|Python\-urllib|libwww|httpunit|nutch|phpcrawl|msnbot|jyxobot|FAST\-WebCrawler|FAST Enterprise Crawler|biglotron|teoma|convera|seekbot|gigablast|exabot|ngbot|ia_archiver|GingerCrawler|webmon |httrack|webcrawler|grub\.org|UsineNouvelleCrawler|antibot|netresearchserver|speedy|fluffy|bibnum\.bnf|findlink|msrbot|panscient|yacybot|AISearchBot|IOI|ips\-agent|tagoobot|MJ12bot|dotbot|woriobot|yanga|buzzbot|mlbot|yandexbot|purebot|Linguee Bot|Voyager|CyberPatrol|voilabot|baiduspider|citeseerxbot|spbot|twengabot|postrank|turnitinbot|scribdbot|page2rss|sitebot|linkdex|Adidxbot|blekkobot|ezooms|dotbot|Mail\.RU_Bot|discobot|heritrix|findthatfile|europarchive\.org|NerdByNature\.Bot|sistrix crawler|ahrefsbot|Aboundex|domaincrawler|wbsearchbot|summify|ccbot|edisterbot|seznambot|ec2linkfinder|gslfbot|aihitbot|intelium_bot|facebookexternalhit|yeti|RetrevoPageAnalyzer|lb\-spider|sogou|lssbot|careerbot|wotbox|wocbot|ichiro|DuckDuckBot|lssrocketcrawler|drupact|webcompanycrawler|acoonbot|openindexspider|gnam gnam spider|web\-archive\-net\.com\.bot|backlinkcrawler|coccoc|integromedb|content crawler spider|toplistbot|seokicks\-robot|it2media\-domain\-crawler|ip\-web\-crawler\.com|siteexplorer\.info|elisabot|proximic|changedetection|blexbot|arabot|WeSEE:Search|niki\-bot|CrystalSemanticsBot|rogerbot|360Spider|psbot|InterfaxScanBot|Lipperhey SEO Service|CC Metadata Scaper|g00g1e\.net|GrapeshotCrawler|urlappendbot|brainobot|fr\-crawler|binlar|SimpleCrawler|Livelapbot|Twitterbot|cXensebot|smtbot|bnf\.fr_bot|A6\-Indexer|ADmantX|Facebot|Twitterbot|OrangeBot|memorybot|AdvBot|MegaIndex|SemanticScholarBot|ltx71|nerdybot|xovibot|BUbiNG|Qwantify|archive\.org_bot|Applebot|TweetmemeBot|crawler4j|findxbot|SemrushBot|yoozBot|lipperhey|y!j\-asr|Domain Re\-Animator Bot|AddThis|YisouSpider|BLEXBot|YandexBot|SurdotlyBot|AwarioRssBot|FeedlyBot|Barkrowler|Gluten Free Crawler|Cliqzbot)";

		return preg_match("/{$botRegexPattern}/", $user_agent);
	}
    
    /**
     * Detecta a referência da navegação
     */
    private static function get_referer()
    {
		$referer      = filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_VALIDATE_URL);
		$referer_host = parse_url($referer, PHP_URL_HOST);
		$host         = filter_input(INPUT_SERVER, 'SERVER_NAME');

		if (!$referer)
		{
			$retorno = 'Acesso Direto';
	    }
		else if ($referer_host == $host)
		{
			$retorno = 'Navegação Interna';
		}
		else
		{
			$retorno = $referer;
		}
		return $retorno;
	}


}

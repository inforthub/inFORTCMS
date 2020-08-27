<?php
/**
 * Click Active Record
 *
 * @version     1.0
 * @package     model
 * @subpackage  estatisticas
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class Click extends TRecord
{
    const TABLENAME = 'click';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $midia;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('dt_clique');
        parent::addAttribute('pagina');
        parent::addAttribute('ip');
        parent::addAttribute('cidade');
        parent::addAttribute('regiao');
        parent::addAttribute('pais');
        parent::addAttribute('navegador');
        parent::addAttribute('plataforma');
        parent::addAttribute('midia_id');
    }

    
    /**
     * Method set_midia
     * Sample of usage: $click->midia = $object;
     * @param $object Instance of Midia
     */
    public function set_midia(Midia $object)
    {
        $this->midia = $object;
        $this->midia_id = $object->id;
    }
    
    /**
     * Method get_midia
     * Sample of usage: $click->midia->attribute;
     * @returns Midia instance
     */
    public function get_midia()
    {
        // loads the associated object
        if (empty($this->midia))
            $this->midia = new Midia($this->midia_id);
    
        // returns the associated object
        return $this->midia;
    }
    
    /**
     * Método estático que registra um clique vindo de um POST
     */
    public static function registrar()
    {
        $uri        = filter_input(INPUT_SERVER,'REQUEST_URI',FILTER_DEFAULT);
		$ip         = THelper::get_ip_address();
		$user_agent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');

		// pegamos os dados do POST
		$uri = isset($_POST["url"]) ? $_POST["url"] : $uri;
		$ref = isset($_POST["ref"]) ? $_POST["ref"] : null;

		if (!empty($ref))
		{
    		$geo = null;
    		if ( $ip <> '::1' && $ip <> '127.0.0.1' )
            {
                // consultamos os dados no GEO
                $geoplugin = new TGeoPlugin();
        		$geo = $geoplugin->locate($ip);
            }
    		
    		// verificar outros formatos de whatsapp
    		$whats = substr($ref, 0, 36); //36 -13
    		if ($whats == 'https://api.whatsapp.com/send?phone=')
    		{
    		    $ref = 'https://wa.me/'. substr($ref, 36, 13); // pegamos só os numeros
    		}
    		//https://api.whatsapp.com/send?phone=5511988889999
    		//https://wa.me/5511988889999
 		    
    		$midia = Midia::findURL($ref); //Midia::where('url','=',$ref)->first();
    		
    		if ($midia)
    		{
        		// prepara a zona (sempre UTF-8)
                setlocale( LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'pt_BR.utf-8', 'portuguese' );
                date_default_timezone_set( 'America/Sao_Paulo' );
                
        		$click = new Click;
        		$click->dt_clique  = date('Y-m-d H:i:s');
        		$click->pagina     = $uri;
        		$click->ip         = $ip;
        		$click->cidade     = (isset($geo->city)) ? $geo->city : 'Desconhecida';
        		$click->regiao     = (isset($geo->region)) ? $geo->region : 'Desconhecida';
        		$click->pais       = (isset($geo->country)) ? $geo->country : 'Desconhecido';
        		$click->navegador  = THelper::get_browser($user_agent);
        		$click->plataforma = THelper::get_platform($user_agent);
        		$click->midia_id   = $midia->id;
        		// salvando os dados no banco
        		$click->store();
            }
        }
    }
    
    /**
     * Método totalizadores
     */
    public static function getTotais($midia_id)
    {
        $dados['u_24_horas'] = 0;
		$dados['u_7_dias']   = 0;
		$dados['u_30_dias']  = 0;
		$dados['u_365_dias'] = 0;
		$dados['total']      = 0;
        
        //Últimas 24 horas
		$periodo = date("Y-m-d H:i:s", strtotime("-24 hours"));
		$dados['u_24_horas'] = self::where('midia_id','=',$midia_id)->where('dt_clique','>=',$periodo)->count();
        
        //Últimos 7 dias
		$periodo = date("Y-m-d H:i:s", strtotime("-7 days"));
        $dados['u_7_dias']   = self::where('midia_id','=',$midia_id)->where('dt_clique','>=',$periodo)->count();
        
        //Últimos 30 dias
		$periodo = date("Y-m-d H:i:s", strtotime("-30 days"));
		$dados['u_30_dias']  = self::where('midia_id','=',$midia_id)->where('dt_clique','>=',$periodo)->count();
		
		//Últimos 365 dias
		$periodo = date("Y-m-d H:i:s", strtotime("-365 days"));
		$dados['u_365_dias'] = self::where('midia_id','=',$midia_id)->where('dt_clique','>=',$periodo)->count();
		
		$dados['total']      = self::where('midia_id','=',$midia_id)->count();
		
		return $dados;
    }
    
    /**
     * Retorna o total de cliques por página
     */
    public static function getTotaisPagina($midia_id)
    {
        $conn = TTransaction::get(); // obtém a conexão
        $result = $conn->query('SELECT pagina, COUNT(id) as views FROM click WHERE midia_id = '.$midia_id.' GROUP BY pagina ORDER BY views DESC LIMIT 20');
        return $result->fetchALL(PDO::FETCH_OBJ);
    }
    


}

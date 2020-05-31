<?php
/**
 * THelper Class
 *
 * @version    1.3
 * @package    util
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 */
class THelper // extends TElement
{
    //function __construct(){}
    
    /**
     * Método que recebe uma data e retorna por extenso
     */
    public static function dataPorExtenso( $data, $semana = TRUE )
    {
        // prepara a zona (sempre UTF-8)
        setlocale( LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'pt_BR.utf-8', 'portuguese' );
        date_default_timezone_set( 'America/Sao_Paulo' );

        $sem = strftime( '%A', strtotime($data) );
        $dia = strftime( '%d', strtotime($data) );
        $mes = strftime( '%B', strtotime($data) );
        $ano = strftime( '%Y', strtotime($data) );
        
        if ( $semana )
            return $sem.', '.$dia.' de '.ucwords( $mes ).' de '.$ano;
        
        return $dia.' de '.ucwords( $mes ).' de '.$ano;
    }
    
    /*
     * Função que recebe um numero formatado e retorna um numero float.
     */
    public static function moedaToFloat( $strNumero )
    {
    	if ( is_null($strNumero) )
            $strNumero = 0;
            
    	//Remove simbolos de moeda e espaços em branco
    	$strNumero = trim( str_replace( "R$", null, $strNumero ) );
    	$strNumero = trim( str_replace( "$", null, $strNumero ) );
    
    	//Converte no padrão americano
    	$valor = str_replace( array(".",","), array("","."), $strNumero);
    	$valor = floatval($valor);
    
    	return $valor;
    }
    
    /**
     * Método que recebe um numero float e retorna formatado no padrão BR
     */
    public static function floatToMoeda( $float )
    {
        if ( is_null($float) )
            $float = 0;

        return number_format($float,2,',','.');
    }
    
    /**
     * Função que recebe um numero e retorna uma string com o número por extenso.
     */
    public static function numeroPorExtenso( $valor = 0, $moeda = FALSE, $bolPalavraFeminina = FALSE )
    {
    	//$valor = self::moedaToFloat( $valor );
    
    	$singular = NULL;
    	$plural = NULL;
    
    	if ( $moeda ) {
    		$singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
    		$plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões", "quatrilhões");
    	} else {
    		$singular = array("", "", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
    		$plural = array("", "", "mil", "milhões", "bilhões", "trilhões", "quatrilhões");
    	}
    
    	$c = array("", "cem", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
    	$d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa");
    	$d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezesete", "dezoito", "dezenove");
    	$u = array("", "um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove");
    
    	if ( $bolPalavraFeminina ) {
    
    		if ($valor == 1) {
    			$u = array("", "uma", "duas", "três", "quatro", "cinco", "seis", "sete", "oito", "nove");
    		} else {
    			$u = array("", "um", "duas", "três", "quatro", "cinco", "seis", "sete", "oito", "nove");
    		}
    
    		$c = array("", "cem", "duzentas", "trezentas", "quatrocentas", "quinhentas", "seiscentas", "setecentas", "oitocentas", "novecentas");
    
    	}
    
    	$z = 0;
    
    	$valor = number_format( $valor, 2, ".", "." );
    	$inteiro = explode( ".", $valor );
    
    	for ( $i = 0; $i < count( $inteiro ); $i++ ) {
    		for ( $ii = mb_strlen( $inteiro[$i] ); $ii < 3; $ii++ ) {
    			$inteiro[$i] = "0" . $inteiro[$i];
    		}
    	}
    
    	// $fim identifica onde que deve se dar junção de centenas por "e" ou por "," ;)
    	$rt = null;
    	$fim = count( $inteiro ) - ($inteiro[count( $inteiro ) - 1] > 0 ? 1 : 2);
    	for ( $i = 0; $i < count( $inteiro ); $i++ )
    	{
    		$valor = $inteiro[$i];
    		$rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
    		$rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
    		$ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";
    
    		$r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd && $ru) ? " e " : "") . $ru;
    		$t = count( $inteiro ) - 1 - $i;
    		$r .= $r ? " " . ($valor > 1 ? $plural[$t] : $singular[$t]) : "";
    		if ( $valor == "000")
    			$z++;
    		elseif ( $z > 0 )
    			$z--;
    
    		if ( ($t == 1) && ($z > 0) && ($inteiro[0] > 0) )
    			$r .= ( ($z > 1) ? " de " : "") . $plural[$t];
    
    		if ( $r )
    			$rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
    	}
    
    	$rt = mb_substr( $rt, 1 );
    	
    	// garantindo que tudo esteja convertido para UTF-8
    	$retorno = utf8_encode( $rt ? trim( $rt ) : "zero" );
    
    	return $retorno;
    }
    
    /**
     * Função que calcula datas
     * @param $data   : é a data no formato Y-m-d 
     * @param $dias   : quantidade de dias para somar
     * @param $meses  : quantidade de meses para somar
     * @param $anos   : quantidade de anos para somar
     */
    public static function somarData($data, $dias, $meses = 0, $ano = 0)
    {
        //passe a data no formato yyyy-mm-dd
        $data = explode("-", $data);
        $newData = date("Y-m-d", mktime(0, 0, 0, $data[1] + $meses, $data[2] + $dias, $data[0] + $ano) );
        return $newData;
    }
    
    /*
     * Função que retorna parte de uma string sem quebrar as palavras
     */
    public static function substrWords(string $str, $no_letter = 50)
    {
    	if(strlen($str) > $no_letter) {
    		$str = substr($str,0,strpos($str,' ',$no_letter)) . "..."; //strpos to find ' ' after 50 characters.
    	}
    
    	return $str;
    }
    
	
	function imageCreateFromAny($filepath)
	{
        $type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize()
        $allowedTypes = array(
            1,  // [] gif
            2,  // [] jpg
            3,  // [] png
            6   // [] bmp
        );
        if (!in_array($type, $allowedTypes))
        {
            return false;
        }
        switch ($type)
        {
            case 1 :
                $im = imageCreateFromGif($filepath);
                break;
            case 2 :
                $im = imageCreateFromJpeg($filepath);
                break;
            case 3 :
                $im = imageCreateFromPng($filepath);
                break;
            case 6 :
                $im = imageCreateFromBmp($filepath);
                break;
        }
        return $im;
    }
	
	/**
	 * Método estático para converter imagens para o formato WebP
	 * @param $file    - caminho do arquivo de origem
	 * @param $alvo    - caminho do arquivo final
	 */
	public static function toWebP($file, $alvo)
	{
        $type = exif_imagetype($file); // [] if you don't have exif you could use getImageSize()
        $allowedTypes = array(
            1,  // [] gif
            2,  // [] jpg
            3,  // [] png
            6   // [] bmp
        );
        switch ($type)
        {
            case 1 :
                $srcImg = imageCreateFromGif($file);
                break;
            case 2 :
                $srcImg = imageCreateFromJpeg($file);
                break;
            case 3 :
                $srcImg = imageCreateFromPng($file);
                break;
            case 6 :
                $srcImg = imageCreateFromBmp($file);
                break;
        }

        $im = imagecreatetruecolor(imagesx($srcImg), imagesy($srcImg));
        imagecopy($im, $srcImg, 0, 0, 0, 0, imagesx($srcImg), imagesy($srcImg));

        // salvamos a imagem convertida, com 80% de qualidade
        $ret = imagewebp($im, $alvo, 80);
        
        // liberamos a memória
        imagedestroy($im);
        
        if (!$ret)
        {
            throw new Exception(AdiantiCoreTranslator::translate('Error while copying file to ^1', $target_file));
        }
	}
	
	
	/**
	 * Método para converter imagem GIF e PNG para JPG com fundo branco
	 * @param $width - redimenciona pelo width
	 */
	public static function GIFPNGtoJPG($filePath_origem, $filePath_destino, $width = NULL)
	{
        $arquivo = pathinfo($filePath_origem);
        $filePath_destino = $filePath_destino . ".jpg";
        
        // se for jpg ou jpeg, ignora o processo
        if ($arquivo['extension'] != 'jpg' && $arquivo['extension'] != 'jpeg')
        {
            if ($arquivo['extension'] == 'png')
            {
                $image = imagecreatefrompng($filePath_origem);
            }
            else if ($arquivo['extension'] == 'gif')
            {
                $image = imagecreatefromgif($filePath_origem);
            }
        
            //$image = imagecreatefrompng($filePath_origem);
            $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
            imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
            imagealphablending($bg, TRUE);
            imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
            imagedestroy($image);
            // redimenciona se necessário
            if ( $width )
            {
                $origWidth = imagesx($bg);
                $origHeight = imagesy($bg);
            
                $ratio = $origWidth / $width;
                $height = $origHeight / $ratio;
            
                $thumbImg = imagecreatetruecolor($width, $height);
                imagecopyresized($thumbImg, $bg, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);
                
                $ret['height'] = $height;
            }
            else
            {
                $thumbImg = $bg;
                imagedestroy($bg);
                
                $ret['width'] = imagesx($thumbImg);
                $ret['height'] = imagesy($thumbImg);
            }
            $quality = 90; // 0 = worst / smaller file, 100 = better / bigger file 
            imagejpeg($thumbImg, $filePath_destino, $quality);
            imagedestroy($thumbImg);
        }
        else
        {
            $image = imagecreatefromjpeg($filePath_origem);
            $quality = 90; // 0 = worst / smaller file, 100 = better / bigger file 
            
            if ( $width )
            {
                $origWidth = imagesx($image);
                $origHeight = imagesy($image);
            
                $ratio = $origWidth / $width;
                $height = $origHeight / $ratio;
            
                $thumbImg = imagecreatetruecolor($width, $height);
                imagecopyresized($thumbImg, $image, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);
                
                $ret['height'] = $height;
                
                imagejpeg($thumbImg, $filePath_destino, $quality);
                imagedestroy($thumbImg);
            }
            else
            {
                $ret['width'] = imagesx($image);
                $ret['height'] = imagesy($image);
 
                imagejpeg($image, $filePath_destino, $quality);
            }
            
            imagedestroy($image);
        }
        return $filePath_destino;
    }
	
	/**
	 * Método para redimencionar imagens
	 */
	public static function criarThumbnail($imageDirectory, $imageName, $thumbDirectory, $thumbWidth)
	{
        $explode = explode(".", $imageName);
        $filetype = $explode[1];
    
        if ($filetype == 'jpg') {
            $srcImg = imagecreatefromjpeg("$imageDirectory/$imageName");
        } else
        if ($filetype == 'jpeg') {
            $srcImg = imagecreatefromjpeg("$imageDirectory/$imageName");
        } else
        if ($filetype == 'png') {
            $srcImg = imagecreatefrompng("$imageDirectory/$imageName");
        } else
        if ($filetype == 'gif') {
            $srcImg = imagecreatefromgif("$imageDirectory/$imageName");
        }
    
        $origWidth = imagesx($srcImg);
        $origHeight = imagesy($srcImg);
    
        $ratio = $origWidth / $thumbWidth;
        $thumbHeight = $origHeight / $ratio;
    
        $thumbImg = imagecreatetruecolor($thumbWidth, $thumbHeight);
        imagecopyresized($thumbImg, $srcImg, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $origWidth, $origHeight);
    
        if ($filetype == 'jpg') {
            imagejpeg($thumbImg, "$thumbDirectory/$imageName");
        } else
        if ($filetype == 'jpeg') {
            imagejpeg($thumbImg, "$thumbDirectory/$imageName");
        } else
        if ($filetype == 'png') {
            imagepng($thumbImg, "$thumbDirectory/$imageName");
        } else
        if ($filetype == 'gif') {
            imagegif($thumbImg, "$thumbDirectory/$imageName");
        }
        
        imagedestroy($thumbImg);
    }
    
    /**
     * Método para formatar um link
     * @param $value    string do link
     * @param $obj      TRUE retorna um objeto HyperLink, FALSE retorna string 
     */
    public static function formataLink( $value, $obj = FALSE )
    {
        $protocolo = substr($value, 0, 7);
        $link = $value;
        
        switch ($protocolo)
        {
            case 'http://':
                $link = str_replace('http://','',$value);
                break;
            case 'https:/':
                $protocolo .= '/';
                $link = str_replace('https://','',$value);
                break;
            default :
                $protocolo = 'http://';
        }
        
        $ret = new THyperLink($link, $protocolo . $link );
        
        return ($obj == FALSE) ? $ret->__toString() : $ret;
    }
    
    /**
     * onSite - adiciona o "http://' na url
     * @param $str String com a url
     */
    public static function formataURL( $str )
    {
        if ($str === 'http://' OR $str === 'https://' OR $str === '')
		{
			return '';
		}

		if (strpos($str, 'http://') !== 0 && strpos($str, 'https://') !== 0)
		{
			return 'http://'.$str;
		}

		return $str;
    }
    
    /**
     * Método para limpar todas as variáveis temporárias que estejam na sessão
     */
    public static function clearSession()
    {
        $arr_key = ['logged','login','userid','usergroupids','userunitids','username','usermail','frontpage','programs','userunitid','userunitname'];
        
        if (defined('APPLICATION_NAME'))
        {
            foreach( $_SESSION[APPLICATION_NAME] as $key => $value )
            {
                if ( !in_array($key,$arr_key) )
                {
                    TSession::delValue($key);
                }
            }
        }
        else
        {
            foreach( $_SESSION as $key => $value )
            {
                if ( !in_array($key,$arr_key) )
                {
                    TSession::delValue($key);
                }
            }
        }
    }
    
    /**
     * Método para exibir uma imagem no datagrid
     * @param $image
     * @param $height       tamanho em 'px' ou '%'
     * @param $class        user-image ou img-circle
     * @param $style        outros estilos
     */
    public static function showImagem($image, $height = '28px', $class = 'img-circle', $style='position: absolute; margin-left: -14px; margin-top: -4px;')
    {
        $img = new TImage($image);
        $img->class = $class; //user-image ou img-circle
        $img->style = 'height: '.$height.'; '.$style;
        return $img;
    }
    
    
    /**
     * Método para criar um bloco div no estilo Bootstrap
     * @param $object     objeto a ser inserido na div
     * @param $col        valor da coluna em string (1-12)
     */
    public static function divBootstrap($object, $col = '6')
    {
        $div = TElement::tag('div', $object, ['class'=>'col-lg-'.$col]);
        return $div;
    }
    
    /**
     * Transforma uma string em url-amigavel usando URLify.
     * @param $str    string a ser analizada
     * @param $fone   bool   indica ser um numero de telefone e aplica regras diferenciadas
     */
    public static function urlAmigavel($str, $fone=false)
    {
        if ($fone)
        {
            $str = str_replace(['-',' ','.'],'',$str);
        }
        //$str = trim($str); // elimia espaços em branco no iní­cio e no final da string
        //$str = strtolower( URLify::filter( $str ) ); // remove os acentos e converte para minusculas
        $str = URLify::filter( $str, 100, 'latin' ); // remove os acentos e converte para minusculas
        //$slugify = new Slugify();
        //return $slugify->slugify($str);
        return $str;
    }

    /**
     * Monta um alerta estilo Bootstrap
     * @param $tipo        pode ser: success, info, warning, danger
     * @param $mensagem    string
     */
    public static function alerta($tipo, $mensagem, $destaque = NULL)
    {
        $div = TElement::tag('div', '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>', ['class'=>'alert alert-'.$tipo, 'role'=>'alert']);
        $msg = $destaque ? TElement::tag('strong', $destaque) .$mensagem : $mensagem;
        $div->add($msg);
        
        return $div;
    }
    
    /**
     * Método TAppLink para adicionar um botão com waves-effect
     * @param    $value    text content
     * @param    $action   TAction Object
     * @param    $icon     text icon (fa:user)
     * @param    $class    text class
     */
    public static function TAppLink($value, TAction $action, $icon = null, $class = 'btn btn-primary btn-lg')
    {
        $btn = new TActionLink($value, $action, null, null, null, $icon);
        $btn->class = $class . ' waves-effect';
        return $btn;
    }
    
    
    /**
     * Pega o número de IP do usuário
     */
    public static function get_ip_address()
    {
        $ip_keys = array('HTTP_CF_CONNECTING_IP','HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($ip_keys as $key)
        {
            if (array_key_exists($key, $_SERVER) === true)
            {
                foreach (explode(',', $_SERVER[$key]) as $ip)
                {
                    // trim for safety measures
                    $ip = trim($ip);
                    // attempt to validate IP
                    if (self::validate_ip($ip))
                    {
                        return $ip;
                    }
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
    }
    
    /**
     * Valida um número de IP
     */
    public static function validate_ip($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false)
        {
            return false;
        }
        return true;
    }
    
    /**
     * Determina o browser do usuário
     */
    public static function get_browser($user_agent)
    {
        $browsers = array(
        	'OPR'					=> 'Opera',
        	'Flock'					=> 'Flock',
        	'Edge'					=> 'Edge',
        	'Chrome'				=> 'Chrome',
        	'Opera.*?Version'		=> 'Opera',
        	'Opera'					=> 'Opera',
        	'MSIE'					=> 'Internet Explorer',
        	'Internet Explorer'		=> 'Internet Explorer',
        	'Trident.* rv'          => 'Internet Explorer',
        	'Shiira'				=> 'Shiira',
        	'Firefox'				=> 'Firefox',
        	'Chimera'				=> 'Chimera',
        	'Phoenix'				=> 'Phoenix',
        	'Firebird'				=> 'Firebird',
        	'Camino'				=> 'Camino',
        	'Netscape'				=> 'Netscape',
        	'OmniWeb'				=> 'OmniWeb',
        	'Safari'				=> 'Safari',
        	'Mozilla'				=> 'Mozilla',
        	'Konqueror'				=> 'Konqueror',
        	'icab'					=> 'iCab',
        	'Lynx'					=> 'Lynx',
        	'Links'					=> 'Links',
        	'hotjava'				=> 'HotJava',
        	'amaya'					=> 'Amaya',
        	'IBrowse'				=> 'IBrowse',
        	'Maxthon'				=> 'Maxthon',
        	'Ubuntu'				=> 'Ubuntu Web Browser'
        );
        
        foreach ($browsers as $key => $value)
        {
			if(preg_match('|' . $key . '.*?([0-9\.]+)|i', $user_agent))
				return $value;
		}
    }
    
    /**Jornal Folha de Araçoiaba
     * Determina a plataforma atual do usuário
     */
    public function get_platform($user_agent)
	{
		$platforms = array(
        	'windows nt 10.0'               => 'Windows 10',
        	'windows nt 6.3'                => 'Windows 8.1',
        	'windows nt 6.2'                => 'Windows 8',
        	'windows nt 6.1'                => 'Windows 7',
        	'windows nt 6.0'                => 'Windows Vista',
        	'windows nt 5.2'                => 'Windows 2003',
        	'windows nt 5.1'                => 'Windows XP',
        	'windows nt 5.0'                => 'Windows 2000',
        	'windows nt 4.0'                => 'Windows NT 4.0',
        	'winnt4.0'			=> 'Windows NT 4.0',
        	'winnt 4.0'			=> 'Windows NT',
        	'winnt'				=> 'Windows NT',
        	'windows 98'                    => 'Windows 98',
        	'win98'				=> 'Windows 98',
        	'windows 95'                    => 'Windows 95',
        	'win95'				=> 'Windows 95',
        	'windows phone'			=> 'Windows Phone',
        	'windows'			=> 'Unknown Windows OS',
        	'android'			=> 'Android',
        	'blackberry'                    => 'BlackBerry',
        	'iphone'			=> 'iOS',
        	'ipad'				=> 'iOS',
        	'ipod'				=> 'iOS',
        	'os x'				=> 'Mac OS X',
        	'ppc mac'			=> 'Power PC Mac',
        	'freebsd'			=> 'FreeBSD',
        	'ppc'				=> 'Macintosh',
        	'linux'				=> 'Linux',
        	'debian'			=> 'Debian',
        	'sunos'				=> 'Sun Solaris',
        	'beos'				=> 'BeOS',
        	'apachebench'                   => 'ApacheBench',
        	'aix'				=> 'AIX',
        	'irix'				=> 'Irix',
        	'osf'				=> 'DEC OSF',
        	'hp-ux'				=> 'HP-UX',
        	'netbsd'			=> 'NetBSD',
        	'bsdi'				=> 'BSDi',
        	'openbsd'			=> 'OpenBSD',
        	'gnu'				=> 'GNU/Linux',
        	'unix'				=> 'Unknown Unix OS',
        	'symbian' 			=> 'Symbian OS'
        );
        
		foreach ($platforms as $key => $value)
		{
			if(preg_match('|' . preg_quote($key) . '|i', $user_agent))
				return $value;
		}
	}
    
    /**
     * Remove todos os arquivos, sub-diretorios e seus arquivos
     * de dentro do caminho fornecido.
     * 
     * @param string $dir Caminho completo para o diretorio a esvaziar.
     */
    public static function apagarTudo ($dir)
    {
        // implementar regra para não apagar pastas do sistema
        if (in_array($dir,['../lib','../templates','../admin','/app','/lib','/rest','/vendor','/']) && $dir !== '../images')
            throw new Exception('error','Ação não permitida!');
        
        if (is_dir($dir))
        {
            $iterator = new \FilesystemIterator($dir);
    
            if ($iterator->valid())
            {
                $di = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
                $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
    
                foreach ( $ri as $file )
                {
                    $file->isDir() ?  rmdir($file) : unlink($file);
                }
            }
        }
    }
    
    /**
     * Retorna o tamanho de um arquivo de forma "legível"
     * @param int $size    Tamanho do arquivo em Bytes
     */
    public static function fileSize(int $size)
    {
        $base = log($size) / log(1024);
        $suffix = array(" B", " KB", " MB", " GB", " TB");
        $f_base = floor($base);
        return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
    }
    
    /******************************************************************************************/
    /******************************************************************************************/
    
    /**
     * Método para retornar o nome do Usuário
     */
    public static function showUserName( $value )
    {
        TTransaction::open('permission');
        $ret = SystemUser::getUserName($value);
        TTransaction::close();
        return $ret;
    }
    
    /**
     * Pega todas as preferências do site ou uma em específico
     * @param $id    string
     */
    public static function getPreferences($id=null)
    {
        try
        {
            // open a transaction with database
            TTransaction::open('permission');
            $preferences = empty($id) ? SystemPreference::getAllPreferences() : SystemPreference::getPreference($id);
            // close the transaction
            TTransaction::close();
            
            if ($preferences)
            {
                return $preferences;
            } 
            return FALSE;
        }
        catch (Exception $e) // in case of exception
        {
            return FALSE;
            // shows the exception error message
            //new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * Retorna o thema atual
     */
    public static function getTheme()
    {
        try
        {
            TTransaction::open('sistema');
            $template = Template::where('padrao','=','t')->load();
            TTransaction::close();
            
            return $template[0]->nome_fisico;
        }
        catch (Exception $e) // in case of exception
        {
            return FALSE;
            // shows the exception error message
            //new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * Registra um acesso
     */
    public static function setTrafego()
    {
        try
        {
            TTransaction::open('sistema');
            Trafego::registrar();
            TTransaction::close();
        }
        catch (Exception $e)
        {
            return FALSE;
            TTransaction::rollback();
        }
    }

}

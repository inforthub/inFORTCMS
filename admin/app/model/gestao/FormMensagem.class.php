<?php
/**
 * FormMensagem Active Record
 *
 * @version    1.0
 * @package    model
 * @subpackage gestao
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 */
class FormMensagem extends TRecord
{
    const TABLENAME = 'form_mensagem';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $formulario;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('assunto');
        parent::addAttribute('mensagem');
        parent::addAttribute('email_origem');
        parent::addAttribute('email_destino');
        parent::addAttribute('dt_mensagem');
        parent::addAttribute('enviada');
        parent::addAttribute('formulario_id');
    }

    
    /**
     * Method set_formulario
     * Sample of usage: $form_mensagem->formulario = $object;
     * @param $object Instance of Formulario
     */
    public function set_formulario(Formulario $object)
    {
        $this->formulario = $object;
        $this->formulario_id = $object->id;
    }
    
    /**
     * Method get_formulario
     * Sample of usage: $form_mensagem->formulario->attribute;
     * @returns Formulario instance
     */
    public function get_formulario()
    {
        // loads the associated object
        if (empty($this->formulario))
            $this->formulario = new Formulario($this->formulario_id);
    
        // returns the associated object
        return $this->formulario;
    }
    
    
    /**
     * Métido estático que guarda a mensagem no banco e envia o e-mail
     * @param $formulario url do formulário
     */
    public static function Enviar($formulario)
    {
        try
        {
            $campos = [];
            $ret = '';
            $preferencias = SystemPreference::getAllPreferences();
            
            // pegamos os dados do formulário
            $form = Formulario::getByUrl($formulario);
            
            // verificando o captcha, se necessário
            switch ($form->recaptcha)
            {
                case 'r':
                    $campos = self::reCaptcha($preferencias);
                    break;
                case 'h':
                    $campos = self::hCaptcha($preferencias);
                    break;
                default :
                    // pegamos os dados do Post
                    foreach ($_POST as $key => $value)
                    {
                        $campos[$key] = trim($value);
                    }
                    break;
            }
            

            if ($form instanceof Formulario && count($campos) > 0)
            {
                // montamos o corpo da mensagem conforme o modelo
                $parse        = new TParser;
                $mensagem     = $parse->parse_string( $form->html_email, $campos, true);
                $assunto      = isset($campos['assunto']) ? $campos['assunto'] : 'Mensagem vinda do site - '.$preferencias['pref_site_nome'];
                $email_origem = isset($campos['email']) ? $campos['email'] : $preferencias['mail_from'];
                $email_para   = isset($form->email_destino) ? $form->email_destino : $preferencias['mail_to'];
                $email_de     = $preferencias['mail_from'];
                
                if (!filter_var($email_origem, FILTER_VALIDATE_EMAIL))
    			{
    				throw new Exception("Ocorreu um erro! Formato de e-mail inválido.");
    			}
                
                //Setando os headers
			    $cabecalho = implode ( "\n",array ( 
			    	"MIME-Version: 1.1",
			    	"X-Priority: 3",
			    	"Content-Type: text/html; charset=UTF-8",
			    	"From: $email_de <$email_de>",
			    	"Return-Path: ".$email_origem,
			    	//"Cc: $this->com_copia",
			    	//"Bcc: $this->com_copia_oculta",
			    	"Reply-To: ".$email_origem,
			    	"X-Mailer: PHP/".phpversion()
			    ) );
			    /*
                // montamos o cabeçalho do e-mail
                $cabecalho   = "MIME-Version: 1.0\r\n";
    			$cabecalho 	.= "Content-type: text/html; charset=iso-8859-1\r\n";
    			$cabecalho 	.= "From:$email_origem";
    			$cabecalho 	.= "Reply-To: $email_origem\r\n"."X-Mailer: PHP/".phpversion();
    			*/
    			TTransaction::open('sistema');
                
                // preparamos a mensagem para ser quardada em banco de dados
                $object = new FormMensagem;
                $object->assunto       = $assunto;
                $object->mensagem      = $mensagem;
                $object->email_origem  = $email_origem;
                $object->email_destino = $email_para;
                //$object->dt_mensagem   = date('Y-m-d H:i:s');
                //$object->enviada       = 'f';
                $object->formulario_id = $form->id;
    			
    			// enviamos o e-mail
    			if(mail($email_para, $assunto, $mensagem, $cabecalho))
    			{
    				$ret = ['status'=>1, 'mensagem'=>$form->msg_sucesso];
    				$object->enviada = 't';
    			}
    			else
    			{
    				$ret = ['status'=>0, 'mensagem'=>$form->msg_erro];
    			}
                
                // salva a mensagem no banco de dados
                $object->store();
                
                TTransaction::close();
                
                return $ret;
            }
            
            throw new Exception("Ocorreu um erro! Verifique os campos e tente novamente.");
        }
        catch (Exception $e) // in case of exception
        {
            //$ret_mensagem = isset($form->msg_erro) ? $form->msg_erro .'<br>'. $e->getMessage() : $e->getMessage();
            $ret_mensagem = $e->getMessage();
            
            // montando array de retorno
            $ret = ['status'=>0, 'mensagem'=>$ret_mensagem];
            
            // undo all pending operations
            TTransaction::rollback();
            
            return $ret;
        }
    }
    
    /**
     * Faz a verificação por reCaptcha
     */
    public static function reCaptcha($preferencias)
    {
        // carregamos a classe ReCaptcha
        require('../lib/recaptcha/autoload.php');
        
        $arr = [];
        $recaptchaSecret = $preferencias['pref_recaptcha_secretkey'];

        /* validate the ReCaptcha, if something is wrong, we throw an Exception,
		i.e. code stops executing and goes to catch() block */
    
        if (!isset($_POST['g-recaptcha-response'])) {
			throw new Exception("ReCaptcha não configurado.");
        }
        
        $recaptcha = new \ReCaptcha\ReCaptcha($recaptchaSecret, new \ReCaptcha\RequestMethod\CurlPost());
        
         /* we validate the ReCaptcha field together with the user's IP address */
        $response = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
        
        if ($response->isSuccess())
        {
            // pegamos os dados do Post
            foreach ($_POST as $key => $value)
            {
                $arr[$key] = trim($value);
            }
            
            // removemos a respota do recaptcha, se houver
            unset($arr['g-recaptcha-response']);
        }
        
        return $arr; 
    }
    
    /**
     * Faz a verificação por hCaptcha
     */
    public static function hCaptcha($preferencias)
    {
        $arr = [];
        
        if(isset($_POST['h-captcha-response']) && !empty($_POST['h-captcha-response']))
        {
            $secret = $preferencias['pref_hcaptcha_secretkey'];
            $verifyResponse = file_get_contents('https://hcaptcha.com/siteverify?secret='.$secret.'&response='.$_POST['h-captcha-response'].'&remoteip='.$_SERVER['REMOTE_ADDR']);
            $responseData = json_decode($verifyResponse);
            if($responseData->success)
            {
                // pegamos os dados do Post
                foreach ($_POST as $key => $value)
                {
                    $arr[$key] = trim($value);
                }
                
                // removemos a respota do recaptcha, se houver
                unset($arr['h-captcha-response']);
            }
        }
        
        return $arr;
    }
    


}

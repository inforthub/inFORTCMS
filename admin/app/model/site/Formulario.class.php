<?php
/**
 * Formulario Active Record
 * @author  <your-name-here>
 */
class Formulario extends TRecord
{
    const TABLENAME = 'formulario';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('html_email');
        parent::addAttribute('html_site');
        parent::addAttribute('msg_erro');
        parent::addAttribute('msg_sucesso');
        parent::addAttribute('email_destino');
        parent::addAttribute('ativo');
    }
    
    /**
     * Método padrão para envio de email
     */
    public static function sendMail($param)
    {
        $arr = ["1"=>'Indicação', "2"=>'Google', "3"=>'Facebook', "4"=>'Outro'];
        $param["comonosachou"] = $arr[$param["comonosachou"]];
        
        $contato = new Contato;
        
        // salvando o email em banco de dados
        $contato->nome         = $param['nome'];
        $contato->empresa      = $param['empresa'];
        $contato->telefone     = $param['telefone'];
        $contato->email        = $param['email'];
        $contato->assunto      = $param['assunto'];
        $contato->comonosachou = $param["comonosachou"];
        $contato->mensagem     = $param['mensagem'];
        $contato->ip           = THelper::get_ip_address();
        $contato->status       = 'f'; 
        
        $ret = self::enviar($param);
        
        if ($ret[0])
        {
            $contato->status = 't';
            $contato->store();
            
            return THelper::alerta('success','E-mail enviado com sucesso. Obrigado pelo contato.','Muito bem!');
        }
        else
        {
            $contato->store();
            
            return THelper::alerta('danger','Não foi possível enviar seu e-mail. Tente novamente mais tarde.<br>'.$ret[1]->getMessage(),'Opa!');
        }
    }
    
    /*
     *
     */
    public static function enviar($param)
    {
        try
        {
            TTransaction::open('permission');
            
            $preferences = SystemPreference::getAllPreferences();
            /*
            $preferences->pref_smtp_auth
            $preferences->pref_smtp_host
            $preferences->pref_smtp_port
            $preferences->pref_smtp_user
            $preferences->pref_smtp_pass
            
            $preferences->pref_mail_from
            $preferences->pref_mail_to
            
            $pref_site_nome
            $pref_site_dominio
            */
            TTransaction::close();
            
            // iniciando variáveis
            $parse = new TParser;
            $mail  = new TMail;
            
            // montando o html do email
            //$mail_template = file_get_contents('app/resources/email_contato.html');
            //$msg = $parse->parse_string($mail_template, $param, TRUE); //  str_replace('{PROTOCOLO}', $protocolo, $mail_template);
            
            // formatando a mensagem
			$msg  = '<h2><b>Formulário de Contato</b></h2>';
			$msg .= '<p>Email originado do site da <b>Clínica Paraíso</b> em '.date('d-m-Y').' às '.date('H:i:s').'</p><hr>';
			$msg .= '<p><b>Nome:</b> '.$param["nome"].'<br />';
			$msg .= '<b>Email:</b> '.$param["email"].'<br />';
			$msg .= '<b>Telefone:</b> '.$param["telefone"].'<br />';
			$msg .= '<b>Empresa:</b> '.$param["empresa"].'<br />';
			$msg .= '<b>Como nos achou:</b> '.$param["comonosachou"].'<br />';
			$msg .= '<b>Assunto:</b> '.$param["assunto"].'</p><hr>';
			$msg .= '<b>Mensagem:</b><br /> '.$param["mensagem"].'<br /><br />';

			if ($param['assunto'] == '' OR $param['assunto'] == NULL)
			{
				$param['assunto'] = 'Formulário de Contato da Loja Maestro';
			}

            // setando os parametros para envio
            $mail->setFrom( trim($preferences['pref_mail_from']), 'Site Loja Maestro' ); // email de origem
            $mail->addAddress( trim($preferences['pref_mail_to']), 'Loja Maestro' ); // destinatário
            $mail->setSubject($param['assunto']); // assunto

            //$mail->SMTPSecure = 'ssl';
            //$mail->SMTPAuth = true;
            if ($preferences['pref_smtp_auth'])
            {
                $mail->SetUseSmtp();
                $mail->SetSmtpHost($preferences['pref_smtp_host'], $preferences['pref_smtp_port']); // smtp host, porta
                $mail->SetSmtpUser($preferences['pref_smtp_user'], $preferences['pref_smtp_pass']); // smtp user, senha
            }
            
            $mail->setHtmlBody($msg);
            
            // enviando e-mail
            $mail->send();
            
            return array(TRUE); //THelper::alerta('success','E-mail enviado com sucesso. Obrigado pelo contato.','Muito bem!');
        }
        catch(Exception $e)
        {
            //new TMessage('error', 'Não foi possível enviar seu e-mail.');
            return array(FALSE, $e); //THelper::alerta('danger','Não foi possível enviar seu e-mail. Tente novamente mais tarde.<br>'.$e->getMessage(),'Opa!');
        }
    }


}

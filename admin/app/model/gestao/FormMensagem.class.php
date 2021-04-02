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
            
            // pegamos os dados do Post
            foreach ($_POST as $key => $value)
            {
                $campos[$key] = trim($value);
            }
            
            // pegamos os dados do formulário
            $form = Formulario::getByUrl($formulario);
            
            if ($form instanceof Formulario && count($campos) > 0)
            {
                // montamos o corpo da mensagem conforme o modelo
                $parse        = new TParser;
                $mensagem     = $parse->parse_string( $form->html_email, $campos, true);
                $assunto      = isset($campos['assunto']) ? $campos['assunto'] : 'Mensagem vinda do site - '.THelper::getPreferences('pref_site_nome');
                $email_origem = isset($campos['email']) ? $campos['email'] : THelper::getPreferences('mail_from');;
                $email_para   = isset($form->email_destino) ? $form->email_destino : THelper::getPreferences('mail_to');

                TTransaction::open('sistema');
                
                // quardamos a mensagem no banco de dados
                $object = new FormMensagem;
                $object->assunto       = $assunto;
                $object->mensagem      = $mensagem;
                $object->email_origem  = $email_origem;
                $object->email_destino = $email_para;
                //$object->dt_mensagem   = date('Y-m-d H:i:s');
                //$object->enviada       = 'f';
                $object->formulario_id = $form->id;
                $object->store();
                
                TTransaction::close();
                
                // enviamos o email
                MailService::send( $email_para, $assunto, $mensagem, 'html' );
                
                TTransaction::open('sistema');
                
                $object->enviada = 't';
                $object->store();
                
                TTransaction::close();
                
                // montando array de retorno
                $ret = ['titulo'=>'Mensagem Enviada', 'mensagem'=>$form->msg_sucesso];
                
                return $ret;
            }
            
            throw new Exception("Ocorreu um erro! Tente novamente mais tarde.");
        }
        catch (Exception $e) // in case of exception
        {
            $ret_mensagem = isset($form->msg_erro) ? $form->msg_erro : $e->getMessage();
            
            // montando array de retorno
            $ret = ['titulo'=>'Erro', 'mensagem'=>$ret_mensagem];
            
            // undo all pending operations
            TTransaction::rollback();
            
            return $ret;
        }
    }
    


}

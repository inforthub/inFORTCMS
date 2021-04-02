<?php
/**
 * FormularioForm Registration
 *
 * @version     1.0
 * @package     control
 * @subpackage  gestao
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class FormularioForm extends TPage
{
    protected $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('sistema');              // defines the database
        $this->setActiveRecord('Formulario');     // defines the active record
        $this->setAfterSaveAction( new TAction(['FormularioList', 'onClear']) );
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Formulario');
        $this->form->setFormTitle('Editor de Formulario');
        $this->form->setFieldSizes('100%');
        

        // create the form fields
        $id            = new TEntry('id');
        $nome          = new TEntry('nome');
        $url           = new TEntry('url');
        $html_email    = new TTextSourceCode('html_email');
        $html_site     = new TTextSourceCode('html_site');
        $msg_erro      = new TEntry('msg_erro');
        $msg_sucesso   = new TEntry('msg_sucesso');
        $email_destino = new TEntry('email_destino');
        $captcha       = new TCombo('recaptcha');
        $ativo         = new TRadioGroup('ativo');
        $code          = new TSourceCode('code');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] , [] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] , [ new TLabel('Url') ], [ $url ] );
        $this->form->addFields( [ new TLabel('Html do Site') ], [ $html_site ] );
        $this->form->addFields( [ new TLabel('Html do Email') ], [ $html_email ] );
        $this->form->addFields( [ new TLabel('Mensagem de Erro') ], [ $msg_erro ] );
        $this->form->addFields( [ new TLabel('Mensagem de Sucesso') ], [ $msg_sucesso ] );
        $this->form->addFields( [ new TLabel('Habilitar Captcha') ], [$captcha] );
        $this->form->addFields( [], [ new TLabel('Adicione o seguinte código no formulário:'), $code] );
        $this->form->addFields( [ new TLabel('Email Destino') ], [ $email_destino ] , [ new TLabel('Ativo') ], [ $ativo ] );

        $nome->addValidation('Nome', new TRequiredValidator);
        $url->addValidation('Url', new TRequiredValidator);
        $html_email->addValidation('Html Email', new TRequiredValidator);
        $html_site->addValidation('Html Site', new TRequiredValidator);
        $msg_erro->addValidation('Msg Erro', new TRequiredValidator);
        $msg_sucesso->addValidation('Msg Sucesso', new TRequiredValidator);
        $email_destino->addValidation('Email Destino', new TRequiredValidator);
        $email_destino->addValidation('Email Destino', new TEmailValidator);


        // set sizes
        $id->setEditable(FALSE);
        $captcha->addItems(['n'=>'Nenhum', 'r'=>'reCaptcha v3', 'h'=>'hCaptcha']);
        $captcha->setValue('n');
        $ativo->setSize(80);
        $ativo->addItems(['t'=>'Sim','f'=>'Não']);
        $ativo->setLayout('horizontal');
        $ativo->setUseButton();


        // criando eventos
        $nome->setExitAction(new TAction([$this,'onNomeChange']));
        $captcha->setChangeAction(new TAction(array($this, 'onChangeCaptcha')));
        self::onChangeCaptcha( ['recaptcha' => 'n'] );
         
        // create the form actions
        $this->addActionButton(_t('Save'), new TAction([$this, 'onSave'],['static'=>'1']), 'far:envelope','btn-primary');
        $this->addActionButton(_t('New'), new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->addActionButton( _t('Back'), new TAction(['FormularioList', 'onClear']), 'fa:arrow-alt-circle-left blue' );
        $this->form->addHeaderActionLink( _t('Back'),  new TAction(['FormularioList', 'onClear']), 'far:arrow-alt-circle-left blue');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'FormularioList'));
        $container->add($this->form);
        
        parent::add($container);
    }
    
    /**
     * Método addActionButton para adicionar um botão com waves-effect
     * @param    $label    text content
     * @param    $action   TAction Object
     * @param    $icon     text icon (fa:user)
     * @param    $class    text class
     */
    private function addActionButton($label, TAction $action, $icon = null, $class = 'btn-default')
    {
        $btn = $this->form->addAction($label, $action, $icon);
        $btn->class = 'btn btn-sm '.$class.' waves-effect';

        return $btn;
    }
    
    /**
     * Preenche o campo URL com o nome
     * @param $param Request
     */
    public static function onNomeChange( $param )
    {
        $obj = new StdClass;
        $obj->url = THelper::urlAmigavel( $param['nome'] );
        
        TForm::sendData('form_Formulario',$obj);
    }
    
    /**
     * Exibe dados para inserção no form, conforme o captcha escolhido
     * @param $param Request
     */
    public static function onChangeCaptcha( $param )
    {
        $obj = new StdClass;
        
        switch ($param['recaptcha'])
        {
            case 'r':
                $obj->code = '<div class="g-recaptcha" data-sitekey="{recaptcha_key}" data-callback="verifyRecaptchaCallback" data-expired-callback="expiredRecaptchaCallback"></div>';
                TQuickForm::showField('form_Formulario','code');
                TForm::sendData('form_Formulario',$obj);
                break;
            case 'h':
                $obj->code = '<div class="h-captcha" data-sitekey="{hcaptcha_key}"></div>';
                TQuickForm::showField('form_Formulario','code');
                TForm::sendData('form_Formulario',$obj);
                break;
            default:
                TQuickForm::hideField('form_Formulario', 'code');
                break;
        }
        
    }
    
    /**
     * 
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                TTransaction::open('sistema');
                
                $key = $param['key'];
                
                $object = new Formulario($key);
                $this->form->setData($object);
                
                TTransaction::close(); // close transaction
                
                self::onChangeCaptcha( ['recaptcha' => $object->recaptcha] );
                
                return $object;
    	    }
    	    else
            {
                $this->onClear($param);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    
}

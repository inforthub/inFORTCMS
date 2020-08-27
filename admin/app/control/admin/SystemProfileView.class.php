<?php
/**
 * SystemProfileView
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     André Ricardo Fort
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemProfileView extends TPage
{
    private $form;
    
    public function __construct()
    {
        parent::__construct();
        
        // criando o formulário
        $this->form = new BootstrapFormBuilder('form_PerfilUser');
        $this->form->setFieldSizes('100%');
        $this->form->setColumnClasses(2,['col-sm-3','col-sm-9']);
        $this->form->enableCSRFProtection();
        
        // Mudando a cor do cabeçalho
        $this->form->setProperty('class','perfil');
        //$this->form->setHeaderProperty('class','header bg-red');
        
        // criando os campos
        $name      = new TEntry('name');
        $login     = new TEntry('login');
        $email     = new TEntry('email');
        $photo     = new TFile('photo');
        $password1 = new TPassword('password1');
        $password2 = new TPassword('password2');
        
        // ajustando os parametros dos campos
        $login->setEditable(FALSE);
        $photo->setAllowedExtensions( ['jpg'] );
        
        // criando botão para o gerador de senha
        $btn_generate = new TActionLink('Gerador de Senha', new TAction(array('GeradorSenhaForm', 'onView')),null,null,null,'fas:magic' );
        $btn_generate->addStyleClass('btn btn-success btn-sm waves-effect');
        
        // adicionando os campos ao formulário
        $this->form->addFields( [new TLabel(_t('Name'))] , [$name] );
        $this->form->addFields( [new TLabel(_t('Login'))] , [$login] );
        $this->form->addFields( [new TLabel(_t('Email'))] , [$email] );
        $this->form->addFields( [new TLabel(_t('Photo'))] , [$photo] );
        $this->form->addFields( [new TLabel(_t('Password'))] , [$password1] );
        $this->form->addFields( [new TLabel(_t('Password confirmation'))] , [$password2] );
        $this->form->addFields( [] , [$btn_generate] );
        
        // criando validações
        $name->addValidation(_t('Name'), new TRequiredValidator);
        $login->addValidation(_t('Email'), new TRequiredValidator);
        $email->addValidation(_t('Password'), new TRequiredValidator);
        
        // criando os botões
        $btn = $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:save');
        $btn->class = 'btn btn-primary waves-effect';

        
        $html = new THtmlRenderer('app/resources/system_profile.html');
        $replaces = array();
        
        try
        {
            TTransaction::open('permission');
            
            $user= SystemUser::newFromLogin(TSession::getValue('login'));
            $this->form->setData($user); // preenche o formulário com os dados do usuário logado
            $replaces = $user->toArray();
            $replaces['frontpage'] = $user->frontpage_name;
            $replaces['groupnames'] = str_replace(',Standard','',$user->getSystemUserGroupNames());
            $replaces['groupnames'] = str_replace(',',', ',$replaces['groupnames']);
            
            // dados do usuário
            $replaces['login'] = $user->login;
            $replaces['email'] = $user->email;
            
            TTransaction::open('log');
            $replaces['log_user'] = SystemAccessLog::where('login','=',TSession::getValue('login'))->count();
            TTransaction::close();
            
            // formulário
            $replaces['form'] = $this->form;
            
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
        
        $html->enableSection('main', $replaces);
        $html->enableTranslation();
        
        $container = TVBox::pack($html);
        $container->style = 'width: 100%';
        parent::add($container);
    }
    
    /**
     * 
     */
    public function onSave($param)
    {
        try
        {
            $this->form->validate();
            
            $object = $this->form->getData();
            
            TTransaction::open('permission');
            $user = SystemUser::newFromLogin( TSession::getValue('login') );
            $user->name = $object->name;
            $user->email = $object->email;
            
            if( $object->password1 )
            {
                if( $object->password1 != $object->password2 )
                {
                    throw new Exception(_t('The passwords do not match'));
                }
                
                $user->password = SystemUser::createHashString( $object->password1 );
                //$user->password = md5($object->password1);
            }
            else
            {
                unset($user->password);
            }
            
            $user->store();
            
            if ($object->photo)
            {
                $source_file   = 'tmp/'.$object->photo;
                $target_file   = 'app/images/photos/' . TSession::getValue('login') . '.jpg';
                $finfo         = new finfo(FILEINFO_MIME_TYPE);
                
                if (file_exists($source_file) AND $finfo->file($source_file) == 'image/jpeg')
                {
                    // move to the target directory
                    rename($source_file, $target_file);
                }
            }
            
            $this->form->setData($object);
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    
}

<?php
/**
 * SystemProfileForm
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemProfileForm extends TPage
{
    private $form;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->form = new BootstrapFormBuilder;
        $this->form->setFormTitle(_t('Profile'));
        $this->form->setClientValidation(true);
        $this->form->enableCSRFProtection();
        
        $name  = new TEntry('name');
        $login = new TEntry('login');
        $email = new TEntry('email');
        $photo = new TFile('photo');
        $password1 = new TPassword('password1');
        $password2 = new TPassword('password2');
        $login->setEditable(FALSE);
        $photo->setAllowedExtensions( ['jpg'] );
        
        $this->form->addFields( [new TLabel(_t('Name'))] , [$name] , [new TLabel(_t('Login'))] , [$login] );
        $this->form->addFields( [new TLabel(_t('Email'))] , [$email] , [new TLabel(_t('Photo'))] , [$photo] );
        $this->form->addFields( [new TLabel(_t('Password'))] , [$password1] , [new TLabel(_t('Password confirmation'))] , [$password2] );
        
        $name->addValidation(_t('Name'), new TRequiredValidator);
        $login->addValidation(_t('Name'), new TRequiredValidator);
        $email->addValidation(_t('Name'), new TRequiredValidator);
        $email->addValidation( _t('Email'), new TEmailValidator);
        
        // criando os botões
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary waves-effect';

        parent::add($this->form);
    }
    
    public function onEdit($param)
    {
        try
        {
            TTransaction::open('permission');
            $login = SystemUser::newFromLogin( TSession::getValue('login') );
            $this->form->setData($login);
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
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
            
            TSession::setValue('username', $user->name);
            TSession::setValue('usermail', $user->email);
            
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

<?php
/**
 * SystemPreferenceForm
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemPreferenceForm extends TStandardForm
{
    protected $form; // formulário
    
    /**
     * método construtor
     * Cria a página e o formulário de cadastro
     */
    function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('permission');
        $this->setActiveRecord('SystemPreference');
        
        // cria o formulário
        $this->form = new BootstrapFormBuilder('form_preferences');
        $this->form->setFormTitle(_t('Preferences'));
        
        // cria os campos do formulário
        $mail_domain = new TEntry('mail_domain');
        $smtp_auth   = new TCombo('smtp_auth');
        $smtp_host   = new TEntry('smtp_host');
        $smtp_port   = new TEntry('smtp_port');
        $smtp_user   = new TEntry('smtp_user');
        $smtp_pass   = new TPassword('smtp_pass');
        $mail_from   = new TEntry('mail_from');
        $mail_support= new TEntry('mail_support');
        
        $smtp_host->placeholder = 'ssl://smtp.gmail.com, tls://server.company.com';
        
        $yesno = array();
        $yesno['1'] = _t('Yes');
        $yesno['0'] = _t('No');
        $smtp_auth->addItems($yesno);
        
        $this->form->addFields( [new TLabel(_t('Mail from'))], [$mail_from] );
        $this->form->addFields( [new TLabel(_t('SMTP Auth'))], [$smtp_auth] );
        $this->form->addFields( [new TLabel(_t('SMTP Host'))], [$smtp_host] );
        $this->form->addFields( [new TLabel(_t('SMTP Port'))], [$smtp_port] );
        $this->form->addFields( [new TLabel(_t('SMTP User'))], [$smtp_user] );
        $this->form->addFields( [new TLabel(_t('SMTP Pass'))], [$smtp_pass] );
        $this->form->addFields( [new TLabel(_t('Support mail'))], [$mail_support] );
        
        $mail_from->setSize('70%');
        $smtp_auth->setSize('70%');
        $smtp_host->setSize('70%');
        $smtp_port->setSize('70%');
        $smtp_user->setSize('70%');
        $smtp_pass->setSize('70%');
        $mail_support->setSize('70%');
        
        $btn = $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';
        
        $container = new TVBox;
        $container->{'style'} = 'width: 100%;';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }
    
    /**
     * Carrega o formulário de preferências
     */
    function onEdit($param)
    {
        try
        {
            // open a transaction with database
            TTransaction::open($this->database);
            
            $preferences = SystemPreference::getAllPreferences();
            if ($preferences)
            {
                $this->form->setData((object) $preferences);
            } 
            
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    function onSave()
    {
        try
        {
            // open a transaction with database
            TTransaction::open($this->database);
            
            // get the form data
            $data = $this->form->getData();
            $data_array = (array) $data;
            
            foreach ($data_array as $property => $value)
            {
                $object = new SystemPreference;
                $object->{'id'}    = $property;
                $object->{'value'} = $value;
                $object->store();
            }
            
            // fill the form with the active record data
            $this->form->setData($data);
            
            // close the transaction
            TTransaction::close();
            
            // shows the success message
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
            // reload the listing
        }
        catch (Exception $e) // in case of exception
        {
            // get the form data
            $object = $this->form->getData($this->activeRecord);
            
            // fill the form with the active record data
            $this->form->setData($object);
            
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
}

<?php
/**
 * SystemMessageForm
 *
 * @version    1.0
 * @package    control
 * @subpackage communication
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemMessageForm extends TWindow
{
    protected $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        parent::setSize(0.5, null);
        parent::setMinWidth(0.9, 800);
        parent::removePadding();
        parent::setTitle( _t('Send message') );
        parent::setProperty('class', 'window_modal');
        
        // creates the form
        $this->form = new BootstrapFormWrapper(new TQuickForm('form_SystemMessage'));
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setProperty('style', 'border:0');
        
        // create the form fields
        $system_user_to_id = new TDBUniqueSearch('system_user_to_id', 'permission', 'SystemUser', 'id', 'name');
        $subject = new TEntry('subject');
        $message = new TText('message');
        $system_user_to_id->setMinLength(2);

        // add the fields
        $this->form->addQuickField(_t('User'), $system_user_to_id,  '90%', new TRequiredValidator);
        $this->form->addQuickField(_t('Subject'), $subject,  '90%', new TRequiredValidator );
        $this->form->addQuickField(_t('Message'), $message,  '90%', new TRequiredValidator );
        $message->setSize('90%', '100');
        
        // create the form actions
        $btn = $this->form->addQuickAction(_t('Send'), new TAction(array($this, 'onSend')), 'far:envelope');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addQuickAction(_t('Clear form'),  new TAction(array($this, 'onClear')), 'fa:eraser red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%; margin:40px';
        $container->add($this->form);
        
        parent::add($container);
    }
    
    public function onClear($param)
    {
    
    }
    
    public function onSend($param)
    {
        try
        {
            // open a transaction with database
            TTransaction::open('communication');
            
            // get the form data
            $data = $this->form->getData();
            // validate data
            $this->form->validate();
            
            $object = new SystemMessage;
            $object->system_user_id = TSession::getValue('userid');
            $object->system_user_to_id = $data->system_user_to_id;
            $object->subject = $data->subject;
            $object->message = $data->message;
            $object->dt_message = date('Y-m-d H:i:s');
            $object->checked = 'N';
            
            // stores the object
            $object->store();
            
            // fill the form with the active record data
            $this->form->setData($data);
            
            // close the transaction
            TTransaction::close();
            
            // shows the success message
            new TMessage('info', _t('Message sent successfully'));
            parent::closeWindow();
            return $object;
        }
        catch (Exception $e) // in case of exception
        {
            // get the form data
            $object = $this->form->getData();
            
            // fill the form with the active record data
            $this->form->setData($object);
            
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
}

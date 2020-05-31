<?php
/**
 * SystemProgramForm
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemProgramForm extends TStandardForm
{
    protected $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param)
    {
        parent::__construct();
                
        // creates the form
        
        $this->form = new BootstrapFormBuilder('form_SystemProgram');
        $this->form->setFormTitle(_t('Program'));
        
        // defines the database
        parent::setDatabase('permission');
        
        // defines the active record
        parent::setActiveRecord('SystemProgram');
        
        // create the form fields
        $id            = new TEntry('id');
        $controller    = new TUniqueSearch('controller');
        $name          = new TEntry('name');
        $groups        = new TDBCheckGroup('groups', 'permission', 'SystemGroup', 'id', 'name');
        
        $id->setEditable(false);
        $controller->addItems($this->getPrograms( empty($param['id']) ));
        $controller->setMinLength(0);
        $controller->setChangeAction(new TAction([$this, 'onChangeController']));
        $groups->setLayout('horizontal');
        
        if ($groups->getLabels())
        {
            foreach ($groups->getLabels() as $label)
            {
                $label->setSize(200);
            }
        }
        
        // add the fields
        $this->form->addFields( [new TLabel('ID')], [$id] );
        $this->form->addFields( [new TLabel(_t('Controller'))], [$controller] );
        $this->form->addFields( [new TLabel(_t('Name'))], [$name] );
        $this->form->addFields( [new TFormSeparator(_t('Groups'))] );
        $this->form->addFields( [$groups] );
        
        $id->setSize('30%');
        $name->setSize('70%');
        $controller->setSize('70%');
        
        // validations
        $name->addValidation(_t('Name'), new TRequiredValidator);
        $controller->addValidation(('Controller'), new TRequiredValidator);

        // add form actions
        $btn = $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addActionLink(_t('Clear'), new TAction(array($this, 'onEdit')), 'fa:eraser red');
        $this->form->addActionLink(_t('Back'),new TAction(array('SystemProgramList','onReload')),'far:arrow-alt-circle-left blue');

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml','SystemProgramList'));
        $container->add($this->form);
        
        
        // add the container to the page
        parent::add($container);
    }
    
    /**
     * Change controller, generate name
     */
    public static function onChangeController($param)
    {
        if (!empty($param['controller']) AND empty($param['name']))
        {
            $obj = new stdClass;
            $obj->name = preg_replace('/([a-z])([A-Z])/', '$1 $2', $param['controller']);
            TForm::sendData('form_SystemProgram', $obj);
        }
    }
    
    /**
     * Return all the programs under app/control
     */
    public function getPrograms( $just_new_programs = false )
    {
        try
        {
            TTransaction::open('permission');
            $registered_programs = SystemProgram::getIndexedArray('id', 'controller');
            TTransaction::close();
            
            $entries = array();
            $iterator = new AppendIterator();
            $iterator->append(new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/control'), RecursiveIteratorIterator::CHILD_FIRST));
            $iterator->append(new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/view'),    RecursiveIteratorIterator::CHILD_FIRST));
            
            foreach ($iterator as $arquivo)
            {
                if (substr($arquivo, -4) == '.php')
                {
                    $name = $arquivo->getFileName();
                    $pieces = explode('.', $name);
                    $class = (string) $pieces[0];
                    
                    if ($just_new_programs)
                    {
                        if (!in_array($class, $registered_programs) AND !in_array($class, array_keys(TApplication::getDefaultPermissions())) AND substr($class,0,6) !== 'System')
                        {
                            $entries[$class] = $class;
                        } 
                    }
                    else
                    {
                        $entries[$class] = $class;
                    }
                }
            }
            
            ksort($entries);
            return $entries;
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     * @param  $param An array containing the GET ($_GET) parameters
     */
    public function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                $key=$param['key'];
                
                TTransaction::open($this->database);
                $class = $this->activeRecord;
                $object = new $class($key);
                
                $groups = array();
                
                if( $groups_db = $object->getSystemGroups() )
                {
                    foreach( $groups_db as $group )
                    {
                        $groups[] = $group->id;
                    }
                }
                $object->groups = $groups;
                $this->form->setData($object);
                
                TTransaction::close();
                
                return $object;
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave()
    {
        try
        {
            TTransaction::open($this->database);
            
            $data = $this->form->getData();
            
            $object = new SystemProgram;
            $object->id = $data->id;
            $object->name = $data->name;
            $object->controller = $data->controller;
            
            $this->form->validate();
            $object->store();
            $data->id = $object->id;
            $this->form->setData($data);
            
            $object->clearParts();
            
            if( !empty($data->groups) )
            {
                foreach( $data->groups as $group_id )
                {
                    $object->addSystemGroup( new SystemGroup($group_id) );
                }
            }
            
            TTransaction::close();
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
            
            return $object;
        }
        catch (Exception $e) // in case of exception
        {
            // get the form data
            $object = $this->form->getData($this->activeRecord);
            $this->form->setData($object);
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}

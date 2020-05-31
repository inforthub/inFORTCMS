<?php
/**
 * SystemGroup
 *
 * @version    1.0
 * @package    model
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemGroup extends TRecord
{
    const TABLENAME  = 'system_group';
    const PRIMARYKEY = 'id';
    const IDPOLICY   = 'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('name');
    }
    
    /**
     * Clone the entire object and related ones
     */
    public function cloneGroup()
    {
        $programs = $this->getSystemPrograms();
        $users    = $this->getSystemUsers();
        
        unset($this->id);
        $this->name .= ' (clone)';
        $this->store();
        
        if ($programs)
        {
            foreach ($programs as $program)
            {
                $this->addSystemProgram( $program );
            }
        }
        
        if ($users)
        {
            foreach ($users as $user)
            {
                $this->addSystemUser( $user );
            }
        }
    }
    
    /**
     * Add a SystemProgram to the SystemGroup
     * @param $object Instance of SystemProgram
     */
    public function addSystemProgram(SystemProgram $systemprogram)
    {
        if (SystemGroupProgram::where('system_program_id','=',$systemprogram->id)->where('system_group_id','=',$this->id)->count() == 0)
        {
            $object = new SystemGroupProgram;
            $object->system_program_id = $systemprogram->id;
            $object->system_group_id = $this->id;
            $object->store();
        }
    }
    
    /**
     * Add a SystemUser to the SystemGroup
     * @param $object Instance of SystemUser
     */
    public function addSystemUser(SystemUser $systemuser)
    {
        if (SystemUserGroup::where('system_user_id','=',$systemuser->id)->where('system_group_id','=',$this->id)->count() == 0)
        {
            $object = new SystemUserGroup;
            $object->system_user_id  = $systemuser->id;
            $object->system_group_id = $this->id;
            $object->store();
        }
    }
    
    /**
     * Return the SystemProgram's
     * @return Collection of SystemProgram
     */
    public function getSystemPrograms()
    {
        $system_programs = array();
        
        // load the related System_program objects
        $repository = new TRepository('SystemGroupProgram');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('system_group_id', '=', $this->id));
        $system_group_system_programs = $repository->load($criteria);
        if ($system_group_system_programs)
        {
            foreach ($system_group_system_programs as $system_group_system_program)
            {
                $system_programs[] = new SystemProgram( $system_group_system_program->system_program_id );
            }
        }
        
        return $system_programs;
    }

    /**
     * Return the SystemUser's
     * @return Collection of SystemUser
     */
    public function getSystemUsers()
    {
        $system_users = array();
        
        // load the related System_user objects
        $repository = new TRepository('SystemUserGroup');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('system_group_id', '=', $this->id));
        $system_group_system_users = $repository->load($criteria);
        if ($system_group_system_users)
        {
            foreach ($system_group_system_users as $system_group_system_user)
            {
                $system_users[] = new SystemUser( $system_group_system_user->system_user_id );
            }
        }
        
        return $system_users;
    }
    
    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        // delete the related objects
        SystemGroupProgram::where('system_group_id', '=', $this->id)->delete();
        SystemUserGroup::where('system_group_id', '=', $this->id)->delete();
    }
    
    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        // delete the related System_groupSystem_program objects
        $id = isset($id) ? $id : $this->id;
        
        SystemGroupProgram::where('system_group_id', '=', $id)->delete();
        SystemUserGroup::where('system_group_id', '=', $id)->delete();
        
        // delete the object itself
        parent::delete($id);
    }
}

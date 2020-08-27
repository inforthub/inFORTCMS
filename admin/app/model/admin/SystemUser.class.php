<?php
/**
 * SystemUser
 *
 * @version    1.0
 * @package    model
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemUser extends TRecord
{
    const TABLENAME = 'system_user';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    // use SystemChangeLogTrait;
    
    private $frontpage;
    private $unit;
    private $system_user_groups = array();
    private $system_user_programs = array();
    private $system_user_units = array();

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('name');
        parent::addAttribute('login');
        parent::addAttribute('password');
        parent::addAttribute('email');
        parent::addAttribute('frontpage_id');
        parent::addAttribute('system_unit_id');
        parent::addAttribute('active');
    }
    
    /**
     * Clone the entire object and related ones
     */
    public function cloneUser()
    {
        $groups   = $this->getSystemUserGroups();
        $units    = $this->getSystemUserUnits();
        $programs = $this->getSystemUserPrograms();
        
        unset($this->id);
        $this->name .= ' (clone)';
        $this->store();
        
        if ($groups)
        {
            foreach ($groups as $group)
            {
                $this->addSystemUserGroup( $group );
            }
        }
        
        if ($units)
        {
            foreach ($units as $unit)
            {
                $this->addSystemUserUnit( $unit );
            }
        }
        
        if ($programs)
        {
            foreach ($programs as $program)
            {
                $this->addSystemUserProgram( $program );
            }
        }
    }
    
    /**
     * Returns the frontpage name
     */
    public function get_frontpage_name()
    {
        // loads the associated object
        if (empty($this->frontpage))
            $this->frontpage = new SystemProgram($this->frontpage_id);
    
        // returns the associated object
        return $this->frontpage->name;
    }
    
    /**
     * Returns the frontpage
     */
    public function get_frontpage()
    {
        // loads the associated object
        if (empty($this->frontpage))
            $this->frontpage = new SystemProgram($this->frontpage_id);
    
        // returns the associated object
        return $this->frontpage;
    }
    
   /**
     * Returns the unit
     */
    public function get_unit()
    {
        // loads the associated object
        if (empty($this->unit))
            $this->unit = new SystemUnit($this->system_unit_id);
    
        // returns the associated object
        return $this->unit;
    }
    
    /**
     * Add a Group to the user
     * @param $object Instance of SystemGroup
     */
    public function addSystemUserGroup(SystemGroup $systemgroup)
    {
        $object = new SystemUserGroup;
        $object->system_group_id = $systemgroup->id;
        $object->system_user_id = $this->id;
        $object->store();
    }
    
    /**
     * Add a Unit to the user
     * @param $object Instance of SystemUnit
     */
    public function addSystemUserUnit(SystemUnit $systemunit)
    {
        $object = new SystemUserUnit;
        $object->system_unit_id = $systemunit->id;
        $object->system_user_id = $this->id;
        $object->store();
    }
    
    /**
     * Return the user' group's
     * @return Collection of SystemGroup
     */
    public function getSystemUserGroups()
    {
        return parent::loadAggregate('SystemGroup', 'SystemUserGroup', 'system_user_id', 'system_group_id', $this->id);
    }
    
    /**
     * Return the user' unit's
     * @return Collection of SystemUnit
     */
    public function getSystemUserUnits()
    {
        return parent::loadAggregate('SystemUnit', 'SystemUserUnit', 'system_user_id', 'system_unit_id', $this->id);
    }
    
    /**
     * Add a program to the user
     * @param $object Instance of SystemProgram
     */
    public function addSystemUserProgram(SystemProgram $systemprogram)
    {
        $object = new SystemUserProgram;
        $object->system_program_id = $systemprogram->id;
        $object->system_user_id = $this->id;
        $object->store();
    }
    
    /**
     * Return the user' program's
     * @return Collection of SystemProgram
     */
    public function getSystemUserPrograms()
    {
        return parent::loadAggregate('SystemProgram', 'SystemUserProgram', 'system_user_id', 'system_program_id', $this->id);
    }
    
    /**
     * Get user group ids
     */
    public function getSystemUserGroupIds( $as_string = false )
    {
        $groupids = array();
        $groups = $this->getSystemUserGroups();
        if ($groups)
        {
            foreach ($groups as $group)
            {
                $groupids[] = $group->id;
            }
        }
        
        if ($as_string)
        {
            return implode(',', $groupids);
        }
        
        return $groupids;
    }
    
    /**
     * Get user unit ids
     */
    public function getSystemUserUnitIds( $as_string = false )
    {
        $unitids = array();
        $units = $this->getSystemUserUnits();
        if ($units)
        {
            foreach ($units as $unit)
            {
                $unitids[] = $unit->id;
            }
        }
        
        if ($as_string)
        {
            return implode(',', $unitids);
        }
        
        return $unitids;
    }
    
    /**
     * Get user group names
     */
    public function getSystemUserGroupNames()
    {
        $groupnames = array();
        $groups = $this->getSystemUserGroups();
        if ($groups)
        {
            foreach ($groups as $group)
            {
                $groupnames[] = $group->name;
            }
        }
        
        return implode(',', $groupnames);
    }
    
    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        SystemUserGroup::where('system_user_id', '=', $this->id)->delete();
        SystemUserUnit::where('system_user_id', '=', $this->id)->delete();
        SystemUserProgram::where('system_user_id', '=', $this->id)->delete();
    }
    
    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        // delete the related System_userSystem_user_group objects
        $id = isset($id) ? $id : $this->id;
        
        SystemUserGroup::where('system_user_id', '=', $id)->delete();
        SystemUserUnit::where('system_user_id', '=', $id)->delete();
        SystemUserProgram::where('system_user_id', '=', $id)->delete();
        
        // delete the object itself
        parent::delete($id);
    }
    
    /**
     * Validate user login
     * @param $login String with user login
     */
    public static function validate($login)
    {
        $user = self::newFromLogin($login);
        
        if ($user instanceof SystemUser)
        {
            if ($user->active == 'N')
            {
                throw new Exception(_t('Inactive user'));
            }
        }
        else
        {
            //throw new Exception(_t('User not found'));
            throw new Exception(_t('Incorrect Username or Password'));
        }
        
        return $user;
    }
    
    /**
     * Authenticate the user
     * @param $login String with user login
     * @param $password String with user password
     * @returns TRUE if the password matches, otherwise throw Exception
     */
    public static function authenticate($login, $password)
    {
        $user = self::newFromLogin($login);
        //if (!hash_equals($user->password, md5($password)))
        if (!hash_equals($user->password, self::createHashString($password)))
        {
            //throw new Exception(_t('Wrong password'));
            throw new Exception(_t('Incorrect Username or Password'));
        }
        
        return $user;
    }
    
    /**
     * Returns a SystemUser object based on its login
     * @param $login String with user login
     */
    static public function newFromLogin($login)
    {
        return SystemUser::where('login', '=', $login)->first();
    }
    
    /**
     * Returns a SystemUser object based on its e-mail
     * @param $email String with user email
     */
    static public function newFromEmail($email)
    {
        return SystemUser::where('email', '=', $email)->first();
    }
    
    /**
     * Return the programs the user has permission to run
     */
    public function getPrograms()
    {
        $programs = array();
        
        foreach( $this->getSystemUserGroups() as $group )
        {
            foreach( $group->getSystemPrograms() as $prog )
            {
                $programs[$prog->controller] = true;
            }
        }
                
        foreach( $this->getSystemUserPrograms() as $prog )
        {
            $programs[$prog->controller] = true;
        }
        
        return $programs;
    }
    
    /**
     * Return the programs the user has permission to run
     */
    public function getProgramsList()
    {
        $programs = array();
        
        foreach( $this->getSystemUserGroups() as $group )
        {
            foreach( $group->getSystemPrograms() as $prog )
            {
                $programs[$prog->controller] = $prog->name;
            }
        }
                
        foreach( $this->getSystemUserPrograms() as $prog )
        {
            $programs[$prog->controller] = $prog->name;
        }
        
        asort($programs);
        return $programs;
    }
    
    /**
     * Check if the user is within a group
     */
    public function checkInGroup( SystemGroup $group )
    {
        $user_groups = array();
        foreach( $this->getSystemUserGroups() as $user_group )
        {
            $user_groups[] = $user_group->id;
        }
    
        return in_array($group->id, $user_groups);
    }
    
    /**
     *
     */
    public static function getInGroups( $groups )
    {
        $collection = [];
        $users = self::all();
        if ($users)
        {
            foreach ($users as $user)
            {
                foreach ($groups as $group)
                {
                    if ($user->checkInGroup($group))
                    {
                        $collection[] = $user;
                    }
                }
            }
        }
        return $collection;
    }

    /**
     * Creates a hash string based on algoritm and salt key.
     * 
     * @param  mixed  algoritm code (md5,sha1,sha256).
     * @param  mixed  Salt key.
     * @param  mixed  Password text.
     *
     * @return string Hash string.
     */
    public static function createHashString($password, $algoritm = 'sha256', $key = CMS_SALTKEY)
    {
        $context = hash_init($algoritm, HASH_HMAC, $key);
        hash_update($context, $password);
    
        return hash_final($context);
    }

    /**
     * Retorna o nome do usuário baseado no id
     * @param $id Integer
     */
     public static function getUserName($id)
     {
         $obj = SystemUser::find($id);
         if ($obj)
         {
             return $obj->name;
         }
     }
}

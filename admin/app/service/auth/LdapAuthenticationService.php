<?php
class LdapAuthenticationService
{
	public static function authenticate($user, $password)
	{
        $ldap = parse_ini_file('app/config/ldap.ini');
        $ds   = ldap_connect($ldap['server'], $ldap['port']);
        
        if ($ds)
        {
            if (@ldap_bind($ds, $user.'@'.$ldap['domain'], $password))
            {
                return true;
            }
        }
        throw new Exception(_t('Invalid LDAP credentials'));
	}
}

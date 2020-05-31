<?php
class SystemUserCliService
{
    /**
     * Retorna todos produtos entre $from e $to
     * @param $request HTTP request
     */
    public static function create( $request )
    {
        TTransaction::open('permission');
        $response = array();
        
        $request['password'] = md5($request['password']);
        
        $user = new SystemUser;
        $user->fromArray($request);
        $user->store();
        TTransaction::close();
        return $user;
    }
}
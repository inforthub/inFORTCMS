<?php
require_once 'request.php';

try
{
    $body['filters'] = [ ['login', '=', 'pedro'] ];
    $location = 'http://git/template/users';
    $user = request($location, 'GET', $body, 'Basic 123');
    
    if (!$user)
    {
        $body = ['name' => 'Pedro paulo',
                 'login' => 'pedro',
                 'password' => md5('123'),
                 'email' => 'pedro.paulo@teste.com',
                 'active' => '1' ];
        $location = 'http://git/template/users';
        $data = request($location, 'POST', $body, 'Basic 123');
        print_r($data);
        
        $body = ['system_user_id' => $data->id,
                 'system_group_id' => '2' ];
        $location = 'http://git/template/user-groups';
        print_r( request($location, 'POST', $body, 'Basic 123') );
    }
}
catch (Exception $e)
{
    print $e->getMessage();
}
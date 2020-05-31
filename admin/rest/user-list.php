<?php
require_once 'request.php';

try
{
    $body['order'] = 'id';
    $body['direction'] = 'asc';
    $location = 'http://git/template/users';
    $users = request($location, 'GET', $body, 'Basic 123');
    
    print_r($users);
}
catch (Exception $e)
{
    print $e->getMessage();
}
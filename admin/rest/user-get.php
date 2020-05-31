<?php
require_once 'request.php';

try
{
    $location = 'http://git/template/users/2';
    $user = request($location, 'GET', [], 'Basic 123');
    
    print_r($user);
}
catch (Exception $e)
{
    print $e->getMessage();
}
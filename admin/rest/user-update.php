<?php
require_once 'request.php';

try
{
    $body['name'] = 'Pedro Paulo - changed';
    $location = 'http://git/template/users/3';
    request($location, 'PUT', $body, 'Basic 123');
}
catch (Exception $e)
{
    print $e->getMessage();
}
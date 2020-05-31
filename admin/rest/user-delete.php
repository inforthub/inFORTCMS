<?php
require_once 'request.php';

try
{
    $location = 'http://git/template/users/3';
    request($location, 'DELETE', [], 'Basic 123');
}
catch (Exception $e)
{
    print $e->getMessage();
}
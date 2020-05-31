<?php
function request($url, $method = 'POST', $params = [], $authorization = null)
{
    $ch = curl_init();
    
    if ($method == 'POST' OR $method == 'PUT')
    {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_POST, true);

    }
    else if ( ($method == 'GET' OR $method == 'DELETE') AND !empty($params))
    {
        $url .= '?'.http_build_query($params);
    }
    
    $defaults = array(
        CURLOPT_URL => $url, 
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CONNECTTIMEOUT => 10
    );
    
    if (!empty($authorization))
    {
        $defaults[CURLOPT_HTTPHEADER] = ['Authorization: '. $authorization];
    }
    
    curl_setopt_array($ch, $defaults);
    $output = curl_exec ($ch);
    
    curl_close ($ch);
    $return = (array) json_decode($output);
    
    if (json_last_error() !== JSON_ERROR_NONE)
    {
        throw new Exception('Return is not JSON. Check the URL: ' . $output);
    }
    if ($return['status'] == 'error') {
        throw new Exception($return['data']);
    }
    return $return['data'];
}

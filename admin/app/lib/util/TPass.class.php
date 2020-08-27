<?php
/**
 * TPass Class
 *
 * @version    1.0
 * @package    util
 * @subpackage lib
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 */
class TPass
{
    /**
     * Método gerador de senhas aleatórias
     */
    public static function GerarSenha($tam = 12, $si = false)
    {
        return self::makePass($tam, true, true, $si);
    }
    
    
    /**
     * Método gerador de token aleatório
     */
    public static function GerarToken($tam = 256, $ma = false, $si = false)
    {
        return self::makePass($tam, $ma, true, $si);
    }
    
    
    /**
     * Método gerador de códigos aleatórios
     */
    public static function makePass($tam, $maiusculas = true, $numeros = true, $simbolos = false)
    {
        $cod = str_shuffle('abcdefghijklmnopqrstuvwxyz');
        //$lmi = str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        //$num = str_shuffle('1234567890') . (((date('Ymd') / 12) * 24) + mt_rand(800, 9999));
        //$smb = str_shuffle('!@#$%*-');
        
        $ret = '';
        
        if ($maiusculas) $cod .= str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        if ($numeros)    $cod .= str_shuffle('1234567890') . uniqid();
        if ($simbolos)   $cod .= str_shuffle('!@#$%*()!@#$%*()!@#$%*()');
        
        $len = strlen($cod);
        for ($n = 1; $n <= $tam; $n++)
        {
            $rand = mt_rand(1, $len);
            $ret .= $cod[$rand-1];
        }
        
        return $ret;
    }
}

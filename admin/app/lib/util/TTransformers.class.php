<?php
/**
 * TTransformers Class
 *
 * Classe com os métodos transformadores padrão do sistema
 *
 * @version    1.0
 * @package    util
 * @subpackage lib
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class TTransformers
{
    private $database = 'sistema';
    
    /**
     * Método transformador de data no padrão BR
     */
    public static function formataDataBR( $value, $object, $row )
    {
        if ($value != '')
        {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        }
        else
        {
            return '';
        }
    }
    
    /**
     * Método transformador de data e hora no padrão BR
     */
    public static function formataDataHoraBR( $value, $object, $row )
    {
        if ($value != '')
        {
            $date = new DateTime($value);
            return $date->format('d/m/Y - H:i');
        }
        else
        {
            return '';
        }
    }
    
    /**
     * Método transformador de numero float no padrão BR
     */
    public static function formataNumero( $value, $object, $row )
    {
        if (is_numeric($value))
        {
            return number_format($value,2,',','.');
        }
        return $value;
    }
    
    /**
     * Método transformador de numero float no padrão Moeda BR
     */
    public static function formataMoedaBR( $value, $object, $row )
    {
        if (is_numeric($value))
        {
            return 'R$ '. number_format($value,2,',','.');
        }
        return $value;
    }
    
    /**
     * Método para exibir a imagem no datagrid
     */
    public static function showImagem($image, $object, $row)
    {
        return THelper::showImagem($image,'28px','img-circle','');
    }
    
    /**
     * Método para exibir um icone no datagrid
     */
    public static function showIcone($icone, $object, $row)
    {
        $imagem = new TElement('i');
        
        $fa_class = substr($icone,4);
        if (strstr($icone, '#') !== FALSE)
        {
            list($fa_class, $fa_color) = explode('#', $fa_class);
        }
        
        $imagem->{'class'} = $icone;
        if (isset($fa_color))
        {
            $imagem->{'style'} .= "; color: #{$fa_color};";
        }
        $imagem->add('');
        return $imagem; 
    }
    
    /**
     * Método transformador de links
     */
    public static function formataLink( $value, $object, $row )
    {
        return THelper::formataLink($value, true);
    }
    
    /**
     * Retorna parte de um texto
     */
    public static function cortaTexto( $value, $object, $row )
    {
        return THelper::substrWords( $value );
    }
    
    /**
     * Método transformador de campo booleano para SIM e NÃO
     */
    public static function formataSimNao( $value, $object, $row )
    {
        $campo = $value;
        
        // testando campo booleano
        if ( is_bool($value) )
            $campo = (!$value) ? 'f' : 't';
        else if ( is_int($value) )
            $campo = ($value==1) ? 't' : 'f';
        else if ( $value == '1' || $value == 't' )
            $campo = 't';
        else
            $campo = 'f';

        $class = ($campo=='f') ? 'danger' : 'success';
        $label = ($campo=='f') ? _t('No') : _t('Yes');
        $div = new TElement('span');
        $div->class="label label-{$class}";
        $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
        $div->add($label);
        return $div;
    }
    
    /**
     * Método transformador de campo tipo cor
     */
    public static function formataCor( $value, $object, $row )
    {
        return '<i class="fa fa-square fa-lg" style="color:'.$value.'"></i>';
    }
    
    /**
     * Método transformador de campo tipo pessoa para Física e Jurídica
     */
    public static function formataTipoPessoa( $value, $object, $row )
    {
        return $value == 'F' ? 'Física' : 'Jurídica';
    }
    
    /********************************************************
     * Métodos transformadores com acesso ao Banco de Dados *
     ********************************************************/
    
    /**
     * Método para retornar o nome do Usuário
     */
    public static function showUserName( $value, $object, $row )
    {
        return THelper::showUserName($value);
    }
    
    /**
     * Método para retornar o titulo de um menu
     */
    public static function showMenuTitulo( $value, $object, $row )
    {
        TTransaction::open('sistema');
        $obj = Menu::find($value);
        $ret = null;
        if ( $obj )
        {
            $ret = $obj->titulo;
        }
        TTransaction::close();
        return $ret;
    }
    
    /**
     * Método para retornar o nome de um Tipo do sistema (Site, Blog, etc)
     */
    public static function showTipo( $value, $object, $row )
    {
        TTransaction::open('sistema');
        $obj = Tipo::find($value);
        $ret = null;
        if ( $obj )
        {
            $ret = $obj->nome;
        }
        TTransaction::close();
        return $ret;
    }
    
    /**
     * Método para retornar o nome de uma Mídia
     */
    public static function showMidia( $value, $object, $row )
    {
        TTransaction::open('sistema');
        $obj = Midia::find($value);
        $ret = null;
        if ( $obj )
        {
            $ret = $obj->nome;
        }
        TTransaction::close();
        return $ret;
    }
        
    /**
     * Método para retornar o nome de uma Categoria
     */
    public static function showCategoria( $value, $object, $row )
    {
        TTransaction::open('sistema');
        $obj = Artigo::find($value);
        $ret = null;
        if ( $obj && $obj->modo == 'c')
        {
            $ret = $obj->titulo;
        }
        TTransaction::close();
        return $ret;
    }
    
    
}

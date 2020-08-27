<?php
namespace App\Base;

use Exception;
use Adianti\Core\AdiantiCoreTranslator;
use WebPConvert\WebPConvert;
use Cocur\Slugify\Slugify;

/**
 * File Save Trait
 *
 * @version    1.0
 * @package    util
 * @subpackage lib
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 * @author     Nataniel Rabaioli
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
trait AppFileSaveTrait
{
    /**
     * Salva o arquivo com base na raiz do site
     * @param $object      Active Record
     * @param $data        Form data
     * @param $input_name  Input field name
     * @param $target_path Target file path
     */
    public function saveFile($object, $data, $input_name, $target_path)
    {
        $dados_file = json_decode(urldecode($data->$input_name));
        $target_path = '..'.$target_path; // Volta um nivel

        if (isset($dados_file->fileName))
        {
            $pk = $object->getPrimaryKey();
            
            //$target_path.= '/' . $object->$pk;
            
            // prepara a zona (sempre UTF-8)
            setlocale( LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'pt_BR.utf-8', 'portuguese' );
            date_default_timezone_set( 'America/Sao_Paulo' );
            
            // verificar o ano e o mes
            $ano = strftime( '%Y' );
            $mes = strftime( '%B' );

            // colocar '/ANO/MES'
            $target_path .= '/'.$ano.'/'.$mes;

            $source_file = $dados_file->fileName;
            $target_file = strpos($dados_file->fileName, $target_path) === FALSE ? $target_path . '/' . $dados_file->fileName : $dados_file->fileName;
            $target_file = str_replace('tmp/', '', $target_file);
            
            $class = get_class($object);
            $obj_store = new $class;
            $obj_store->$pk = $object->$pk;
            $obj_store->$input_name = $target_file;
            
            $delFile = null;
            
            if (!empty($dados_file->delFile))
            {
                $obj_store->$input_name = '';
                $dados_file->fileName = '';
                
                if (is_file(urldecode($dados_file->delFile)))
                {
                    $delFile = urldecode($dados_file->delFile);
                    
                    if (file_exists($delFile))
                    {
                        unlink($delFile);
                    }
                }
            }
    
            if (!empty($dados_file->newFile))
            {
                if (file_exists($source_file))
                {
                    if (!file_exists($target_path))
                    {
                        if (!mkdir($target_path, 0777, true))
                        {
                            throw new Exception(AdiantiCoreTranslator::translate('Permission denied') . ': '. $target_path);
                        }
                    }
                    
                    // if the user uploaded a source file
                    if (file_exists($target_path))
                    {
                        $target = pathinfo($target_file);
                        
                        $target_file = $target['dirname'].'/'.self::urlAmigavel($target['filename']).'.'.$target['extension'];

                        $arquivo = pathinfo($source_file);
                        
                        // verificando se é uma imagem
                        if ( !in_array($arquivo['extension'],['jpg','jpeg','png','gif']) )
                        {
                            // move to the target directory
                            if (!rename($source_file, $target_file))
                            {
                                throw new Exception(AdiantiCoreTranslator::translate('Error while copying file to ^1', $target_file));
                            }
                            $obj_store->formato = $arquivo['extension'];
                        }
                        else
                        {
                            // convertemos para webp
                            $target_file = str_replace('.'.$arquivo['extension'],'.webp',$target_file);
                            
                            self::redimenciona($target_file,1000);
                            
                            $options = [];
                            WebPConvert::convert($source_file, $target_file, $options);
                            
                            $obj_store->formato = 'webp';
                        }
                        
                        // removemos o ".." antes de salvar
                        $obj_store->$input_name = $target_file;
                    }
                }
            }
            elseif ($dados_file->fileName != $delFile)
            {
                $obj_store->$input_name = $dados_file->fileName;
            }
            
            $obj_store->store();
            
            if ($obj_store->$input_name)
            {
                $dados_file->fileName = $obj_store->$input_name;
                $data->$input_name = urlencode(json_encode($dados_file));
            }
            else
            {
                $data->$input_name = '';
            }
        }
    }
    
    /**
	 * Método para redimencionar imagens
	 */
	public static function redimenciona($imageName, $thumbWidth)
	{
        $arquivo = pathinfo($imageName);
        
        switch ( $arquivo['extension'] )
	    {
	        case 'jpg':
	        case 'jpeg':
                $srcImg = imagecreatefromjpeg($imageName);
                break;
            case 'png':
                $srcImg = imagecreatefrompng($imageName);
                break;
            case 'gif':
                $srcImg = imagecreatefromgif($imageName);
                break;
        }

        $origWidth = imagesx($srcImg);
        $origHeight = imagesy($srcImg);
    
        $ratio = $origWidth / $thumbWidth;
        $thumbHeight = $origHeight / $ratio;
    
        $thumbImg = imagecreatetruecolor($thumbWidth, $thumbHeight);
        imagecopyresized($thumbImg, $srcImg, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $origWidth, $origHeight);
        
        switch ( $arquivo['extension'] )
	    {
	        case 'jpg':
	        case 'jpeg':
	            imagejpeg($thumbImg, $imageName);
                break;
            case 'png':
                imagepng($thumbImg, $imageName);
                break;
            case 'gif':
                imagegif($thumbImg, $imageName);
                break;
        }

        imagedestroy($thumbImg);
    }
    
	/**
     * Transforma uma string em url-amigavel usando Slugify.
     * @param $str    string a ser analizada
     */
    public static function urlAmigavel($str)
    {
        $slugify = new Slugify();
        //$str = strtolower( URLify::downcode( $str ) ); // remove os acentos e converte para minusculas
        return $slugify->slugify(time().'-'.$str);
    }
    
}

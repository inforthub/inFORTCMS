<?php
/**
 * Template Active Record
 *
 * @version    1.0
 * @package    model
 * @subpackage configuracoes
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 */
class Template extends TRecord
{
    const TABLENAME = 'template';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('nome_fisico');
        parent::addAttribute('script_head');
        parent::addAttribute('script_body');
        parent::addAttribute('dt_cadastro');
        parent::addAttribute('padrao');
    }
    
    /**
     * Método que atualiza a coluna 'padrao' de todos os registros para 'FALSE'
     */
    public static function clear_padrao()
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('nome', '!=', ''));
        
        $repos = new TRepository('Template');
        $repos->update(['padrao'=>'f'],$criteria);
    }
    
    /**
     * Método executado após uma ação de Delete de uma template
     */
    public function onAfterDelete($object)
    {
        // apagando arquivos e pastas da template
        self::DeletaDir('../templates/'.$object->nome_fisico);
    }
    
    /**
     * Método recursivo para deletar diretórios e arquivos
     */
    private static function DeletaDir($DirFont)
	{
		if ($dd = opendir($DirFont)) {
			while (false !== ($Arq = readdir($dd))) {
				if($Arq != "." && $Arq != ".."){
					$PathIn = "$DirFont/$Arq";
					if(is_dir($PathIn)){
						self::DeletaDir($PathIn);
					}elseif(is_file($PathIn)){
						unlink($PathIn);
					}
				}
			}
			closedir($dd);
		}
		rmdir($DirFont);
	}
	
    /**
     * Método recursivo para cópiar diretórios e arquivos
     */
    public static function CopiaDir($DirFont, $DirDest)
	{
		mkdir($DirDest);
		if ($dd = opendir($DirFont)) {
			while (false !== ($Arq = readdir($dd))) {
				if($Arq != "." && $Arq != ".."){
					$PathIn = "$DirFont/$Arq";
					$PathOut = "$DirDest/$Arq";
					if(is_dir($PathIn)){
						self::CopiaDir($PathIn, $PathOut);
					}elseif(is_file($PathIn)){
						copy($PathIn, $PathOut);
					}
				}
			}
			closedir($dd);
		}
	}


}

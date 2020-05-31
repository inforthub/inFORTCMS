<?php
/**
 * Modulo Active Record
 * @author  <your-name-here>
 */
class Modulo extends TRecord
{
    const TABLENAME = 'modulo';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $modelo_modulo;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('variavel');
        parent::addAttribute('parametros');
        parent::addAttribute('ordem');
        parent::addAttribute('ativo');
        parent::addAttribute('modelo_modulo_id');
        parent::addAttribute('artigo_id');
    }

    
    /**
     * Method set_modelo_modulo
     * Sample of usage: $modulo->modelo_modulo = $object;
     * @param $object Instance of ModeloModulo
     */
    public function set_modelo_modulo(ModeloModulo $object)
    {
        $this->modelo_modulo = $object;
        $this->modelo_modulo_id = $object->id;
    }
    
    /**
     * Method get_modelo_modulo
     * Sample of usage: $modulo->modelo_modulo->attribute;
     * @returns ModeloModulo instance
     */
    public function get_modelo_modulo()
    {
        // loads the associated object
        if (empty($this->modelo_modulo))
            $this->modelo_modulo = new ModeloModulo($this->modelo_modulo_id);
    
        // returns the associated object
        return $this->modelo_modulo;
    }
    
    /**
     * Pega um módulo com base na variável
     */
    public static function getModuloVariavel($variavel)
    {
        $modulo = Modulo::where('variavel','=',$variavel)->first();
        
        if ($modulo)
        {
            //montamos o html do modulo
            $parse = new TParser;
            $param = unserialize($modulo->parametros);
            $arr['root'] = $this->_pref['pref_site_dominio'];
            
            // verificamos se existem campos com conteúdo
            if ( isset($param['campos']) && is_array($param['campos']) )
            {
                // carregamos o conteúdo dos campos
                foreach ($param['campos'] as $key => $value)
                {
                    $arr[$key] = $value;
                }
            }
            
            $html = $modulo->modelo_modulo->html;

            // renderizamos uma string com o html pronto e concatenamos na página
            return $parse->parse_string($html, $arr, TRUE);
        }
        
        return '';
    }
    
    
    
}

<?php
/**
 * ModuloForm Form
 *
 * @version     1.0
 * @package     control
 * @subpackage  site
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2019 (https://www.infort.eti.br)
 */
class ModuloForm extends TWindow
{
    protected $form; // form
    protected $campos;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        parent::setTitle('Formulário de Módulo');
        parent::setSize(0.7,null);
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Modulo');
        $this->form->setFieldSizes('100%');


        // create the form fields
        $id               = new TEntry('id');
        $nome             = new TEntry('nome');
        $variavel         = new TEntry('variavel');
        $ativo            = new TRadioGroup('ativo');
        $modelo_modulo_id = new TDBCombo('modelo_modulo_id','sistema','ModeloModulo','id','nome','nome');
        //$modelo_modulo_id = new TDBUniqueSearch('modelo_modulo_id','sistema','ModeloModulo','id','nome');
        
        // definindo as propriedades dos campos
        $id->setEditable(FALSE);
        $id->setSize('50%');
        $ativo->setSize(80);
        $ativo->addItems(['t'=>'Sim','f'=>'Não']);
        $ativo->setLayout('horizontal');
        $ativo->setUseButton();
        $modelo_modulo_id->enableSearch();
        //$modelo_modulo_id->setMinLength(1);
        
        // criando eventos
        $nome->setExitAction(new TAction([$this,'onNomeChange']));
        $modelo_modulo_id->setChangeAction(new TAction([$this,'onModeloChange']));
        
        // defininda as validações
        $nome->addValidation('Nome', new TRequiredValidator);
        $modelo_modulo_id->addValidation('Modelo de Módulo', new TRequiredValidator);
        
        // criando frame para os campos dinâmicos
        $this->campos = TElement::tag('div', '', ['id'=>'campos_modulo']);
        
        // adicionando os campos ao formulário
        $this->form->addFields( [new TLabel('ID')], [$id] );
        $this->form->addFields( [new TLabel('Nome')], [$nome], [new TLabel('Modelo de Módulo')], [$modelo_modulo_id] );
        $this->form->addContent( [new TFormSeparator('Campos do Módulo')] );
        $this->form->addContent( [$this->campos] );
        $this->form->addContent( [new TFormSeparator('')] );
        $this->form->addFields( [new TLabel('Ativo')], [$ativo] );
        
        
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'fa:save' );
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction( _t('New'), new TAction(array($this, 'onEdit')), 'fa:eraser red' );
        
        parent::add($this->form);
    }
    
    /**
     * Preenche o campo Variável com o nome
     * @param $param Request
     */
    public static function onNomeChange( $param )
    {
        $obj = new StdClass;
        $obj->variavel = THelper::urlAmigavel( $param['nome'] );

        TForm::sendData('form_Modulo',$obj);
    }
    
    /**
     * Método chamado ao escolher um modelo de módulo
     */
    public static function onModeloChange( $param )
    {
        $html = self::getCampos( $param['modelo_modulo_id'] );
        $html = str_replace("'", '"', $html);
        $html = str_replace(['<script language="JavaScript">','</script>'], "§" , $html);
        
        // pegamos todas as ocorrencias de scripts e separamos da string html
        $script = '';
        preg_match_all('/§(.*?)§/', $html, $resultado);
        if (count($resultado[1]) > 0)
        {
            foreach ($resultado[1] as $value)
            {
                $script .= $value;
            }
        }
        
        // removemos todas as ocorrencias de scripts da string html
        $html = preg_replace('/§(.*?)§/','', $html);
        
        //$this->campos->add($html);
        TScript::create('
        $("#campos_modulo").html(\''.$html.'\'); '. $script .'
        ');
    }
    
    /**
     * Método privado para criar os campos dinâmicos conforme o modelo de módulo
     * @param $id        
     * @param $dados    
     */
    private static function getCampos( $id, $dados=NULL )
    {
        try
        {
            TTransaction::open('sistema'); // open a transaction
            
            // criando html de retorno
            $html = new TElement('div');
            
            $modulo = ModeloModulo::find($id);
            if ($modulo instanceof ModeloModulo)
            {
                $param = unserialize($modulo->parametros);
    
                // criando os campos dinâmicos se necessário
    		    if ( isset($param['campos']) && is_array($param['campos']) )
    		    {
    		        foreach ($param['campos'] as $key => $value)
    			    {
    			        // criando os campos dinâmicos
                        $label = new TLabel(ucfirst($key));
                        $campo = new THidden('campo[]');
                        $campo->setValue($key);
                        
                        $linha = new TElement('div');
                        $linha->class = 'form-group tformrow';
                        
                        $linha->add(THelper::divBootstrap(TElement::tag('div',[$label, $campo], ['class'=>'fb-inline-field-container ','style'=>"display: inherit;vertical-align:top;;width: 100%"]),'2 fb-field-container control-label'));
                        
                        $content = isset($dados[$key]) ? $dados[$key] : '';
                        
                        switch ($value)
                        {
                            case '0': //loop
                            case '6': //video
                            case '5': //audio
                            case '4': //imagem
                            case '3':
                                $conteudo = new THtmlEditor('conteudo[]');
                                $conteudo->setValue($content);
                                $conteudo->setSize('100%',100);
                                break;
                            case '2':
                                $conteudo = new TText('conteudo[]');
                                $conteudo->setValue($content);
                                $conteudo->setSize('100%',100);
                                break;
                            case '1':
                            default:
                                $conteudo = new TEntry('conteudo[]');
                                $conteudo->setValue($content);
                                $conteudo->setSize('100%');
                                break;
                        }
                        $conteudo->class = 'form-control tfield';
                        
                        $linha->add(THelper::divBootstrap(TElement::tag('div',$conteudo,['class'=>'fb-inline-field-container form-line','style'=>"display: inherit;vertical-align:top;;width: 100%"]),'10 fb-field-container'));

                        $html->add( $linha );
                        /*
                        // adicionando campos ao form
                        $this->form->addField($campo);
                        $this->form->addField($conteudo);*/
    			    }
    		    }
            }
            
            TTransaction::close(); // close the transaction
            
            return $html;
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
            return '';
        }
    }

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open('sistema'); // open a transaction
            
            $this->form->validate(); // validate form data
            
            $object = new Modulo;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
            
            // preparando os parâmetros
            $parametros = '';
            // verificamos e montamos os campos
            if( !empty($param['campo']) AND is_array($param['campo']) )
            {
                $parametros = array();
                foreach( $param['campo'] as $key => $value)
                {
                    $parametros['campos'][$value] = $param['conteudo'][$key];
                }
            }
            // verificando se o módulo busca dados em uma tabela no BD
            if( isset($param['bd']) AND !empty($param['bd']) )
            {
                $parametros['banco'] = unserialize(base64_decode($param['bd']));
            }
            $object->parametros = serialize($parametros);
            
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            
            TTransaction::close(); // close the transaction
            
            $campos     = isset($parametros['campos']) ? $parametros['campos'] : NULL;
            $html = self::getCampos( $object->modelo_modulo_id, $campos );
            $this->campos->add($html);
            
            
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'), new TAction(['ModuloList','onClear']));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('sistema'); // open a transaction
                $object = new Modulo($key); // instantiates the Active Record
                
                // lendo os parametros
                $parametros = unserialize($object->parametros);
                $campos     = isset($parametros['campos']) ? $parametros['campos'] : NULL;
                $html = self::getCampos( $object->modelo_modulo_id, $campos );
                $this->campos->add($html);
                
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
}

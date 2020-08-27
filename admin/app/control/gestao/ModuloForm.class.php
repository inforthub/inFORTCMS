<?php
/**
 * ModuloForm Form
 *
 * @version     1.0
 * @package     control
 * @subpackage  gestao
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class ModuloForm extends TPage
{
    protected $form; // form
    protected $campos;
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        $this->setDatabase('sistema');        // defines the database
        $this->setActiveRecord('Modulo');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Modulo');
        $this->form->setFormTitle('Formulário de Módulo');
        $this->form->setFieldSizes('100%');
        

        // create the form fields
        $id             = new TEntry('m_id');
        $nome           = new TEntry('m_nome');
        $modelo_html_id = new TDBCombo('modelo_html_id','sistema','ModeloHTML','id','nome','nome');
        $posicao        = new TDBUniqueSearch('m_posicao', 'sistema', 'Posicao', 'nome', 'nome');
        $ordem          = new THidden('m_ordem');
        $ativo          = new TRadioGroup('m_ativo');


        // criando frame para os campos dinâmicos
        $this->campos = TElement::tag('div', '', ['id'=>'campos_modulo']);

        // adicionando os campos ao formulário
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] , [ $ordem ]);
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Modelo Html') ], [ $modelo_html_id ] );
        $this->form->addContent( [new TFormSeparator('Campos do Módulo')] );
        $this->form->addContent( [$this->campos] );
        $this->form->addContent( [new TFormSeparator('')] );
        $this->form->addFields( [ new TLabel('Posicao') ], [ $posicao ] , [ new TLabel('Ativo') ], [ $ativo ] );

        
    	// definindo as validações
        $nome->addValidation('Nome', new TRequiredValidator);
        $modelo_html_id->addValidation('Modelo de Módulo', new TRequiredValidator);


    	// criando eventos
        $modelo_html_id->setChangeAction(new TAction([$this,'onModeloChange'],['static'=>'1']));
        

        // definindo parâmetros dos campos
        $id->setEditable(FALSE);
        $ativo->setSize(80);
        $ativo->addItems(['t'=>'Sim','f'=>'Não']);
        $ativo->setLayout('horizontal');
        $ativo->setUseButton();
        $posicao->setMinLength(1);
        $posicao->setMask('<b>{template->nome}</b> - {nome}');
        $modelo_html_id->enableSearch();
        

        // create the form actions
        $this->addActionButton(_t('Save'), new TAction([$this, 'onSave'],['static'=>'1']), 'far:envelope','btn-primary');
        $this->addActionButton(_t('New'), new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->addActionButton( _t('Back'), new TAction(['ModuloList', 'onClear']), 'fa:arrow-alt-circle-left blue' );
        $this->form->addHeaderActionLink( _t('Back'),  new TAction(['ModuloList', 'onClear']), 'far:arrow-alt-circle-left blue');
                
        parent::add($this->form);
    }
    
    /**
     * Método addActionButton para adicionar um botão com waves-effect
     * @param    $label    text content
     * @param    $action   TAction Object
     * @param    $icon     text icon (fa:user)
     * @param    $class    text class
     */
    private function addActionButton($label, TAction $action, $icon = null, $class = 'btn-default')
    {
        $btn = $this->form->addAction($label, $action, $icon);
        $btn->class = 'btn btn-sm '.$class.' waves-effect';

        return $btn;
    }

    /**
     * Método chamado ao escolher um modelo de página html
     */
    public function onModeloChange( $param )
    {
        $html = $this->getCampos( $param['modelo_html_id'] );
        if ($html)
        {
            $html = str_replace("'", "\'", $html);
            $html = str_replace(["<script language=\'JavaScript\'>","<script language='JavaScript'>",'<script language="JavaScript">','</script>'], "§" , $html);
            
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
        }
        
        $this->campos->add($html);
        TScript::create('$("#campos_modulo").html(\''.$html.'\'); '. str_replace("\'","'",$script));
    }
    
    /**
     * Método privado para criar os campos dinâmicos conforme o modelo de página
     * @param $id        
     * @param $dados    
     */
    private function getCampos( $id, $dados=NULL )
    {
        try
        {
            TTransaction::open($this->database); // open a transaction
            
            // criando html de retorno
            $html = new TElement('div');
            
            $modulo = ModeloHTML::find($id);
            if ($modulo instanceof ModeloHTML)
            {
                $param = json_decode($modulo->parametros,true);
                $loop  = false;
    
                // criando os campos dinâmicos se necessário
    		    if ( isset($param['campos']) && is_array($param['campos']) )
    		    {
    		        $fieldlist = new TFieldList;
    		        $div_loop  = new TElement('div'); 
    		        $div_loop->class = 'form-group tformrow row';
    		        $btn_imagem = false;
    		        $nome_loop = '';
    		        
    		        foreach ($param['campos'] as $key => $value)
    			    {
    			        // verificamos se é um loop
    			        if ($value == '0')
    			        {
    			            $loop = ($loop) ? false : true;
 
    			            if ($loop)
    			            {
    			                $nome_loop = $key;
    			                
    			                // criando os campos dinâmicos
                                $label = new TLabel(ucfirst($key));

                                $div_loop->add(THelper::divBootstrap(TElement::tag('div',$label, ['class'=>'fb-inline-field-container ','style'=>"display: inherit;vertical-align:top;width: 100%"]),'2 fb-field-container control-label'));
                                
    			                $uniq       = new THidden('uniq[]');

    			                // criamos um campo do tipo fieldlist
        			            $fieldlist = new TFieldList;
        			            //$fieldlist->generateAria();
                                $fieldlist->width = '100%';
                                $fieldlist->name  = 'loop_fieldlist';
                                $fieldlist->addField( '<b>Unniq</b>',  $uniq,   ['width' => '0%', 'uniqid' => true] );
    			            }
    			            else
    			            {
    			                $fieldlist->enableSorting();
    			                
                                if ($btn_imagem)
                                {
                                    // criando o frame da imagem
                                    $frame = new TPicture('photo_frame[]'); //TElement('div');
                                    $frame->class = 'form-control tfield';
                                    $frame->style = 'width:50px;height:auto;border:1px solid gray;padding:0px;';
                                    
                                    $fieldlist->addField( 'foto', $frame );
                                    $fieldlist->addButtonAction(new TAction([__CLASS__,'onBuscaImagem'],['campo'=>$btn_imagem,'imagem'=>'photo_frame']), 'fa:image purple', 'Selecionar Imagem');
                                }
                                
                                $fieldlist->addHeader();
                                
                                //verificamos se tem dados a serem inseridos no fieldlist
                                if (!empty($dados[$nome_loop]))
                                {
                                    foreach($dados[$nome_loop] as $item)
                                    {
                                        $obj = new stdClass;
                                        foreach($item as $key=>$value)
                                        {
                                            $obj->$key = $value;
                                        }
                                        if ($btn_imagem)
                                        {
                                            //exibir imagem aqui
                                            $obj->photo_frame = "<img style='width:100%' src='{$item[$btn_imagem]}'>";
                                        }
                                        $fieldlist->addDetail( $obj );
                                    }
                                }
                                else
                                {
                                    $fieldlist->addDetail( new stdClass );
    			                }
    			                
    			                $fieldlist->addCloneAction();
    			                
    			                
    			                $frame = new TFrame;
                                $frame->add($fieldlist);
    			                
                                $div_loop->add(THelper::divBootstrap(TElement::tag('div',$frame,['class'=>'fb-inline-field-container form-line','style'=>"display: inherit;vertical-align:top;width: 100%"]),'10 fb-field-container'));
                                $html->add( $div_loop );
    			            }
    			        }
    			        else
    			        {
        			        $content = isset($dados[$key]) ? $dados[$key] : '';
        			        
        			        if ($loop)
        			        {
                                $conteudo = new TEntry($key.'[]');
                                $conteudo->setSize('100%');
                                
                                switch ($value)
                                {
                                    case '4': //imagem
                                        $btn_imagem = $key;
                                        $conteudo->setEditable(false);
                                        break;
                                    case '5': //file
                                        break;
                                    case '3':
                                    case '2':
                                        $conteudo = new TText($key.'[]');
                                        $conteudo->setSize('100%',100);
                                        break;
                                    
                                    case '1':
                                    default:
                                        break;
                                }
                                $conteudo->setValue($content);
                                $conteudo->class = 'form-control tfield';

                                $fieldlist->addField( '<b>'.new TLabel(ucfirst($key)).'</b>', $conteudo ); //,  ['width' => '50%']);
        			        }
        			        else
        			        {
                                // criando os campos dinâmicos
                                $label = new TLabel(ucfirst($key));
                                $campo = new THidden('campo[]');
                                $campo->setValue($key);
                                
                                $linha = new TElement('div');
                                $linha->class = 'form-group tformrow row';
                                
                                $linha->add(THelper::divBootstrap(TElement::tag('div',[$label, $campo], ['class'=>'fb-inline-field-container ','style'=>"display: inherit;vertical-align:top;width: 100%"]),'2 fb-field-container control-label'));
                                
                                $conteudo = new TEntry('conteudo[]');
                                $conteudo->setSize('100%');
                                switch ($value)
                                {
                                    //case '0': //loop
                                    case '5': //file
                                        break;
                                    case '4': //imagem
                                        // criando o frame da imagem
                                        $frame = new TPicture('imagem_frame'); //TElement('div');
                                        $frame->class = 'form-control tfield';
                                        $frame->setId('imagem_frame_' . mt_rand(1000000000, 1999999999));
                                        $frame->style = 'width:200px;height:auto;border:1px solid gray;padding:0px;margin: 10px 0;';
                                        
                                        if (!empty($content))
                                        {
                                            //exibir imagem aqui
                                            $frame->setValue("<img style='width:100%' src='{$content}'>");
                                        }
                                        
                                        // criand botão de busca de imagem
                                        $btn =  new TActionLink('Escolher imagem', new TAction([__CLASS__,'onBuscaImagem'],['campo'=>$conteudo->getId(),'imagem'=>$frame->getId(),'single'=>true]), null,null,null,'fa:image purple' );
                                        $btn->class = 'btn btn-default';

                                        // adicionando os campos na linha
                                        $linha->add(THelper::divBootstrap(TElement::tag('div',[$frame,$btn],['class'=>'fb-inline-field-container form-line','style'=>"display: inherit;vertical-align:top;width: 100%"]),'10 fb-field-container'));
                                        $html->add( $linha );
                                        
                                        $linha = new TElement('div');
                                        $linha->class = 'form-group tformrow row';
                                        $linha->add(THelper::divBootstrap(TElement::tag('div','', ['class'=>'fb-inline-field-container ','style'=>"display: inherit;vertical-align:top;width: 100%"]),'2 fb-field-container control-label'));
                                        $conteudo->setEditable(false);
                                        
                                        break;
                                    case '3':
                                        $conteudo = new THtmlEditor('conteudo[]');
                                        $conteudo->setSize('100%',150);
                                        break;
                                    case '2':
                                        $conteudo = new TText('conteudo[]');
                                        $conteudo->setSize('100%',100);
                                        break;
                                    case '1':
                                    default:
                                        $conteudo = new TEntry('conteudo[]');
                                        $conteudo->setSize('100%');
                                        break;
                                }
                                $conteudo->setValue($content);
                                $conteudo->class = 'form-control tfield';
                                
                                $linha->add(THelper::divBootstrap(TElement::tag('div',$conteudo,['class'=>'fb-inline-field-container form-line','style'=>"display: inherit;vertical-align:top;width: 100%"]),'10 fb-field-container'));
        
                                $html->add( $linha );
                            }
                            $this->form->addField($conteudo);
                        }
    			    }
    		    }
            }
            else
            {
                $html = null;
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
     * Chama a classe de seleção de imagem
     */
    public static function onBuscaImagem($param)
    {
        if ($param['campo'])
        {
            // verificamos se o campo de imagem é simples ou de um fieldlist
            $single = isset($param['single']) ? $param['single'] : null;
            
            $param['campo']  = ($single) ? $param['campo'] : $param['campo'].'_'.$param['_row_id'];
            $param['imagem'] = ($single) ? $param['imagem'] : $param['imagem'].'_'.$param['_row_id'];
            $param['form']  = 'form_Modulo';
            
            TSession::setValue('Classe_Retorno_Busca_Imagem',$param);
            AdiantiCoreApplication::loadPage('SelecaoImagem');
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
            TTransaction::open($this->database); // open a transaction
            
            // validando os campos
            $this->form->validate();
            
            $object = new Modulo;  // create an empty object
            $tmp = $this->form->getData(); // get form data as array
            
            $data['id']             = $tmp->m_id;
            $data['nome']           = $tmp->m_nome;
            $data['modelo_html_id'] = $tmp->modelo_html_id;
            $data['posicao']        = $tmp->m_posicao;
            $data['ordem']          = $tmp->m_ordem;
            $data['ativo']          = $tmp->m_ativo;
            
            $object->fromArray( (array) $data); // load the object with data
            
            // definindo a ordem do menu
            if ( empty($object->ordem) )
            { 
                $total = Modulo::where('posicao', '=', $object->posicao)->count();
                $object->ordem = $total+1;
            }
            
            // preparando os parametros e montando o html
            if (!empty($object->modelo_html_id))
            {
                $modelo_html = ModeloHTML::find($object->modelo_html_id);
                
                // preparando os parâmetros
                $parametros = [];
                $arr_parse  = [];
                // verificamos e montamos os campos
                if( !empty($param['campo']) && is_array($param['campo']) )
                {
                    //$parametros = array();
                    foreach( $param['campo'] as $key => $value)
                    {
                        $parametros['campos'][$value] = $param['conteudo'][$key];
                        $arr_parse[$value] = str_replace('..','{root}', $param['conteudo'][$key]);
                    }
                }
                
                // verificamos se tem algum loop
                if ( !empty($param['uniq']) && is_array($param['uniq']) )
                {
                    $param_mod = json_decode($modelo_html->parametros);
                    $loop = false;
                    $campo_loop = [];
                    $nome_loop  = '';
                    foreach($param_mod->campos as $key => $value)
                    {
                        if ($value == '0')
                        {
                            $loop = ($loop) ? false : true;
                            if ($loop) $nome_loop = $key;
                            continue;
                        }
                        
                        if ($loop)
                        {
                            $campo_loop[] = $key;
                        }
                    }
                    
                    foreach( $param['uniq'] as $key => $value )
                    {
                        $arr = [];
                        foreach($campo_loop as $val)
                        {
                            $arr[$val] = $param[$val][$key];
                        }
                        $parametros[$nome_loop][$key] = $arr;
                        $arr_parse[$nome_loop][] = $arr;
                    }
                }
                
                $object->parametros = json_encode( empty($parametros) ? '' : $parametros );
                
                // aplicando o parse no html e salvando em artigo
                $parse = new TParser;
                $object->html = $parse->parse_string($modelo_html->html, $arr_parse, TRUE);
            }
            
            $object->store(); // save the object

            // get the generated id
            $tmp->m_id = $object->id;
            
            $this->form->setData($tmp); // fill form data
            TTransaction::close(); // close the transaction
            
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
                TTransaction::open($this->database); // open a transaction
                $object = new Modulo($key); // instantiates the Active Record
                
                // pegando o modelo de html
                if ( !empty($object->modelo_html_id) )
                {
                    // verificando se tem algum loop
                    $param_mod = json_decode($object->modelo_html->parametros);
                    $nome_loop = '';
                    foreach($param_mod->campos as $key => $value)
                    {
                        if ($value == '0')
                        {
                            $nome_loop = $key;
                            break;
                        }
                    }
                    
                    // lendo os parametros
                    $parametros     = json_decode($object->parametros, true);
                    $campos         = (isset($parametros['campos'])) ? $parametros['campos'] : NULL;
                    if (isset($parametros[$nome_loop]))
                    {
                        $campos[$nome_loop] = $parametros[$nome_loop];
                    }
                    $html = $this->getCampos( $object->modelo_html_id, $campos );
                    $this->campos->add($html);
                }
                //var_dump($object);
                $obj = new stdClass;
                $obj->m_id           = $object->id;
                $obj->m_nome         = $object->nome;
                $obj->html           = $object->html;
                $obj->modelo_html_id = $object->modelo_html_id;
                $obj->parametros     = $object->parametros;
                $obj->m_posicao      = $object->posicao;
                $obj->m_ordem        = $object->ordem;
                $obj->m_ativo        = $object->ativo;

                $this->form->setData($obj); // fill the form
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

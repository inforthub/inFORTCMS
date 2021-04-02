<?php
/**
 * PaginaForm Form
 *
 * @version     1.0
 * @package     control
 * @subpackage  site
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class PaginaForm extends TPage
{
    protected $form; // form
    protected $campos;
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        $this->setDatabase('sistema');        // defines the database
        $this->setActiveRecord('Artigo');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Pagina');
        $this->form->setFormTitle('Formulário de Página');
        $this->form->setFieldSizes('100%');
        
        $criteria  = TCriteria::create(['tipo_id'=>1,'modo'=>'c','ativo'=>'t']); //pegamos somente as categorias ativas do tipo site
        $criteria2 = TCriteria::create(['active'=>'Y']);

        // criando campos do formulário
        $id             = new TEntry('id');
        $titulo         = new TEntry('titulo');
        $url            = new TEntry('url');
        //$resumo        = new TText('resumo');
        $artigo         = new TTextSourceCode('artigo');
        $metadesc       = new TText('metadesc');
        $metakey        = new TMultiEntry('metakey');
        $dt_post        = new TDateTime('dt_post');
        $dt_edicao      = new TDateTime('dt_edicao');
        $visitas        = new TEntry('visitas');
        $usuario_id     = new TDBCombo('usuario_id','sistema','SystemUser','id','name','name',$criteria2);
        $ativo          = new TRadioGroup('ativo');
        // nesse caso, devemos buscar por categorias
        $categoria_id   = new TDBCombo('categoria_id', 'sistema', 'Artigo', 'id', 'titulo', 'titulo', $criteria);
        $modelo_html_id = new TDBCombo('modelo_html_id','sistema','ModeloHTML','id','nome','nome');
        $script_head    = new TTextSourceCode('script_head');
        $script_body    = new TTextSourceCode('script_body');
        
        // criando frame para os campos dinâmicos
        $this->campos = TElement::tag('div', '', ['id'=>'campos_modulo']);


        // adicionando os campos ao formulário
        $this->form->appendPage('Dados da Página');
        $this->form->addFields( [ new TLabel('ID') ], [$id], [ new TLabel('Usuário') ], [ $usuario_id ] );
        $this->form->addFields( [ new TLabel('Título') ], [$titulo] , [ new TLabel('URL')], [$url]);
        //$this->form->addFields( [ new TLabel('Resumo')], [$resumo] );
        $this->form->addFields( [ new TLabel('Categoria') ], [ $categoria_id ] , [ new TLabel('Modelo de Página') ], [$modelo_html_id] );
        $this->form->addContent( [ new TFormSeparator('Campos da Página') ] );
        $this->form->addContent( [$this->campos] );
        $this->form->addFields( [ new TLabel('HTML') ], [ $artigo ] );
        $this->form->addContent( [ new TFormSeparator('Outros Campos') ] );
        $this->form->addFields( [ new TLabel('Data Post') ], [ $dt_post ] , [ new TLabel('Data Edição') ], [ $dt_edicao ] );
        $this->form->addFields( [ new TLabel('Ativo')], [$ativo] , [ new TLabel('Visitas') ], [ $visitas ] );
        //$this->form->appendPage('Estilos e Scripts');
        $this->form->addFields( [new TFormSeparator('Estilos e Scripts')] );
        $this->form->addFields( [new TLabel('<b>'.htmlentities('Início do HTML - antes do </head>').'</b>')] );
        $this->form->addFields( [ $script_head ] );
        $this->form->addFields( [new TLabel('<b>'.htmlentities('Final do HTML - antes do </body>').'</b>')] );
        $this->form->addFields( [ $script_body ] );
        $this->form->appendPage('SEO');
        $this->form->addFields( [ new TLabel('Meta Descrição') ], [$metadesc] );
        $this->form->addFields( [ new TLabel('Palavras Chave') ], [$metakey] );

        
	// definindo as validações
        $titulo->addValidation('Titulo', new TRequiredValidator);
        $url->addValidation('Url', new TRequiredValidator);
        //$artigo->addValidation('HTML', new TRequiredValidator);
        $metadesc->addValidation('Metadesc', new TRequiredValidator);
        $metakey->addValidation('Metakey', new TRequiredValidator);


        // criando eventos
        $titulo->setExitAction(new TAction([$this,'onTituloChange']));
        $modelo_html_id->setChangeAction(new TAction([$this,'onModeloChange'],['static'=>'1']));


        // definindo parâmetros dos campos
        $id->setEditable(FALSE);
        $usuario_id->setEditable(FALSE);
        $metadesc->setSize('100%',100);
        $metakey->setSize('100%',60);
        $url->forceLowerCase();
        $dt_post->setEditable(FALSE);
        $dt_post->setMask('dd/mm/yyyy hh:ii');
        $dt_post->setDatabaseMask('yyyy-mm-dd hh:ii');
        $dt_edicao->setEditable(FALSE);
        $dt_edicao->setMask('dd/mm/yyyy hh:ii');
        $dt_edicao->setDatabaseMask('yyyy-mm-dd hh:ii');
        $visitas->setEditable(FALSE);
        $ativo->setSize(80);
        $ativo->addItems(['t'=>'Sim','f'=>'Não']);
        $ativo->setLayout('horizontal');
        $ativo->setUseButton();
        $modelo_html_id->enableSearch();
        
        
        // create the form actions
        $this->addActionButton(_t('Save'), new TAction([$this, 'onSave'],['static'=>'1']), 'far:envelope','btn-primary');
        $this->addActionButton(_t('New'), new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->addActionButton( _t('Back'), new TAction(['PaginaList', 'onClear']), 'fa:arrow-alt-circle-left blue' );
        $this->form->addHeaderActionLink( _t('Back'),  new TAction(['PaginaList', 'onClear']), 'far:arrow-alt-circle-left blue');
        //$this->form->addHeaderActionLink( _t('Close'),  new TAction([__CLASS__, 'onClose'], ['static'=>'1']), 'fa:times red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'PaginaList'));
        $container->add($this->form);
        
        parent::add($container);
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
     * Preenche o campo URL com o título
     * @param $param Request
     */
    public static function onTituloChange( $param )
    {
        $obj = new StdClass;
        $obj->url = THelper::urlAmigavel( $param['titulo'] );
        
        TForm::sendData('form_Pagina',$obj);
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

            BootstrapFormBuilder::hideField('form_Pagina','artigo');
        }
        else
        {
            $html   = '';
            $script = '';
            BootstrapFormBuilder::showField('form_Pagina','artigo');
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
            TTransaction::open('sistema'); // open a transaction
            
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
                                    case '6': //file
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
                                    case '5': //icone
                                        $conteudo = new TIcon('conteudo[]');
                                        $conteudo->setSize('100%');
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
            $param['form']  = 'form_Pagina';
            
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
            
            $object = new Artigo;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
            
            // verificando campos de data
            if (empty($object->dt_post))
            {
                $object->dt_post = date('Y-m-d H:i:s');
            }
            
            if (empty($object->visitas))
            {
                $object->visitas = 0;
            }
            
            // preparando as palavras chave
            $object->metakey = implode(',',$data->metakey);
            
            // garantindo outras informações
            $object->tipo_id    = Tipo::getIdByNome('Site');
            $object->modo       = 'a'; // artigo
            $object->usuario_id = TSession::getValue('userid');
            
            // preparando os parametros e montando o html
            if (!empty($object->modelo_html_id))
            {
                $modelo_html = ModeloHTML::find($object->modelo_html_id);
                
                // preparando os parâmetros
                $parametros = '';
                $arr_parse = [];
                // verificamos e montamos os campos
                if( !empty($param['campo']) && is_array($param['campo']) )
                {
                    $parametros = array();
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
                
                $object->parametros = json_encode($parametros);
                
                // aplicando o parse no html e salvando em artigo
                $parse = new TParser;
                $object->artigo = $parse->parse_string($modelo_html->html, $arr_parse, TRUE);
            }
            
            $object->store(); // save the object

            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'), new TAction(['PaginaList','onClear']));
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
                $object = new Artigo($key); // instantiates the Active Record
                
                // preparando as palavras chave
                $object->metakey = explode(',',$object->metakey);
                
                // pegando o modelo de html
                if ( !empty($object->modelo_html_id) )
                {
                    // verificando se tem algum loop
                    $param_mod = json_decode($object->modelo_html->parametros);
                    $nome_loop  = '';
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

                    BootstrapFormBuilder::hideField('form_Pagina','artigo');
                }

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

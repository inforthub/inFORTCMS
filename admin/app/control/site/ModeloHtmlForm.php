<?php
/**
 * ModeloHtmlForm Registration
 *
 * @version     1.0
 * @package     control
 * @subpackage  site
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class ModeloHtmlForm extends TWindow
{
    protected $form; // form
    protected $fieldlist;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        parent::setSize(0.8, null);
        parent::setMinWidth(0.9,1400);
        parent::setPosition(null,10);
        parent::removePadding();
        parent::removeTitleBar();
        //parent::disableEscape();

        // creates the form
        $this->form = new BootstrapFormBuilder('form_ModeloHtml');
        $this->form->setFormTitle('Formulário de Modelo de Página HTML');
        $this->form->setFieldSizes('100%');

        // Mudando a cor do cabeçalho
        $this->form->setHeaderProperty('class','header bg-blue-grey');
        
        // criando os campos do form
        $id           = new TEntry('id');
        $nome         = new TEntry('nome');
        $html         = new TTextSourceCode('html');
        // campos dinâmicos
        $campo        = new TEntry('campo[]');
        $tipo         = new TCombo('tipo[]');
        
        
        // adicionando conteúdo do Combo
        $tipo->addItems([
            '1'=>'Caixa de Texto - Text',
            '2'=>'Área de Texto - Textarea',
            '3'=>'Área de Texto HTML - Textarea',
            '4'=>'Arquivo - Imagem',
            '5'=>'Icone',
            //'6'=>'Arquivo - File',
            '0'=>'Loop'
        ]);
        
        // definindo as propriedades dos campos
        $id->setEditable(FALSE);
        $id->setSize('50%');
        $campo->setSize('100%');
        $tipo->setSize('100%');
        
        $nome->addValidation('Nome', new TRequiredValidator);
        $html->addValidation('Html', new TRequiredValidator);


        // criando uma listagem de campos dinâmica
        $this->fieldlist = new TFieldList;
        $this->fieldlist->width = '100%';
        $this->fieldlist->name  = 'field_list';
        $this->fieldlist->addField( '<b>Nome do Campo</b>', $campo,  ['width' => '50%']);
        $this->fieldlist->addField( '<b>Tipo do Campo</b>', $tipo,  ['width' => '50%']);
        $this->form->addField($campo);
        $this->form->addField($tipo);
        $this->fieldlist->enableSorting();
        $frame = new TFrame;
        $frame->add($this->fieldlist);

        // adicionando os campos ao formulário
        $this->form->addFields( [new TLabel('ID')], [$id] , []);
        $this->form->addFields( [new TLabel('Nome')], [$nome] );
        $this->form->addContent( [new TLabel('Campos')], [$frame] );
        $this->form->addFields( [new TLabel('Layout Html'), $html] );
        
        
        // criando os botões do formulário
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
        $btn = $this->form->addHeaderAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addHeaderActionLink( _t('Close'),  new TAction([__CLASS__, 'onClose'], ['static'=>'1']), 'fa:times red');

        parent::add($this->form);
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
            
            $object = new ModeloHTML;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
            
            // preparando os parâmetros
            $parametros = '';
            if( !empty($param['campo']) AND is_array($param['campo']) )
            {
                $parametros = array();
                foreach( $param['campo'] as $row => $campo)
                {
                    $parametros['campos'][$param['campo'][$row]] = $param['tipo'][$row];
                }
            }
            
            $object->parametros = json_encode($parametros);

            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'), new TAction(['ModeloHtmlList','onClear']));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            
            $this->fieldlist->addHeader();
            foreach ($param['campo'] as $key => $campo)
            {
                $campo_detail = new stdClass;
                $campo_detail->campo = $param['campo'][$key];
                $campo_detail->tipo  = $param['tipo'][$key];
                
                $this->fieldlist->addDetail($campo_detail);
            }
            $this->fieldlist->addCloneAction();
            
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Clear form
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
        
        $this->fieldlist->addHeader();
        $this->fieldlist->addDetail( new stdClass );
        $this->fieldlist->addCloneAction();
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
                TTransaction::open('sistema'); // open a transaction
                
                $key = $param['key'];  // get the parameter $key
                $object = new ModeloHTML($key); // instantiates the Active Record
                
                // lendo os parametros
                $parametros = json_decode($object->parametros);
                
                if ( isset($parametros->campos) )
                {
                    $this->fieldlist->addHeader();
                    foreach ($parametros->campos as $key => $campo)
                    {
                        $campo_detail = new stdClass;
                        $campo_detail->campo = $key;
                        $campo_detail->tipo  = $campo;
                        
                        $this->fieldlist->addDetail($campo_detail);
                    }
                    
                    $this->fieldlist->addCloneAction();
                }
                else
                {
                    $this->onClear($param);
                }
                
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->onClear($param);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Closes window
     */
    public static function onClose()
    {
        parent::closeWindow();
    }
    
    
}

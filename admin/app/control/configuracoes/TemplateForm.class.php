<?php
/**
 * TemplateForm Registration
 *
 * @version    1.0
 * @package    control
 * @subpackage configuracoes
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 */
class TemplateForm extends TWindow
{
    protected $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        parent::setSize(0.8, null);
        parent::setMinWidth(0.9,900);
        parent::removePadding();
        parent::removeTitleBar();
        //parent::disableEscape();
        
        $this->setDatabase('sistema');              // defines the database
        $this->setActiveRecord('Template');     // defines the active record
        
        $this->setAfterSaveAction(new TAction(['TemplateList','onReload'],['register_state'=>'true']));
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Template');
        $this->form->setFormTitle('Formulário de Preferencias da Template');
        $this->form->setFieldSizes('100%');
        
        // Mudando a cor do cabeçalho
        $this->form->setHeaderProperty('class','header bg-blue-grey');

        // create the form fields
        $id          = new TEntry('id');
        $nome        = new TEntry('nome');
        $nome_fisico = new TEntry('nome_fisico');
        $script_head = new TTextSourceCode('script_head');
        $script_body = new TTextSourceCode('script_body');
        $padrao      = new TRadioGroup('padrao');
        
        // parametros dos campos
        $id->setEditable(FALSE);
        $padrao->setSize(80);
        $padrao->addItems(['t'=>_t('Yes'),'f'=>_t('No')]);
        $padrao->setLayout('horizontal');
        $padrao->setUseButton();
        
        // defininda as validações
        $nome->addValidation('Nome', new TRequiredValidator);
        //$nome->addValidation('Nome', new TUniqueValidator);
        $nome_fisico->addValidation('Nome Físico', new TRequiredValidator);
        //$nome_fisico->addValidation('Nome Físico', new TUniqueValidator);
        $padrao->addValidation('Ativo', new TRequiredValidator);


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] , [ new TLabel('Nome Físico') ], [ $nome_fisico ] );
        $this->form->addFields( [new TFormSeparator('Estilos e Scripts')] );
        $this->form->addFields( [new TLabel('<b>'.htmlentities('Início do HTML - antes do </head>').'</b>')] );
        $this->form->addFields( [ $script_head ] );
        $this->form->addFields( [new TLabel('<b>'.htmlentities('Final do HTML - antes do </body>').'</b>')] );
        $this->form->addFields( [ $script_body ] );
        $this->form->addFields( [ new TLabel('Padrao') ], [ $padrao ] );

        $nome->addValidation('Nome', new TRequiredValidator);
        $nome_fisico->addValidation('Nome Fisico', new TRequiredValidator);
        
        // criando os botões do formulário
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->form->addHeaderActionLink( _t('Close'),  new TAction([__CLASS__, 'onClose'], ['static'=>'1']), 'fa:times red');
        
        parent::add($this->form);
    }
    
    /**
     * Closes window
     */
    public static function onClose()
    {
        parent::closeWindow();
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

            $this->form->validate(); // validate form data
            
            $object = new Template;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
            
            if ( !empty($object->id) )
            {
                // verifica se devemos alterar o nome fisico da pasta da template
                $temp_atual = new Template($object->id);
                
                if ($object->nome_fisico != $temp_atual->nome_fisico)
                {
                    // verificando sa já existe uma pasta com esse nome
                    if (is_dir('../templates/'.$object->nome_fisico))
                        throw new Exception(_t("A directory with this name already exists."));
                    
                    $r = rename('../templates/'.$temp_atual->nome_fisico,'../templates/'.$object->nome_fisico);
                    
                    if (!$r)
                        $object->nome_fisico = $temp_atual->nome_fisico;
                }
            }
            
            // verifica se mudou o template padrão
            if ( $object->padrao == 't' )
            {
                $count = Template::countObjects();
                if ( $count > 0 )
                    Template::clear_padrao();
            }
            
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'), new TAction(['TemplateList','onClear']));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }

    
}

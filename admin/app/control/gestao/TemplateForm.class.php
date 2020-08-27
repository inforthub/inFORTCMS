<?php
/**
 * TemplateForm Registration
 *
 * @version     1.0
 * @package     control
 * @subpackage  gestao
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
class TemplateForm extends TWindow
{
    protected $form; // form
    protected $fieldlist;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param)
    {
        parent::__construct($param);
        parent::setSize(0.8, null);
        parent::setMinWidth(0.9,900);
        parent::removePadding();
        parent::removeTitleBar();
        //parent::disableEscape();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Template');
        $this->form->setFormTitle('Formulário de Preferencias da Template');
        $this->form->setFieldSizes('100%');
        
        // Mudando a cor do cabeçalho
        $this->form->setHeaderProperty('class','header bg-blue-grey');
        
        // master fields
        $id          = new TEntry('id');
        $nome        = new TEntry('nome');
        $nome_fisico = new TEntry('nome_fisico');
        $script_head = new TTextSourceCode('script_head');
        $script_body = new TTextSourceCode('script_body');
        $padrao      = new TRadioGroup('padrao');

        // sizes
        $id->setEditable(FALSE);
        $id->setSize('50%');
        $nome->setSize('100%');
        $padrao->setSize(80);
        $padrao->addItems(['t'=>_t('Yes'),'f'=>_t('No')]);
        $padrao->setLayout('horizontal');
        $padrao->setUseButton();
        
        // definindo as validações
        $nome->addValidation('Nome', new TRequiredValidator);
        $nome_fisico->addValidation('Nome Físico', new TRequiredValidator);
        $padrao->addValidation('Ativo', new TRequiredValidator);
        
        
        // add form fields to the form
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] , [ new TLabel('Nome Físico') ], [ $nome_fisico ] );
        $this->form->addFields( [new TFormSeparator('Estilos e Scripts')] );
        $this->form->addFields( [new TLabel('<b>'.htmlentities('Início do HTML - antes do </head>').'</b>')] );
        $this->form->addFields( [ $script_head ] );
        $this->form->addFields( [new TLabel('<b>'.htmlentities('Final do HTML - antes do </body>').'</b>')] );
        $this->form->addFields( [ $script_body ] );
        $this->form->addFields( [ new TLabel('Padrao') ], [ $padrao ] );
        
        
        // detail fields
        $this->fieldlist = new TFieldList;
        $this->fieldlist-> width = '100%';
        $this->fieldlist->enableSorting();

        $nome = new TEntry('list_nome[]');
        $ativo = new TCombo('list_ativo[]');

        $nome->setSize('100%');
        $ativo->setSize('100%');
        $ativo->addItems(['t'=>_t('Yes'),'f'=>_t('No')]);
        //$ativo->setLayout('horizontal');
        //$ativo->setUseButton();

        $this->fieldlist->addField( '<b>Nome</b>', $nome);
        $this->fieldlist->addField( '<b>Ativo</b>', $ativo);

        $this->form->addField($nome);
        $this->form->addField($ativo);
        
        $this->form->addFields( [new TFormSeparator('Posições') ] );
        $this->form->addFields( [$this->fieldlist] );
        

        // criando os botões do formulário
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onClear']), 'fa:eraser red');
        $this->form->addHeaderActionLink( _t('Close'),  new TAction([__CLASS__, 'onClose'], ['static'=>'1']), 'fa:times red');
        
        parent::add($this->form);
    }
    
    /**
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param)
    {
        try
        {
            TTransaction::open('sistema');
            
            if (isset($param['key']))
            {
                $key = $param['key'];
                
                $object = new Template($key);
                $this->form->setData($object);
                
                $items  = Posicao::where('template_id', '=', $key)->load();
                
                if ($items)
                {
                    $this->fieldlist->addHeader();
                    foreach($items  as $item )
                    {
                        $detail = new stdClass;
                        $detail->list_nome = $item->nome;
                        $detail->list_ativo = $item->ativo;
                        $this->fieldlist->addDetail($detail);
                    }
                    
                    $this->fieldlist->addCloneAction();
                }
                else
                {
                    $this->onClear($param);
                }
                
                TTransaction::close(); // close transaction
    	    }
    	    else
            {
                $this->onClear($param);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Clear form
     */
    public function onClear($param)
    {
        $this->fieldlist->addHeader();
        $this->fieldlist->addDetail( new stdClass );
        $this->fieldlist->addCloneAction();
    }
    
    /**
     * Save the Template and the Posicao's
     */
    public static function onSave($param)
    {
        try
        {
            TTransaction::open('sistema');
            
            $id = (int) $param['id'];
            $master = new Template;
            $master->fromArray( $param);
            
            if ( !empty($master->id) )
            {
                // verifica se devemos alterar o nome fisico da pasta da template
                $temp_atual = new Template($master->id);
                
                if ($master->nome_fisico != $temp_atual->nome_fisico)
                {
                    // verificando sa já existe uma pasta com esse nome
                    if (is_dir('../templates/'.$master->nome_fisico))
                        throw new Exception(_t("A directory with this name already exists."));
                    
                    $r = rename('../templates/'.$temp_atual->nome_fisico,'../templates/'.$master->nome_fisico);
                    
                    if (!$r)
                        $master->nome_fisico = $temp_atual->nome_fisico;
                }
            }
            
            // verifica se mudou o template padrão
            if ( $master->padrao == 't' )
            {
                $count = Template::countObjects();
                if ( $count > 0 )
                    Template::clear_padrao();
            }
            
            // verifica e salva as posições
            $master->clearParts();
            if( !empty($param['list_nome']) AND is_array($param['list_nome']) )
            {
                foreach( $param['list_nome'] as $row => $nome)
                {
                    if (!empty($nome))
                    {
                        $detail = new Posicao;
                        $detail->template_id = $master->id;
                        $detail->nome = $param['list_nome'][$row];
                        $detail->ativo = $param['list_ativo'][$row];
                        
                        $master->addPosicao($detail);
                    }
                }
            }
            
            
            $master->store(); // save master object
            
            $data = new stdClass;
            $data->id = $master->id;
            TForm::sendData('form_Template', $data);
            TTransaction::close(); // close the transaction
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), new TAction(['TemplateList','onClear']));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
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

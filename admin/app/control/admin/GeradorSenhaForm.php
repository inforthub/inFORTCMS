<?php
/**
 * GeradorSenha Form
 *
 * @version    1.0
 * @package    util
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 */

class GeradorSenhaForm extends TWindow
{
    //protected $form;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        parent::setSize(0.7, null);
        parent::setMinWidth(0.9,650);
        parent::removePadding();
        parent::removeTitleBar();
        //parent::disableEscape();
        
        $form = new BootstrapFormBuilder('form_GeradorSenha');
        $form->setFormTitle('Gerador de Senhas');
        $form->setFieldSizes('100%');
        
        // Mudando a cor do cabeçalho
        $form->setHeaderProperty('class','header bg-blue-grey');

        // criando os campos do formulário
        $senha      = new TEntry('senha');
        $tam        = new TSpinner('tam');
        $maiusculas = new TRadioGroup('maiusculas');
        $numeros    = new TRadioGroup('numeros');
        $simbolos   = new TRadioGroup('simbolos');
        
        // adicionando os campos no formulário
        $form->addFields( [ new TLabel('Tamanho') ], [ $tam ] , [ new TLabel('Maiúsculas') ], [ $maiusculas ] );
        $form->addFields( [ new TLabel('Números') ], [ $numeros ] , [ new TLabel('Simbolos') ], [ $simbolos ] );
        $form->addFields( [ new TLabel('Senha') ], [ $senha ] );
        
        $arr = ['t' => 'Sim', 'f' => 'Não'];
        
        // definindo os parâmetros dos campos
        $tam->setRange(8, 64, 1);
        $maiusculas->addItems( $arr );
        $maiusculas->setLayout('horizontal');
        $maiusculas->setValue('t');
        $numeros->addItems( $arr );
        $numeros->setLayout('horizontal');
        $numeros->setValue('t');
        $simbolos->addItems( $arr );
        $simbolos->setLayout('horizontal');
        $simbolos->setValue('t');
        
        // criando os botões do formulário
        $btn = $form->addAction(_t('Generate'), new TAction([$this, 'onGerar']), 'fas:magic');
        $btn->class = 'btn btn-sm btn-primary waves-effect';
        $form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        parent::add($form);
    }
    
    /**
     * Método que gera a senha
     */
    public static function onGerar($param)
    {
        $tam        = $param['tam'];
        $maiusculas = ($param['maiusculas'] == 't') ? true : false;
        $numeros    = ($param['numeros'] == 't') ? true : false;
        $simbolos   = ($param['simbolos'] == 't') ? true : false;
        
        $senha = TPass::makePass($tam, $maiusculas, $numeros, $simbolos);
        
        $obj = new StdClass;
        $obj->senha = $senha;
        
        TForm::sendData('form_GeradorSenha',$obj);
    }
    
    public function onView() {}
    
    /**
     * Closes window
     */
    public static function onClose()
    {
        parent::closeWindow();
    }
    
}

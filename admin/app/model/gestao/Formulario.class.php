<?php
/**
 * Formulario Active Record
 *
 * @version    1.0
 * @package    model
 * @subpackage gestao
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 */
class Formulario extends TRecord
{
    const TABLENAME = 'formulario';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('url');
        parent::addAttribute('html_site');
        parent::addAttribute('html_email');
        parent::addAttribute('msg_erro');
        parent::addAttribute('msg_sucesso');
        parent::addAttribute('email_destino');
        parent::addAttribute('script');
        parent::addAttribute('ativo');
    }
    
    /**
     * Retorna todas as variáveis (url) dos formulários ativos
     */
    public static function getVariaveis()
    {
        return Formulario::select('url')->where('ativo','=','t')->load();
    }
    
    /**
     * Retorna todas as variáveis (url) dos formulários ativos
     */
    public static function getFormulários()
    {
        return Formulario::where('ativo','=','t')->load();
    }
    
    /**
     * Retorna o formulário com base na URL
     * @param $url nome da url string
     */
    public static function getByUrl($url)
    {
        return Formulario::where('url','=',$url)->where('ativo','=','t')->first();
    }
    
    /**
     * Método mágico que cria o script necessário para o envio do formulário
     */
    public function onBeforeStore($object)
    {
        //file_put_contents('/tmp/log.txt', 'onBeforeStore:' . json_encode($object)."\n", FILE_APPEND);
        $root = THelper::getPreferences('pref_site_dominio');
        
        // criando o script e o modal
        $object->script = '<script type="text/javascript">
            $(document).ready(function(){
            
                // validando os campos
                var valida = $("#'.$object->url.'").validate({
                   submitHandler: function (form) {
                      // Impedindo o form de submeter
                      event.preventDefault();
                
                      // desabilitando botao enviar
                      $("#enviar").attr("disabled","disabled");
                      
                      var frm = $( "#'.$object->url.'" );
                
                      // limpando a div result
                      //$("#result").html("");
                
                      // pegando os valores do form
                      var values = frm.serialize();
                
                      // enviando os dados via post e exibindo o resultado na div
                      $.ajax({
                          url: "'.$root.'/enviar/'.$object->url.'",
                          type: "post",
                          data: values,
                          dataType: "json",
                          success: function(e){
                              console.log(e);
                              frm[0].reset();
                              $( ".modalHeader h2" ).html( e.titulo );
                              $( ".modalContent" ).html( e.mensagem );
                              window.location.replace("'.$root.'/#openModal");
                              $("#enviar").removeAttr("disabled");
                          },
                          error: function(e){
                              console.log(e);
                              $( ".modalHeader h2" ).html( e.titulo );
                              $( ".modalContent" ).html( e.mensagem );
                              window.location.replace("'.$root.'/#openModal");
                              $("#enviar").removeAttr("disabled");
                          }
                      });
                  }
                });
                
                $("#limpar").click(function(){
                    valida.resetForm();
                    // reabilita botão enviar
                    $("#enviar").removeAttr("disabled");
                });
                
                /*
                $("#'.$object->url.'").submit( function() {
                    var frm = $( "#'.$object->url.'" );

                    $.ajax({ type:"POST", url:"'.$root.'/enviar/'.$object->url.'", data:frm.serialize(), dataType: "json",
                        success: function(data){
                            console.log(data);
                            $( ".modalHeader h2" ).html( data.titulo );
                            $( ".modalContent" ).html( data.mensagem );
                            window.location.replace("'.$root.'/#openModal");
                            frm[0].reset();
                        },
                        error: function(data){
                            console.log(data);
                            window.location.replace("'.$root.'/#openModal");
                            frm[0].reset();
                        }
                    }).done(function(e){
            			frm[0].reset();
            		}).fail(function(e){
            			frm[0].reset();
            		});
                    return false;
                });
                */
            });
        </script>';
        
        // aplicando o id na tag FORM
        $dom = new DOMDocument();
        $dom->loadHTML($object->html_site, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $forms = $dom->getElementsByTagName('form');
        foreach ($forms as $form)
        {
            $form->setAttribute('id',$object->url);
            //$form->setAttribute('action','#openModal');
            $form->removeAttribute('action');
            $object->html_site = $dom->saveHTML();
            break;
        }
    }

    
    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        $id = isset($id) ? $id : $this->id;
        
        parent::deleteComposite('FormMensagem', 'formulario_id', $id);
    
        // delete the object itself
        parent::delete($id);
    }



}

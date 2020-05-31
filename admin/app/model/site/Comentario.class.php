<?php
/**
 * Comentario Active Record
 * @author  <your-name-here>
 */
class Comentario extends TRecord
{
    const TABLENAME = 'comentario';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('email');
        parent::addAttribute('titulo');
        parent::addAttribute('comentario');
        parent::addAttribute('dt_post');
        parent::addAttribute('resposta_id');
        parent::addAttribute('artigo_id');
    }
    
    /**
     * Retorna todos os comentários de um Post
     */
    public static function getComentarioPost($post_id)
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('artigo_id', '=', $post_id));
        return Comentario::getObjects($criteria);
        //$repos = new TRepository('Comentario');
        //return $repos->load($criteria);
    }
    
    /**
     * Retorna todas as respostas de um comentário
     */
    public static function getRespComentario($id)
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('resposta_id', '=', $id));
        return Comentario::getObjects($criteria);
        //$repos = new TRepository('Comentario');
        //return $repos->load($criteria);
    }


}

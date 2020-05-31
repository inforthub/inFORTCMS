<?php
/**
 * TemplateFunctions Class
 *
 * Classe com os métodos expecíficos para a template
 *
 * @version     1.0
 * @package     lib
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2019 (https://www.infort.eti.br)
 *
 */
class TemplateFunctions
{
    
    /**
     * Renderiza o menu
     */
    public static function renderMenu($root, $url)
    {
        try
        {
            TTransaction::open('sistema');
            
            $menus = Menu::where('ativo','=','t')->where('menu_pai_id','=',0)->orderBy('ordem')->load();
            //$ul = new TElement('ul');
            $ret = '';
            if ($menus)
            {
                foreach($menus as $menu)
                {
                    $li = new TElement('li');
                    
                    // identificamos se a página atual pertence ao menu
                    if ( '/'.$menu->url == $url || ($url == '/' && $menu->inicial == 't') )
					   $li->class = 'active';
                    
                    // verifica se tem submenus
                    $submenu = $menu->get_submenu();
                    if ($submenu)
                    {
                        $li->class = 'nav-item dropdown';
                        
                        $a = new THyperLink($menu->titulo, $root.$menu->url);
                        //$a->class = 'nav-link';
                        $a->{'target'} = '_self';
                        $a->{'data-toggle'} = $menu->header_class; //'dropdown';
                        //$a->{'role'} = 'button';
                        $li->add($a);
                        
                        $div = TElement::tag('div','',['class'=>'dropdown-menu']);
                        
                        foreach($submenu as $sub)
                        {
                            $a = new THyperLink($sub->titulo, $root.$menu->url.'/'.$sub->url);
                            $a->class = 'dropdown-item';
                            $a->{'target'} = '_self';
                            $div->add($a);
                        }
                        
                        $li->add($div);
                    }
                    else
                    {
                        $a = new THyperLink($menu->titulo, $root.$menu->url);
                        //$a->class = 'nav-link';
                        $a->{'target'} = '_self';
                        //$a->{'data-toggle'} = '';
                        //$a->{'role'} = 'button';
                        $li->add($a);
                    }
                    /*
                    <li class="active"><a href="./home.html">Home</a></li>
                    <li><a href="./gallery.html">Gallery</a></li>
                    <li><a href="./gallery-single.html">Single gallery</a></li>
                    <li><a href="./blog.html">Blog</a></li>
                    <li><a href="./contact.html">Contact</a></li>
                    */
                                        
                    //$ul->add($li);
					$ret .= $li->getContents();
                }
            }
            
            TTransaction::close();
            
            return $ret; //$ul->getContents();
        }
        catch (Exception $e) // in case of exception
        {
            //new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
            return '';
        }
    }
    
}

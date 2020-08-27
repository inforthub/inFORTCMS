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
    public function __construct($url=null)
    {
        $this->setReplaces($url);
    }
    
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
					$li->class = 'nav-item';
                    
                    // verifica se tem submenus
                    $submenu = $menu->get_submenu();
                    if ($submenu)
                    {
                        $li->class = 'nav-item dropdown';
                        
                        $a = new THyperLink('<i class="ni ni-bold-right d-lg-none"></i><span class="nav-link-inner--text">'.$menu->titulo.'</span>', $root.$menu->apelido);
                        $a->class = 'nav-link';
                        $a->{'target'} = '_self';
                        $a->{'data-toggle'} = $menu->header_class; //'dropdown';
                        $a->{'role'} = 'button';
                        $li->add($a);
                        
                        $div = TElement::tag('div','',['class'=>'dropdown-menu']);
                        
                        foreach($submenu as $sub)
                        {
                            $a = new THyperLink($sub->titulo, $root.$menu->apelido.'/'.$sub->apelido);
                            $a->class = 'dropdown-item';
                            $a->{'target'} = '_self';
                            $div->add($a);
                        }
                        
                        $li->add($div);
                    }
                    else
                    {
                        $a = new THyperLink('<i class="ni ni-bold-right d-lg-none"></i><span class="nav-link-inner--text">'.$menu->titulo.'</span>', $root.$menu->apelido);
                        $a->class = 'nav-link';
                        $a->{'target'} = '_self';
                        $a->{'data-toggle'} = '';
                        $a->{'role'} = 'button';
                        $li->add($a);
                    }
                                        
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

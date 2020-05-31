<?php
/**
 * Application menu editor item renderer
 *
 * @version    7.0
 * @package    app
 * @subpackage lib
 * @author     Pablo Dall'Oglio
 * @author     Artur Comunello
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class ApplicationMenuEditorItemRenderer extends TElement
{
    private $label;
    private $action;
    private $image;
    private $menu;
    private $level;
    private $color;
    
    /**
     * Class constructor
     * @param $label  The menu label
     * @param $action The menu action
     * @param $image  The menu image
     * @param $level  The menu level
     */
    public function __construct($label, $action, $image = NULL, $level = 0)
    {
        parent::__construct('div');
        
        $this->label  = $label;
        $this->action = $action;
        $this->level  = $level;
        $this->color  = NULL;
        
        if ($image)
        {
            $image  = str_replace(':', ' fa-', $image);
            $pieces = explode(' ', $image);
            $image  = "{$pieces[0]} {$pieces[1]}";

            $this->image = $image;

            if (isset($pieces[3]))
            {
                $this->color = $pieces[3];
            }
        }
    }
    
    /**
     * Define the submenu for the item
     * @param $menu A TMenu object
     */
    public function setMenu(ApplicationMenuEditorRenderer $menu)
    {
        $this->{'class'} = 'dropdown-submenu';
        $this->menu = $menu;
    }
    
    /**
     * Shows the item renderer
     */
    public function show()
    {
        $icon   = new TCombo($this->level . '_icon_' . mt_rand(1000000000, 1999999999));
        $label  = new TEntry($this->level . '_label_' . mt_rand(1000000000, 1999999999));
        $action = new TEntry($this->level . '_action_' . mt_rand(1000000000, 1999999999));
        $tcolor = new TColor($this->level . '_color_' . mt_rand(1000000000, 1999999999));
        
        $icon->enableSearch();
        $icon->addItems([$this->image => "<span><i class='combo-icons fa {$this->image}'></i> <span class='combo-icons-text'>{$this->image}</span></span>"]);
        
        $icon->setValue($this->image);
        $label->onChange = '__menu_editor_update_label(this)';
        
        $icon->style   = 'width:100%';
        $label->style  = 'width:100%';
        $action->style = 'width:100%';
        $tcolor->style = 'width:100%';

        $icon->class .= ' mbicons';
        
        TTransaction::open('permission');
        $programs = SystemProgram::getIndexedArray('controller', 'name');
        $action->setCompletion( array_keys($programs) );
        TTransaction::close();

        $move = new TImage('fa:arrows-alt');
        $move->style = 'cursor:move';
        $move->title = _t('Move item');
        
        // field align
        $label_align        = new TLabel($move . $this->label);
        $label_align->style = 'margin-left:'.(20 * ($this->level - 1)).'px';
        
        $div_label_align = new TElement('div');
        $div_label_align->class = 'col-sm-2';
        $div_label_align->style = 'padding:5px;line-height:25px;';
        $div_label_align->add($label_align);

        $label->setValue($this->label);
        $action->setValue($this->action);
        $tcolor->setValue($this->color);
        
        $dropdown = new TDropDown('', '');
        $dropdown->addAction(_t('Add item above'), "__menu_editor_add_sibling(this, 'top')");
        $dropdown->addAction(_t('Add item below'), "__menu_editor_add_sibling(this, 'bottom')");
        $dropdown->addAction(_t('Add child item'), "__menu_editor_add_child(this)");
        $dropdown->addAction(_t('Remove item'),    "__menu_editor_remove_item(this)");
        $dropdown->class = 'menueditor-dropdown-menu';
        $dropdown->setPullSide('right');
        
        $div1 = new TElement('div');
        $div1->class = 'col-sm-3 menueditor-fields-row';
        $div1->add($label);
        $div1->level = $this->level;
        
        $div2 = new TElement('div');
        $div2->class = 'col-sm-2 menueditor-fields-row';
        $div2->add($action);
        $div2->level = $this->level;
        
        $div3 = new TElement('div');
        $div3->class = 'col-sm-2 menueditor-fields-row';
        $div3->add($icon);
        $div3->level = $this->level;
        
        $div4 = new TElement('div');
        $div4->class = 'col-sm-2 menueditor-fields-row';
        $div4->add($tcolor);
        $div4->level = $this->level;
        
        $div5 = new TElement('div');
        $div5->class = 'col-sm-1 menueditor-fields-row';
        $div5->add($dropdown);
        $div5->level = $this->level;
        
        $linha = new TElement('div');
        $linha->add($div_label_align);
        $linha->add($div1);
        $linha->add($div2);
        $linha->add($div3);
        $linha->add($div4);
        $linha->add($div5);
        
        $linha->class = 'row linha';
        $linha->style = 'margin:unset;';
        $linha->level = $this->level;
        
        $this->add($linha);
        $this->class = 'level-'.$this->level;
        
        parent::add($this->menu);
        parent::show();
    }
}

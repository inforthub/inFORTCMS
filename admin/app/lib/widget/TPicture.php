<?php

use Adianti\Widget\Form\AdiantiWidgetInterface;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Form\TEntry;
use Adianti\Control\TAction;

/**
 * TPicture Widget
 *
 * @version    1.0
 * @package    widget
 * @subpackage lib
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2020 inFORT (https://www.infort.eti.br)
 *
 */
//class TPicture extends TEntry implements AdiantiWidgetInterface
class TPicture extends TField implements AdiantiWidgetInterface
{
    protected $id;
    protected $value;
    protected $name;
    
    /**
     * Class Constructor
     * @param $name Name of the widget
     */
    public function __construct($name)
    {
        parent::__construct($name);
        //$this->id = 'thidden_'.mt_rand(1000000000, 1999999999);
        //$this->tag->{'autocomplete'} = 'off';
    }
    
    /**
     * Shows the widget at the screen
     */
    public function show()
    {
        
        $wrapper = new TElement('div');
        $wrapper->{'class'} = $this->tag->{'class'}; //'input-group';
        $wrapper->{'name'}  = $this->name;
        $wrapper->{'widget'} = 'tpicture';
        $wrapper->{'style'} = $this->style;
        
        
        if ($this->id and empty($wrapper->{'id'}))
        {
            $wrapper->{'id'} = $this->id;
        }
        else
        {
            $wrapper->{'id'} = 'tpicture_' . mt_rand(1000000000, 1999999999);
        }

        $wrapper->add($this->value);
        $wrapper->show();
    }
}

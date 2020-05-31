<?php
/**
 * Application menu editor renderer
 *
 * @version    7.0
 * @package    app
 * @subpackage lib
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class ApplicationMenuEditorRenderer extends TElement
{
    private $items;
    private $menu_level;
    
    /**
     * Class Constructor
     * @param $xml SimpleXMLElement parsed from XML Menu
     */
    public function __construct($xml, $menu_level = 1)
    {
        parent::__construct('div');
        parent::setProperty('sort-menu', 'order-menu');

        $this->items = array();
        $this->menu_level = $menu_level;
        
        if ($xml instanceof SimpleXMLElement)
        {
            $this->parse($xml);
        }
    }
    
    /**
     * Add a MenuItem
     * @param $menuitem A TMenuItem Object
     */
    public function addMenuItem(ApplicationMenuEditorItemRenderer $menuitem)
    {
        $this->items[] = $menuitem;
    }
    
    /**
     * Return the menu items
     */
    public function getMenuItems()
    {
        return $this->items;
    }
    
    /**
     * Parse a XMLElement reading menu entries
     * @param $xml A SimpleXMLElement Object
     * @param $permission_callback check permission callback
     */
    public function parse($xml, $permission_callback = NULL)
    {
        $i = 0;
        foreach ($xml as $xmlElement)
        {
            $atts     = $xmlElement->attributes();
            $label    = (string) $atts['label'];
            $action   = (string) $xmlElement-> action;
            $icon     = (string) $xmlElement-> icon;
            $menu     = NULL;
            $menuItem = new ApplicationMenuEditorItemRenderer($label, $action, $icon, $this->menu_level);
            
            if ($xmlElement->menu)
            {
                $menu = new ApplicationMenuEditorRenderer($xmlElement-> menu-> menuitem, $this->menu_level +1);
                $menuItem->setMenu($menu);
            }
            
            // just child nodes have actions
            if ( $action )
            {
                // menus without permission check
                $this->addMenuItem($menuItem);
            }
            // parent nodes are shown just when they have valid children (with permission)
            else if ( isset($menu) AND count($menu->getMenuItems()) > 0)
            {
                $this->addMenuItem($menuItem);
            }
            
            $i ++;
        }
    }
    
    /**
     * Shows the widget at the screen
     */
    public function show()
    {
        if ($this->items)
        {
            foreach ($this->items as $item)
            {
                parent::add($item);
            }
        }
        parent::show();
    }
}

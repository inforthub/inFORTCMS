<?php
/**
 * SystemMenuEditor
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @author     Artur Comunello
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemMenuEditor extends TPage
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
        
        $xml = new SimpleXMLElement(file_get_contents('menu.xml'));
        
        $menu = new ApplicationMenuEditorRenderer($xml);
        
        $order = new TElement('div');
        $order->class = 'col-sm-2';
        $order->add(_t('Order'));
        
        $label = new TElement('div');
        $label->class = 'col-sm-3';
        $label->add(_t('Label'));
        
        $action = new TElement('div');
        $action->class = 'col-sm-2';
        $action->add(_t('Action'));

        $icon = new TElement('div');
        $icon->class = 'col-sm-2';
        $icon->add(_t('Icon'));
        
        $color = new TElement('div');
        $color->class = 'col-sm-2';
        $color->add(_t('Color'));
        
        $titles = new TElement('div');
        $titles->class = 'row menueditor-fields-row menueditor-title';
        $titles->style = "font-size: 18px; font-weight: bold; margin: unset;height: 35px;border-bottom: 1px solid #cccccc;margin-bottom: 10px;";
        
        $titles->add($order);
        $titles->add($label);
        $titles->add($action);
        $titles->add($icon);
        $titles->add($color);
        
        $form = new TElement('form');
        $form->id = 'menu-editor';
        $form->name = 'menu-editor';
        $form->add($titles);
        $form->add($menu);

        $btn = TButton::create('save', ['SystemMenuEditor', 'onSaveMenu'], 'Salvar', 'fa:save');
        $btn->class = 'btn btn-success';
        $btn->setFormName('menu-editor');

        $panel = TPanelGroup::pack("Menu builder", $form, $btn);
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($panel);
        
        parent::add($container);

        TTransaction::open('permission');
        $programs = SystemProgram::getIndexedArray('controller', 'controller');
        $programs = implode(';', $programs);
        TTransaction::close();
        
        TScript::create("__menu_editor_start('{$programs}')");
    }
    
    /**
     * Save menu
     * @param $param Request
     */
    public static function onSaveMenu($param)
    {
        unset($param['class']);
        unset($param['method']);
        unset($param['static']);
     
        $domDocument                     = new DOMDocument('1.0', "UTF-8");
        $domDocument->formatOutput       = true;
        $domDocument->preserveWhiteSpace = false;

        $menuRoot = $domDocument->createElement('menu');
        $menuRoot = $domDocument->appendChild($menuRoot);
        
        $icon     = false;
        $linha     = 1;
        $count     = 0;
        $nodeItems = [];
        $itemNode  = $domDocument->createElement('menuitem');

        $objectLinha   = new stdClass;
        $valiation_msg = [];

        foreach ($param as $key => $value)
        {
            $count ++;

            $key_data = explode('_', $key);
            $field    = $key_data[1];

            if ($field == 'icon')
            {
                if ($value)
                {
                    $icon = true;
                    $value = str_replace('far fa-', 'far:', $value);
                    $value = str_replace('fas fa-', 'fas:', $value);
                    $value = str_replace('fa-', 'fa:', $value);
                    $value = str_replace('far-', 'far:', $value);
                    $value = str_replace('fas-', 'fas:', $value);
                    $value .= ' fa-fw';
                }
            }

            if ($field == 'label')
            {
                $domAttribute        = $domDocument->createAttribute('label');
                $domAttribute->value = $value;

                $itemNode->appendChild($domAttribute);
            }
            else
            {
                if ($value)
                {
                    if($field == 'color' AND $icon)
                    {
                        $icon = false;

                        $domElement->nodeValue .= " {$value}";
                    }
                    else
                    {
                        $domElement = $domDocument->createElement($field, $value);
                        $domElement = $itemNode->appendChild($domElement);
                    }
                }
            }

            $objectLinha->$field = $value;
            
            if ($count % 4 == 0)
            {
                $objectLinha->chave = $key;
 
                $tem_filho = (bool) self::getChildKeys($param, $key);
                $mensagem  = self::validateMenu($objectLinha, $linha, $tem_filho);
 
                if ($mensagem)
                {
                    $valiation_msg[] = $mensagem;
                }
                
                $objectLinha             = new stdClass;
                $itemNode->child_keys = self::getChildKeys($param, $key);
                $nodeItems[$key]         = $itemNode;
                $itemNode                = $domDocument->createElement('menuitem');
                $linha++;
            }
        }

        if (!empty($valiation_msg))
        {
            $mensagem = AdiantiCoreTranslator::translate('Required fields') . ': ' . '<br>' . implode('<br>', $valiation_msg);
            
            new TMessage('error', $mensagem);
            return false;
        }
        
        foreach ($nodeItems as $key => $itemNode)
        {
            if (self::getLevel($key) == 1)
            {
                $node = self::createAllChildNodes($nodeItems, $itemNode, $domDocument);
                $menuRoot->appendChild($node);
            }
        }
        
        if (is_writable('menu.xml'))
        {
            $domDocument->save('menu.xml');
            $action = new TAction([SystemMenuEditor::class, "reloadMenu"]);
            new TMessage('info', _t('Menu saved'), $action);
        }
        else
        {
            new TMessage('error', _t('Permission denied') . ': <b>menu.xml</b>');
        }
    }
 
    public function reloadMenu()
    {
        AdiantiCoreApplication::gotoPage('SystemMenuEditor');
    }
 
    /**
     * Create child node
     */
    public static function createAllChildNodes($nodeItems, $nodeItem, $domDocument)
    {
        if (empty($nodeItem->child_keys))
        {
            return $nodeItem;
        }
        
        $menuNode = $domDocument->createElement('menu');

        foreach ($nodeItem->child_keys as $chave_filho)
        {
            $node = self::createAllChildNodes($nodeItems, $nodeItems[$chave_filho], $domDocument);
            $menuNode->appendChild($node);
        }
        
        $nodeItem->appendChild($menuNode);

        return $nodeItem;
    }

    /**
     * Get child keys
     */
    public static function getChildKeys($nodeItems, $chave)
    {
        $nivel_pai     = self::getLevel($chave);
        $nivel_filho   = $nivel_pai +1;
        $child_keys = [];

        while ($chave_atual = self::getNextKey($nodeItems, $chave))
        {
            $chave = $chave_atual;
            
            if (!strpos($chave_atual, 'color'))
            {
                continue;
            }

            $nivel_atual = self::getLevel($chave_atual);

            if ($nivel_atual == $nivel_pai)
            {
                break;
            }

            if ($nivel_atual == $nivel_filho)
            {
                $child_keys[] = $chave_atual;
            }
        }

        return $child_keys;
    }

    /**
     * Validate menu
     */
    public static function validateMenu($menu, $linha, $tem_filho)
    {
        $fields   = [];
        
        if (empty($menu->label))
        {
            $fields[] = _t('Label');
        }
 
        if (self::getLevel($menu->chave) != 1 AND empty($menu->action) AND !$tem_filho)
        {
            $fields[] = _t('Action');
        }

        if (!empty($fields))
        {
            return "#{$linha}: " . implode(", ", $fields);
        }
    }

    /**
     * Get next key
     */
    public static function getNextKey($nodeItems, $chave_atual)
    {
        $chaves = array_keys($nodeItems);

        $aux_key = array_search($chave_atual, $chaves);
        $aux_key++;
        
        if (isset($chaves[$aux_key]))
        {
            return $chaves[$aux_key];   
        }

        return false;
    }

    /**
     * Get level
     */
    public static function getLevel($key)
    {
        return explode('_', $key)[0];
    }
}

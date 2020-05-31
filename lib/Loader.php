<?php
namespace Infort\Core;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Classe loader, baseado no Framework class autoloader do Adianti v5.7
 *
 * @version    1.0
 * @package    lib
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2019 (https://www.infort.eti.br)
 */
class Loader
{
    static private $classMap;
    
    /**
     * Load the class map
     */
    public static function loadClassMap()
    {
        self::$classMap = self::getMap();
    }
    
    /**
     * Define the class path
     * @param $class Class name
     * @param $path  Class path
     */
    public static function setClassPath($class, $path)
    {
        self::$classMap[$class] = $path;
    }
    
    /**
     * Core autloader
     * @param $className Class name
     */
    public static function autoload($className)
    {
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if (strrpos($className, '\\') !== FALSE)
        {
            $pieces    = explode('\\', $className);
            $className = array_pop($pieces);
            $namespace = implode('\\', $pieces);
        }
        $fileName = 'lib'.'\\'.strtolower($namespace).'\\'.$className.'.php';
        $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $fileName);
        
        if (file_exists($fileName))
        {
            //echo "PSR: $className <br>";
            require_once $fileName;
            self::globalScope($className);
        }
        else
        {
            if (!self::legacyAutoload($className))
            {
                self::AppLoader($className);
            }
        }
    }
    
    /**
     * autoloader
     * @param $class classname
     */
    public static function legacyAutoload($class)
    {
        if (isset(self::$classMap[$class]))
        {
            if (file_exists(self::$classMap[$class]))
            {
                //echo 'Classmap '.self::$classMap[$class] . '<br>';
                require_once self::$classMap[$class];
                
                self::globalScope($class);
                return TRUE;
            }
        }
    }
    
    /**
     * make a class global
     */
    public static function globalScope($class)
    {
        if (isset(self::$classMap[$class]) AND self::$classMap[$class])
        {
            if (!class_exists($class, FALSE))
            {
                $ns = self::$classMap[$class];
                $ns = str_replace('/', '\\', $ns);
                $ns = str_replace('lib\\adianti', 'Adianti', $ns);
                $ns = str_replace('.class.php', '', $ns);
                $ns = str_replace('.php', '', $ns);
                
                //echo "&nbsp;&nbsp;&nbsp;&nbsp;Mapping: $ns, $class<br>";
                if (class_exists($ns) OR interface_exists($ns))
                {
                    class_alias($ns, $class, FALSE);
                }
            }
        }
    }
    
    
    /***************************************************
                  AdiantiApplicationLoader
    ***************************************************/
    
    public static function AppLoader($class)
    {
        $folders = array();
        $folders[] = 'app/model';
        $folders[] = 'app/lib/util';
        $folders[] = '../lib';

        foreach ($folders as $folder)
        {
            if (file_exists("{$folder}/{$class}.class.php"))
            {
                require_once "{$folder}/{$class}.class.php";
                return TRUE;
            }
            else
            {
                try
                {
                    if (file_exists($folder))
                    {
                        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder),
                                                               RecursiveIteratorIterator::SELF_FIRST) as $entry)
                        {
                            if (is_dir($entry))
                            {
                                if (file_exists("{$entry}/{$class}.class.php"))
                                {
                                    require_once "{$entry}/{$class}.class.php";
                                    return TRUE;
                                }
                            }
                        }
                    }
                }
                catch(Exception $e)
                {
                    new TMessage('error', $e->getMessage());
                }
            }
        }
    }
    
    
    /***************************************************
                       AdiantiClassMap
    ***************************************************/
    
    
    public static function getMap()
    {
        $classPath = array();
        //$classPath['TStandardForm']              = 'lib/adianti/base/TStandardForm.php';
        //$classPath['TStandardFormList']          = 'lib/adianti/base/TStandardFormList.php';
        //$classPath['TStandardList']              = 'lib/adianti/base/TStandardList.php';
        //$classPath['TStandardSeek']              = 'lib/adianti/base/TStandardSeek.php';
        //$classPath['TAction']                    = 'lib/adianti/control/TAction.php';
        //$classPath['TPage']                      = 'lib/adianti/control/TPage.php';
        //$classPath['TWindow']                    = 'lib/adianti/control/TWindow.php';
        //$classPath['AdiantiApplicationLoader']   = 'lib/adianti/core/AdiantiApplicationLoader.php';
        //$classPath['AdiantiApplicationConfig']   = 'lib/adianti/core/AdiantiApplicationConfig.php';
        //$classPath['AdiantiClassMap']            = 'lib/adianti/core/AdiantiClassMap.php';
        //$classPath['AdiantiCoreApplication']     = 'lib/adianti/core/AdiantiCoreApplication.php';
        //$classPath['AdiantiCoreLoader']          = 'lib/adianti/core/AdiantiCoreLoader.php';
        $classPath['AdiantiCoreTranslator']      = 'lib/adianti/core/AdiantiCoreTranslator.php';
        $classPath['AdiantiTemplateParser']      = 'lib/adianti/core/AdiantiTemplateParser.php';
        $classPath['TConnection']                = 'lib/adianti/database/TConnection.php';
        $classPath['TCriteria']                  = 'lib/adianti/database/TCriteria.php';
        //$classPath['TExpression']                = 'lib/adianti/database/TExpression.php';
        $classPath['TFilter']                    = 'lib/adianti/database/TFilter.php';
        $classPath['TDatabase']                  = 'lib/adianti/database/TDatabase.php';
        $classPath['TRecord']                    = 'lib/adianti/database/TRecord.php';
        $classPath['TRepository']                = 'lib/adianti/database/TRepository.php';
        //$classPath['TSqlDelete']                 = 'lib/adianti/database/TSqlDelete.php';
        //$classPath['TSqlInsert']                 = 'lib/adianti/database/TSqlInsert.php';
        //$classPath['TSqlMultiInsert']            = 'lib/adianti/database/TSqlMultiInsert.php';
        $classPath['TSqlSelect']                 = 'lib/adianti/database/TSqlSelect.php';
        //$classPath['TSqlStatement']              = 'lib/adianti/database/TSqlStatement.php';
        $classPath['TSqlUpdate']                 = 'lib/adianti/database/TSqlUpdate.php';
        $classPath['TTransaction']               = 'lib/adianti/database/TTransaction.php';
        //$classPath['TLogger']                    = 'lib/adianti/log/TLogger.php';
        //$classPath['TLoggerHTML']                = 'lib/adianti/log/TLoggerHTML.php';
        //$classPath['TLoggerSTD']                 = 'lib/adianti/log/TLoggerSTD.php';
        //$classPath['TLoggerTXT']                 = 'lib/adianti/log/TLoggerTXT.php';
        //$classPath['TLoggerXML']                 = 'lib/adianti/log/TLoggerXML.php';
        //$classPath['AdiantiRegistryInterface']   = 'lib/adianti/registry/AdiantiRegistryInterface.php';
        //$classPath['TAPCache']                   = 'lib/adianti/registry/TAPCache.php';
        $classPath['TSession']                   = 'lib/adianti/registry/TSession.php';
        //$classPath['AdiantiAutocompleteService'] = 'lib/adianti/service/AdiantiAutocompleteService.php';
        //$classPath['AdiantiMultiSearchService']  = 'lib/adianti/service/AdiantiMultiSearchService.php';
        //$classPath['AdiantiUploaderService']     = 'lib/adianti/service/AdiantiUploaderService.php';
        //$classPath['AdiantiRecordService']       = 'lib/adianti/service/AdiantiRecordService.php';
        //$classPath['TCNPJValidator']             = 'lib/adianti/validator/TCNPJValidator.php';
        //$classPath['TCPFValidator']              = 'lib/adianti/validator/TCPFValidator.php';
        //$classPath['TEmailValidator']            = 'lib/adianti/validator/TEmailValidator.php';
        //$classPath['TFieldValidator']            = 'lib/adianti/validator/TFieldValidator.php';
        //$classPath['TMaxLengthValidator']        = 'lib/adianti/validator/TMaxLengthValidator.php';
        //$classPath['TMaxValueValidator']         = 'lib/adianti/validator/TMaxValueValidator.php';
        //$classPath['TMinLengthValidator']        = 'lib/adianti/validator/TMinLengthValidator.php';
        //$classPath['TMinValueValidator']         = 'lib/adianti/validator/TMinValueValidator.php';
        //$classPath['TNumericValidator']          = 'lib/adianti/validator/TNumericValidator.php';
        //$classPath['TRequiredValidator']         = 'lib/adianti/validator/TRequiredValidator.php';
        $classPath['TElement']                   = 'lib/adianti/widget/base/TElement.php';
        //$classPath['TScript']                    = 'lib/adianti/widget/base/TScript.php';
        //$classPath['TStyle']                     = 'lib/adianti/widget/base/TStyle.php';
        //$classPath['TExpander']                  = 'lib/adianti/widget/container/TExpander.php';
        //$classPath['TFrame']                     = 'lib/adianti/widget/container/TFrame.php';
        //$classPath['THBox']                      = 'lib/adianti/widget/container/THBox.php';
        //$classPath['TJQueryDialog']              = 'lib/adianti/widget/container/TJQueryDialog.php';
        //$classPath['TNotebook']                  = 'lib/adianti/widget/container/TNotebook.php';
        //$classPath['TPanel']                     = 'lib/adianti/widget/container/TPanel.php';
        //$classPath['TPanelGroup']                = 'lib/adianti/widget/container/TPanelGroup.php';
        //$classPath['TScroll']                    = 'lib/adianti/widget/container/TScroll.php';
        //$classPath['TTable']                     = 'lib/adianti/widget/container/TTable.php';
        //$classPath['TTableCell']                 = 'lib/adianti/widget/container/TTableCell.php';
        //$classPath['TTableRow']                  = 'lib/adianti/widget/container/TTableRow.php';
        //$classPath['TVBox']                      = 'lib/adianti/widget/container/TVBox.php';
        //$classPath['TDataGrid']                  = 'lib/adianti/widget/datagrid/TDataGrid.php';
        //$classPath['TDataGridAction']            = 'lib/adianti/widget/datagrid/TDataGridAction.php';
        //$classPath['TDataGridActionGroup']       = 'lib/adianti/widget/datagrid/TDataGridActionGroup.php';
        //$classPath['TDataGridColumn']            = 'lib/adianti/widget/datagrid/TDataGridColumn.php';
        //$classPath['TPageNavigation']            = 'lib/adianti/widget/datagrid/TPageNavigation.php';
        //$classPath['TInputDialog']               = 'lib/adianti/widget/dialog/TInputDialog.php';
        //$classPath['TMessage']                   = 'lib/adianti/widget/dialog/TMessage.php';
        //$classPath['TAlert']                     = 'lib/adianti/widget/dialog/TAlert.php';
        //$classPath['TQuestion']                  = 'lib/adianti/widget/dialog/TQuestion.php';
        //$classPath['AdiantiWidgetInterface']     = 'lib/adianti/widget/form/AdiantiWidgetInterface.php';
        //$classPath['AdiantiFormInterface']       = 'lib/adianti/widget/form/AdiantiFormInterface.php';
        //$classPath['TButton']                    = 'lib/adianti/widget/form/TButton.php';
        //$classPath['TCheckButton']               = 'lib/adianti/widget/form/TCheckButton.php';
        //$classPath['TCheckGroup']                = 'lib/adianti/widget/form/TCheckGroup.php';
        //$classPath['TColor']                     = 'lib/adianti/widget/form/TColor.php';
        //$classPath['TIcon']                      = 'lib/adianti/widget/form/TIcon.php';
        //$classPath['TCombo']                     = 'lib/adianti/widget/form/TCombo.php';
        //$classPath['TComboCombined']             = 'lib/adianti/widget/form/TComboCombined.php';
        //$classPath['TDate']                      = 'lib/adianti/widget/form/TDate.php';
        //$classPath['TDateTime']                  = 'lib/adianti/widget/form/TDateTime.php';
        //$classPath['TTime']                      = 'lib/adianti/widget/form/TTime.php';
        //$classPath['TEntry']                     = 'lib/adianti/widget/form/TEntry.php';
        //$classPath['TNumeric']                   = 'lib/adianti/widget/form/TNumeric.php';
        //$classPath['TField']                     = 'lib/adianti/widget/form/TField.php';
        //$classPath['TFile']                      = 'lib/adianti/widget/form/TFile.php';
        //$classPath['TMultiFile']                 = 'lib/adianti/widget/form/TMultiFile.php';
        //$classPath['TForm']                      = 'lib/adianti/widget/form/TForm.php';
        //$classPath['THidden']                    = 'lib/adianti/widget/form/THidden.php';
        //$classPath['THtmlEditor']                = 'lib/adianti/widget/form/THtmlEditor.php';
        //$classPath['TLabel']                     = 'lib/adianti/widget/form/TLabel.php';
        //$classPath['TMultiField']                = 'lib/adianti/widget/form/TMultiField.php';
        //$classPath['TMultiSearch']               = 'lib/adianti/widget/form/TMultiSearch.php';
        //$classPath['TMultiEntry']                = 'lib/adianti/widget/form/TMultiEntry.php';
        //$classPath['TUniqueSearch']              = 'lib/adianti/widget/form/TUniqueSearch.php';
        //$classPath['TPassword']                  = 'lib/adianti/widget/form/TPassword.php';
        //$classPath['TRadioButton']               = 'lib/adianti/widget/form/TRadioButton.php';
        //$classPath['TRadioGroup']                = 'lib/adianti/widget/form/TRadioGroup.php';
        //$classPath['TSeekButton']                = 'lib/adianti/widget/form/TSeekButton.php';
        //$classPath['TSelect']                    = 'lib/adianti/widget/form/TSelect.php';
        //$classPath['TSlider']                    = 'lib/adianti/widget/form/TSlider.php';
        //$classPath['TSortList']                  = 'lib/adianti/widget/form/TSortList.php';
        //$classPath['TSpinner']                   = 'lib/adianti/widget/form/TSpinner.php';
        //$classPath['TText']                      = 'lib/adianti/widget/form/TText.php';
        //$classPath['TFieldList']                 = 'lib/adianti/widget/form/TFieldList.php';
        //$classPath['TFormSeparator']             = 'lib/adianti/widget/form/TFormSeparator.php';
        //$classPath['TMenu']                      = 'lib/adianti/widget/menu/TMenu.php';
        //$classPath['TMenuBar']                   = 'lib/adianti/widget/menu/TMenuBar.php';
        //$classPath['TMenuItem']                  = 'lib/adianti/widget/menu/TMenuItem.php';
        //$classPath['TMenuParser']                = 'lib/adianti/widget/menu/TMenuParser.php';
        $classPath['THtmlRenderer']              = 'lib/adianti/widget/template/THtmlRenderer.php';
        //$classPath['TBreadCrumb']                = 'lib/adianti/widget/util/TBreadCrumb.php';
        //$classPath['TProgressBar']               = 'lib/adianti/widget/util/TProgressBar.php';
        //$classPath['TCalendar']                  = 'lib/adianti/widget/util/TCalendar.php';
        //$classPath['TFullCalendar']              = 'lib/adianti/widget/util/TFullCalendar.php';
        //$classPath['TDropDown']                  = 'lib/adianti/widget/util/TDropDown.php';
        //$classPath['TExceptionView']             = 'lib/adianti/widget/util/TExceptionView.php';
        //$classPath['TImage']                     = 'lib/adianti/widget/util/TImage.php';
        //$classPath['TSourceCode']                = 'lib/adianti/widget/util/TSourceCode.php';
        //$classPath['TTreeView']                  = 'lib/adianti/widget/util/TTreeView.php';
        //$classPath['TXMLBreadCrumb']             = 'lib/adianti/widget/util/TXMLBreadCrumb.php';
        //$classPath['TTextDisplay']               = 'lib/adianti/widget/util/TTextDisplay.php';
        //$classPath['TActionLink']                = 'lib/adianti/widget/util/TActionLink.php';
        $classPath['THyperLink']                 = 'lib/adianti/widget/util/THyperLink.php';
        //$classPath['TDBCheckGroup']              = 'lib/adianti/widget/wrapper/TDBCheckGroup.php';
        //$classPath['TDBCombo']                   = 'lib/adianti/widget/wrapper/TDBCombo.php';
        //$classPath['TDBEntry']                   = 'lib/adianti/widget/wrapper/TDBEntry.php';
        //$classPath['TDBMultiSearch']             = 'lib/adianti/widget/wrapper/TDBMultiSearch.php';
        //$classPath['TDBRadioGroup']              = 'lib/adianti/widget/wrapper/TDBRadioGroup.php';
        //$classPath['TDBSeekButton']              = 'lib/adianti/widget/wrapper/TDBSeekButton.php';
        //$classPath['TDBSelect']                  = 'lib/adianti/widget/wrapper/TDBSelect.php';
        //$classPath['TDBSortList']                = 'lib/adianti/widget/wrapper/TDBSortList.php';
        //$classPath['TDBUniqueSearch']            = 'lib/adianti/widget/wrapper/TDBUniqueSearch.php';
        //$classPath['TQuickForm']                 = 'lib/adianti/widget/wrapper/TQuickForm.php';
        //$classPath['TQuickGrid']                 = 'lib/adianti/widget/wrapper/TQuickGrid.php';
        //$classPath['TQuickNotebookForm']         = 'lib/adianti/widget/wrapper/TQuickNotebookForm.php';
        //$classPath['AdiantiPDFDesigner']         = 'lib/adianti/wrapper/AdiantiPDFDesigner.php';
        //$classPath['AdiantiUIBuilder']           = 'lib/adianti/wrapper/AdiantiUIBuilder.php';
        //$classPath['BootstrapNotebookWrapper']   = 'lib/adianti/wrapper/BootstrapNotebookWrapper.php';
        //$classPath['BootstrapDatagridWrapper']   = 'lib/adianti/wrapper/BootstrapDatagridWrapper.php';
        //$classPath['BootstrapFormWrapper']       = 'lib/adianti/wrapper/BootstrapFormWrapper.php';
        //$classPath['BootstrapFormBuilder']       = 'lib/adianti/wrapper/BootstrapFormBuilder.php';
               
        return $classPath;
    }
    
    /**
     * Return classes allowed to be directly executed
     *
    public static function getAllowedClasses()
    {
        return array('AdiantiAutocompleteService', 'AdiantiMultiSearchService', 'AdiantiUploaderService', 'TStandardSeek');
    }
    
    /**
     * Return internal classes
     *
    public static function getInternalClasses()
    {
        return array_diff( array_keys(self::getMap()), self::getAllowedClasses() );
    }
    */
    
}
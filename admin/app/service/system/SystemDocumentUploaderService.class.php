<?php
/**
 * Document uploader listener
 *
 * @version    3.0
 * @package    service
 * @author     Nataniel Rabaioli
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006-2014 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemDocumentUploaderService
{
    function show()
    {
        $content_type_list = array();
        $content_type_list['txt']  = 'text/plain';
        $content_type_list['html'] = 'text/html';
        $content_type_list['pdf']  = 'application/pdf';
        $content_type_list['zip']  = 'application/zip';
        $content_type_list['rtf']  = 'application/rtf';
        $content_type_list['csv']  = 'application/csv';
        $content_type_list['doc']  = 'application/msword';
        $content_type_list['docx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        $content_type_list['xls']  = 'application/vnd.ms-excel';
        $content_type_list['xlsx'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $content_type_list['ppt']  = 'application/vnd.ms-powerpoint';
        $content_type_list['pptx'] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
        $content_type_list['odt']  = 'application/vnd.oasis.opendocument.text';
        $content_type_list['ods']  = 'application/vnd.oasis.opendocument.spreadsheet';
        
        $block_extensions = ['php', 'php3', 'php4', 'phtml', 'pl', 'py', 'jsp', 'asp', 'htm', 'shtml', 'sh', 'cgi', 'htaccess'];
        
        $folder = 'tmp/';
        $response = array();
        if (isset($_FILES['fileName']))
        {
            $file = $_FILES['fileName'];
            if( $file['error'] === 0 && $file['size'] > 0 )
            {
                $path = $folder.$file['name'];
                
                // check blocked file extension, not using finfo because file.php.2 problem
                foreach ($block_extensions as $block_extension)
                {
                    if (strpos(strtolower($file['name']), ".{$block_extension}") !== false)
                    {
                        $response = array();
                        $response['type'] = 'error';
                        $response['msg']  = AdiantiCoreTranslator::translate('Extension not allowed');
                        echo json_encode($response);
                        return;
                    }
                }
                
                // check file extension
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                if (!in_array($finfo->file($file['tmp_name']), array_values($content_type_list)))
                {
                    $response = array();
                    $response['type'] = 'error';
                    $response['msg'] = AdiantiCoreTranslator::translate('Extension not allowed');
                    echo json_encode($response);
                    return;
                }
                
                if (is_writable($folder) )
                {
                    if( move_uploaded_file( $file['tmp_name'], $path ) )
                    {
                        $response['type'] = 'success';
                        $response['fileName'] = $file['name'];
                    }
                    else
                    {
                        $response['type'] = 'error';
                        $response['msg'] = '';
                    }
                }
                else
                {
                    $response['type'] = 'error';
                    $response['msg'] = "Permission denied: {$path}";
                }
                echo json_encode($response);
            }
        }
    }
}

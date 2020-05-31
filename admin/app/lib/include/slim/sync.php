<?php

require_once('slim.php');

// Get posted data
$images = Slim::getImages();

// No image found under the supplied input name
if ($images == false) {

    // inject your own auto crop or fallback script here
    echo '<p>Slim was not used to upload these images.</p>';

}
else {

    // Could be multiple slim croppers
    foreach ($images as $image) {

        $files = array();

        // save output data if set
        if (isset($image['output']['data'])) {

            // Save the file
            $name = $image['output']['name'];

            // We'll use the output crop data
            $data = $image['output']['data'];

            // If you want to store the file in another directory pass the directory name as the third parameter.
            // $file = Slim::saveFile($data, $name, 'my-directory/');

            // If you want to prevent Slim from adding a unique id to the file name add false as the fourth parameter.
            // $file = Slim::saveFile($data, $name, 'tmp/', false);
            $output = Slim::saveFile($data, $name);

            array_push($files, $output);
        }

        // save input data if set
        if (isset ($image['input']['data'])) {

            // Save the file
            $name = $image['input']['name'];

            // We'll use the output crop data
            $data = $image['input']['data'];

            // If you want to store the file in another directory pass the directory name as the third parameter.
            // $file = Slim::saveFile($data, $name, 'my-directory/');

            // If you want to prevent Slim from adding a unique id to the file name add false as the fourth parameter.
            // $file = Slim::saveFile($data, $name, 'tmp/', false);
            $input = Slim::saveFile($data, $name);

            array_push($files, $input);
        }

        $filenames = join(', ', array_map(function($file){ return ellipsis($file['name'], 100); }, $files));
        $images = array_map(function($file) { return '<img src="' . $file['path'] . '" alt=""/>'; }, $files);

    echo '
    <h1>You uploaded "' . $filenames . '"</h1>
    
    ' . join('<br>', $images) . '
    
    <div class="table-wrapper">   
    <table>
        <thead>
            <tr>
                <th>Property</th>
                <th>Value</th>
            </tr>
        </thead>
        ';
        if (isset($image['input']['data'])) {
            echo '
            <tbody>
                <tr>
                    <th colspan="2">Input</th>
                </tr>
                <tr>
                    <th>Name</th>
                    <td>' . ellipsis($image['input']['name'],100) . '</td>
                </tr>
                <tr>
                    <th>Type</th>
                    <td>' . $image['input']['type'] . '</td>
                </tr>
                <tr>
                    <th>Size</th>
                    <td>' . $image['input']['size'] . '</td>
                </tr>
                <tr>
                    <th>Width</th>
                    <td>' . $image['input']['width'] . '</td>
                </tr>
                <tr>
                    <th>Height</th>
                    <td>' . $image['input']['height'] . '</td>
                </tr>
                    <tr>
                        <th>Data</th>
                        '
                        .
                        (isset($image['input']['data']) ?
                            '<td class="bytes">' . ellipsis(base64_encode($image['output']['data']), 50) . '</td>' :
                            '<td>Input data was not sent, add the "input" value to the <code>data-post</code> property to send it along.</td>')
                        .
                        '
                    </tr>
            </tbody>
            ';
        }
        if (isset($image['output']['data'])) {
            echo '
                <tbody>
                    <tr>
                        <th colspan="2">Output</th>            
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>' . $image['output']['name'] . '</td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <td>' . $image['output']['type'] . '</td>
                    </tr>
                    <tr>
                        <th>Width</th>
                        <td>' . $image['output']['width'] . '</td>
                    </tr>
                    <tr>
                        <th>Height</th>
                        <td>' . $image['output']['height'] . '</td>
                    </tr>
                    <tr>
                        <th>Data</th>
                        '
                        .
                        (isset($image['output']['data']) ?
                            '<td class="bytes">' . ellipsis(base64_encode($image['output']['data']), 50) . '</td>' :
                            '<td>Output data was not sent, add the "output" value to the <code>data-post</code> property to send it along.</td>')
                        .
                        '
                    </tr>
                </tbody>
            ';
        }
        if (isset($image['actions'])) {
            echo '
                <tbody>
                <tr>
                    <th colspan="2">Crop</th>
                </tr>
                <tr>
                    <th>Type</th>
                    <td>' . $image['actions']['crop']['type'] . '</td>
                </tr>
                <tr>
                    <th>X</th>
                    <td>' . $image['actions']['crop']['x'] . '</td>
                </tr>
                <tr>
                    <th>Y</th>
                    <td>' . $image['actions']['crop']['y'] . '</td>
                </tr>
                <tr>
                    <th>Width</th>
                    <td>' . $image['actions']['crop']['width'] . '</td>
                </tr>
                <tr>
                    <th>Height</th>
                    <td>' . $image['actions']['crop']['height'] . '</td>
                </tr>
            </tbody>
            <tbody>
                <tr>
                    <th colspan="2">Size</th>            
                </tr>
                <tr>
                    <th>Width</th>
                    <td>' . $image['actions']['size']['width'] . '</td>
                </tr>
                <tr>
                    <th>Height</th>
                    <td>' . $image['actions']['size']['height'] . '</td>
                </tr>
            </tbody>
            ';
        }
        echo '
        <tbody>
            <tr>
                <th colspan="2">Meta</th>            
            </tr>
            <tr>
                <th>Data</th>
                <td>' . ($image['meta'] ? json_encode($image['meta']) : 'No meta data received') . '</td>
            </tr>
        </tbody>
    </table>
    </div>';

    }
}

function ellipsis($str, $len = 50) {
    return strlen($str) > $len ? substr($str, 0, $len) . '...' : $str;
}
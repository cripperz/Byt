<?php

require_once "mysql.php";
require_once "tools.php";
require_once "upload_tools.php";

if (isset($_GET['f']) && !empty($_GET['f']))
{
    $params = explode("-", $_GET['f']);
    $hash = $params[0];

    try {
        $result = get_uploaded_file_info($hash);
    } catch (Exception $e) {
        header('HTTP/1.1 500 Internal Server Error', true, 500);
        die(json_encode(array('error' => $e->getMessage())));
    }

    $file = UPLOAD_DIRECTORY.$result['path'];

    if (!file_exists($file)) {
        header('HTTP/1.0 404 Not Found');
        echo "<h1>404 Not Found</h1>";
        exit();
    } else {

        if (isset($params[1]) && !empty($params[1])) {
            $password = $params[1];

            try {
                $file = decrypt_file($file, $password);
                $file_to_delete = $file;
            } catch (Exception $e) {
                header('HTTP/1.1 500 Internal Server Error', true, 500);
                die(json_encode(array('error' => $e->getMessage())));
            }
        }

        header_remove();
        header('Content-type: '.get_mime_type($file));
        header('Content-disposition: inline;filename="'.$result['filename'].'"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.filesize($file));
        header('Accept-Ranges: bytes');
        @readfile($file);

        if ($result['dl_count_left'] > 0)
            consume_file_download_count($hash);
        if (isset($file_to_delete))
            unlink($file_to_delete);
        die;
    }
}
else
{
    header('HTTP/1.0 404 Not Found');
    echo "<h1>404 Not Found</h1>";
    exit();
}

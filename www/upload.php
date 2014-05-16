<?php

require_once "upload_tools.php";
require_once "shorter_tools.php";

if (isset($_FILES['file'])) {
    if ($_FILES['file']['error'] > 0) {

        error_log("Error: ".$_FILES['file']['error']);
        echo '{"error":"An error append =/"}';
    } else {

        $directory = random_upload_dir();
        if (mkdir($directory)) {
            try {
                $password = rand_sha1(8);
                $target_file = encrypt_file($directory, $_FILES['file']['tmp_name'], $password);
                $hash = register_uploaded_file($target_file, $_FILES['file']['name']);
            } catch (Exception $e) {
                header('HTTP/1.1 500 Internal Server Error', true, 500);
                die(json_encode(array('error' => $e->getMessage())));
            }

            $link = make_short(HOST_URL."file.php?f=".$hash);
            header('Content-type: application/json');
            die('{"link":"'.$link.'", "key":"'.$password.'", "filename":"'.$_FILES['file']['name'].'"}');
        }
        else
        {
            error_log("Error: fail to create remote dir: ".$directory);

            header('HTTP/1.1 500 Internal Server Error', true, 500);
            die(json_encode(array('error' => 'An error append =/')));
        }
    }
}
else
{
    header('HTTP/1.1 400 Bad Request', true, 400);
    die(json_encode(array('error' => 'No file supplied')));
}

?>

<?php

require "mysql.php";
require "tools.php";

if (isset($_GET['f']) && !empty($_GET['f']))
{
    $file = $_GET['f'];

    $db = open_database();

    $req = $db->prepare("SELECT path, filename FROM files WHERE hash = :hash");
    $req->execute(array('hash' => $file));
    $result = $req->fetch();

    $file = $result['path'];

    if (!file_exists($file)) {
        header('HTTP/1.0 404 Not Found');
        echo "<h1>404 Not Found</h1>";
        exit();
    } else {
        header_remove();
        header('Content-type: '.get_mime_type($file));
        header('Content-disposition: inline;filename="'.$result['filename'].'"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.filesize($file));
        header('Accept-Ranges: bytes');
        @readfile($file);
        die;
    }
}
else
{
    header('HTTP/1.0 404 Not Found');
    echo "<h1>404 Not Found</h1>";
    exit();
}

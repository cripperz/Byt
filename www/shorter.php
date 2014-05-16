<?php

require_once "shorter_tools.php";
require_once "tools.php";

if (isset($_GET['url']) && !empty($_GET['url'])) {
    
    $url = $_GET['url'];
    
    if (!isValidUrl($url)) {
        header('HTTP/1.1 500 Internal Server Error', true, 500);
        die(json_encode(array('error' => 'Invalid url')));
    } else {
        die(json_encode(array('url' => make_short($url))));
    }
}
else if (isset($_GET['hash']) && !empty($_GET['hash'])) {
    $params = explode("-", $_GET['hash']);
    $hash = $params[0];

    if (!is_sha1($hash)) {
        header('HTTP/1.1 500 Internal Server Error', true, 500);
        die(json_encode(array('error' => 'Invalid link')));
    } else {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: '.get_url($hash).(isset($params[1]) && !empty($params[1]) ? "-".$params[1] : ""));
    }
}
else
{
    header('HTTP/1.1 400 Bad Request', true, 400);
    die(json_encode(array('error' => 'No link supplied')));
}

?>

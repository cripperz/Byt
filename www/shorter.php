<?php

require "url_shorter.php";
require "tools.php";

if (isset($_GET['url']) && !empty($_GET['url'])) {
    
    $url = $_GET['url'];
    
    if (!isValidUrl($url)) {
        echo "Invalid url";
    } else {
        echo make_short($url);
    }
}
else if (isset($_GET['hash']) && !empty($_GET['hash'])) {
    $hash = $_GET['hash'];

    if (!is_sha1($hash)) {
        echo "Invalid link";
    } else {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: '.get_url($hash));
    }
}
else
{
    echo "<form method='get' action='shorter.php'>";
    echo "<input type='text' name='url' />";
    echo "<input type='submit' value='Reduce !' />";
    echo "</form>";
}

?>

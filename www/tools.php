<?php

function isValidURL($url) { return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url); }
function is_sha1($str) { return preg_match('/^[0-9a-f]+$/i', $str); }

function get_mime_type($file)
{
    $finfo = finfo_open(FILEINFO_MIME);
    $mime = finfo_open($finfo, $file);
    finfo_close($finfo);
    return $mime;
}

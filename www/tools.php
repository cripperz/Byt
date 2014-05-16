<?php

function isValidURL($url) { return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url); }
function is_sha1($str) { return preg_match('/^[0-9a-f]+$/i', $str); }

function get_mime_type($file)
{
    $finfo = finfo_open(FILEINFO_MIME);
    $mime = finfo_file($finfo, $file);
    finfo_close($finfo);
    return $mime;
}

function rand_sha1($length) {
  $max = ceil($length / 40);
  $random = '';
  for ($i = 0; $i < $max; $i ++) {
    $random .= sha1(microtime(true).mt_rand(10000,90000));
  }
  return substr($random, 0, $length);
}

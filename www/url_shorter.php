<?php

require "mysql.php";

function rand_sha1($length) {
  $max = ceil($length / 40);
  $random = '';
  for ($i = 0; $i < $max; $i ++) {
    $random .= sha1(microtime(true).mt_rand(10000,90000));
  }
  return substr($random, 0, $length);
}

function make_short($url)
{
    $domaine='http://byt.tl/';
    $hash_length=8;

    $db = open_database();

    $sha = rand_sha1($hash_length);
    $req = $db->prepare("INSERT INTO urls (`hash`, `url`) VALUES (:hash, :url)");
    $req->execute(array('hash' => $sha, 'url' => $url));

    return $domaine.$sha;
}

function get_url($hash)
{
    $db = open_database();

    $req = $db->prepare("SELECT url FROM urls WHERE hash = :hash");
    $req->execute(array('hash' => $hash));
    $result = $req->fetch();
    return $result['url'];
}



<?php

require_once "config.php";
require_once "mysql.php";

function random_upload_dir()
{
    $name = "";
    do {
        $name = md5(uniqid(rand(), true));
    } while (file_exists(UPLOAD_DIRECTORY.$name));

    return $name;
}

function encrypt_file($upload_dir, $source, $password)
{
    $target_file = $upload_dir."/".rand_sha1(8).".aes";
    $command = "/usr/bin/openssl enc -a -aes-256-cbc -in ".$source." -out ".UPLOAD_DIRECTORY.$target_file." -k ".$password;
    system($command, $retval);
    if ($retval != 0)
        throw new Exception("Fail to encrypt file.");
    return $target_file;
}

function decrypt_file($file, $password)
{
    if (!is_dir(DECRYPT_TMP_DIRECTORY))
        mkdir(DECRYPT_TMP_DIRECTORY);

    $tmp_location = DECRYPT_TMP_DIRECTORY.rand_sha1(20);

    $command = "/usr/bin/openssl enc -d -a -aes-256-cbc -in ".$file." -out ".$tmp_location." -k ".$password;
    system($command, $retval);
    if ($retval != 0)
        throw new Exception("Fail to decrypt file.");
    return $tmp_location;
}

function register_uploaded_file($path, $filename, $dlCountLeft=-1, $expire_date=-1)
{
    $db = open_database();

    try {
        $hash = rand_sha1(20);
        $req = $db->prepare("INSERT INTO files (path, filename, hash, dl_count_left, expire_date) VALUES (:path, :filename, :hash, :dlcount, :expire)");
        $req->execute(array('path' => $path, 'filename' => $filename, 'hash' => $hash, 'dlcount' => $dlCountLeft, 'expire' => $expire_date));
    } catch (Exception $e) {
        error_log("Error: (".$e->getCode()."): ".$e->getMessage());
        throw new Exception("Fail to save file.");
    }
    return $hash;
}

function get_uploaded_file_info($hash)
{
    $db = open_database();

    try {
        $req = $db->prepare("SELECT path, filename, dl_count_left FROM files WHERE hash = :hash AND expire_date < UNIX_TIMESTAMP() AND dl_count_left != 0");
        $req->execute(array('hash' => $hash));
        $result = $req->fetch();
    } catch (Exception $e) {
        error_log("Error: (".$e->getCode()."): ".$e->getMessage());
        throw new Exception("Fail to get file.");
    }

    if ($req->rowCount() == 0)
        throw new Exception("File not found");

    return array('path' => $result['path'], 'filename' => $result['filename'], 'dl_count_left' => $result['dl_count_left']);
}

function consume_file_download_count($hash)
{
    $db = open_database();
    try {
        $req = $db->prepare("UPDATE files SET dl_count_left = dl_count_left - 1 WHERE hash = :hash AND dl_count_left > 0");
        $req->execute(array('hash' => $hash));
    } catch (Exception $e) {
        error_log("Error: (".$e->getCode()."): ".$e->getMessage());
        throw new Exception("Fail to update download left count.");
    }
}

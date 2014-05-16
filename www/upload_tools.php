<?php

require_once "config.php";
require_once "mysql.php";

function random_upload_dir()
{
    $name = "";
    do {
        $name = UPLOAD_DIRECTORY.md5(uniqid(rand(), true));
    } while (file_exists($name));

    return $name;
}

function encrypt_file($upload_dir, $source, $password)
{
    $target_file = $upload_dir."/".rand_sha1(8).".aes";
    $command = "/usr/bin/openssl enc -a -aes-256-cbc -in ".$source." -out ".$target_file." -k ".$password;
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

function register_uploaded_file($path, $filename)
{
    $db = open_database();

    try {
        $hash = rand_sha1(20);
        $req = $db->prepare("INSERT INTO files (path, filename, hash) VALUES (:path, :filename, :hash)");
        $req->execute(array('path' => $path, 'filename' => $filename, 'hash' => $hash));
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
        $req = $db->prepare("SELECT path, filename FROM files WHERE hash = :hash");
        $req->execute(array('hash' => $hash));
        $result = $req->fetch();
    } catch (Exception $e) {
    error_log("Error: (".$e->getCode()."): ".$e->getMessage());
    throw new Exception("Fail to get file.");
}

    if ($req->rowCount() == 0)
        throw new Exception("Invalid hash");

    return array('path' => $result['path'], 'filename' => $result['filename']);
}

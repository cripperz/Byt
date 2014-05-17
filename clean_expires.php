<?php

define("MYSQL_HOST", "localhost");
define("MYSQL_PORT", 3306);
define("MYSQL_DB_NAME", "byttl");
define("MYSQL_USERNAME", "");
define("MYSQL_PASSWORD", "");

define("UPLOAD_DIRECTORY", "/home/bytlt/files/");
define("LOG_FILE", "./clean_expires.log");

function open_database() {

    try {
        $db = new PDO('mysql:host='.MYSQL_HOST.';port='.MYSQL_PORT.';dbname='.MYSQL_DB_NAME, MYSQL_USERNAME, MYSQL_PASSWORD);
    } catch (Exception $e) {
        error_log("Error: (".$e->getCode()."): ".$e->getMessage()."\n", 3, LOG_FILE);
    }
    return $db;
}

$db = open_database();

$req = $db->prepare("SELECT id, path FROM files WHERE dl_count_left = 0 OR (expire_date < UNIX_TIMESTAMP() AND expire_date != -1)");
$req->execute();

$count = 0;
while ($row = $req->fetch(PDO::FETCH_ASSOC)) {

    if (!file_exists(UPLOAD_DIRECTORY.$row['path']))
        continue;

    unlink(UPLOAD_DIRECTORY.$row['path']);
    rmdir(UPLOAD_DIRECTORY.dirname($row['path']));

    $req2 = $db->prepare("DELETE FROM files WHERE id = :id");
    $req2->execute(array('id' => $row['id']));

    ++$count;
}

if ($count > 0)
    error_log(date('Y-m-d H:i:s').": Cleanup ".$count." files.\n", 3, LOG_FILE);

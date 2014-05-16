<?php

require_once "config.php";

function open_database() {

    try {
        $db = new PDO('mysql:host='.MYSQL_HOST.';port='.MYSQL_PORT.';dbname='.MYSQL_DB_NAME, MYSQL_USERNAME, MYSQL_PASSWORD);
    } catch (Exception $e) {
        error_log("Error: (".$e->getCode()."): ".$e->getMessage());

        header('HTTP/1.1 500 Internal Server Error', true, 500);
        die(json_encode(array('error' => 'An error append =/')));
    }
    return $db;
}

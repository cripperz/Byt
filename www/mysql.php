<?php
function open_database() {

    $host='localhost';
    $port='3306';
    $db_name='db_name';
    $user='user_name';
    $pass='password';

    try {
        $db = new PDO('mysql:host='.$host.';port='.$port.';dbname='.$db_name, $user, $pass);
    } catch (Exception $e) {
        error_log("Error: (".$e->getCode()."): ".$e->getMessage());
        die("An error append =/");
    }
    return $db;
}

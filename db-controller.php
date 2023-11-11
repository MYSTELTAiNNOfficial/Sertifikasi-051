<?php
    $host = "localhost";
    $user = "root";
    $pwd  = "";
    $db   = "sertifikasi_051";
    
    $conn = new mysqli($host, $user, $pwd, $db) or die("Error connect to database");
    return $conn;

    if(!$conn){
        die("Connection Failed: ".$conn->connect_error);
    }

?>
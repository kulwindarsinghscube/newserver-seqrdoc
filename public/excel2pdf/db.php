<?php
    //DB details
    /*$dbHost = 'localhost';
    $dbUsername = 'pdf_2_u';
    $dbPassword = 'La7gt13%';
    $dbName = 'pdf_to_pdf';*/ 

    require('constants.php');

    $dbHost = servername;
    $dbUsername = username;
    $dbPassword =password;
    $dbName = dbName;

    //Create connection and select DB
    $db = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);
    if($db->connect_error){
        die("Unable to connect database: " . $db->connect_error);
    }
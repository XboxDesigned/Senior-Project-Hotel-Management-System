<?php

$dsn = 'mysql:host=localhost;dbname=hotel_management'; 
$username = 'root';
$password = '';

try {
    $db = new PDO($dsn, $username, $password); //creates PDO
}
catch (PDOException $e){
    $error_message = $e->getMessage();
    echo '<p> Connection failed due to error : $error_message </p>';
}
?>
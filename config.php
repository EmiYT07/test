<?php

$servername = "sql307.infinityfree.com";

$username = "if0_37048662";

$password = "cspyuQWplu39YHt";

$dbname = "if0_37048662_emiytcl";



$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset('utf8mb4'); // Establece la codificación de caracteres



if ($conn->connect_error) {

    die("Connection failed: " . $conn->connect_error);

}

?>


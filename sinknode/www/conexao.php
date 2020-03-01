<?php
$servername = "localhost";
$username = "usuario";
$password = "ifsuldeminas";
$dbname = "esp32";
$conn = new mysqli($servername, $username, $password, $dbname);
if(mysqli_connect_error()){
    die("Erro na conexão com o banco de dados.");
}



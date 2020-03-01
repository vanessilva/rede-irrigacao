<?php
include "./conexao.php";
header('Content-Type: text/plain');



if (@$_POST['x'] == 'tudo') {
    $sql = "DELETE FROM sensores WHERE node_ip = ?";
    if ($query = $conn->prepare($sql)) {
        # Troca os valores da consulta .
        $query->bind_param('s', $_POST['node_ip']);
        $query->execute();

    } else {
        $error = $conn->errno . ' ' . $conn->error;
        echo "1 ==== $error"; 
    }
}


$sql = "DELETE FROM no_sensores WHERE id = ?";
# Prepara a consulta:
if ($query = $conn->prepare($sql)) {
    # Troca os valores da consulta:
    $query->bind_param('i', $_POST['id']);
    # Executa a consulta ao banco:
    $query->execute();
} else {
    $error = $conn->errno . ' ' . $conn->error;
    echo "2 ==== $error"; 
}




header('Location: ./');


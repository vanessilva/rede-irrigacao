<?php

include("conexao.php");
header('Content-Type: text/plain');

$sql = "UPDATE no_sensores SET nome_planta=?, node_ip=?, minimo=?, maximo=? WHERE id = ?";
# Prepara a consulta:
if ($query = $conn->prepare($sql)) {
    # Troca os valores da consulta .
    $query->bind_param('ssddi', $_POST['nome_planta'], $_POST['node_ip'], $_POST['minimo'], $_POST['maximo'], $_POST['id']);
    # Executa a consulta ao banco
    $query->execute();
} else {
    $error = $conn->errno . ' ' . $conn->error;
    echo $error; 
}


header('Location: ./');


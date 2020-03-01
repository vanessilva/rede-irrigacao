<?php

include("conexao.php");
header('Content-Type: text/plain');

$sql = "INSERT INTO no_sensores (nome_planta, node_ip, minimo, maximo) VALUES (?,?,?,?)";
# Prepara a consulta:
if ($query = $conn->prepare($sql)) {
    # Troca os valores da consulta:
    $query->bind_param('ssdd', $_POST['nome_planta'], $_POST['node_ip'], $_POST['minimo'], $_POST['maximo']);
    # Executa a consulta ao banco:
    $query->execute();
} else {
    $error = $conn->errno . ' ' . $conn->error;
    echo $error;
}

header('Location: ./');

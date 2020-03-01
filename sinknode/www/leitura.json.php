<?php
include "./conexao.php";

header('Content-Type: application/json');


// Essa consulta retorna o últimos valores de cada nó
$sql = "SELECT nome_planta, minimo, maximo, n.id, s1.higrometro, s1.umidade, s1.temperatura, s1.data_hora, n.node_ip, s1.node_ip
            FROM no_sensores n 
            JOIN sensores s1 ON s1.node_ip = n.node_ip 
            LEFT OUTER JOIN sensores s2 ON s1.node_ip = n.node_ip and  s1.node_ip = s2.node_ip and (s1.data_hora < s2.data_hora)
            WHERE s2.id IS NULL";
$query = $conn->query($sql);
$basico = $query->fetch_all(MYSQLI_ASSOC);




echo json_encode(array(
    'basico' => $basico
    )
);


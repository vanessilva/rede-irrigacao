<?php
include "./conexao.php";
header('Content-Type: application/json');
$id = (int)@$_GET['id'];
$sql_id = $id > 0 ? " AND n1.id = $id" : '';
$sql = "SELECT n1.id, nome_planta, 
            data_hora,  
            higrometro, umidade, temperatura FROM sensores s1 
            INNER JOIN no_sensores n1 ON n1.node_ip = s1.node_ip
            WHERE data_hora > DATE_SUB(CURDATE(), INTERVAL 1 DAY)
            $sql_id
            ORDER BY s1.data_hora asc
            LIMIT 30";
$query = $conn->query($sql);
$grafico_hoje = $query->fetch_all(MYSQLI_ASSOC);
$sql = "SELECT nome_planta, 
            date(data_hora) AS data_hora, 
            avg(higrometro) as higrometro, 
            avg(umidade) as umidade, 
            avg(temperatura) as temperatura
            FROM sensores s1 
            INNER JOIN no_sensores n1 ON n1.node_ip = s1.node_ip
            WHERE data_hora > DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            $sql_id
            GROUP BY date(data_hora)
            ORDER BY s1.data_hora asc";
$query = $conn->query($sql);
$grafico_semana = $query->fetch_all(MYSQLI_ASSOC);
$sql = "SELECT nome_planta, 
            date(data_hora) AS data_hora, 
            avg(higrometro) as higrometro, 
            avg(umidade) as umidade, 
            avg(temperatura) as temperatura
            FROM sensores s1 
            INNER JOIN no_sensores n1 ON n1.node_ip = s1.node_ip
            WHERE data_hora > DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            $sql_id
            GROUP BY month(data_hora)
            ORDER BY s1.data_hora asc";
$query = $conn->query($sql);
$grafico_mes = $query->fetch_all(MYSQLI_ASSOC);
echo json_encode(
    array(
        'hoje' => $grafico_hoje,
        'semana' => $grafico_semana,
        'mes' => $grafico_mes,
        'get' => $_GET
    ),
    JSON_PRETTY_PRINT
);

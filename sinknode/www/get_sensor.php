<?php

// Inclui o arquivo de configuração do banco:
include("conexao.php");

//==== Inicio do tratamento dos dados enviados ==== #

// Valores vazios não devem ser aceitos.
// Se for detectado algum valor vazio:
if (!isset($_GET["h"]) || !isset($_GET["u"]) || !isset($_GET["t"])) {
   // Dados incompletos
   header('Content-Type: text/plain');
   echo ("Erro: dados incompletos");   

} else { // Dados completos, continuar...

   // Definindo nas variáveis:
   $h = @$_GET["h"]; // higrômetro
   $u = @$_GET["u"]; // umidade do ar
   $t = @$_GET["t"]; // temperatura
   $erro = 0;
   // Caso tenha algum valor NaN, registrar isso na variável $erro:
   if ($h == 'nan' || $u == 'nan' || $t == 'nan'){
        $erro = 1;
   }
   
   // Detectando o IP do nó:
   $http_client_ip = @$_SERVER['HTTP_CLIENT_IP'];
   $http_x_forwarded_for = @$_SERVER['HTTP_X_FORWARDED_FOR'];
   $remote_addr = @$_SERVER['REMOTE_ADDR'];
   if (!empty($http_client_ip)) {
      $ip = $http_client_ip;
   } else if (!empty($http_x_forwarded_for)) {
      $ip = $http_x_forwarded_for;
   } else {
      $ip = $remote_addr;
   }

   // Insere os dados na tabela sensores:
   $sql = "INSERT INTO 
            sensores (higrometro, umidade, temperatura, node_ip, erro) 
            VALUES (?, ?, ?, ?, ?)";
   // Preparação da consulta evitando SQL injection:
   $query = $conn->prepare($sql);
   // Troca dos valores '?' da consulta:
   $query->bind_param('iiisi', $h, $u, $t, $ip, $erro);
   // Executa a consulta ao banco:
   $query->execute();


   // Consulta SQL que seleciona o registro do nó
   // baseando-se no endereço IP:
   $sql = "SELECT minimo, maximo 
             FROM no_sensores 
             WHERE node_ip=? LIMIT 1";
   // Prepara a consulta:
   $query = $conn->prepare($sql);
   // Troca os valores '?' da consulta:
   $query->bind_param('s', $ip);
   // Executa a consulta ao banco:
   $query->execute();
   // Aplica nas variáveis $min e $max:
   $query->bind_result($min, $max);
   $query->fetch();


   header('Content-Type: text/plain');
   echo ("|$min|$max|"); // os dados de mínimo e máximo serão enviados ao nó
   
}

// ==== Fim do tratamento dos dados enviados ==== #


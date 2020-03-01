<?php

// Inclui o arquivo de configuração do banco:
include("conexao.php");

// A função echo_dados_envio define o que será enviado ao nó.
function echo_dados_envio($min, $max)
{
   echo ("|$min|$max|");
}

//==== Inicio do tratamento dos dados enviados ==== #

// Se for detectado algum valor vazio:
if (!isset($_GET["h"]) || !isset($_GET["u"]) || !isset($_GET["t"])) {
   // Dados incompletos
   header('Content-Type: text/plain');
   echo ("Erro: dados incompletos");   

} else { // Dados completos

   // Definindo nas variáveis:
   $h = @$_GET["h"];
   $u = @$_GET["u"];
   $t = @$_GET["t"];
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
   $sql = "INSERT INTO sensores (higrometro, umidade, temperatura, node_ip, erro) VALUES (?, ?, ?, ?, ?)";
   // Prepara a consulta:
   $query = $conn->prepare($sql);
   // Troca os valores '?' da consulta:
   $query->bind_param('iiisi', $h, $u, $t, $ip, $erro);
   // Executa a consulta ao banco:
   $query->execute();


   // Consulta SQL que seleciona o registro do nó baseando-se no endereço IP:
   $sql = "SELECT minimo, maximo FROM no_sensores WHERE node_ip=? LIMIT 1";
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
   echo_dados_envio($min, $max);
   
}

// ==== Fim do tratamento dos dados enviados ==== #


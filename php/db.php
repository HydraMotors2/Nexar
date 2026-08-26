<?php

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'nexar';

$connection = new mysqli($host, $user, $password, $database);

if ($connection->connect_error) {
    die('Erro de conexão: ' . $connection->connect_error);
}

$connection->set_charset('utf8mb4');


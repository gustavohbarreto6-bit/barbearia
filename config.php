<?php
// (clínica: o config.php não muda; o banco continua sendo saep_db)
// Configuração da conexão com o banco de dados MySQL usando MySQLi
$servername = "localhost"; // Nome do servidor do banco (localmente localhost)
$username = "root";        // Usuário do banco de dados
$password = "Senai@118";            // Senha do banco (vazia no XAMPP padrão)
$dbname = "saep_db";       // Nome do banco de dados a ser usado

// Chave usada para criptografar e descriptografar o CPF com AES_ENCRYPT / AES_DECRYPT
// Tem que ser a MESMA chave usada no script saep_db.sql
$chave = "saep2026";

// Cria a conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica se houve erro na conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Opcional: Definição do charset para evitar problemas com acentuação
$conn->set_charset("utf8");

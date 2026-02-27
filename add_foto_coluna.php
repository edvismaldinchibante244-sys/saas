<?php
$host = 'localhost';
$db = 'restaurante_saas';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->exec('ALTER TABLE usuarios ADD COLUMN foto VARCHAR(255) AFTER ativo');
    echo 'Campo foto adicionado com sucesso!';
} catch (PDOException $e) {
    echo 'Erro: ' . $e->getMessage();
}

<?php
// Teste de conexão direta ao banco
$host = "localhost";
$db_name = "restaurante_saas";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✓ Conexão OK!\n\n";

    // Verificar tabelas
    echo "Tabelas existentes:\n";
    $stmt = $conn->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "- " . $row[0] . "\n";
    }

    echo "\n\nUsuários:\n";
    $stmt = $conn->query("SELECT id, nome, email, ativo FROM usuarios");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- ID: " . $row['id'] . " | " . $row['nome'] . " | " . $row['email'] . " | Ativo: " . $row['ativo'] . "\n";
    }
} catch (PDOException $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}

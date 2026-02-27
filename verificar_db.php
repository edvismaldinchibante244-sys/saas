<?php
$host = "localhost";
$db_name = "restaurante_saas";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✓ Conectado!\n\n";

    // Ver tabelas
    echo "Tabelas:\n";
    $stmt = $conn->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "- " . $row[0] . "\n";
    }

    echo "\nUsuários:\n";
    $stmt = $conn->query("SELECT id, nome, email, ativo FROM usuarios");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- ID: " . $row['id'] . " | " . $row['nome'] . " | " . $row['email'] . " | Ativo: " . $row['ativo'] . "\n";
    }

    echo "\nRestaurante:\n";
    $stmt = $conn->query("SELECT id, nome, status, plano, data_fim FROM restaurantes");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- ID: " . $row['id'] . " | " . $row['nome'] . " | Status: " . $row['status'] . " | Plano: " . $row['plano'] . " | Data fim: " . $row['data_fim'] . "\n";
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

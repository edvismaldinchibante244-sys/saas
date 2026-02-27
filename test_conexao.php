<?php

/**
 * Teste de Conexão ao Banco de Dados
 */

echo "<h2>Teste de Conexão</h2>";

$host = "localhost";
$db_name = "restaurante_saas";
$username = "root";
$password = "";

try {
    // Tentar conectar
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>✓ Conexão OK!</p>";

    // Verificar tabelas
    echo "<h3>Tabelas existentes:</h3>";
    $stmt = $conn->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($tabelas) > 0) {
        foreach ($tabelas as $tabela) {
            echo "<p>- $tabela</p>";
        }
    } else {
        echo "<p style='color:red'>Nenhuma tabela encontrada!</p>";
    }

    // Verificar usuários
    echo "<h3>Usuários:</h3>";
    $stmt = $conn->query("SELECT id, nome, email, perfil, ativo FROM usuarios");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($usuarios) > 0) {
        foreach ($usuarios as $u) {
            echo "<p>ID: {$u['id']} | Nome: {$u['nome']} | Email: {$u['email']} | Perfil: {$u['perfil']} | Ativo: {$u['ativo']}</p>";
        }
    } else {
        echo "<p style='color:red'>Nenhum usuário encontrado!</p>";
    }

    // Verificar restaurantes
    echo "<h3>Restaurantes:</h3>";
    $stmt = $conn->query("SELECT id, nome, status FROM restaurantes");
    $restaurantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($restaurantes) > 0) {
        foreach ($restaurantes as $r) {
            echo "<p>ID: {$r['id']} | Nome: {$r['nome']} | Status: {$r['status']}</p>";
        }
    } else {
        echo "<p style='color:red'>Nenhum restaurante encontrado!</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

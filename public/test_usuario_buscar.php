<?php

/**
 * Teste: Buscar Usuário
 */

// Simular sessão de admin
session_start();
$_SESSION['logado'] = true;
$_SESSION['usuario_id'] = 1;
$_SESSION['restaurante_id'] = 1;
$_SESSION['perfil'] = 'ADMIN';

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Erro de conexão!");
}

echo "<h2>Teste: Buscar Usuário ID=1</h2>";

$id = 1;
$query = "SELECT id, nome, email, perfil, ativo FROM usuarios WHERE id = :id AND restaurante_id = :rid LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->bindParam(':rid', $_SESSION['restaurante_id'], PDO::PARAM_INT);
$stmt->execute();

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    echo "<p style='color:green'>✓ Usuário encontrado!</p>";
    echo "<pre>";
    print_r($usuario);
    echo "</pre>";
} else {
    echo "<p style='color:red'>✗ Usuário não encontrado!</p>";

    // Verificar se existe em outro restaurante
    $query2 = "SELECT id, nome, email, perfil, ativo, restaurante_id FROM usuarios WHERE id = :id LIMIT 1";
    $stmt2 = $db->prepare($query2);
    $stmt2->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt2->execute();
    $usuario2 = $stmt2->fetch(PDO::FETCH_ASSOC);

    if ($usuario2) {
        echo "<p style='color:orange'>⚠ Usuário existe mas é de outro restaurante!</p>";
        echo "<pre>";
        print_r($usuario2);
        echo "</pre>";
    }
}

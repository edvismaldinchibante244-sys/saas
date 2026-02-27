<?php
// Teste simples - simular chamada real
session_start();

// Simular que o usuário está logado como admin
$_SESSION['restaurante_id'] = 1;
$_SESSION['usuario_id'] = 1;
$_SESSION['perfil'] = 'ADMIN';
$_SESSION['nome'] = 'Administrador';

include_once '../config/database.php';

header('Content-Type: application/json');

// Testar ID 1
$id = 1;

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(array("success" => false, "message" => "Sem conexão"));
    exit;
}

$query = "SELECT id, nome, email, perfil, ativo FROM usuarios WHERE id = ? AND restaurante_id = ? LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute([$id, $_SESSION['restaurante_id']]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    echo json_encode(array("success" => true, "data" => $usuario));
} else {
    echo json_encode(array("success" => false, "message" => "Não encontrado"));
}

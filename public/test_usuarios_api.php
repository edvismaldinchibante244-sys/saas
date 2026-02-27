<?php
// Teste direto da API de listar usuários

include_once '../config/database.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

// Testar com restaurante_id = 1
$restaurante_id = 1;

$stmt = $db->prepare("
    SELECT id, nome, email, perfil, ativo, foto
    FROM usuarios 
    WHERE restaurante_id = ? 
    ORDER BY nome
");
$stmt->execute([$restaurante_id]);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "data" => $usuarios,
    "count" => count($usuarios)
]);

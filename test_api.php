<?php
// Simular sessão e testar API
session_start();
$_SESSION['restaurante_id'] = 1;
$_SESSION['perfil'] = 'ADMIN';

include_once 'config/database.php';
include_once 'app/Auth.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

echo "Conexão: " . ($db ? "OK" : "FALHOU") . "\n";

if (!$db) {
    echo json_encode(array("success" => false, "message" => "Erro de conexão"));
    exit;
}

// Testar query direta
$query = "SELECT id, nome, email, perfil, ativo FROM usuarios WHERE id = 1 AND restaurante_id = 1 LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    echo "Usuário encontrado: ";
    print_r($usuario);
} else {
    echo "Nenhum usuário encontrado";
}

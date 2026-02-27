<?php
// Simular exactly a chamada da API via browser
session_start();
$_SESSION['restaurante_id'] = 1;
$_SESSION['perfil'] = 'ADMIN';

include_once '../../config/database.php';

header('Content-Type: application/json');

// Simular GET params
$_GET['id'] = 1;

if (!isset($_SESSION['restaurante_id'])) {
    echo json_encode(array("success" => false, "message" => "Não autenticado"));
    exit;
}

if (empty($_GET['id'])) {
    echo json_encode(array("success" => false, "message" => "ID não fornecido"));
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Verificar se a conexão foi estabelecida
if (!$db) {
    echo json_encode(array("success" => false, "message" => "Erro de conexão com o banco de dados"));
    exit;
}

$query = "SELECT id, nome, email, perfil, ativo FROM usuarios WHERE id = :id AND restaurante_id = :rid LIMIT 1";
$stmt  = $db->prepare($query);
$stmt->bindParam(':id',  intval($_GET['id']), PDO::PARAM_INT);
$stmt->bindParam(':rid', $_SESSION['restaurante_id'], PDO::PARAM_INT);
$stmt->execute();

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    echo json_encode(array("success" => true, "data" => $usuario));
} else {
    echo json_encode(array("success" => false, "message" => "Usuário não encontrado"));
}

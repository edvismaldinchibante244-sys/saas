<?php

/**
 * Teste direto da API de busca de usuário
 * Simula a chamada que o JavaScript faz
 */

// Simular sessão de administrador logado
session_start();
$_SESSION['logado'] = true;
$_SESSION['usuario_id'] = 1;
$_SESSION['restaurante_id'] = 1;
$_SESSION['perfil'] = 'ADMIN';
$_SESSION['nome'] = 'Administrador';

// Simular o GET que o JavaScript enviaria
$_GET['id'] = 1;

echo "<h2>Teste: API usuario_buscar.php</h2>";
echo "<p>Sessão simulada:</p>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<hr>";

// Incluir o arquivo da API e capturar a saída
ob_start();
include_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    echo json_encode(array("success" => false, "message" => "Não autenticado"));
    exit;
}

if (!isset($_SESSION['restaurante_id'])) {
    echo json_encode(array("success" => false, "message" => "Restaurante não identificado"));
    exit;
}

if ($_SESSION['perfil'] !== 'ADMIN') {
    echo json_encode(array("success" => false, "message" => "Sem permissão"));
    exit;
}

if (empty($_GET['id'])) {
    echo json_encode(array("success" => false, "message" => "ID não fornecido"));
    exit;
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(array("success" => false, "message" => "Erro de conexão"));
    exit;
}

try {
    $query = "SELECT id, nome, email, perfil, ativo FROM usuarios WHERE id = :id AND restaurante_id = :rid LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', intval($_GET['id']), PDO::PARAM_INT);
    $stmt->bindParam(':rid', $_SESSION['restaurante_id'], PDO::PARAM_INT);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        echo json_encode(array("success" => true, "data" => $usuario));
    } else {
        echo json_encode(array("success" => false, "message" => "Usuário não encontrado"));
    }
} catch (PDOException $e) {
    echo json_encode(array("success" => false, "message" => "Erro: " . $e->getMessage()));
}

<?php

/**
 * API - Buscar Produto
 */

session_start();
include_once '../../config/database.php';
include_once '../../app/Produto.php';

header('Content-Type: application/json');

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

$produto = new Produto($db);
$data = $produto->buscarPorId($_GET['id'], $_SESSION['restaurante_id']);

if ($data) {
    echo json_encode(array("success" => true, "data" => $data));
} else {
    echo json_encode(array("success" => false, "message" => "Produto não encontrado"));
}

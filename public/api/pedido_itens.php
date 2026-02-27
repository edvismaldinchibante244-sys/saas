<?php

/**
 * API - Buscar Itens do Pedido
 */

session_start();
include_once '../../config/database.php';

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

// Verificar se pedido pertence ao restaurante
$query_ped = "SELECT id, observacao FROM pedidos WHERE id = :id AND restaurante_id = :rid LIMIT 1";
$stmt_ped  = $db->prepare($query_ped);
$stmt_ped->bindParam(':id',  intval($_GET['id']), PDO::PARAM_INT);
$stmt_ped->bindParam(':rid', $_SESSION['restaurante_id'], PDO::PARAM_INT);
$stmt_ped->execute();
$pedido = $stmt_ped->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    echo json_encode(array("success" => false, "message" => "Pedido não encontrado"));
    exit;
}

// Buscar itens
$query = "SELECT ip.*, p.nome as produto_nome
          FROM itens_pedido ip
          INNER JOIN produtos p ON ip.produto_id = p.id
          WHERE ip.pedido_id = :pedido_id";
$stmt  = $db->prepare($query);
$stmt->bindParam(':pedido_id', $pedido['id'], PDO::PARAM_INT);
$stmt->execute();
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(array(
    "success"    => true,
    "itens"      => $itens,
    "observacao" => $pedido['observacao']
));



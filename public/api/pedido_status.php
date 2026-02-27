<?php

/**
 * API - Atualizar Status do Pedido
 */

session_start();
include_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['restaurante_id'])) {
        echo json_encode(array("success" => false, "message" => "Não autenticado"));
        exit;
    }

    if (empty($_POST['id']) || empty($_POST['status'])) {
        echo json_encode(array("success" => false, "message" => "Dados incompletos"));
        exit;
    }

    $status_validos = ['CONFIRMADO', 'PREPARANDO', 'PRONTO', 'ENTREGUE', 'CANCELADO'];
    if (!in_array($_POST['status'], $status_validos)) {
        echo json_encode(array("success" => false, "message" => "Status inválido"));
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    $query = "UPDATE pedidos SET status = :status, atualizado_em = NOW()
              WHERE id = :id AND restaurante_id = :rid";
    $stmt  = $db->prepare($query);
    $stmt->bindParam(':status', $_POST['status']);
    $stmt->bindParam(':id',     intval($_POST['id']), PDO::PARAM_INT);
    $stmt->bindParam(':rid',    $_SESSION['restaurante_id'], PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(array("success" => true, "message" => "Pedido atualizado!"));
    } else {
        echo json_encode(array("success" => false, "message" => "Erro ao atualizar pedido"));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Método não permitido"));
}



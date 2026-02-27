<?php

/**
 * API - Deletar Usuário
 */

session_start();
include_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['restaurante_id']) || $_SESSION['perfil'] !== 'ADMIN') {
        echo json_encode(array("success" => false, "message" => "Sem permissão"));
        exit;
    }

    if (empty($_POST['id'])) {
        echo json_encode(array("success" => false, "message" => "ID não fornecido"));
        exit;
    }

    $id = intval($_POST['id']);

    // Não pode deletar a si mesmo
    if ($id === intval($_SESSION['usuario_id'])) {
        echo json_encode(array("success" => false, "message" => "Você não pode deletar sua própria conta"));
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    // Verificar se o usuário pertence ao restaurante
    $query_check = "SELECT id FROM usuarios WHERE id = :id AND restaurante_id = :rid LIMIT 1";
    $stmt_check  = $db->prepare($query_check);
    $stmt_check->bindParam(':id',  $id, PDO::PARAM_INT);
    $stmt_check->bindParam(':rid', $_SESSION['restaurante_id'], PDO::PARAM_INT);
    $stmt_check->execute();

    if ($stmt_check->rowCount() === 0) {
        echo json_encode(array("success" => false, "message" => "Usuário não encontrado"));
        exit;
    }

    $query = "DELETE FROM usuarios WHERE id = :id AND restaurante_id = :rid";
    $stmt  = $db->prepare($query);
    $stmt->bindParam(':id',  $id, PDO::PARAM_INT);
    $stmt->bindParam(':rid', $_SESSION['restaurante_id'], PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(array("success" => true, "message" => "Usuário deletado com sucesso!"));
    } else {
        echo json_encode(array("success" => false, "message" => "Erro ao deletar usuário"));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Método não permitido"));
}



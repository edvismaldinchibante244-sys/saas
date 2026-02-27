<?php

/**
 * API - Atualizar Status da Mesa
 */

session_start();
include_once '../../config/database.php';
include_once '../../app/Mesa.php';

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

    $status_validos = ['LIVRE', 'OCUPADA', 'RESERVADA'];
    if (!in_array($_POST['status'], $status_validos)) {
        echo json_encode(array("success" => false, "message" => "Status inválido"));
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    $mesa = new Mesa($db);

    if ($mesa->atualizarStatus(intval($_POST['id']), $_POST['status'])) {
        $labels = ['LIVRE' => 'liberada', 'OCUPADA' => 'ocupada', 'RESERVADA' => 'reservada'];
        echo json_encode(array(
            "success" => true,
            "message" => "Mesa " . ($labels[$_POST['status']] ?? $_POST['status']) . " com sucesso!"
        ));
    } else {
        echo json_encode(array("success" => false, "message" => "Erro ao atualizar mesa"));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Método não permitido"));
}



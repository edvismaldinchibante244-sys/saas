<?php

/**
 * API - Cadastrar Nova Mesa
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

    // Apenas ADMIN pode cadastrar mesas
    if ($_SESSION['perfil'] !== 'ADMIN') {
        echo json_encode(array("success" => false, "message" => "Sem permissão"));
        exit;
    }

    if (empty($_POST['numero']) || empty($_POST['capacidade'])) {
        echo json_encode(array("success" => false, "message" => "Preencha todos os campos"));
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    $mesa = new Mesa($db);

    // Verificar se número já existe
    $query = "SELECT id FROM mesas WHERE restaurante_id = :rid AND numero = :numero LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':rid', $_SESSION['restaurante_id']);
    $stmt->bindParam(':numero', $_POST['numero']);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(array("success" => false, "message" => "Já existe uma mesa com este número"));
        exit;
    }

    $query2 = "INSERT INTO mesas (restaurante_id, numero, capacidade, status)
               VALUES (:restaurante_id, :numero, :capacidade, 'LIVRE')";
    $stmt2 = $db->prepare($query2);
    $stmt2->bindParam(':restaurante_id', $_SESSION['restaurante_id']);
    $stmt2->bindParam(':numero', $_POST['numero'], PDO::PARAM_INT);
    $stmt2->bindParam(':capacidade', $_POST['capacidade'], PDO::PARAM_INT);

    if ($stmt2->execute()) {
        echo json_encode(array("success" => true, "message" => "Mesa cadastrada com sucesso!"));
    } else {
        echo json_encode(array("success" => false, "message" => "Erro ao cadastrar mesa"));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Método não permitido"));
}



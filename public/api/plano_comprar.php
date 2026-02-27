<?php

/**
 * API - Comprar Plano
 * Registra pedido de upgrade de plano
 */

session_start();
include_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['restaurante_id']) || $_SESSION['perfil'] !== 'ADMIN') {
        echo json_encode(["success" => false, "message" => "Sem permissão"]);
        exit;
    }

    $plano_novo = $_POST['plano'] ?? '';
    $metodo = $_POST['metodo'] ?? '';
    $valor = 0;

    // Verificar plano válido
    $planos_validos = ['PROFISSIONAL', 'ENTERPRISE'];
    if (!in_array($plano_novo, $planos_validos)) {
        echo json_encode(["success" => false, "message" => "Plano inválido"]);
        exit;
    }

    // Verificar método de pagamento válido
    $metodos_validos = ['DINHEIRO', 'MPESA', 'CARTAO', 'TRANSFERENCIA'];
    if (!in_array($metodo, $metodos_validos)) {
        echo json_encode(["success" => false, "message" => "Método de pagamento inválido"]);
        exit;
    }

    // Definir preço do plano
    $precos = [
        'PROFISSIONAL' => 1500.00,
        'ENTERPRISE' => 3000.00
    ];
    $valor = $precos[$plano_novo];

    // Conectar ao banco
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        echo json_encode(["success" => false, "message" => "Erro de conexão"]);
        exit;
    }

    // Buscar plano atual do restaurante
    $stmt = $db->prepare("SELECT plano FROM restaurantes WHERE id = ?");
    $stmt->execute([$_SESSION['restaurante_id']]);
    $restaurante = $stmt->fetch(PDO::FETCH_ASSOC);
    $plano_atual = $restaurante['plano'] ?? 'BASICO';

    // Não permitir downgrade
    if ($plano_novo === 'PROFISSIONAL' && ($plano_atual === 'PROFISSIONAL' || $plano_atual === 'ENTERPRISE')) {
        echo json_encode(["success" => false, "message" => "Você já possui este plano ou um superior"]);
        exit;
    }
    if ($plano_novo === 'ENTERPRISE' && $plano_atual === 'ENTERPRISE') {
        echo json_encode(["success" => false, "message" => "Você já possui este plano"]);
        exit;
    }

    // Criar registro de compra
    $query = "INSERT INTO compras_planos (restaurante_id, plano_atual, plano_novo, valor, metodo_pagamento, status) 
              VALUES (:rid, :plano_atual, :plano_novo, :valor, :metodo, 'PENDENTE')";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':rid', $_SESSION['restaurante_id']);
    $stmt->bindParam(':plano_atual', $plano_atual);
    $stmt->bindParam(':plano_novo', $plano_novo);
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':metodo', $metodo);

    if ($stmt->execute()) {
        $compra_id = $db->lastInsertId();
        echo json_encode([
            "success" => true,
            "message" => "Pedido de upgrade criado! Aguarde aprovação do administrador.",
            "data" => [
                "id" => $compra_id,
                "plano" => $plano_novo,
                "valor" => $valor,
                "metodo" => $metodo,
                "status" => "PENDENTE"
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Erro ao criar pedido"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método não permitido"]);
}

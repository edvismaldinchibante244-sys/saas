<?php

/**
 * API - Aprovar Compra de Plano
 * Usado pelo administrador para ativar o plano após verificar o pagamento
 */

session_start();
include_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['restaurante_id']) || $_SESSION['perfil'] !== 'ADMIN') {
        echo json_encode(["success" => false, "message" => "Sem permissão"]);
        exit;
    }

    $compra_id = intval($_POST['compra_id'] ?? 0);
    $acao = $_POST['acao'] ?? ''; // 'aprovar' ou 'rejeitar'

    if ($compra_id <= 0) {
        echo json_encode(["success" => false, "message" => "ID inválido"]);
        exit;
    }

    if (!in_array($acao, ['aprovar', 'rejeitar'])) {
        echo json_encode(["success" => false, "message" => "Ação inválida"]);
        exit;
    }

    // Conectar ao banco
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        echo json_encode(["success" => false, "message" => "Erro de conexão"]);
        exit;
    }

    // Buscar compra
    $stmt = $db->prepare("SELECT * FROM compras_planos WHERE id = ? AND restaurante_id = ?");
    $stmt->execute([$compra_id, $_SESSION['restaurante_id']]);
    $compra = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$compra) {
        echo json_encode(["success" => false, "message" => "Compra não encontrada"]);
        exit;
    }

    if ($compra['status'] !== 'PENDENTE') {
        echo json_encode(["success" => false, "message" => "Esta compra já foi processada"]);
        exit;
    }

    if ($acao === 'rejeitar') {
        // Apenas rejeitar
        $stmt = $db->prepare("UPDATE compras_planos SET status = 'CANCELADO', observacao = 'Rejeitado pelo administrador' WHERE id = ?");
        $stmt->execute([$compra_id]);

        echo json_encode(["success" => true, "message" => "Compra rejeitada"]);
        exit;
    }

    // Aprovar - atualizar compra e plano do restaurante
    try {
        $db->beginTransaction();

        // 1. Atualizar status da compra
        $stmt = $db->prepare("UPDATE compras_planos SET status = 'APROVADO', data_pagamento = NOW(), observacao = 'Aprovado pelo administrador' WHERE id = ?");
        $stmt->execute([$compra_id]);

        // 2. Atualizar plano do restaurante
        $stmt = $db->prepare("UPDATE restaurantes SET plano = ? WHERE id = ?");
        $stmt->execute([$compra['plano_novo'], $_SESSION['restaurante_id']]);

        $db->commit();

        // Atualizar sessão se necessário
        if (isset($_SESSION['plano'])) {
            $_SESSION['plano'] = $compra['plano_novo'];
        }

        echo json_encode([
            "success" => true,
            "message" => "Plano ativado com sucesso!",
            "data" => [
                "plano_ativado" => $compra['plano_novo']
            ]
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(["success" => false, "message" => "Erro ao processar: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método não permitido"]);
}

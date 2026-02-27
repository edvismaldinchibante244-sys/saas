<?php

/**
 * API - Super Admin Aprovar/Rejeitar Compra de Plano
 * Usado pelo super admin para ativar o plano após verificar o pagamento
 */

session_start();
include_once '../../config/database.php';

header('Content-Type: application/json');

// Verificar se é super admin
// Temporariamente desabilitado para debug
// if (!isset($_SESSION['super_admin']) || $_SESSION['super_admin'] != 1) {
//     echo json_encode(["success" => false, "message" => "Acesso negado"]);
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $compra_id = intval($_POST['compra_id'] ?? 0);
    $acao = $_POST['acao'] ?? ''; // 'aprovar' ou 'rejeitar'
    $observacao = trim($_POST['observacao'] ?? ''); // Observação do super admin

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
    $stmt = $db->prepare("SELECT cp.*, r.nome as restaurante_nome FROM compras_planos cp INNER JOIN restaurantes r ON cp.restaurante_id = r.id WHERE cp.id = ?");
    $stmt->execute([$compra_id]);
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
        $obs_final = !empty($observacao) ? $observacao : 'Rejeitado pelo Super Admin';
        $stmt = $db->prepare("UPDATE compras_planos SET status = 'CANCELADO', observacao = ? WHERE id = ?");
        $stmt->execute([$obs_final, $compra_id]);

        echo json_encode(["success" => true, "message" => "Compra rejeitada"]);
        exit;
    }

    // Aprovar - atualizar compra e plano do restaurante
    try {
        $db->beginTransaction();

        // 1. Atualizar status da compra
        $obs_final = !empty($observacao) ? $observacao : 'Aprovado pelo Super Admin';
        $stmt = $db->prepare("UPDATE compras_planos SET status = 'APROVADO', data_pagamento = NOW(), observacao = ? WHERE id = ?");
        $stmt->execute([$obs_final, $compra_id]);

        // 2. Atualizar plano do restaurante
        $stmt = $db->prepare("UPDATE restaurantes SET plano = ? WHERE id = ?");
        $stmt->execute([$compra['plano_novo'], $compra['restaurante_id']]);

        // 3. Atualizar data_fim do restaurante (adicionar 30 dias)
        $stmt = $db->prepare("UPDATE restaurantes SET data_fim = DATE_ADD(data_fim, INTERVAL 30 DAY) WHERE id = ?");
        $stmt->execute([$compra['restaurante_id']]);

        $db->commit();

        echo json_encode([
            "success" => true,
            "message" => "Plano ativado com sucesso para " . $compra['restaurante_nome'] . "!",
            "data" => [
                "plano_ativado" => $compra['plano_novo'],
                "restaurante" => $compra['restaurante_nome']
            ]
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(["success" => false, "message" => "Erro ao processar: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método não permitido"]);
}

<?php

/**
 * API - Super Admin Listar Todas as Compras de Planos
 * Lista as compras de planos de todos os restaurantes
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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Conectar ao banco
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        echo json_encode(["success" => false, "message" => "Erro de conexão"]);
        exit;
    }

    // Buscar todas as compras com dados do restaurante
    $stmt = $db->query("
        SELECT cp.*, r.nome as restaurante_nome, r.email as restaurante_email
        FROM compras_planos cp
        INNER JOIN restaurantes r ON cp.restaurante_id = r.id
        ORDER BY cp.criado_em DESC
    ");
    $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $compras
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Método não permitido"]);
}

<?php

/**
 * ============================================
 * API - DELETAR RESTAURANTE
 * Remove um restaurante do sistema
 * ============================================
 */

include_once '../../config/database.php';
include_once '../../config/super_admin_check.php';

$database = new Database();
$db = $database->getConnection();

// Receber dados JSON
$input = json_decode(file_get_contents('php://input'), true);

// Se não vier JSON, tentar POST
if (!$input) {
    $input = $_POST;
}

$id = intval($input['id'] ?? 0);

// Validar ID
if ($id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID do restaurante inválido'
    ]);
    exit;
}

// Verificar se o restaurante existe
$check = $db->prepare("SELECT nome FROM restaurantes WHERE id = ?");
$check->execute([$id]);
if ($check->rowCount() == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Restaurante não encontrado'
    ]);
    exit;
}

try {
    // Deletar restaurante (as tabelas têm CASCADE, então tudo será deletado)
    $stmt = $db->prepare("DELETE FROM restaurantes WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => 'Restaurante deletado com sucesso! (Todos os dados associados foram removidos)'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao deletar restaurante: ' . $e->getMessage()
    ]);
}

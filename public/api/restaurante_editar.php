<?php

/**
 * ============================================
 * API - EDITAR RESTAURANTE
 * Atualiza os dados de um restaurante
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
$nome = trim($input['nome'] ?? '');
$email = trim($input['email'] ?? '');
$telefone = trim($input['telefone'] ?? '');
$endereco = trim($input['endereco'] ?? '');
$cidade = trim($input['cidade'] ?? '');
$nuit = trim($input['nuit'] ?? '');
$plano = $input['plano'] ?? 'BASICO';
$status = $input['status'] ?? 'ATIVO'; // pode incluir PENDENTE agora

// Validar ID
if ($id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID do restaurante inválido'
    ]);
    exit;
}

// Validar campos obrigatórios
if (empty($nome)) {
    echo json_encode([
        'success' => false,
        'message' => 'Nome do restaurante é obrigatório'
    ]);
    exit;
}

if (empty($email)) {
    echo json_encode([
        'success' => false,
        'message' => 'Email do restaurante é obrigatório'
    ]);
    exit;
}

// Validar email único (excluindo o próprio restaurante)
$check = $db->prepare("SELECT id FROM restaurantes WHERE email = ? AND id != ?");
$check->execute([$email, $id]);
if ($check->rowCount() > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Email já está em uso por outro restaurante'
    ]);
    exit;
}

// Validar planos permitidos
$planos_validos = ['BASICO', 'PROFISSIONAL', 'ENTERPRISE'];
if (!in_array($plano, $planos_validos)) {
    $plano = 'BASICO';
}

// Validar status
$status_validos = ['PENDENTE','ATIVO', 'BLOQUEADO', 'CANCELADO'];
if (!in_array($status, $status_validos)) {
    $status = 'PENDENTE';
}

try {
    // Atualizar restaurante
    $query = "UPDATE restaurantes SET 
                nome = :nome, 
                email = :email, 
                telefone = :telefone, 
                endereco = :endereco, 
                cidade = :cidade, 
                nuit = :nuit, 
                plano = :plano, 
                status = :status 
              WHERE id = :id";

    $stmt = $db->prepare($query);
    $stmt->execute([
        ':nome' => $nome,
        ':email' => $email,
        ':telefone' => $telefone,
        ':endereco' => $endereco,
        ':cidade' => $cidade,
        ':nuit' => $nuit,
        ':plano' => $plano,
        ':status' => $status,
        ':id' => $id
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Restaurante atualizado com sucesso!',
        'data' => [
            'id' => $id,
            'nome' => $nome,
            'email' => $email,
            'plano' => $plano,
            'status' => $status
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar restaurante: ' . $e->getMessage()
    ]);
}

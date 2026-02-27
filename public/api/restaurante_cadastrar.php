<?php

/**
 * ============================================
 * API - CADASTRAR NOVO RESTAURANTE
 * Cria um novo restaurante no sistema
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

// Validar dados obrigatórios
$nome = trim($input['nome'] ?? '');
$email = trim($input['email'] ?? '');
$telefone = trim($input['telefone'] ?? '');
$endereco = trim($input['endereco'] ?? '');
$cidade = trim($input['cidade'] ?? '');
$nuit = trim($input['nuit'] ?? '');
$plano = $input['plano'] ?? 'BASICO';
$senha_admin = $input['senha_admin'] ?? 'admin123';
$nome_admin = trim($input['nome_admin'] ?? 'Administrador');

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

// Validar email único
$check = $db->prepare("SELECT id FROM restaurantes WHERE email = ?");
$check->execute([$email]);
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

try {
    $db->beginTransaction();

    // Calcular datas
    $data_inicio = date('Y-m-d');
    $data_fim = date('Y-m-d', strtotime('+1 year'));

    // Inserir restaurante
    // quando restaurantes são cadastrados via formulário público ficam como PENDENTE
    $query = "INSERT INTO restaurantes 
              (nome, email, telefone, endereco, cidade, nuit, plano, status, data_inicio, data_fim) 
              VALUES (:nome, :email, :telefone, :endereco, :cidade, :nuit, :plano, 'PENDENTE', :data_inicio, :data_fim)";

    $stmt = $db->prepare($query);
    $stmt->execute([
        ':nome' => $nome,
        ':email' => $email,
        ':telefone' => $telefone,
        ':endereco' => $endereco,
        ':cidade' => $cidade,
        ':nuit' => $nuit,
        ':plano' => $plano,
        ':data_inicio' => $data_inicio,
        ':data_fim' => $data_fim
    ]);

    $restaurante_id = $db->lastInsertId();

    // Criar usuário administrador para o restaurante
    $senha_hash = password_hash($senha_admin, PASSWORD_BCRYPT);

    $query_user = "INSERT INTO usuarios 
                   (restaurante_id, nome, email, senha, perfil, ativo) 
                   VALUES (:rid, :nome, :email, :senha, 'ADMIN', 1)";

    $stmt_user = $db->prepare($query_user);
    $stmt_user->execute([
        ':rid' => $restaurante_id,
        ':nome' => $nome_admin,
        ':email' => $email,
        ':senha' => $senha_hash
    ]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Restaurante cadastrado com sucesso!',
        'data' => [
            'id' => $restaurante_id,
            'nome' => $nome,
            'email' => $email,
            'plano' => $plano,
            'admin_senha' => $senha_admin
        ]
    ]);
} catch (PDOException $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao cadastrar restaurante: ' . $e->getMessage()
    ]);
}

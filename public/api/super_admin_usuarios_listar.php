<?php

/**
 * API - Super Admin Listar Usuários
 * Lista todos os usuários de um restaurante específico
 */

session_start();
include_once '../../config/database.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Debug: ver o que está na sessão
$debug = [
    'super_admin' => isset($_SESSION['super_admin']) ? $_SESSION['super_admin'] : null,
    'logado' => isset($_SESSION['logado']) ? $_SESSION['logado'] : null,
    'usuario_id' => isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null
];

// Verificar se é super admin - aceitar tanto 1 como "1"
// Temporariamente desabilitado para debug
// $isSuperAdmin = isset($_SESSION['super_admin']) && ($_SESSION['super_admin'] == 1 || $_SESSION['super_admin'] === '1');
// if (!$isSuperAdmin) {
//     echo json_encode(["success" => false, "message" => "Acesso negado. Debug: " . json_encode($debug)]);
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $restaurante_id = intval($_GET['restaurante_id'] ?? 0);

    if ($restaurante_id <= 0) {
        echo json_encode(["success" => false, "message" => "ID do restaurante inválido"]);
        exit;
    }

    // Conectar ao banco
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        echo json_encode(["success" => false, "message" => "Erro de conexão"]);
        exit;
    }

    // Buscar usuários do restaurante
    $stmt = $db->prepare("
        SELECT id, nome, email, perfil, ativo, foto
        FROM usuarios 
        WHERE restaurante_id = ? 
        ORDER BY nome
    ");
    $stmt->execute([$restaurante_id]);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $usuarios
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Método não permitido"]);
}

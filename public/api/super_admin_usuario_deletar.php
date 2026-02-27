<?php

/**
 * API - Super Admin Deletar Usuário
 * Deleta um usuário de um restaurante
 */

session_start();
include_once '../../config/database.php';

header('Content-Type: application/json');

// Verificar se é super admin
if (!isset($_SESSION['super_admin']) || $_SESSION['super_admin'] != 1) {
    echo json_encode(["success" => false, "message" => "Acesso negado"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario_id = intval($_POST['usuario_id'] ?? 0);

    if ($usuario_id <= 0) {
        echo json_encode(["success" => false, "message" => "ID do usuário inválido"]);
        exit;
    }

    // Conectar ao banco
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        echo json_encode(["success" => false, "message" => "Erro de conexão"]);
        exit;
    }

    // Verificar se usuário existe
    $stmt = $db->prepare("SELECT id, nome, perfil FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode(["success" => false, "message" => "Usuário não encontrado"]);
        exit;
    }

    // Não permitir excluir o próprio super admin
    if ($usuario['perfil'] === 'ADMIN' && $usuario_id == $_SESSION['usuario_id']) {
        echo json_encode(["success" => false, "message" => "Não é possível excluir seu próprio usuário"]);
        exit;
    }

    // Deletar usuário
    try {
        $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);

        echo json_encode([
            "success" => true,
            "message" => "Usuário excluído com sucesso!"
        ]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Erro ao excluir: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método não permitido"]);
}

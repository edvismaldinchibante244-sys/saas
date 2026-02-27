<?php

/**
 * API - Super Admin Estatísticas
 * Retorna estatísticas gerais do sistema
 */

session_start();
include_once '../../config/database.php';

header('Content-Type: application/json');

// Verificar se é super admin
if (!isset($_SESSION['super_admin']) || $_SESSION['super_admin'] != 1) {
    echo json_encode(["success" => false, "message" => "Acesso negado"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Conectar ao banco
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        echo json_encode(["success" => false, "message" => "Erro de conexão"]);
        exit;
    }

    try {
        // Total de restaurantes
        $stmt = $db->query("SELECT COUNT(*) as total FROM restaurantes");
        $totalRestaurantes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Restaurantes ativos
        $stmt = $db->query("SELECT COUNT(*) as total FROM restaurantes WHERE status = 'ATIVO'");
        $restaurantesAtivos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Restaurantes suspensos/bloqueados
        $stmt = $db->query("SELECT COUNT(*) as total FROM restaurantes WHERE status = 'BLOQUEADO' OR status = 'CANCELADO'");
        $restaurantesSuspensos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Assinaturas expirando (próximos 7 dias)
        $stmt = $db->query("SELECT COUNT(*) as total FROM restaurantes WHERE status = 'ATIVO' AND data_fim BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
        $assinaturasExpirando = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Receita mensal estimada (baseado nos planos ativos)
        $stmt = $db->query("
            SELECT 
                SUM(CASE 
                    WHEN plano = 'PROFISSIONAL' THEN 1500 
                    WHEN plano = 'ENTERPRISE' THEN 3000 
                    ELSE 0 
                END) as receita_mensal
            FROM restaurantes 
            WHERE status = 'ATIVO'
        ");
        $receitaMensal = $stmt->fetch(PDO::FETCH_ASSOC)['receita_mensal'] ?? 0;

        echo json_encode([
            "success" => true,
            "data" => [
                "total_restaurantes" => intval($totalRestaurantes),
                "restaurantes_ativos" => intval($restaurantesAtivos),
                "restaurantes_suspensos" => intval($restaurantesSuspensos),
                "assinaturas_expirando" => intval($assinaturasExpirando),
                "receita_mensal" => floatval($receitaMensal)
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Erro: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método não permitido"]);
}

<?php

/**
 * ============================================
 * PROCESSAMENTO DE LOGIN
 * ============================================
 */

session_start();

// Incluir arquivos necessários
include_once '../config/database.php';
include_once '../app/Auth.php';

// Headers JSON
header('Content-Type: application/json');

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obter dados do POST
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';

    // Validar campos
    if (empty($email) || empty($senha)) {
        echo json_encode(array("success" => false, "message" => "Preencha todos os campos."));
        exit;
    }

    // Conectar ao banco
    $database = new Database();
    $db = $database->getConnection();

    // Criar instância do Auth
    $auth = new Auth($db);

    // Tentar fazer login
    $resultado = $auth->login($email, $senha);

    if ($resultado['success']) {
        // Salvar dados na sessão
        $_SESSION['logado'] = true;
        $_SESSION['usuario_id'] = $resultado['data']['id'];
        $_SESSION['restaurante_id'] = $resultado['data']['restaurante_id'];
        $_SESSION['nome'] = $resultado['data']['nome'];
        $_SESSION['email'] = $resultado['data']['email'];
        $_SESSION['perfil'] = $resultado['data']['perfil'];
        $_SESSION['plano'] = $resultado['data']['plano'];
        $_SESSION['foto'] = $resultado['data']['foto'] ?? '';

        // Verificar se é super admin
        if (isset($resultado['data']['super_admin']) && $resultado['data']['super_admin'] == 1) {
            $_SESSION['super_admin'] = 1;
            $redirect = "admin.php";
        } else {
            $_SESSION['super_admin'] = 0;
            $redirect = "dashboard.php";
        }

        echo json_encode(array(
            "success" => true,
            "message" => "Login realizado com sucesso!",
            "redirect" => $redirect
        ));
    } else {
        echo json_encode($resultado);
    }
} else {
    echo json_encode(array("success" => false, "message" => "Método não permitido."));
}

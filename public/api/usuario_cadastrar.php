<?php

/**
 * API - Cadastrar Usuário
 */

session_start();
include_once '../../config/database.php';
include_once '../../app/Auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['restaurante_id']) || $_SESSION['perfil'] !== 'ADMIN') {
        echo json_encode(array("success" => false, "message" => "Sem permissão"));
        exit;
    }

    if (empty($_POST['nome']) || empty($_POST['email']) || empty($_POST['senha']) || empty($_POST['perfil'])) {
        echo json_encode(array("success" => false, "message" => "Preencha todos os campos obrigatórios"));
        exit;
    }

    if (strlen($_POST['senha']) < 6) {
        echo json_encode(array("success" => false, "message" => "A senha deve ter pelo menos 6 caracteres"));
        exit;
    }

    $perfis_validos = ['ADMIN', 'CAIXA', 'GARCOM'];
    if (!in_array($_POST['perfil'], $perfis_validos)) {
        echo json_encode(array("success" => false, "message" => "Perfil inválido"));
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    // Verificar se email já existe
    $query_check = "SELECT id FROM usuarios WHERE email = :email LIMIT 1";
    $stmt_check = $db->prepare($query_check);
    $stmt_check->bindParam(':email', $_POST['email']);
    $stmt_check->execute();

    if ($stmt_check->rowCount() > 0) {
        echo json_encode(array("success" => false, "message" => "Este email já está em uso"));
        exit;
    }

    // Processar upload de foto
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        // novo diretório público de imagens (relative à pasta api)
        $upload_dir = '../images/';

        // Criar diretório se não existir
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_tmp = $_FILES['foto']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['foto']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validar extensão - apenas PNG e JPEG
        $allowed_ext = ['png', 'jpg', 'jpeg'];
        if (in_array($file_ext, $allowed_ext)) {
            if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
                // armazenar caminho relativo ao public para exibição
                $foto = 'images/' . $file_name;
            }
        }
    }

    // Hash da senha
    $senha_hash = password_hash($_POST['senha'], PASSWORD_BCRYPT);

    // Inserir usuário
    $query = "INSERT INTO usuarios (restaurante_id, nome, email, senha, perfil, ativo, foto) 
              VALUES (:rid, :nome, :email, :senha, :perfil, 1, :foto)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':rid', $_SESSION['restaurante_id']);
    $stmt->bindParam(':nome', $_POST['nome']);
    $stmt->bindParam(':email', $_POST['email']);
    $stmt->bindParam(':senha', $senha_hash);
    $stmt->bindParam(':perfil', $_POST['perfil']);
    $stmt->bindParam(':foto', $foto);

    if ($stmt->execute()) {
        echo json_encode(array("success" => true, "message" => "Usuário cadastrado com sucesso!"));
    } else {
        echo json_encode(array("success" => false, "message" => "Erro ao cadastrar usuário"));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Método não permitido"));
}

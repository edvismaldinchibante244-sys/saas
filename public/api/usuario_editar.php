<?php

/**
 * API - Editar Usuário
 */

session_start();
include_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['restaurante_id']) || $_SESSION['perfil'] !== 'ADMIN') {
        echo json_encode(array("success" => false, "message" => "Sem permissão"));
        exit;
    }

    if (empty($_POST['usuario_id']) || empty($_POST['nome']) || empty($_POST['email']) || empty($_POST['perfil'])) {
        echo json_encode(array("success" => false, "message" => "Preencha todos os campos obrigatórios"));
        exit;
    }

    $perfis_validos = ['ADMIN', 'CAIXA', 'GARCOM'];
    if (!in_array($_POST['perfil'], $perfis_validos)) {
        echo json_encode(array("success" => false, "message" => "Perfil inválido"));
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    // Verificar se a conexão foi estabelecida
    if (!$db) {
        echo json_encode(array("success" => false, "message" => "Erro de conexão com o banco de dados"));
        exit;
    }

    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $id    = intval($_POST['usuario_id']);

    // Verificar se email já existe em outro usuário
    $query_check = "SELECT id FROM usuarios WHERE email = :email AND id != :id LIMIT 1";
    $stmt_check  = $db->prepare($query_check);
    $stmt_check->bindParam(':email', $_POST['email']);
    $stmt_check->bindParam(':id', $id);
    $stmt_check->execute();

    if ($stmt_check->rowCount() > 0) {
        echo json_encode(array("success" => false, "message" => "Este email já está em uso por outro usuário"));
        exit;
    }

    // Processar upload de foto - PNG e JPEG
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../images/';

        // Criar diretório se não existir
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_tmp = $_FILES['foto']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['foto']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validar extensão - PNG e JPEG
        $allowed_ext = ['png', 'jpg', 'jpeg'];
        if (in_array($file_ext, $allowed_ext)) {
            if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
                $foto = 'images/' . $file_name;
            }
        }
    }

    // Atualizar dados básicos
    if (!empty($foto)) {
        // Com foto nova
        $query = "UPDATE usuarios SET nome = :nome, email = :email, perfil = :perfil, ativo = :ativo, foto = :foto
                  WHERE id = :id AND restaurante_id = :rid";
        $stmt  = $db->prepare($query);
        $stmt->bindParam(':foto', $foto);
    } else {
        // Mantém foto atual
        $query = "UPDATE usuarios SET nome = :nome, email = :email, perfil = :perfil, ativo = :ativo
                  WHERE id = :id AND restaurante_id = :rid";
        $stmt  = $db->prepare($query);
    }

    $stmt->bindParam(':nome',   $_POST['nome']);
    $stmt->bindParam(':email',  $_POST['email']);
    $stmt->bindParam(':perfil', $_POST['perfil']);
    $stmt->bindParam(':ativo',  $ativo, PDO::PARAM_INT);
    $stmt->bindParam(':id',     $id, PDO::PARAM_INT);
    $stmt->bindParam(':rid',    $_SESSION['restaurante_id'], PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Atualizar senha se fornecida
        if (!empty($_POST['senha'])) {
            if (strlen($_POST['senha']) < 6) {
                echo json_encode(array("success" => false, "message" => "A senha deve ter pelo menos 6 caracteres"));
                exit;
            }
            $senha_hash  = password_hash($_POST['senha'], PASSWORD_BCRYPT);
            $query_senha = "UPDATE usuarios SET senha = :senha WHERE id = :id";
            $stmt_senha  = $db->prepare($query_senha);
            $stmt_senha->bindParam(':senha', $senha_hash);
            $stmt_senha->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt_senha->execute();
        }
        echo json_encode(array("success" => true, "message" => "Usuário atualizado com sucesso!"));
    } else {
        echo json_encode(array("success" => false, "message" => "Erro ao atualizar usuário"));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Método não permitido"));
}

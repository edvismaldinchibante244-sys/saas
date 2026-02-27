<?php
/**
 * API - Cadastrar Categoria
 */

session_start();
include_once '../../config/database.php';
include_once '../../app/Categoria.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Verificar se está logado
    if(!isset($_SESSION['restaurante_id'])) {
        echo json_encode(array("success" => false, "message" => "Não autenticado"));
        exit;
    }
    
    // Validar campo obrigatório
    if(empty($_POST['nome'])) {
        echo json_encode(array("success" => false, "message" => "Digite o nome da categoria"));
        exit;
    }
    
    // Conectar ao banco
    $database = new Database();
    $db = $database->getConnection();
    
    // Criar instância da categoria
    $categoria = new Categoria($db);
    
    $categoria->restaurante_id = $_SESSION['restaurante_id'];
    $categoria->nome = trim($_POST['nome']);
    $categoria->descricao = $_POST['descricao'] ?? '';
    $categoria->ativo = 1;
    
    if($categoria->cadastrar()) {
        // Buscar o ID da categoria criada
        $lastId = $db->lastInsertId();
        echo json_encode(array(
            "success" => true, 
            "message" => "Categoria criada com sucesso!",
            "categoria" => array(
                "id" => $lastId,
                "nome" => $categoria->nome
            )
        ));
    } else {
        echo json_encode(array("success" => false, "message" => "Erro ao criar categoria"));
    }
    
} else {
    echo json_encode(array("success" => false, "message" => "Método não permitido"));
}
?>

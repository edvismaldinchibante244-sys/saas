<?php
/**
 * API - Cadastrar Produto
 */

session_start();
include_once '../../config/database.php';
include_once '../../app/Produto.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Verificar se está logado
    if(!isset($_SESSION['restaurante_id'])) {
        echo json_encode(array("success" => false, "message" => "Não autenticado"));
        exit;
    }
    
    // Validar campos obrigatórios
    if(empty($_POST['nome']) || empty($_POST['preco'])) {
        echo json_encode(array("success" => false, "message" => "Preencha todos os campos obrigatórios"));
        exit;
    }
    
    // Conectar ao banco
    $database = new Database();
    $db = $database->getConnection();
    
    // Criar instância do produto
    $produto = new Produto($db);
    
    $produto->restaurante_id = $_SESSION['restaurante_id'];
    $produto->categoria_id = !empty($_POST['categoria_id']) ? $_POST['categoria_id'] : null;
    $produto->nome = $_POST['nome'];
    $produto->descricao = $_POST['descricao'] ?? '';
    $produto->preco = $_POST['preco'];
    $produto->custo = $_POST['custo'] ?? 0;
    $produto->estoque = $_POST['estoque'] ?? 0;
    $produto->estoque_minimo = $_POST['estoque_minimo'] ?? 5;
    $produto->ativo = isset($_POST['ativo']) ? 1 : 0;

    // handle image upload
    $produto->imagem = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../images/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $tmp_name = $_FILES['imagem']['tmp_name'];
        $orig_name = basename($_FILES['imagem']['name']);
        $ext = pathinfo($orig_name, PATHINFO_EXTENSION);
        $filename = uniqid('p_', true) . '.' . $ext;
        $dest = $upload_dir . $filename;
        if (move_uploaded_file($tmp_name, $dest)) {
            $produto->imagem = 'images/' . $filename;
        }
    }
    
    if($produto->cadastrar()) {
        echo json_encode(array("success" => true, "message" => "Produto cadastrado com sucesso!"));
    } else {
        echo json_encode(array("success" => false, "message" => "Erro ao cadastrar produto"));
    }
    
} else {
    echo json_encode(array("success" => false, "message" => "Método não permitido"));
}
?>



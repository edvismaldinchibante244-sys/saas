<?php
/**
 * API - Deletar Produto
 */

session_start();
include_once '../../config/database.php';
include_once '../../app/Produto.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if(!isset($_SESSION['restaurante_id'])) {
        echo json_encode(array("success" => false, "message" => "Não autenticado"));
        exit;
    }
    
    if(empty($_POST['id'])) {
        echo json_encode(array("success" => false, "message" => "ID não fornecido"));
        exit;
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    $produto = new Produto($db);
    
    if($produto->deletar($_POST['id'], $_SESSION['restaurante_id'])) {
        echo json_encode(array("success" => true, "message" => "Produto deletado com sucesso!"));
    } else {
        echo json_encode(array("success" => false, "message" => "Erro ao deletar produto"));
    }
    
} else {
    echo json_encode(array("success" => false, "message" => "Método não permitido"));
}
?>



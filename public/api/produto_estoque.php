<?php
/**
 * API - Atualizar Estoque
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
    
    if(empty($_POST['id']) || !isset($_POST['quantidade'])) {
        echo json_encode(array("success" => false, "message" => "Dados incompletos"));
        exit;
    }
    
    $quantidade = (int)$_POST['quantidade'];
    $tipo = $quantidade >= 0 ? 'ENTRADA' : 'SAIDA';
    $quantidade = abs($quantidade);
    
    $database = new Database();
    $db = $database->getConnection();
    
    $produto = new Produto($db);
    
    if($produto->atualizarEstoque($_POST['id'], $quantidade, $tipo)) {
        $mensagem = $tipo == 'ENTRADA' ? "Estoque adicionado!" : "Estoque retirado!";
        echo json_encode(array("success" => true, "message" => $mensagem));
    } else {
        echo json_encode(array("success" => false, "message" => "Erro ao atualizar estoque"));
    }
    
} else {
    echo json_encode(array("success" => false, "message" => "Método não permitido"));
}
?>



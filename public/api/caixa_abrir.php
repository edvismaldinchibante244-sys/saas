<?php
/**
 * API - Abrir Caixa
 */

session_start();
include_once '../../config/database.php';
include_once '../../app/Caixa.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if(!isset($_SESSION['restaurante_id'])) {
        echo json_encode(array("success" => false, "message" => "Não autenticado"));
        exit;
    }
    
    if(empty($_POST['abertura'])) {
        echo json_encode(array("success" => false, "message" => "Valor de abertura não informado"));
        exit;
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    $caixa = new Caixa($db);
    
    // Verificar se já tem caixa aberto hoje
    $caixa_existente = $caixa->caixaAbertoHoje($_SESSION['restaurante_id']);
    
    if($caixa_existente) {
        echo json_encode(array("success" => false, "message" => "Já existe um caixa aberto hoje"));
        exit;
    }
    
    $caixa->restaurante_id = $_SESSION['restaurante_id'];
    $caixa->usuario_id = $_SESSION['usuario_id'];
    $caixa->abertura = $_POST['abertura'];
    
    if($caixa->abrir()) {
        echo json_encode(array("success" => true, "message" => "Caixa aberto com sucesso!"));
    } else {
        echo json_encode(array("success" => false, "message" => "Erro ao abrir caixa"));
    }
    
} else {
    echo json_encode(array("success" => false, "message" => "Método não permitido"));
}
?>



<?php
/**
 * API - Criar Venda
 */

session_start();
include_once '../../config/database.php';
include_once '../../app/Venda.php';
include_once '../../app/Caixa.php';
include_once '../../app/Produto.php';
include_once '../../app/Mesa.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Verificar autenticação
    if(!isset($_SESSION['restaurante_id'])) {
        echo json_encode(array("success" => false, "message" => "Não autenticado"));
        exit;
    }
    
    // Obter dados JSON
    $dados = json_decode(file_get_contents("php://input"), true);
    
    if(empty($dados['itens']) || empty($dados['forma_pagamento'])) {
        echo json_encode(array("success" => false, "message" => "Dados incompletos"));
        exit;
    }
    
    // Conectar ao banco
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // Iniciar transação
        $db->beginTransaction();
        
        // Verificar se há caixa aberto
        $caixa = new Caixa($db);
        $caixa_aberto = $caixa->caixaAbertoHoje($_SESSION['restaurante_id']);
        
        if(!$caixa_aberto) {
            throw new Exception("Não há caixa aberto");
        }
        
        // Criar venda
        $venda = new Venda($db);
        $venda->restaurante_id = $_SESSION['restaurante_id'];
        $venda->usuario_id = $_SESSION['usuario_id'];
        $venda->caixa_id = $caixa_aberto['id'];
        $venda->mesa_id = $dados['mesa_id'] ?? null;
        $venda->total = $dados['total'];
        $venda->desconto = $dados['desconto'] ?? 0;
        $venda->total_final = $dados['total_final'];
        $venda->forma_pagamento = $dados['forma_pagamento'];
        $venda->status = 'PAGO';
        $venda->numero_fatura = $venda->gerarNumeroFatura($_SESSION['restaurante_id']);
        
        $venda_id = $venda->criar();
        
        if(!$venda_id) {
            throw new Exception("Erro ao criar venda");
        }
        
        // Adicionar itens
        $produto = new Produto($db);
        
        foreach($dados['itens'] as $item) {
            // Adicionar item à venda
            if(!$venda->adicionarItem($venda_id, $item['id'], $item['quantidade'], $item['preco'])) {
                throw new Exception("Erro ao adicionar item");
            }
            
            // Dar baixa no estoque
            $produto->atualizarEstoque($item['id'], $item['quantidade'], 'SAIDA');
        }
        
        // Se tem mesa, marcar como ocupada
        if($dados['mesa_id']) {
            $mesa = new Mesa($db);
            $mesa->atualizarStatus($dados['mesa_id'], 'OCUPADA');
        }
        
        // Confirmar transação
        $db->commit();
        
        echo json_encode(array(
            "success" => true, 
            "message" => "Venda realizada com sucesso!",
            "venda_id" => $venda_id,
            "numero_fatura" => $venda->numero_fatura
        ));
        
    } catch(Exception $e) {
        // Reverter transação
        $db->rollBack();
        echo json_encode(array("success" => false, "message" => $e->getMessage()));
    }
    
} else {
    echo json_encode(array("success" => false, "message" => "Método não permitido"));
}
?>



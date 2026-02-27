<?php

/**
 * API - Fechar Caixa
 */

session_start();
include_once '../../config/database.php';
include_once '../../app/Caixa.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['restaurante_id'])) {
        echo json_encode(array("success" => false, "message" => "Não autenticado"));
        exit;
    }

    if (!isset($_POST['fechamento']) || $_POST['fechamento'] === '') {
        echo json_encode(array("success" => false, "message" => "Valor de fechamento não informado"));
        exit;
    }

    $fechamento = floatval($_POST['fechamento']);

    if ($fechamento < 0) {
        echo json_encode(array("success" => false, "message" => "Valor de fechamento inválido"));
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    $caixa = new Caixa($db);

    // Verificar se existe caixa aberto
    $caixa_aberto = $caixa->caixaAbertoHoje($_SESSION['restaurante_id']);

    if (!$caixa_aberto) {
        echo json_encode(array("success" => false, "message" => "Não há caixa aberto para fechar"));
        exit;
    }

    // Fechar o caixa
    if ($caixa->fechar($caixa_aberto['id'], $fechamento)) {
        // Calcular diferença
        $total_vendas = $caixa->totalVendas($caixa_aberto['id']);
        $esperado = $caixa_aberto['abertura'] + $total_vendas;
        $diferenca = $fechamento - $esperado;

        echo json_encode(array(
            "success"    => true,
            "message"    => "Caixa fechado com sucesso!",
            "resumo"     => array(
                "abertura"    => $caixa_aberto['abertura'],
                "vendas"      => $total_vendas,
                "esperado"    => $esperado,
                "fechamento"  => $fechamento,
                "diferenca"   => $diferenca
            )
        ));
    } else {
        echo json_encode(array("success" => false, "message" => "Erro ao fechar caixa"));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Método não permitido"));
}



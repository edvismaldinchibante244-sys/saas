<?php
/**
 * ============================================
 * API - CRIAR PEDIDO VIA CARDÁPIO DIGITAL
 * Chamado pelo cliente após escanear QR Code
 * ============================================
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

include_once '../../config/database.php';

// Ler JSON do body
$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$rid          = (int)($data['rid']          ?? 0);
$mesa_id      = (int)($data['mesa_id']      ?? 0);
$cliente_nome = trim($data['cliente_nome']  ?? '');
$observacao   = trim($data['observacao']    ?? '');
$total        = (float)($data['total']      ?? 0);
$itens        = $data['itens']              ?? [];

// Validações
if (!$rid) {
    echo json_encode(['success' => false, 'message' => 'Restaurante inválido']);
    exit;
}

if (empty($itens)) {
    echo json_encode(['success' => false, 'message' => 'Nenhum item no pedido']);
    exit;
}

if ($total <= 0) {
    echo json_encode(['success' => false, 'message' => 'Total inválido']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Verificar se restaurante existe
    $stmt = $db->prepare("SELECT id FROM restaurantes WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $rid);
    $stmt->execute();
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Restaurante não encontrado']);
        exit;
    }

    // Verificar mesa (se informada)
    if ($mesa_id) {
        $stmt = $db->prepare("SELECT id FROM mesas WHERE id = :id AND restaurante_id = :rid LIMIT 1");
        $stmt->bindParam(':id',  $mesa_id);
        $stmt->bindParam(':rid', $rid);
        $stmt->execute();
        if (!$stmt->fetch()) {
            $mesa_id = 0; // Mesa inválida, ignora
        }
    }

    // Gerar número do pedido
    $stmt = $db->prepare("SELECT COUNT(*) AS total FROM pedidos WHERE restaurante_id = :rid");
    $stmt->bindParam(':rid', $rid);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $numero_pedido = 'PD' . date('Ymd') . str_pad(($row['total'] + 1), 4, '0', STR_PAD_LEFT);

    $db->beginTransaction();

    // Inserir pedido
    $mesa_id_val = $mesa_id ?: null;
    $cliente_val = $cliente_nome ?: null;
    $obs_val     = $observacao   ?: null;

    $stmt = $db->prepare("
        INSERT INTO pedidos
            (restaurante_id, mesa_id, numero_pedido, total, status, cliente_nome, observacao, criado_em)
        VALUES
            (:rid, :mesa_id, :numero_pedido, :total, 'PENDENTE', :cliente_nome, :observacao, NOW())
    ");
    $stmt->bindParam(':rid',           $rid);
    $stmt->bindParam(':mesa_id',       $mesa_id_val);
    $stmt->bindParam(':numero_pedido', $numero_pedido);
    $stmt->bindParam(':total',         $total);
    $stmt->bindParam(':cliente_nome',  $cliente_val);
    $stmt->bindParam(':observacao',    $obs_val);
    $stmt->execute();

    $pedido_id = $db->lastInsertId();

    // Inserir itens do pedido
    $stmt_item = $db->prepare("
        INSERT INTO itens_pedido
            (pedido_id, produto_id, produto_nome, quantidade, preco_unitario, subtotal)
        VALUES
            (:pedido_id, :produto_id, :produto_nome, :quantidade, :preco_unitario, :subtotal)
    ");

    foreach ($itens as $item) {
        $item_produto_id   = (int)($item['id']    ?? 0);
        $item_nome         = trim($item['nome']   ?? '');
        $item_qtd          = (int)($item['qtd']   ?? 1);
        $item_preco        = (float)($item['preco'] ?? 0);
        $item_subtotal     = $item_preco * $item_qtd;

        if (!$item_produto_id || $item_qtd <= 0 || $item_preco <= 0) {
            continue;
        }

        // Verificar se produto pertence ao restaurante
        $stmt_check = $db->prepare("SELECT id, nome FROM produtos WHERE id = :id AND restaurante_id = :rid AND ativo = 1 LIMIT 1");
        $stmt_check->bindParam(':id',  $item_produto_id);
        $stmt_check->bindParam(':rid', $rid);
        $stmt_check->execute();
        $produto_db = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$produto_db) {
            continue; // Produto inválido, pula
        }

        $nome_final = $produto_db['nome'];

        $stmt_item->bindParam(':pedido_id',      $pedido_id);
        $stmt_item->bindParam(':produto_id',     $item_produto_id);
        $stmt_item->bindParam(':produto_nome',   $nome_final);
        $stmt_item->bindParam(':quantidade',     $item_qtd);
        $stmt_item->bindParam(':preco_unitario', $item_preco);
        $stmt_item->bindParam(':subtotal',       $item_subtotal);
        $stmt_item->execute();
    }

    $db->commit();

    echo json_encode([
        'success'       => true,
        'message'       => 'Pedido criado com sucesso!',
        'pedido_id'     => $pedido_id,
        'numero_pedido' => $numero_pedido
    ]);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno. Tente novamente.'
    ]);
}
?>



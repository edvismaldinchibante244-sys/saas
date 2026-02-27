<?php
// Proteção da página
include_once '../config/auth_check.php';
include_once '../config/database.php';
include_once '../app/Venda.php';

// Conectar ao banco
$database = new Database();
$db = $database->getConnection();

$venda = new Venda($db);

// Buscar venda
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if(!$id) {
    header("Location: vendas.php");
    exit;
}

$dados_venda = $venda->buscarPorId($id, $_SESSION['restaurante_id']);

if(!$dados_venda) {
    header("Location: vendas.php");
    exit;
}

// Buscar itens da venda
$itens = $venda->buscarItens($id);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprovante #<?php echo $dados_venda['numero_fatura']; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', monospace;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        .comprovante {
            background: white;
            width: 320px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 12px;
            color: #555;
            line-height: 1.6;
        }

        .info-venda {
            border-bottom: 1px dashed #999;
            padding-bottom: 10px;
            margin-bottom: 10px;
            font-size: 12px;
        }

        .info-venda div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .itens-titulo {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            border-top: 1px dashed #999;
            border-bottom: 1px dashed #999;
            padding: 5px 0;
            margin-bottom: 10px;
        }

        .item {
            font-size: 12px;
            margin-bottom: 8px;
        }

        .item-nome {
            font-weight: bold;
        }

        .item-detalhe {
            display: flex;
            justify-content: space-between;
            color: #555;
        }

        .totais {
            border-top: 2px dashed #333;
            padding-top: 10px;
            margin-top: 10px;
            font-size: 13px;
        }

        .totais div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .total-final {
            font-size: 18px !important;
            font-weight: bold;
            border-top: 1px dashed #999;
            padding-top: 8px;
            margin-top: 5px;
        }

        .pagamento {
            text-align: center;
            margin-top: 15px;
            padding: 10px;
            background: #f8f8f8;
            border: 1px dashed #999;
            font-size: 13px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px dashed #333;
            font-size: 11px;
            color: #777;
            line-height: 1.8;
        }

        .btn-imprimir {
            display: block;
            width: 100%;
            padding: 12px;
            background: #FF6B35;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
        }

        .btn-voltar {
            display: block;
            width: 100%;
            padding: 10px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            margin-top: 8px;
            text-decoration: none;
            text-align: center;
        }

        @media print {
            body { background: white; padding: 0; }
            .comprovante { box-shadow: none; }
            .btn-imprimir, .btn-voltar { display: none; }
        }
    </style>
</head>
<body>

<div class="comprovante">

    <!-- CABEÇALHO -->
    <div class="header">
        <h1>🍽️ RestauranteSaaS</h1>
        <p>
            Comprovante de Venda<br>
            <?php echo date('d/m/Y H:i:s', strtotime($dados_venda['criado_em'])); ?>
        </p>
    </div>

    <!-- INFORMAÇÕES DA VENDA -->
    <div class="info-venda">
        <div>
            <span>Fatura Nº:</span>
            <strong><?php echo $dados_venda['numero_fatura']; ?></strong>
        </div>
        <div>
            <span>Atendente:</span>
            <span><?php echo $dados_venda['usuario_nome']; ?></span>
        </div>
        <?php if($dados_venda['mesa_numero']): ?>
        <div>
            <span>Mesa:</span>
            <span>Mesa <?php echo $dados_venda['mesa_numero']; ?></span>
        </div>
        <?php else: ?>
        <div>
            <span>Local:</span>
            <span>Balcão</span>
        </div>
        <?php endif; ?>
        <div>
            <span>Status:</span>
            <strong style="color: <?php echo $dados_venda['status'] == 'PAGO' ? 'green' : 'red'; ?>">
                <?php echo $dados_venda['status']; ?>
            </strong>
        </div>
    </div>

    <!-- ITENS -->
    <div class="itens-titulo">--- ITENS DO PEDIDO ---</div>

    <?php while($item = $itens->fetch(PDO::FETCH_ASSOC)): ?>
    <div class="item">
        <div class="item-nome"><?php echo $item['produto_nome']; ?></div>
        <div class="item-detalhe">
            <span><?php echo $item['quantidade']; ?>x <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?> MZN</span>
            <strong><?php echo number_format($item['subtotal'], 2, ',', '.'); ?> MZN</strong>
        </div>
    </div>
    <?php endwhile; ?>

    <!-- TOTAIS -->
    <div class="totais">
        <div>
            <span>Subtotal:</span>
            <span><?php echo number_format($dados_venda['total'], 2, ',', '.'); ?> MZN</span>
        </div>
        <?php if($dados_venda['desconto'] > 0): ?>
        <div>
            <span>Desconto:</span>
            <span style="color: red;">- <?php echo number_format($dados_venda['desconto'], 2, ',', '.'); ?> MZN</span>
        </div>
        <?php endif; ?>
        <div class="total-final">
            <span>TOTAL:</span>
            <span><?php echo number_format($dados_venda['total_final'], 2, ',', '.'); ?> MZN</span>
        </div>
    </div>

    <!-- FORMA DE PAGAMENTO -->
    <div class="pagamento">
        💳 Pagamento: <strong>
        <?php
        $formas = [
            'DINHEIRO'      => '💵 Dinheiro',
            'MPESA'         => '📱 M-Pesa',
            'CARTAO'        => '💳 Cartão',
            'TRANSFERENCIA' => '🏦 Transferência'
        ];
        echo $formas[$dados_venda['forma_pagamento']] ?? $dados_venda['forma_pagamento'];
        ?>
        </strong>
    </div>

    <!-- RODAPÉ -->
    <div class="footer">
        <p>Obrigado pela sua preferência!</p>
        <p>Volte sempre 😊</p>
        <p style="margin-top: 8px; font-size: 10px;">
            Sistema RestauranteSaaS<br>
            Desenvolvido para Moçambique 🇲🇿
        </p>
    </div>

    <!-- BOTÕES (não aparecem na impressão) -->
    <button class="btn-imprimir" onclick="window.print()">🖨️ Imprimir Comprovante</button>
    <a href="vendas.php" class="btn-voltar">← Voltar ao PDV</a>

</div>

</body>
</html>

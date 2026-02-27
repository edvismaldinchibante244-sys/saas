<?php
// Proteção da página
include_once '../config/auth_check.php';
include_once '../config/database.php';
include_once '../app/Caixa.php';
include_once '../app/Produto.php';
include_once '../app/Mesa.php';

// Conectar ao banco
$database = new Database();
$db = $database->getConnection();

// Instanciar classes
$caixa   = new Caixa($db);
$produto = new Produto($db);
$mesa    = new Mesa($db);

// Verificar se tem caixa aberto
$caixa_aberto = $caixa->caixaAbertoHoje($_SESSION['restaurante_id']);

// Buscar produtos ativos
$stmt_produtos  = $produto->listar($_SESSION['restaurante_id']);
$todos_produtos = $stmt_produtos->fetchAll(PDO::FETCH_ASSOC);
$lista_produtos = [];
foreach ($todos_produtos as $_p) {
    if ($_p['ativo'] == 1) {
        $lista_produtos[] = $_p;
    }
}

// Buscar mesas
$stmt_mesas = $mesa->mesasLivres($_SESSION['restaurante_id']);
$lista_mesas = $stmt_mesas->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDV - Sistema de Restaurante</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary: #FF6B35;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%);
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
        }

        .sidebar .brand {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar .brand h3 {
            color: white;
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }

        .sidebar .nav-item a {
            color: rgba(255, 255, 255, 0.7);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar .nav-item a:hover,
        .sidebar .nav-item a.active {
            background: rgba(255, 107, 53, 0.2);
            color: white;
            border-left: 3px solid var(--primary);
        }

        .main-content {
            margin-left: 260px;
            padding: 25px;
        }

        .avatar-lg {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .pdv-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
        }

        .produto-btn {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .produto-btn:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.2);
        }

        .produto-btn .icon {
            font-size: 32px;
            margin-bottom: 8px;
        }

        .produto-btn .nome {
            font-weight: 600;
            font-size: 13px;
            color: #2d3748;
        }

        .produto-btn .preco {
            color: var(--primary);
            font-weight: 700;
            font-size: 14px;
        }

        .carrinho-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        @media (max-width: 991px) {
            .sidebar {
                width: 100%;
                position: relative;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            <!-- SIDEBAR -->
            <nav class="sidebar col-md-3 col-lg-2 d-md-block">
                <div class="brand">
                    <h3><i class="fas fa-utensils me-2"></i>RestauranteSaaS</h3>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                    <li class="nav-item"><a href="produtos.php" class="nav-link"><i class="fas fa-pizza-slice"></i> Produtos</a></li>
                    <li class="nav-item"><a href="vendas.php" class="nav-link active"><i class="fas fa-cash-register"></i> Vendas</a></li>
                    <li class="nav-item"><a href="caixa.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Caixa</a></li>
                    <li class="nav-item"><a href="pedidos.php" class="nav-link"><i class="fas fa-mobile-alt"></i> Pedidos Online</a></li>
                    <li class="nav-item"><a href="mesas.php" class="nav-link"><i class="fas fa-chair"></i> Mesas</a></li>
                    <li class="nav-item"><a href="relatorios.php" class="nav-link"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
                    <?php if ($_SESSION['perfil'] == 'ADMIN'): ?>
                        <li class="nav-item"><a href="usuarios.php" class="nav-link"><i class="fas fa-users"></i> Usuários</a></li>
                        <li class="nav-item"><a href="configuracoes.php" class="nav-link"><i class="fas fa-cog"></i> Configurações</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                </ul>
            </nav>

            <!-- CONTEÚDO PRINCIPAL -->
            <main class="main-content col-md-9 ms-sm-auto col-lg-10">

                <!-- TOP BAR -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-0"><i class="fas fa-cash-register text-primary me-2"></i>PDV - Ponto de Venda</h4>
                        <p class="text-muted mb-0" style="font-size: 14px;">
                            <?php if ($caixa_aberto): ?>
                                <span class="text-success">Caixa aberto desde <?php echo date('H:i', strtotime($caixa_aberto['criado_em'])); ?></span>
                            <?php else: ?>
                                <span class="text-danger">Caixa fechado</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nome']); ?>&background=FF6B35&color=fff&size=50" class="avatar-lg">
                </div>

                <!-- AVISO CAIXA FECHADO -->
                <?php if (!$caixa_aberto): ?>
                    <div class="alert alert-warning d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div class="flex-grow-1">Para realizar vendas, é necessário abrir o caixa primeiro.</div>
                        <a href="caixa.php" class="btn btn-warning btn-sm">Abrir Caixa</a>
                    </div>
                <?php endif; ?>

                <?php if ($caixa_aberto): ?>
                    <!-- PDV LAYOUT -->
                    <div class="row g-4">

                        <!-- LADO ESQUERDO: PRODUTOS -->
                        <div class="col-lg-8">
                            <div class="pdv-card p-3">
                                <!-- BUSCA -->
                                <input type="text" id="buscarProduto" class="form-control mb-3" placeholder="🔍 Buscar produto...">

                                <!-- GRID DE PRODUTOS -->
                                <div class="row g-3">
                                    <?php foreach ($lista_produtos as $p): ?>
                                        <div class="col-6 col-md-4 col-lg-3">
                                            <div class="produto-btn" onclick="adicionarAoCarrinho({id: <?php echo $p['id']; ?>, nome: '<?php echo addslashes($p['nome']); ?>', preco: <?php echo $p['preco']; ?>, estoque: <?php echo $p['estoque']; ?>})">
                                                <div class="icon">🍽️</div>
                                                <div class="nome"><?php echo htmlspecialchars($p['nome']); ?></div>
                                                <div class="preco"><?php echo number_format($p['preco'], 2, ',', '.'); ?> MZN</div>
                                                <?php if ($p['estoque'] <= 0): ?>
                                                    <div class="text-danger small">Sem estoque</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (empty($lista_produtos)): ?>
                                        <div class="col-12 text-center py-5 text-muted">
                                            <i class="fas fa-pizza-slice fa-3x mb-3 d-block"></i>
                                            <p>Nenhum produto ativo</p>
                                            <a href="produtos.php" class="btn btn-primary">Cadastrar produtos</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- LADO DIREITO: CARRINHO -->
                        <div class="col-lg-4">
                            <div class="pdv-card p-3" style="position: sticky; top: 25px;">
                                <h5 class="mb-3"><i class="fas fa-shopping-cart me-2"></i>Carrinho</h5>

                                <!-- MESA -->
                                <div class="mb-3">
                                    <label class="form-label">Mesa (opcional)</label>
                                    <select id="mesa_id" class="form-select">
                                        <option value="">Balcão / Sem mesa</option>
                                        <?php foreach ($lista_mesas as $m): ?>
                                            <option value="<?php echo $m['id']; ?>">Mesa <?php echo $m['numero']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- ITENS -->
                                <div id="carrinhoItens" style="min-height: 150px; max-height: 300px; overflow-y: auto;">
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-shopping-cart fa-2x mb-2 d-block"></i>
                                        Carrinho vazio
                                    </div>
                                </div>

                                <!-- TOTAIS -->
                                <div class="border-top pt-3 mt-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span id="subtotal" class="fw-bold">0,00 MZN</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span>Desconto:</span>
                                        <input type="number" id="desconto" class="form-control" style="width: 100px;" value="0" min="0" step="0.01" onchange="calcularTotal()">
                                    </div>
                                    <div class="d-flex justify-content-between mb-3 p-3" style="background: linear-gradient(135deg, #FF6B35, #F7931E); color: white; border-radius: 10px;">
                                        <span class="fw-bold">TOTAL:</span>
                                        <span id="total" class="fw-bold" style="font-size: 20px;">0,00 MZN</span>
                                    </div>
                                </div>

                                <!-- FORMA DE PAGAMENTO -->
                                <div class="mb-3">
                                    <label class="form-label">Forma de Pagamento</label>
                                    <select id="forma_pagamento" class="form-select">
                                        <option value="DINHEIRO">💵 Dinheiro</option>
                                        <option value="MPESA">📱 M-Pesa</option>
                                        <option value="CARTAO">💳 Cartão</option>
                                        <option value="TRANSFERENCIA">🏦 Transferência</option>
                                    </select>
                                </div>

                                <!-- BOTÕES -->
                                <button class="btn btn-success w-100 mb-2 py-2" onclick="finalizarVenda()">
                                    <i class="fas fa-check me-2"></i>Finalizar Venda
                                </button>
                                <button class="btn btn-outline-danger w-100" onclick="limparCarrinho()">
                                    <i class="fas fa-trash me-2"></i>Limpar Carrinho
                                </button>

                                <div id="alertVenda" class="alert mt-3" style="display: none;"></div>
                            </div>
                        </div>

                    </div>
                <?php endif; ?>

            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/pdv.js"></script>
</body>

</html>
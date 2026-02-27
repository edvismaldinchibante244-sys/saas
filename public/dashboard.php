<?php
// Proteção da página
include_once '../config/auth_check.php';
include_once '../config/database.php';
include_once '../app/Venda.php';
include_once '../app/Produto.php';
include_once '../app/Caixa.php';
include_once '../app/Mesa.php';

// Conectar ao banco
$database = new Database();
$db = $database->getConnection();

// Instanciar classes
$venda   = new Venda($db);
$produto = new Produto($db);
$caixa   = new Caixa($db);
$mesa    = new Mesa($db);

// Verificar se é Super Admin
$isSuperAdmin = isset($_SESSION['super_admin']) && $_SESSION['super_admin'] == 1;

// Se for Super Admin, verificar se selecionou um restaurante
if ($isSuperAdmin) {
    // Se o Super Admin enviou formulário para selecionar restaurante
    if (isset($_POST['selecionar_restaurante']) && isset($_POST['restaurante_id'])) {
        $_SESSION['restaurante_selecionado'] = intval($_POST['restaurante_id']);
        $_SESSION['restaurante_id'] = $_SESSION['restaurante_selecionado'];
        // Recarregar a página para atualizar os dados
        header("Location: dashboard.php");
        exit;
    }

    // Se tem restaurante selecionado na sessão, usar
    if (isset($_SESSION['restaurante_selecionado']) && $_SESSION['restaurante_selecionado'] > 0) {
        $_SESSION['restaurante_id'] = $_SESSION['restaurante_selecionado'];
    }
}

$rid = $_SESSION['restaurante_id'];

// Se for Super Admin e não tiver restaurante selecionado, redirecionar para admin
if ($isSuperAdmin && (!isset($_SESSION['restaurante_selecionado']) || $_SESSION['restaurante_selecionado'] == 0)) {
    header("Location: admin.php");
    exit;
}

// --- Dados do dashboard ---
// Total de vendas hoje
$total_hoje = $venda->vendasHoje($rid);
$qtd_hoje   = $venda->contarVendasHoje($rid);

// Caixa aberto
$caixa_aberto = $caixa->caixaAbertoHoje($rid);

// Total de produtos ativos
$total_produtos = $produto->contarAtivos($rid);

// Mesas ocupadas
$stmt_mesas = $mesa->listar($rid);
$todas_mesas = $stmt_mesas->fetchAll(PDO::FETCH_ASSOC);
$mesas_ocupadas = 0;
$total_mesas    = count($todas_mesas);
foreach ($todas_mesas as $m) {
    if ($m['status'] == 'OCUPADA') {
        $mesas_ocupadas++;
    }
}

// Produtos com estoque baixo
$stmt_estoque = $produto->estoqueBaixo($rid);
$estoque_baixo = $stmt_estoque->fetchAll(PDO::FETCH_ASSOC);

// Últimas 8 vendas
$stmt_ultimas = $venda->ultimasVendas($rid, 8);
$ultimas_vendas = $stmt_ultimas->fetchAll(PDO::FETCH_ASSOC);

// Vendas da semana (para gráfico)
$stmt_semana = $venda->vendasPorDia($rid, 7);
$vendas_semana = $stmt_semana->fetchAll(PDO::FETCH_ASSOC);

// Dados do plano
$stmt_plano = $db->prepare("SELECT plano, data_fim FROM restaurantes WHERE id = ?");
$stmt_plano->execute([$rid]);
$dados_plano = $stmt_plano->fetch(PDO::FETCH_ASSOC);
$plano_atual = $dados_plano['plano'] ?? 'BASICO';
$data_fim_plano = $dados_plano['data_fim'] ?? date('Y-m-d');
$dias_restantes = floor((strtotime($data_fim_plano) - time()) / (60 * 60 * 24));
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Restaurante</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary: #FF6B35;
            --primary-dark: #e55a25;
            --secondary: #F7931E;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
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
            border-radius: 0;
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

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.12);
        }

        .stat-card {
            position: relative;
            overflow: hidden;
        }

        .stat-card .stat-icon {
            width: 55px;
            height: 55px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-card .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
        }

        .stat-card .stat-label {
            font-size: 13px;
            color: #718096;
        }

        .stat-card .stat-trend {
            font-size: 12px;
            font-weight: 600;
        }

        .plan-card {
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            text-align: center;
            padding: 20px;
            transition: all 0.3s;
        }

        .plan-card:hover {
            border-color: var(--primary);
            transform: scale(1.02);
        }

        .plan-card.current {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.1), rgba(247, 147, 30, 0.1));
        }

        .plan-card .plan-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .user-dropdown {
            position: relative;
        }

        .user-dropdown .dropdown-menu {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 10px;
        }

        .table-card {
            border-radius: 15px;
            overflow: hidden;
        }

        .table-card thead th {
            background: #f8f9fa;
            border-bottom: none;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #718096;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .quick-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            border-radius: 15px;
            text-decoration: none;
            transition: all 0.3s;
            color: white;
        }

        .quick-action:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .alert-box {
            border-radius: 12px;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .product-img {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            object-fit: cover;
        }

        .avatar-lg {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link active">
                            <i class="fas fa-chart-line"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="produtos.php" class="nav-link">
                            <i class="fas fa-pizza-slice"></i> Produtos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="vendas.php" class="nav-link">
                            <i class="fas fa-cash-register"></i> Vendas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="caixa.php" class="nav-link">
                            <i class="fas fa-money-bill-wave"></i> Caixa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="pedidos.php" class="nav-link">
                            <i class="fas fa-mobile-alt"></i> Pedidos Online
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="mesas.php" class="nav-link">
                            <i class="fas fa-chair"></i> Mesas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="relatorios.php" class="nav-link">
                            <i class="fas fa-chart-bar"></i> Relatórios
                        </a>
                    </li>
                    <?php if ($_SESSION['perfil'] == 'ADMIN'): ?>
                        <li class="nav-item">
                            <a href="usuarios.php" class="nav-link">
                                <i class="fas fa-users"></i> Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="configuracoes.php" class="nav-link">
                                <i class="fas fa-cog"></i> Configurações
                            </a>
                        </li>
                        <?php endif; ?>tura
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </a>
                        </li>
                </ul>
            </nav>

            <!-- CONTEÚDO PRINCIPAL -->
            <main class="main-content col-md-9 ms-sm-auto col-lg-10">

                <!-- TOP BAR -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-0">Olá, <?php echo htmlspecialchars($_SESSION['nome']); ?>! 👋</h4>
                        <p class="text-muted mb-0" style="font-size: 14px;"><?php echo date('d/m/Y - l'); ?></p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="user-dropdown dropdown">
                            <a href="#" class="d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown">
                                <?php
                                $foto_usuario = $_SESSION['foto'] ?? '';
                                if (!empty($foto_usuario)): ?>
                                    <img src="<?php echo htmlspecialchars($foto_usuario); ?>"
                                        alt="<?php echo htmlspecialchars($_SESSION['nome']); ?>" class="avatar-lg"
                                        onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nome']); ?>&background=FF6B35&color=fff&size=50'">
                                <?php else: ?>
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nome']); ?>&background=FF6B35&color=fff&size=50"
                                        alt="<?php echo htmlspecialchars($_SESSION['nome']); ?>" class="avatar-lg">
                                <?php endif; ?>
                                <span class="ms-2 d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['nome']); ?></span>
                                <i class="fas fa-chevron-down ms-2" style="font-size: 12px;"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="configuracoes.php"><i class="fas fa-user me-2"></i>Perfil</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- AVISO CAIXA FECHADO -->
                <?php if (!$caixa_aberto): ?>
                    <div class="alert alert-warning d-flex align-items-center alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div class="flex-grow-1">
                            <strong>Atenção!</strong> O caixa ainda não foi aberto hoje.
                        </div>
                        <a href="caixa.php" class="btn btn-warning btn-sm">Abrir Caixa</a>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- CARDS DE ESTATÍSTICAS -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="stat-value" style="color: #28a745;">
                                            <?php echo number_format($total_hoje, 2, ',', '.'); ?> MZN
                                        </div>
                                        <div class="stat-label">Vendas Hoje</div>
                                        <div class="stat-trend text-success">
                                            <i class="fas fa-arrow-up"></i> <?php echo $qtd_hoje; ?> vendas
                                        </div>
                                    </div>
                                    <div class="stat-icon" style="background: #d4edda; color: #28a745;">
                                        <i class="fas fa-shekel-sign"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="stat-value" style="color: #FF6B35;">
                                            <?php echo $total_produtos; ?>
                                        </div>
                                        <div class="stat-label">Produtos Ativos</div>
                                        <div class="stat-trend text-muted">
                                            <i class="fas fa-box"></i> No cardápio
                                        </div>
                                    </div>
                                    <div class="stat-icon" style="background: #ffe5d0; color: #FF6B35;">
                                        <i class="fas fa-pizza-slice"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="stat-value" style="color: #dc3545;">
                                            <?php echo $mesas_ocupadas; ?>/<?php echo $total_mesas; ?>
                                        </div>
                                        <div class="stat-label">Mesas Ocupadas</div>
                                        <div class="stat-trend text-<?php echo $mesas_ocupadas > 0 ? 'success' : 'muted'; ?>">
                                            <i class="fas fa-chair"></i> <?php echo $total_mesas - $mesas_ocupadas; ?> livres
                                        </div>
                                    </div>
                                    <div class="stat-icon" style="background: #f8d7da; color: #dc3545;">
                                        <i class="fas fa-chair"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="stat-value" style="color: <?php echo $caixa_aberto ? '#28a745' : '#dc3545'; ?>;">
                                            <?php echo $caixa_aberto ? 'ABERTO' : 'FECHADO'; ?>
                                        </div>
                                        <div class="stat-label">Status do Caixa</div>
                                        <div class="stat-trend text-<?php echo $caixa_aberto ? 'success' : 'danger'; ?>">
                                            <i class="fas fa-<?php echo $caixa_aberto ? 'lock-open' : 'lock'; ?>"></i>
                                            <?php echo $caixa_aberto ? 'Operacional' : 'Bloqueado'; ?>
                                        </div>
                                    </div>
                                    <div class="stat-icon" style="background: #d1ecf1; color: #17a2b8;">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GRÁFICO E PLANOS -->
                <div class="row g-4 mb-4">
                    <!-- Gráfico de Vendas -->
                    <div class="col-lg-8">
                        <div class="card h-100">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Vendas da Semana</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="salesChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Planos -->
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-crown me-2 text-warning"></i>Seu Plano</h5>
                                <?php if ($plano_atual != 'ENTERPRISE'): ?>
                                    <a href="configuracoes.php?secao=plano" class="btn btn-sm btn-primary">Upgrade</a>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="plan-card <?php echo $plano_atual == 'ENTERPRISE' ? 'current' : ''; ?> mb-3">
                                    <div class="plan-icon" style="background: linear-gradient(135deg, #FF6B35, #F7931E); color: white;">
                                        <i class="fas fa-<?php echo $plano_atual == 'ENTERPRISE' ? 'crown' : ($plano_atual == 'PROFISSIONAL' ? 'star' : 'user'); ?>"></i>
                                    </div>
                                    <h5 class="mb-1">Plano <?php echo $plano_atual; ?></h5>
                                    <p class="text-muted mb-2" style="font-size: 12px;">
                                        <?php if ($dias_restantes > 0): ?>
                                            Expira em <?php echo $dias_restantes; ?> dias
                                        <?php else: ?>
                                            Assinatura ativa
                                        <?php endif; ?>
                                    </p>
                                    <?php if ($plano_atual == 'ENTERPRISE'): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php endif; ?>
                                </div>

                                <!-- Comparação de Planos -->
                                <h6 class="mb-3" style="font-size: 13px; color: #718096;">Recursos do Plano</h6>
                                <ul class="list-unstyled" style="font-size: 13px;">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>Produtos ilimitados
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>Mesas ilimitadas
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>Relatórios avançados
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>Suporte prioritário
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABELAS -->
                <div class="row g-4 mb-4">
                    <!-- Últimas Vendas -->
                    <div class="col-lg-8">
                        <div class="card table-card">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-shopping-cart me-2 text-success"></i>Últimas Vendas</h5>
                                <a href="vendas.php" class="btn btn-sm btn-outline-primary">Ver Todas</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Fatura</th>
                                                <th>Horário</th>
                                                <th>Mesa</th>
                                                <th>Total</th>
                                                <th>Pagamento</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ultimas_vendas as $v): ?>
                                                <tr>
                                                    <td><strong><?php echo $v['numero_fatura']; ?></strong></td>
                                                    <td><?php echo date('H:i', strtotime($v['criado_em'])); ?></td>
                                                    <td><?php echo $v['mesa_numero'] ? 'Mesa ' . $v['mesa_numero'] : 'Balcão'; ?></td>
                                                    <td><strong><?php echo number_format($v['total_final'], 2, ',', '.'); ?></strong></td>
                                                    <td>
                                                        <span class="badge bg-<?php
                                                                                echo $v['forma_pagamento'] == 'DINHEIRO' ? 'success' : ($v['forma_pagamento'] == 'CARTAO' ? 'info' : 'secondary');
                                                                                ?>">
                                                            <?php echo $v['forma_pagamento']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge bg-<?php
                                                                                        echo $v['status'] == 'PAGO' ? 'success' : ($v['status'] == 'CANCELADO' ? 'danger' : 'warning');
                                                                                        ?> text-white">
                                                            <?php echo $v['status']; ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($ultimas_vendas)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center py-4 text-muted">
                                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                        Nenhuma venda hoje
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estoque Baixo -->
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Estoque Baixo</h5>
                                <a href="produtos.php" class="btn btn-sm btn-outline-warning">Ver Produtos</a>
                            </div>
                            <div class="card-body p-0">
                                <?php if (!empty($estoque_baixo)): ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($estoque_baixo as $ep): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                                <div class="d-flex align-items-center">
                                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($ep['nome']); ?>&background=ffe5d0&color=FF6B35&size=40"
                                                        alt="<?php echo $ep['nome']; ?>" class="product-img me-3">
                                                    <div>
                                                        <div class="fw-semibold"><?php echo htmlspecialchars($ep['nome']); ?></div>
                                                        <small class="text-muted">Mín: <?php echo $ep['estoque_minimo']; ?></small>
                                                    </div>
                                                </div>
                                                <span class="badge bg-danger rounded-pill"><?php echo $ep['estoque']; ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                        <p class="mb-0">Estoque em dia!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ATALHOS RÁPIDOS -->
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <a href="vendas.php" class="quick-action" style="background: linear-gradient(135deg, #FF6B35, #F7931E);">
                            <i class="fas fa-plus-circle fa-2x mb-2"></i>
                            <span>Nova Venda</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="produtos.php" class="quick-action" style="background: linear-gradient(135deg, #28a745, #20c997);">
                            <i class="fas fa-box fa-2x mb-2"></i>
                            <span>Produtos</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="mesas.php" class="quick-action" style="background: linear-gradient(135deg, #17a2b8, #6f42c1);">
                            <i class="fas fa-chair fa-2x mb-2"></i>
                            <span>Mesas</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="relatorios.php" class="quick-action" style="background: linear-gradient(135deg, #6c757d, #343a40);">
                            <i class="fas fa-chart-bar fa-2x mb-2"></i>
                            <span>Relatórios</span>
                        </a>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script do Gráfico -->
    <script>
        // Dados para o gráfico
        const ctx = document.getElementById('salesChart').getContext('2d');

        // Dados dos últimos 7 dias
        const labels = <?php echo json_encode(array_map(function ($d) {
                            return date('d/m', strtotime($d['data']));
                        }, $vendas_semana)); ?>;
        const data = <?php echo json_encode(array_map(function ($d) {
                            return floatval($d['total']);
                        }, $vendas_semana)); ?>;

        // Preencher com zeros se não houver dados
        while (labels.length < 7) {
            labels.unshift('-');
            data.unshift(0);
        }

        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Vendas (MZN)',
                    data: data,
                    borderColor: '#FF6B35',
                    backgroundColor: 'rgba(255, 107, 53, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#FF6B35',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>

</body>

</html>
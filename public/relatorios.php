<?php
// Proteção da página
include_once '../config/auth_check.php';
include_once '../config/database.php';
include_once '../app/Venda.php';

$database = new Database();
$db = $database->getConnection();
$venda = new Venda($db);

$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-01');
$data_fim    = isset($_GET['data_fim'])    ? $_GET['data_fim']    : date('Y-m-d');
$totais = $venda->totalVendas($_SESSION['restaurante_id'], $data_inicio, $data_fim);

$stmt_pag = $db->prepare("SELECT forma_pagamento, COUNT(*) as qtd, SUM(total_final) as total FROM vendas WHERE restaurante_id = ? AND DATE(criado_em) BETWEEN ? AND ? AND status = 'PAGO' GROUP BY forma_pagamento ORDER BY total DESC");
$stmt_pag->execute([$_SESSION['restaurante_id'], $data_inicio, $data_fim]);
$por_pagamento = $stmt_pag->fetchAll(PDO::FETCH_ASSOC);

$stmt_prod = $db->prepare("SELECT p.nome, SUM(iv.quantidade) as qtd_total, SUM(iv.subtotal) as valor_total FROM itens_venda iv INNER JOIN produtos p ON iv.produto_id = p.id INNER JOIN vendas v ON iv.venda_id = v.id WHERE v.restaurante_id = ? AND DATE(v.criado_em) BETWEEN ? AND ? AND v.status = 'PAGO' GROUP BY p.id, p.nome ORDER BY qtd_total DESC LIMIT 10");
$stmt_prod->execute([$_SESSION['restaurante_id'], $data_inicio, $data_fim]);
$top_produtos = $stmt_prod->fetchAll(PDO::FETCH_ASSOC);

$stmt_dias = $db->prepare("SELECT DATE(criado_em) as dia, COUNT(*) as qtd, SUM(total_final) as total FROM vendas WHERE restaurante_id = ? AND DATE(criado_em) BETWEEN ? AND ? AND status = 'PAGO' GROUP BY DATE(criado_em) ORDER BY dia ASC");
$stmt_dias->execute([$_SESSION['restaurante_id'], $data_inicio, $data_fim]);
$vendas_por_dia = $stmt_dias->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Sistema de Restaurante</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
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
            <nav class="sidebar col-md-3 col-lg-2 d-md-block">
                <div class="brand">
                    <h3><i class="fas fa-utensils me-2"></i>RestauranteSaaS</h3>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                    <li class="nav-item"><a href="produtos.php" class="nav-link"><i class="fas fa-pizza-slice"></i> Produtos</a></li>
                    <li class="nav-item"><a href="vendas.php" class="nav-link"><i class="fas fa-cash-register"></i> Vendas</a></li>
                    <li class="nav-item"><a href="caixa.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Caixa</a></li>
                    <li class="nav-item"><a href="pedidos.php" class="nav-link"><i class="fas fa-mobile-alt"></i> Pedidos Online</a></li>
                    <li class="nav-item"><a href="mesas.php" class="nav-link"><i class="fas fa-chair"></i> Mesas</a></li>
                    <li class="nav-item"><a href="relatorios.php" class="nav-link active"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
                    <?php if ($_SESSION['perfil'] == 'ADMIN'): ?>
                        <li class="nav-item"><a href="usuarios.php" class="nav-link"><i class="fas fa-users"></i> Usuários</a></li>
                        <li class="nav-item"><a href="configuracoes.php" class="nav-link"><i class="fas fa-cog"></i> Configurações</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                </ul>
            </nav>

            <main class="main-content col-md-9 ms-sm-auto col-lg-10">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-0"><i class="fas fa-chart-bar text-primary me-2"></i>Relatórios</h4>
                        <p class="text-muted mb-0" style="font-size: 14px;">Análise de vendas e desempenho</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-info btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i> Imprimir</button>
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nome']); ?>&background=FF6B35&color=fff&size=50" class="avatar-lg">
                    </div>
                </div>

                <!-- FILTRO -->
                <form method="GET" action="relatorios.php">
                    <div class="card mb-4">
                        <div class="card-body d-flex align-items-end gap-3 flex-wrap">
                            <div class="flex-grow-1">
                                <label class="form-label">Data Início</label>
                                <input type="date" name="data_inicio" class="form-control" value="<?php echo $data_inicio; ?>">
                            </div>
                            <div class="flex-grow-1">
                                <label class="form-label">Data Fim</label>
                                <input type="date" name="data_fim" class="form-control" value="<?php echo $data_fim; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-2"></i>Filtrar</button>
                            <a href="relatorios.php" class="btn btn-outline-secondary">Mês Atual</a>
                        </div>
                    </div>
                </form>

                <!-- STATS -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card h-100 border-success">
                            <div class="card-body">
                                <div class="text-muted small">Total de Vendas</div>
                                <div class="fw-bold text-success" style="font-size: 28px;"><?php echo number_format($totais['valor_total'] ?? 0, 2, ',', '.'); ?> MZN</div>
                                <div class="text-muted small"><?php echo $totais['total'] ?? 0; ?> vendas no período</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-info">
                            <div class="card-body">
                                <div class="text-muted small">Ticket Médio</div>
                                <div class="fw-bold text-info" style="font-size: 28px;"><?php $ticket = ($totais['total'] > 0) ? ($totais['valor_total'] / $totais['total']) : 0;
                                                                                        echo number_format($ticket, 2, ',', '.'); ?> MZN</div>
                                <div class="text-muted small">Valor médio por venda</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100" style="border-left: 4px solid #FF6B35;">
                            <div class="card-body">
                                <div class="text-muted small">Período</div>
                                <div class="fw-bold" style="font-size: 18px;"><?php echo date('d/m/Y', strtotime($data_inicio)); ?> - <?php echo date('d/m/Y', strtotime($data_fim)); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GRÁFICO -->
                <div class="card mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Vendas por Dia</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($vendas_por_dia)): ?>
                            <?php $max_valor = max(array_column($vendas_por_dia, 'total')); ?>
                            <div class="d-flex align-items-end gap-2" style="height: 200px;">
                                <?php foreach ($vendas_por_dia as $dia): ?>
                                    <?php $altura = $max_valor > 0 ? ($dia['total'] / $max_valor) * 180 : 4; ?>
                                    <div class="flex-fill text-center">
                                        <div class="bg-gradient" style="height: <?php echo max($altura, 4); ?>px; background: linear-gradient(180deg, #FF6B35, #F7931E); border-radius: 4px 4px 0 0;" title="<?php echo number_format($dia['total'], 2, ',', '.'); ?> MZN"></div>
                                        <div class="small text-muted mt-1"><?php echo date('d/m', strtotime($dia['dia'])); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-4">Nenhuma venda no período</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- PAGAMENTOS -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Por Forma de Pagamento</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($por_pagamento)): ?>
                                    <?php $total_geral = array_sum(array_column($por_pagamento, 'total')); ?>
                                    <?php foreach ($por_pagamento as $pag): ?>
                                        <?php $pct = $total_geral > 0 ? ($pag['total'] / $total_geral * 100) : 0; ?>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span><?php echo $pag['forma_pagamento']; ?></span>
                                                <strong><?php echo number_format($pag['total'], 2, ',', '.'); ?> MZN</strong>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-warning" style="width: <?php echo $pct; ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?php echo $pag['qtd']; ?> vendas · <?php echo number_format($pct, 1); ?>%</small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center py-3">Sem dados</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- TOP PRODUTOS -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="fas fa-trophy me-2 text-warning"></i>Produtos Mais Vendidos</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (!empty($top_produtos)): ?>
                                    <?php foreach ($top_produtos as $i => $prod): ?>
                                        <div class="d-flex align-items-center p-3 border-bottom">
                                            <div class="fw-bold me-3" style="width: 24px; height: 24px; border-radius: 50%; background: <?php echo $i == 0 ? '#FFD700' : ($i == 1 ? '#C0C0C0' : ($i == 2 ? '#CD7F32' : '#f8f9fa')); ?>; display: flex; align-items: center; justify-content: center; font-size: 12px; color: <?php echo $i < 3 ? '#333' : '#666'; ?>;">
                                                <?php echo $i + 1; ?>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold"><?php echo $prod['nome']; ?></div>
                                                <small class="text-muted"><?php echo $prod['qtd_total']; ?> unidades</small>
                                            </div>
                                            <div class="fw-bold text-primary"><?php echo number_format($prod['valor_total'], 2, ',', '.'); ?> MZN</div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center py-3">Sem dados</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
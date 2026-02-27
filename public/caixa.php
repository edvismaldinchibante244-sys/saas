<?php
// Proteção da página
include_once '../config/auth_check.php';
include_once '../config/database.php';
include_once '../app/Caixa.php';
include_once '../app/Venda.php';

$database = new Database();
$db = $database->getConnection();

$caixa = new Caixa($db);
$caixa_aberto = $caixa->caixaAbertoHoje($_SESSION['restaurante_id']);

$total_vendas = 0;
if ($caixa_aberto) {
    $total_vendas = $caixa->totalVendas($caixa_aberto['id']);
}

$stmt_historico = $caixa->listar($_SESSION['restaurante_id'], 15);
$historico = $stmt_historico->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caixa - Sistema de Restaurante</title>
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
                    <li class="nav-item"><a href="caixa.php" class="nav-link active"><i class="fas fa-money-bill-wave"></i> Caixa</a></li>
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

            <main class="main-content col-md-9 ms-sm-auto col-lg-10">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-0"><i class="fas fa-money-bill-wave text-success me-2"></i>Controle de Caixa</h4>
                        <p class="text-muted mb-0" style="font-size: 14px;">Abertura e fechamento de caixa</p>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nome']); ?>&background=FF6B35&color=fff&size=50" class="avatar-lg">
                </div>

                <?php if ($caixa_aberto): ?>
                    <div class="card border-success mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="text-success mb-1"><i class="fas fa-check-circle me-2"></i>Caixa Aberto</h5>
                                    <p class="text-muted mb-0" style="font-size: 14px;">
                                        Aberto em: <?php echo date('d/m/Y H:i', strtotime($caixa_aberto['criado_em'])); ?>
                                        &bull; Operador: <?php echo htmlspecialchars($caixa_aberto['usuario_nome'] ?? $_SESSION['nome']); ?>
                                    </p>
                                </div>
                                <button class="btn btn-danger" onclick="abrirModalFechar()"><i class="fas fa-lock me-2"></i>Fechar Caixa</button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="bg-light rounded p-3 text-center">
                                        <div class="fw-bold text-dark" style="font-size: 22px;"><?php echo number_format($caixa_aberto['abertura'], 2, ',', '.'); ?> MZN</div>
                                        <div class="text-muted small">Valor de Abertura</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-light rounded p-3 text-center">
                                        <div class="fw-bold text-success" style="font-size: 22px;"><?php echo number_format($total_vendas, 2, ',', '.'); ?> MZN</div>
                                        <div class="text-muted small">Total em Vendas</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="rounded p-3 text-center" style="background: linear-gradient(135deg, #FF6B35, #F7931E);">
                                        <div class="fw-bold text-white" style="font-size: 22px;"><?php echo number_format($caixa_aberto['abertura'] + $total_vendas, 2, ',', '.'); ?> MZN</div>
                                        <div class="text-white-50 small">Saldo Esperado</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card border-danger mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-danger mb-1"><i class="fas fa-lock me-2"></i>Caixa Fechado</h5>
                                    <p class="text-muted mb-0">Abra o caixa para iniciar as vendas do dia.</p>
                                </div>
                                <button class="btn btn-success" onclick="abrirModalAbrir()"><i class="fas fa-lock-open me-2"></i>Abrir Caixa</button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Histórico de Caixas</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Operador</th>
                                        <th>Abertura</th>
                                        <th>Fechamento</th>
                                        <th>Total Vendas</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historico as $h): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($h['data'])); ?></td>
                                            <td><?php echo htmlspecialchars($h['usuario_nome'] ?? '—'); ?></td>
                                            <td><?php echo number_format($h['abertura'], 2, ',', '.'); ?> MZN</td>
                                            <td><?php echo $h['fechamento'] ? number_format($h['fechamento'], 2, ',', '.') . ' MZN' : '<span class="text-muted">—</span>'; ?></td>
                                            <td><?php echo number_format($caixa->totalVendas($h['id']), 2, ',', '.'); ?> MZN</td>
                                            <td><span class="badge bg-<?php echo $h['status'] == 'ABERTO' ? 'success' : 'danger'; ?>"><?php echo $h['status']; ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($historico)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Nenhum registro</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- MODAL ABRIR CAIXA -->
    <div class="modal fade" id="modalAbrir" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-lock-open me-2"></i>Abrir Caixa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Informe o valor inicial em dinheiro no caixa.</p>
                    <div class="alert" id="alertAbrir" style="display: none;"></div>
                    <form id="formAbrir">
                        <div class="mb-3">
                            <label class="form-label">Valor de Abertura (MZN) *</label>
                            <input type="number" id="valor_abertura" class="form-control" step="0.01" min="0" required placeholder="0.00">
                            <small class="text-muted">Valor em dinheiro para troco</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formAbrir" class="btn btn-success"><i class="fas fa-check me-2"></i>Abrir Caixa</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL FECHAR CAIXA -->
    <div class="modal fade" id="modalFechar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-lock me-2"></i>Fechar Caixa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert" id="alertFechar" style="display: none;"></div>
                    <form id="formFechar">
                        <div class="mb-3">
                            <label class="form-label">Valor de Fechamento (MZN) *</label>
                            <input type="number" id="valor_fechamento" class="form-control" step="0.01" min="0" required placeholder="0.00">
                        </div>
                        <div class="bg-light rounded p-3">
                            <div class="d-flex justify-content-between mb-2"><span>Abertura:</span><strong><?php echo $caixa_aberto ? number_format($caixa_aberto['abertura'], 2, ',', '.') : '0,00'; ?> MZN</strong></div>
                            <div class="d-flex justify-content-between mb-2"><span>Vendas:</span><strong><?php echo number_format($total_vendas, 2, ',', '.'); ?> MZN</strong></div>
                            <hr>
                            <div class="d-flex justify-content-between"><span>Esperado:</span><strong class="text-success"><?php $esperado = $caixa_aberto ? ($caixa_aberto['abertura'] + $total_vendas) : 0;
                                                                                                                            echo number_format($esperado, 2, ',', '.'); ?> MZN</strong></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formFechar" class="btn btn-danger"><i class="fas fa-lock me-2"></i>Fechar Caixa</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/caixa.js"></script>
</body>

</html>
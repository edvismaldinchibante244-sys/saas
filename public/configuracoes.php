<?php
// Proteção da página - apenas ADMIN
include_once '../config/auth_check.php';
checkPermission(['ADMIN']);

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM restaurantes WHERE id = :id LIMIT 1";
$stmt  = $db->prepare($query);
$stmt->bindParam(':id', $_SESSION['restaurante_id']);
$stmt->execute();
$restaurante = $stmt->fetch(PDO::FETCH_ASSOC);

$mensagem = '';
$tipo_msg = '';

// Verificar se há uma seção específica para mostrar
$secao = $_GET['secao'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $cidade   = trim($_POST['cidade'] ?? '');
    $nuit     = trim($_POST['nuit'] ?? '');

    if (empty($nome)) {
        $mensagem = 'O nome do restaurante é obrigatório.';
        $tipo_msg = 'danger';
    } else {
        $query_upd = "UPDATE restaurantes SET nome = :nome, telefone = :telefone, endereco = :endereco, cidade = :cidade, nuit = :nuit WHERE id = :id";
        $stmt_upd  = $db->prepare($query_upd);
        $stmt_upd->bindParam(':nome',     $nome);
        $stmt_upd->bindParam(':telefone', $telefone);
        $stmt_upd->bindParam(':endereco', $endereco);
        $stmt_upd->bindParam(':cidade',   $cidade);
        $stmt_upd->bindParam(':nuit',     $nuit);
        $stmt_upd->bindParam(':id',       $_SESSION['restaurante_id']);

        if ($stmt_upd->execute()) {
            $mensagem = 'Configurações salvas com sucesso!';
            $tipo_msg = 'success';
            $stmt->execute();
            $restaurante = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $mensagem = 'Erro ao salvar configurações.';
            $tipo_msg = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - Sistema de Restaurante</title>

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

        .plan-option {
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
        }

        .plan-option:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .plan-option.selected {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.1), rgba(247, 147, 30, 0.1));
        }

        .plan-option.current {
            border-color: #28a745;
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(40, 167, 69, 0.05));
        }

        .plan-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 10px;
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
                    <li class="nav-item"><a href="relatorios.php" class="nav-link"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
                    <li class="nav-item"><a href="usuarios.php" class="nav-link"><i class="fas fa-users"></i> Usuários</a></li>
                    <li class="nav-item"><a href="configuracoes.php" class="nav-link active"><i class="fas fa-cog"></i> Configurações</a></li>
                    <li class="nav-item"><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                </ul>
            </nav>

            <main class="main-content col-md-9 ms-sm-auto col-lg-10">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-0"><i class="fas fa-cog text-primary me-2"></i>Configurações</h4>
                        <p class="text-muted mb-0" style="font-size: 14px;">Dados do restaurante e assinatura</p>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nome']); ?>&background=FF6B35&color=fff&size=50" class="avatar-lg">
                </div>

                <?php if ($mensagem): ?>
                    <div class="alert alert-<?php echo $tipo_msg; ?> alert-dismissible fade show" role="alert">
                        <?php echo $mensagem; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- PLANO ATUAL -->
                <?php if ($secao !== 'plano'): ?>
                    <div class="card mb-4" style="background: linear-gradient(135deg, #FF6B35, #F7931E); color: white;">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="mb-1"><i class="fas fa-crown me-2"></i>Plano <?php echo $restaurante['plano']; ?></h5>
                                    <p class="mb-0 opacity-75">
                                        Status: <strong><?php echo $restaurante['status']; ?></strong> |
                                        Válido até: <strong><?php echo date('d/m/Y', strtotime($restaurante['data_fim'])); ?></strong>
                                    </p>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <?php
                                    $dias_restantes = ceil((strtotime($restaurante['data_fim']) - time()) / 86400);
                                    ?>
                                    <div class="fs-4 fw-bold"><?php echo $dias_restantes; ?> dias</div>
                                    <small>restantes</small>
                                    <?php if ($restaurante['plano'] != 'ENTERPRISE'): ?>
                                        <div class="mt-2">
                                            <a href="configuracoes.php?secao=plano" class="btn btn-light btn-sm">
                                                <i class="fas fa-arrow-up me-1"></i> Upgrade
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- SEÇÃO PLANO (quando acessar via upgrade) -->
                <?php if ($secao === 'plano'): ?>
                    <div class="card mb-4" id="plano">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-crown me-2 text-warning"></i>Planos de Assinatura</h5>
                            <a href="configuracoes.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i> Fechar
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <!-- Plano Básico -->
                                <div class="col-md-4">
                                    <div class="plan-option <?php echo $restaurante['plano'] === 'BASICO' ? 'current selected' : ''; ?>">
                                        <div class="plan-icon" style="background: linear-gradient(135deg, #6c757d, #343a40); color: white;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <h5>Básico</h5>
                                        <div class="fs-3 fw-bold text-muted">Grátis<span class="small text-muted">/mês</span></div>
                                        <hr>
                                        <ul class="list-unstyled text-start small">
                                            <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Até 50 produtos</li>
                                            <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Até 10 mesas</li>
                                            <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Relatórios básicos</li>
                                        </ul>
                                        <span class="badge bg-secondary">Plano Atual</span>
                                    </div>
                                </div>

                                <!-- Plano Profissional -->
                                <div class="col-md-4">
                                    <div class="plan-option <?php echo $restaurante['plano'] === 'PROFISSIONAL' ? 'current selected' : ''; ?>">
                                        <div class="plan-icon" style="background: linear-gradient(135deg, #17a2b8, #0dcaf0); color: white;">
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <h5>Profissional</h5>
                                        <div class="fs-3 fw-bold text-primary">1.500 MZN<span class="small text-muted">/mês</span></div>
                                        <hr>
                                        <ul class="list-unstyled text-start small">
                                            <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Produtos ilimitados</li>
                                            <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Mesas ilimitadas</li>
                                            <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Relatórios avançados</li>
                                            <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Pedidos online (QR)</li>
                                        </ul>
                                        <?php if ($restaurante['plano'] === 'PROFISSIONAL'): ?>
                                            <span class="badge bg-success">Plano Atual</span>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#modalCompraPlano" onclick="selecionarPlano('PROFISSIONAL', 1500)">
                                                <i class="fas fa-shopping-cart me-1"></i> Comprar
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Plano Enterprise -->
                                <div class="col-md-4">
                                    <div class="plan-option <?php echo $restaurante['plano'] === 'ENTERPRISE' ? 'current selected' : ''; ?>">
                                        <div class="plan-icon" style="background: linear-gradient(135deg, #FF6B35, #F7931E); color: white;">
                                            <i class="fas fa-crown"></i>
                                        </div>
                                        <h5>Enterprise</h5>
                                        <div class="fs-3 fw-bold text-warning">3.000 MZN<span class="small text-muted">/mês</span></div>
                                        <hr>
                                        <ul class="list-unstyled text-start small">
                                            <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Tudo do Profissional</li>
                                            <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Múltiplos restaurantes</li>
                                            <li class="mb-1"><i class="fas fa-check text-success me-2"></i>API de integração</li>
                                            <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Suporte prioritário 24/7</li>
                                        </ul>
                                        <?php if ($restaurante['plano'] === 'ENTERPRISE'): ?>
                                            <span class="badge bg-success">Plano Atual</span>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-warning btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#modalCompraPlano" onclick="selecionarPlano('ENTERPRISE', 3000)">
                                                <i class="fas fa-shopping-cart me-1"></i> Comprar
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- HISTÓRICO DE COMPRAS -->
                    <div class="card mt-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Histórico de Compras</h5>
                        </div>
                        <div class="card-body">
                            <div id="historicoCompras">
                                <p class="text-center text-muted">Carregando...</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row g-4 mt-2">
                    <!-- DADOS DO RESTAURANTE -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="fas fa-store me-2"></i>Dados do Restaurante</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="configuracoes.php">
                                    <div class="mb-3">
                                        <label class="form-label">Nome do Restaurante *</label>
                                        <input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($restaurante['nome']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Telefone</label>
                                        <input type="text" name="telefone" class="form-control" value="<?php echo htmlspecialchars($restaurante['telefone'] ?? ''); ?>" placeholder="+258 84 000 0000">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Endereço</label>
                                        <input type="text" name="endereco" class="form-control" value="<?php echo htmlspecialchars($restaurante['endereco'] ?? ''); ?>" placeholder="Av. Eduardo Mondlane, 123">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Cidade</label>
                                        <input type="text" name="cidade" class="form-control" value="<?php echo htmlspecialchars($restaurante['cidade'] ?? ''); ?>" placeholder="Maputo">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">NUIT</label>
                                        <input type="text" name="nuit" class="form-control" value="<?php echo htmlspecialchars($restaurante['nuit'] ?? ''); ?>" placeholder="400000000">
                                    </div>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Salvar Alterações</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- MINHA CONTA -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Minha Conta</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nome']); ?>&background=FF6B35&color=fff&size=60" class="rounded-circle me-3">
                                    <div>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['nome']); ?></div>
                                        <span class="badge bg-<?php echo $_SESSION['perfil'] == 'ADMIN' ? 'danger' : 'info'; ?>"><?php echo $_SESSION['perfil']; ?></span>
                                    </div>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between py-2">
                                    <span class="text-muted">Restaurante ID</span>
                                    <strong>#<?php echo $_SESSION['restaurante_id']; ?></strong>
                                </div>
                                <a href="usuarios.php" class="btn btn-outline-primary w-100 mt-3"><i class="fas fa-users me-2"></i>Gerenciar Usuários</a>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informações do Sistema</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Versão</span>
                                    <strong>1.0.0</strong>
                                </div>
                                <div class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">PHP</span>
                                    <strong><?php echo phpversion(); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between py-2">
                                    <span class="text-muted">Suporte</span>
                                    <strong>suporte@sabormoz.co.mz</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Modal de Compra de Plano -->
    <div class="modal fade" id="modalCompraPlano" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-shopping-cart me-2"></i>Confirmar Compra de Plano</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="alertModalPlano" class="alert" style="display: none;"></div>

                    <p><strong>Plano:</strong> <span id="planoSelecionadoNome"></span></p>
                    <p><strong>Valor:</strong> <span id="planoSelecionadoValor"></span> MZN/mês</p>

                    <div class="mb-3">
                        <label class="form-label">Método de Pagamento *</label>
                        <select class="form-select" id="metodoPagamento">
                            <option value="">Selecione...</option>
                            <option value="DINHEIRO">💵 Dinheiro</option>
                            <option value="MPESA">📱 M-Pesa</option>
                            <option value="CARTAO">💳 Cartão</option>
                            <option value="TRANSFERENCIA">🏦 Transferência</option>
                        </select>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Após confirmar, aguarde a verificação do pagamento. O plano será ativado após aprovação do administrador.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="confirmarCompraPlano()">
                        <i class="fas fa-check me-1"></i> Confirmar Pedido
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var planoAtual = '';
        var valorAtual = 0;

        // Selecionar plano
        function selecionarPlano(plano, valor) {
            planoAtual = plano;
            valorAtual = valor;
            document.getElementById('planoSelecionadoNome').textContent = plano;
            document.getElementById('planoSelecionadoValor').textContent = valor;
            document.getElementById('metodoPagamento').value = '';
            document.getElementById('alertModalPlano').style.display = 'none';
        }

        // Confirmar compra
        function confirmarCompraPlano() {
            var metodo = document.getElementById('metodoPagamento').value;
            var alertDiv = document.getElementById('alertModalPlano');

            if (!metodo) {
                alertDiv.className = 'alert alert-danger';
                alertDiv.textContent = 'Selecione o método de pagamento!';
                alertDiv.style.display = 'block';
                return;
            }

            // Criar form data
            var formData = new FormData();
            formData.append('plano', planoAtual);
            formData.append('metodo', metodo);

            // Chamar API
            fetch('api/plano_comprar.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        alertDiv.className = 'alert alert-success';
                        alertDiv.textContent = data.message;
                        alertDiv.style.display = 'block';

                        // Fechar modal e recarregar após 2 segundos
                        setTimeout(function() {
                            var modal = bootstrap.Modal.getInstance(document.getElementById('modalCompraPlano'));
                            modal.hide();
                            window.location.reload();
                        }, 2000);
                    } else {
                        alertDiv.className = 'alert alert-danger';
                        alertDiv.textContent = data.message;
                        alertDiv.style.display = 'block';
                    }
                })
                .catch(function(err) {
                    alertDiv.className = 'alert alert-danger';
                    alertDiv.textContent = 'Erro: ' + err.message;
                    alertDiv.style.display = 'block';
                });
        }

        // Carregar histórico de compras
        <?php if ($secao === 'plano'): ?>
            fetch('api/plano_listar.php', {
                    credentials: 'same-origin'
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(data) {
                    var container = document.getElementById('historicoCompras');
                    if (data.success && data.data.length > 0) {
                        var html = '<table class="table table-hover"><thead><tr><th>ID</th><th>De</th><th>Para</th><th>Valor</th><th>Método</th><th>Status</th><th>Data</th></tr></thead><tbody>';
                        data.data.forEach(function(c) {
                            var statusClass = c.status === 'APROVADO' ? 'success' : (c.status === 'PENDENTE' ? 'warning' : 'danger');
                            html += '<tr><td>#' + c.id + '</td><td>' + c.plano_atual + '</td><td>' + c.plano_novo + '</td><td>' + parseFloat(c.valor).toFixed(2) + ' MZN</td><td>' + c.metodo_pagamento + '</td><td><span class="badge bg-' + statusClass + '">' + c.status + '</span></td><td>' + new Date(c.criado_em).toLocaleDateString('pt-BR') + '</td></tr>';
                        });
                        html += '</tbody></table>';
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '<p class="text-center text-muted">Nenhuma compra encontrada</p>';
                    }
                })
                .catch(function(err) {
                    console.error('Erro ao carregar compras:', err);
                    document.getElementById('historicoCompras').innerHTML = '<p class="text-center text-danger">Erro ao carregar histórico</p>';
                });
        <?php endif; ?>
    </script>
</body>

</html>
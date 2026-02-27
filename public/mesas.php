<?php
// Proteção da página
include_once '../config/auth_check.php';
include_once '../config/database.php';
include_once '../app/Mesa.php';

// Conectar ao banco
$database = new Database();
$db = $database->getConnection();

// calcular base URL (sem barra no final) para gerar links de cardápio
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST']
    . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

$mesa = new Mesa($db);
$mesas = $mesa->listar($_SESSION['restaurante_id']);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesas - Sistema de Restaurante</title>

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

        .mesa-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            border: 3px solid transparent;
        }

        .mesa-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.12);
        }

        .mesa-card.livre {
            border-color: #28a745;
        }

        .mesa-card.ocupada {
            border-color: #dc3545;
            background: #fff5f5;
        }

        .mesa-card.reservada {
            border-color: #ffc107;
            background: #fffdf0;
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
                    <li class="nav-item"><a href="vendas.php" class="nav-link"><i class="fas fa-cash-register"></i> Vendas</a></li>
                    <li class="nav-item"><a href="caixa.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Caixa</a></li>
                    <li class="nav-item"><a href="pedidos.php" class="nav-link"><i class="fas fa-mobile-alt"></i> Pedidos Online</a></li>
                    <li class="nav-item"><a href="mesas.php" class="nav-link active"><i class="fas fa-chair"></i> Mesas</a></li>
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
                        <h4 class="mb-0"><i class="fas fa-chair text-primary me-2"></i>Gestão de Mesas</h4>
                        <p class="text-muted mb-0" style="font-size: 14px;">Controle de ocupação das mesas</p>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nome']); ?>&background=FF6B35&color=fff&size=50" class="avatar-lg">
                </div>

                <?php
                // Calcular estatísticas
                $total = 0;
                $livres = 0;
                $ocupadas = 0;
                $reservadas = 0;
                $todas_mesas = [];
                while ($m = $mesas->fetch(PDO::FETCH_ASSOC)) {
                    $todas_mesas[] = $m;
                    $total++;
                    if ($m['status'] == 'LIVRE') $livres++;
                    if ($m['status'] == 'OCUPADA') $ocupadas++;
                    if ($m['status'] == 'RESERVADA') $reservadas++;
                }
                ?>

                <!-- STATS -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="fw-bold text-dark" style="font-size: 32px;"><?php echo $total; ?></div>
                                <div class="text-muted">Total de Mesas</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card h-100 border-success">
                            <div class="card-body text-center">
                                <div class="fw-bold text-success" style="font-size: 32px;"><?php echo $livres; ?></div>
                                <div class="text-muted"><i class="fas fa-circle text-success me-1" style="font-size: 10px;"></i>Livres</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card h-100 border-danger">
                            <div class="card-body text-center">
                                <div class="fw-bold text-danger" style="font-size: 32px;"><?php echo $ocupadas; ?></div>
                                <div class="text-muted"><i class="fas fa-circle text-danger me-1" style="font-size: 10px;"></i>Ocupadas</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card h-100 border-warning">
                            <div class="card-body text-center">
                                <div class="fw-bold text-warning" style="font-size: 32px;"><?php echo $reservadas; ?></div>
                                <div class="text-muted"><i class="fas fa-circle text-warning me-1" style="font-size: 10px;"></i>Reservadas</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LEGENDA E BOTÃO -->
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <div class="d-flex gap-3">
                        <span class="badge bg-success"><i class="fas fa-circle me-1"></i>Livre</span>
                        <span class="badge bg-danger"><i class="fas fa-circle me-1"></i>Ocupada</span>
                        <span class="badge bg-warning text-dark"><i class="fas fa-circle me-1"></i>Reservada</span>
                    </div>
                    <?php if ($_SESSION['perfil'] == 'ADMIN'): ?>
                        <button class="btn btn-primary" onclick="abrirModalNovaMesa()"><i class="fas fa-plus me-2"></i>Nova Mesa</button>
                    <?php endif; ?>
                </div>

                <!-- GRID DE MESAS -->
                <div class="row g-4">
                    <?php foreach ($todas_mesas as $m): ?>
                        <?php $status_lower = strtolower($m['status']); ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="mesa-card <?php echo $status_lower; ?>">
                                <div style="font-size: 40px; margin-bottom: 10px;">
                                    <?php echo $m['status'] == 'LIVRE' ? '🪑' : ($m['status'] == 'OCUPADA' ? '👥' : '📋'); ?>
                                </div>
                                <div class="fw-bold" style="font-size: 20px;">Mesa <?php echo $m['numero']; ?></div>
                                <div class="text-muted small mb-2">👤 <?php echo $m['capacidade'] ?? 4; ?> pessoas</div>
                                <!-- QR code para cardápio online -->
                                <?php
                                    $link = $baseUrl . '/cardapio.php?rid=' . $_SESSION['restaurante_id'] . '&mesa_id=' . $m['id'];
                                ?>
                                <div class="mt-2">
                                    <a href="<?php echo htmlspecialchars($link); ?>" target="_blank" title="Abrir cardápio da mesa">
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php echo urlencode($link); ?>" alt="QR Code" />
                                    </a>
                                </div>
                                <span class="badge bg-<?php echo $status_lower == 'livre' ? 'success' : ($status_lower == 'ocupada' ? 'danger' : 'warning'); ?>">
                                    <?php echo $m['status']; ?>
                                </span>
                                <div class="mt-3 d-flex gap-2 justify-content-center">
                                    <?php if ($m['status'] != 'LIVRE'): ?>
                                        <button class="btn btn-sm btn-success" onclick="atualizarMesa(<?php echo $m['id']; ?>, 'LIVRE')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($m['status'] != 'OCUPADA'): ?>
                                        <button class="btn btn-sm btn-danger" onclick="atualizarMesa(<?php echo $m['id']; ?>, 'OCUPADA')">
                                            <i class="fas fa-user"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($m['status'] != 'RESERVADA'): ?>
                                        <button class="btn btn-sm btn-warning" onclick="atualizarMesa(<?php echo $m['id']; ?>, 'RESERVADA')">
                                            <i class="fas fa-calendar"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($todas_mesas)): ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-chair fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted">Nenhuma mesa cadastrada</p>
                            <button class="btn btn-primary" onclick="abrirModalNovaMesa()"><i class="fas fa-plus me-2"></i>Nova Mesa</button>
                        </div>
                    <?php endif; ?>
                </div>

            </main>
        </div>
    </div>

    <!-- MODAL NOVA MESA -->
    <div class="modal fade" id="modalNovaMesa" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Nova Mesa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert" id="alertMesa" style="display: none;"></div>
                    <form id="formNovaMesa">
                        <div class="mb-3">
                            <label class="form-label">Número da Mesa *</label>
                            <input type="number" id="mesa_numero" name="numero" class="form-control" min="1" required placeholder="Ex: 9">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Capacidade (pessoas) *</label>
                            <input type="number" id="mesa_capacidade" name="capacidade" class="form-control" min="1" value="4" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formNovaMesa" class="btn btn-primary"><i class="fas fa-save me-2"></i>Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/mesas.js"></script>
</body>

</html>
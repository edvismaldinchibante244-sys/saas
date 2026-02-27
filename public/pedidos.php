<?php
// Proteção da página
include_once '../config/auth_check.php';
include_once '../config/database.php';
include_once '../app/Mesa.php';

// Conectar ao banco
$database = new Database();
$db = $database->getConnection();

// URL base para QR Code
$protocol     = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host         = $_SERVER['HTTP_HOST'];
$base_url     = $protocol . '://' . $host;
$script_dir   = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$cardapio_url = $base_url . $script_dir . '/cardapio.php?rid=' . $_SESSION['restaurante_id'];

// Buscar mesas
$mesa_obj    = new Mesa($db);
$stmt_mesas  = $mesa_obj->listar($_SESSION['restaurante_id']);
$todas_mesas = $stmt_mesas->fetchAll(PDO::FETCH_ASSOC);

// Buscar pedidos do dia
$query = "SELECT p.*, m.numero as mesa_numero,
          (SELECT COUNT(*) FROM itens_pedido WHERE pedido_id = p.id) as total_itens
          FROM pedidos p
          LEFT JOIN mesas m ON p.mesa_id = m.id
          WHERE p.restaurante_id = :rid
          AND DATE(p.criado_em) = CURDATE()
          ORDER BY p.criado_em DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':rid', $_SESSION['restaurante_id']);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contadores por status
$contadores = ['NOVO' => 0, 'PREPARANDO' => 0, 'PRONTO' => 0, 'ENTREGUE' => 0, 'CANCELADO' => 0];
foreach ($pedidos as $p) {
    if (isset($contadores[$p['status']])) {
        $contadores[$p['status']]++;
    }
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos Online - Sistema de Restaurante</title>

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

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
        }

        .kanban {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .kanban-col {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            min-height: 250px;
        }

        .kanban-header {
            font-weight: 600;
            font-size: 13px;
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
        }

        .pedido-card {
            background: white;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #dee2e6;
            transition: all 0.2s;
        }

        .pedido-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .status-NOVO {
            border-left-color: #ffc107;
        }

        .status-PREPARANDO {
            border-left-color: #FF6B35;
        }

        .status-PRONTO {
            border-left-color: #28a745;
        }

        .status-ENTREGUE {
            border-left-color: #6c757d;
        }

        .status-CANCELADO {
            border-left-color: #dc3545;
        }

        .bg-NOVO {
            background: #fff3cd !important;
            color: #856404 !important;
        }

        .bg-PREPARANDO {
            background: #ffe5d0 !important;
            color: #c0392b !important;
        }

        .bg-PRONTO {
            background: #d4edda !important;
            color: #155724 !important;
        }

        .bg-ENTREGUE {
            background: #e2e3e5 !important;
            color: #383d41 !important;
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
                    <li class="nav-item"><a href="pedidos.php" class="nav-link active"><i class="fas fa-mobile-alt"></i> Pedidos Online</a></li>
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
                        <h4 class="mb-0"><i class="fas fa-mobile-alt text-primary me-2"></i>Pedidos Online</h4>
                        <p class="text-muted mb-0" style="font-size: 14px;">Pedidos do dia via QR Code</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-info btn-sm" onclick="location.reload()"><i class="fas fa-sync-alt me-1"></i> Atualizar</button>
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nome']); ?>&background=FF6B35&color=fff&size=50" class="avatar-lg">
                    </div>
                </div>

                <!-- QR CODE SECTION -->
                <div class="card mb-4">
                    <div class="card-body d-flex align-items-center gap-4 flex-wrap">
                        <div class="text-center" style="width: 120px; flex-shrink: 0;">
                            <canvas id="qrCodeCanvas" class="img-fluid rounded"></canvas>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-2"><i class="fas fa-qrcode me-2"></i>QR Code do Cardápio</h5>
                            <p class="text-muted mb-2" style="font-size: 14px;">Clientes escaneiam para fazer pedidos pelo celular.</p>
                            <p class="text-muted mb-3" style="font-size: 13px;">URL: <strong><?php echo htmlspecialchars($cardapio_url); ?></strong></p>
                            <div class="d-flex gap-2">
                                <button class="btn btn-success btn-sm" onclick="baixarQRCode()"><i class="fas fa-download me-1"></i> Baixar</button>
                                <button class="btn btn-info btn-sm" onclick="imprimirQRCodes()"><i class="fas fa-print me-1"></i> Imprimir</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STATS -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="fw-bold text-warning" style="font-size: 28px;"><?php echo $contadores['NOVO']; ?></div>
                                <div class="text-muted small">Novos</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="fw-bold" style="color: #FF6B35; font-size: 28px;"><?php echo $contadores['PREPARANDO']; ?></div>
                                <div class="text-muted small">Preparando</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="fw-bold text-success" style="font-size: 28px;"><?php echo $contadores['PRONTO']; ?></div>
                                <div class="text-muted small">Prontos</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="fw-bold text-muted" style="font-size: 28px;"><?php echo $contadores['ENTREGUE']; ?></div>
                                <div class="text-muted small">Entregues</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KANBAN -->
                <?php if (empty($pedidos)): ?>
                    <div class="card text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5>Nenhum pedido hoje</h5>
                        <p class="text-muted">Pedidos via QR Code aparecerão aqui.</p>
                    </div>
                <?php else: ?>
                    <div class="kanban">
                        <?php
                        $colunas = [
                            'NOVO' => ['titulo' => 'Novos', 'proximo' => 'PREPARANDO', 'cor' => 'NOVO'],
                            'PREPARANDO' => ['titulo' => 'Preparando', 'proximo' => 'PRONTO', 'cor' => 'PREPARANDO'],
                            'PRONTO' => ['titulo' => 'Prontos', 'proximo' => 'ENTREGUE', 'cor' => 'PRONTO'],
                            'ENTREGUE' => ['titulo' => 'Entregues', 'proximo' => null, 'cor' => 'ENTREGUE'],
                        ];
                        foreach ($colunas as $status => $info):
                            $pedidos_coluna = array_filter($pedidos, fn($p) => $p['status'] === $status);
                        ?>
                            <div class="kanban-col">
                                <div class="kanban-header bg-<?php echo $info['cor']; ?>">
                                    <span><?php echo $info['titulo']; ?></span>
                                    <span class="badge bg-secondary"><?php echo count($pedidos_coluna); ?></span>
                                </div>
                                <?php if (empty($pedidos_coluna)): ?>
                                    <p class="text-muted text-center small py-4">Nenhum</p>
                                <?php endif; ?>
                                <?php foreach ($pedidos_coluna as $ped): ?>
                                    <div class="pedido-card status-<?php echo $ped['status']; ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <strong><?php echo $ped['numero_pedido']; ?></strong>
                                            <small class="text-muted"><?php echo date('H:i', strtotime($ped['criado_em'])); ?></small>
                                        </div>
                                        <div class="small text-muted mb-2">
                                            <?php echo $ped['mesa_numero'] ? 'Mesa ' . $ped['mesa_numero'] : 'Sem mesa'; ?>
                                            &bull; <?php echo $ped['total_itens']; ?> item(s)
                                        </div>
                                        <div class="fw-bold text-primary mb-2"><?php echo number_format($ped['total'], 2, ',', '.'); ?> MZN</div>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <?php if ($info['proximo']): ?>
                                                <button class="btn btn-sm btn-success" onclick="avancarPedido(<?php echo $ped['id']; ?>, '<?php echo $info['proximo']; ?>')">
                                                    <i class="fas fa-arrow-right"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-info" onclick="verItensPedido(<?php echo $ped['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($ped['status'] != 'ENTREGUE'): ?>
                                                <button class="btn btn-sm btn-danger" onclick="cancelarPedido(<?php echo $ped['id']; ?>)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </main>
        </div>
    </div>

    <!-- MODAL ITENS -->
    <div class="modal fade" id="modalItens" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-list me-2"></i>Itens do Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="listaItens"></div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- QR Code -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>

    <script>
        var cardapioUrl = '<?php echo addslashes($cardapio_url); ?>';
        var baseUrl = '<?php echo addslashes($base_url); ?>';
        var scriptDir = '<?php echo addslashes($script_dir); ?>';
        var restauranteId = <?php echo (int)$_SESSION['restaurante_id']; ?>;
        var todasMesas = <?php echo json_encode($todas_mesas); ?>;

        function gerarQRDataURL(url, size) {
            var qr = qrcode(0, 'M');
            qr.addData(url);
            qr.make();
            var mc = qr.getModuleCount();
            var cell = Math.ceil(size / mc);
            var cv = document.createElement('canvas');
            cv.width = cell * mc;
            cv.height = cell * mc;
            var ctx = cv.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, cv.width, cv.height);
            ctx.fillStyle = '#000000';
            for (var r = 0; r < mc; r++) {
                for (var c = 0; c < mc; c++) {
                    if (qr.isDark(r, c)) {
                        ctx.fillRect(c * cell, r * cell, cell, cell);
                    }
                }
            }
            return cv.toDataURL('image/png');
        }

        document.addEventListener('DOMContentLoaded', function() {
            try {
                var qr = qrcode(0, 'M');
                qr.addData(cardapioUrl);
                qr.make();
                var mc = qr.getModuleCount();
                var cell = Math.floor(116 / mc);
                var canvas = document.getElementById('qrCodeCanvas');
                canvas.width = cell * mc;
                canvas.height = cell * mc;
                var ctx = canvas.getContext('2d');
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.fillStyle = '#000000';
                for (var r = 0; r < mc; r++) {
                    for (var c = 0; c < mc; c++) {
                        if (qr.isDark(r, c)) {
                            ctx.fillRect(c * cell, r * cell, cell, cell);
                        }
                    }
                }
            } catch (e) {}
        });

        function baixarQRCode() {
            var dataUrl = gerarQRDataURL(cardapioUrl, 400);
            var link = document.createElement('a');
            link.download = 'qrcode-cardapio.png';
            link.href = dataUrl;
            link.click();
        }

        function imprimirQRCodes() {
            if (todasMesas.length === 0) {
                alert('Nenhuma mesa cadastrada.');
                return;
            }
            var h = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>QR Codes</title>';
            h += '<style>body{font-family:Arial;padding:20px;}h1{text-align:center;}.grid{display:flex;flex-wrap:wrap;gap:20px;justify-content:center;}.qi{text-align:center;padding:15px;border:2px solid #333;border-radius:10px;width:180px;}.qi img{display:block;margin:0 auto;}</style></head><body>';
            h += '<h1>QR Codes do Restaurante</h1><div class="grid">';
            h += '<div class="qi"><img src="' + gerarQRDataURL(cardapioUrl, 200) + '" width="150"><h3>Geral</h3></div>';
            for (var i = 0; i < todasMesas.length; i++) {
                var m = todasMesas[i];
                var mu = baseUrl + scriptDir + '/cardapio.php?rid=' + restauranteId + '&mesa=' + m.id;
                h += '<div class="qi"><img src="' + gerarQRDataURL(mu, 200) + '" width="150"><h3>Mesa ' + m.numero + '</h3></div>';
            }
            h += '</div></body></html>';
            var win = window.open('', '_blank');
            win.document.write(h);
            win.document.close();
        }

        function avancarPedido(id, status) {
            if (!confirm('Avançar para ' + status + '?')) return;
            var fd = new FormData();
            fd.append('id', id);
            fd.append('status', status);
            fetch('api/pedido_status.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) location.reload();
                    else alert(data.message);
                });
        }

        function cancelarPedido(id) {
            if (!confirm('Cancelar pedido?')) return;
            var fd = new FormData();
            fd.append('id', id);
            fd.append('status', 'CANCELADO');
            fetch('api/pedido_status.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) location.reload();
                    else alert(data.message);
                });
        }

        function verItensPedido(id) {
            fetch('api/pedido_itens.php?id=' + id)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        var html = '';
                        data.itens.forEach(item => {
                            html += '<div class="d-flex justify-content-between py-2 border-bottom">';
                            html += '<span><strong>' + item.produto_nome + '</strong><br><small class="text-muted">' + item.quantidade + 'x ' + parseFloat(item.preco_unitario).toFixed(2).replace('.', ',') + ' MZN</small></span>';
                            html += '<strong class="text-primary">' + parseFloat(item.subtotal).toFixed(2).replace('.', ',') + ' MZN</strong></div>';
                        });
                        document.getElementById('listaItens').innerHTML = html || '<p class="text-muted">Sem itens</p>';
                        new bootstrap.Modal(document.getElementById('modalItens')).show();
                    }
                });
        }

        setTimeout(() => location.reload(), 30000);
    </script>
</body>

</html>
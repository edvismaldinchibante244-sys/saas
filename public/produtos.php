<?php
// Proteção da página
include_once '../config/auth_check.php';
include_once '../config/database.php';
include_once '../app/Produto.php';
include_once '../app/Categoria.php';

// Conectar ao banco
$database = new Database();
$db = $database->getConnection();

// Instanciar classes
$produto   = new Produto($db);
$categoria = new Categoria($db);

// Buscar produtos (array)
$stmt_produtos = $produto->listar($_SESSION['restaurante_id']);
$lista_produtos = $stmt_produtos->fetchAll(PDO::FETCH_ASSOC);

// Buscar categorias para o filtro/modal
$stmt_cat = $categoria->listar($_SESSION['restaurante_id']);
$lista_categorias = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas
$total_produtos = count($lista_produtos);
$produtos_ativos = count(array_filter($lista_produtos, fn($p) => $p['ativo'] == 1));
$estoque_baixo = count(array_filter($lista_produtos, fn($p) => $p['estoque'] <= $p['estoque_minimo']));
// Base URL para formar caminhos de imagens
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://';
$base_url = $protocol . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/');
$base_url = $base_url . '/';
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - Sistema de Restaurante</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary: #FF6B35;
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
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.12);
        }

        .stat-card {
            position: relative;
            overflow: hidden;
        }

        .stat-card .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
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

        .avatar-lg {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .product-img {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            object-fit: cover;
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
                    <li class="nav-item"><a href="produtos.php" class="nav-link active"><i class="fas fa-pizza-slice"></i> Produtos</a></li>
                    <li class="nav-item"><a href="vendas.php" class="nav-link"><i class="fas fa-cash-register"></i> Vendas</a></li>
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
                        <h4 class="mb-0"><i class="fas fa-pizza-slice text-primary me-2"></i>Gestão de Produtos</h4>
                        <p class="text-muted mb-0" style="font-size: 14px;">Cadastre e gerencie seu cardápio</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nome']); ?>&background=FF6B35&color=fff&size=50" alt="<?php echo $_SESSION['nome']; ?>" class="avatar-lg">
                    </div>
                </div>

                <!-- STATS -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted" style="font-size: 13px;">Total de Produtos</div>
                                    <div class="fw-bold" style="font-size: 28px; color: #2d3748;"><?php echo $total_produtos; ?></div>
                                </div>
                                <div class="stat-icon" style="background: #ffe5d0; color: #FF6B35;"><i class="fas fa-box"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted" style="font-size: 13px;">Produtos Ativos</div>
                                    <div class="fw-bold" style="font-size: 28px; color: #28a745;"><?php echo $produtos_ativos; ?></div>
                                </div>
                                <div class="stat-icon" style="background: #d4edda; color: #28a745;"><i class="fas fa-check-circle"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted" style="font-size: 13px;">Estoque Baixo</div>
                                    <div class="fw-bold" style="font-size: 28px; color: #dc3545;"><?php echo $estoque_baixo; ?></div>
                                </div>
                                <div class="stat-icon" style="background: #f8d7da; color: #dc3545;"><i class="fas fa-exclamation-triangle"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AÇÕES -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" id="buscar" class="form-control" placeholder="🔍 Buscar produto...">
                            </div>
                            <div class="col-md-4">
                                <select id="filtroCategoria" class="form-select">
                                    <option value="">Todas as categorias</option>
                                    <?php foreach ($lista_categorias as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['nome']); ?>"><?php echo htmlspecialchars($cat['nome']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary w-100" onclick="abrirModal()"><i class="fas fa-plus me-2"></i>Novo</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABELA -->
                <div class="card table-card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Produto</th>
                                        <th>Categoria</th>
                                        <th>Preço</th>
                                        <th>Estoque</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="tabelaProdutos">
                                    <?php foreach ($lista_produtos as $p): ?>
                                        <tr>
                                            <td><?php echo $p['id']; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php
                                                        $imgSrc = !empty($p['imagem']) ? $base_url . $p['imagem'] : "https://ui-avatars.com/api/?name=" . urlencode($p['nome']) . "&background=ffe5d0&color=FF6B35&size=40";
                                                    ?>
                                                    <?php if (!empty($p['imagem'])): ?>
+                                                        <a href="<?php echo $imgSrc; ?>" target="_blank">
+                                                            <img src="<?php echo $imgSrc; ?>" class="product-img me-3">
+                                                        </a>
+                                                    <?php else: ?>
+                                                        <img src="<?php echo $imgSrc; ?>" class="product-img me-3">
+                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($p['nome']); ?></strong>
                                                        <?php if (!empty($p['descricao'])): ?>
                                                            <div class="text-muted" style="font-size: 12px;"><?php echo htmlspecialchars(substr($p['descricao'], 0, 40)); ?>...</div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($p['categoria_nome'] ?? '—'); ?></td>
                                            <td><strong><?php echo number_format($p['preco'], 2, ',', '.'); ?> MZN</strong></td>
                                            <td>
                                                <?php if ($p['estoque'] <= $p['estoque_minimo']): ?>
                                                    <span class="badge bg-danger">⚠️ <?php echo $p['estoque']; ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-success"><?php echo $p['estoque']; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($p['ativo']): ?>
                                                    <span class="badge bg-success">Ativo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inativo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="editarProduto(<?php echo $p['id']; ?>)"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-warning" onclick="atualizarEstoque(<?php echo $p['id']; ?>)"><i class="fas fa-box"></i></button>
                                                <button class="btn btn-sm btn-danger" onclick="deletarProduto(<?php echo $p['id']; ?>)"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($lista_produtos)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <i class="fas fa-pizza-slice fa-3x text-muted mb-3 d-block"></i>
                                                <p class="text-muted">Nenhum produto cadastrado</p>
                                                <button class="btn btn-primary" onclick="abrirModal()"><i class="fas fa-plus me-2"></i>Cadastrar primeiro produto</button>
                                            </td>
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

    <!-- MODAL -->
    <div class="modal fade" id="modalProduto" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModal"><i class="fas fa-plus me-2"></i>Novo Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert" id="alertModal" style="display: none;"></div>
                    <form id="formProduto">
                        <input type="hidden" id="produto_id" name="produto_id">

                        <div class="mb-3">
                            <label class="form-label">Nome do Produto *</label>
                            <input type="text" id="nome" name="nome" class="form-control" required placeholder="Ex: Frango Grelhado">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-tag me-2"></i>Categoria</label>
                            <div class="input-group mb-2">
                                <input type="text" id="novaCategoria" class="form-control" placeholder="Ex: Bebidas, Sobremesas, Entradas..." autocomplete="off">
                                <button class="btn btn-outline-success" type="button" onclick="adicionarNovaCategoria()" title="Criar nova categoria"><i class="fas fa-plus-circle"></i> Criar</button>
                            </div>
                            <small class="form-text text-muted">Selecione uma categoria existente ou crie uma nova</small>
                            <select id="categoria_id" name="categoria_id" class="form-select mt-2">
                                <option value="">— Selecione uma categoria —</option>
                                <?php foreach ($lista_categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descrição</label>
                            <textarea id="descricao" name="descricao" class="form-control" rows="2" placeholder="Descrição do produto..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Preço de Venda (MZN) *</label>
                                <input type="number" id="preco" name="preco" class="form-control" step="0.01" min="0" required placeholder="0.00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Custo (MZN)</label>
                                <input type="number" id="custo" name="custo" class="form-control" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estoque Inicial</label>
                                <input type="number" id="estoque" name="estoque" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estoque Mínimo</label>
                                <input type="number" id="estoque_minimo" name="estoque_minimo" class="form-control" value="5" min="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Foto do Produto</label>
                            <input type="file" id="imagem" name="imagem" accept="image/*" class="form-control">
                            <img id="imagemPreview" src="" class="img-fluid mt-2" style="max-width:120px; display:none;">
                            <input type="hidden" id="imagem_existing" name="imagem">
                        </div>

                        <div class="form-check">
                            <input type="checkbox" id="ativo" name="ativo" class="form-check-input" checked>
                            <label class="form-check-label">Produto Ativo</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formProduto" class="btn btn-primary"><i class="fas fa-save me-2"></i>Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>const BASE_URL = '<?php echo $base_url; ?>';</script>
    <script src="js/produtos.js"></script>
</body>

</html>
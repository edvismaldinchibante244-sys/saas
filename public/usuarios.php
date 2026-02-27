<?php
// Proteção da página - apenas ADMIN
include_once '../config/auth_check.php';
checkPermission(['ADMIN']);

include_once '../config/database.php';
include_once '../app/Auth.php';

// Conectar ao banco
$database = new Database();
$db = $database->getConnection();

// calcular base URL (sem barra no final)
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST']
    . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');


// Buscar usuários do restaurante
$query = "SELECT id, nome, email, perfil, ativo, foto, criado_em FROM usuarios WHERE restaurante_id = :rid ORDER BY nome ASC";
$stmt = $db->prepare($query);
$stmt->bindParam(':rid', $_SESSION['restaurante_id']);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - Sistema de Restaurante</title>

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
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
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

        .avatar-sm {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
            overflow: hidden;
        }

        .avatar-sm img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .foto-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ddd;
            margin: 0 auto;
            display: block;
        }

        .foto-upload-wrapper {
            text-align: center;
            margin-bottom: 20px;
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
                    <li class="nav-item"><a href="mesas.php" class="nav-link"><i class="fas fa-chair"></i> Mesas</a></li>
                    <li class="nav-item"><a href="relatorios.php" class="nav-link"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
                    <li class="nav-item"><a href="usuarios.php" class="nav-link active"><i class="fas fa-users"></i> Usuários</a></li>
                    <li class="nav-item"><a href="configuracoes.php" class="nav-link"><i class="fas fa-cog"></i> Configurações</a></li>
                    <li class="nav-item"><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                </ul>
            </nav>

            <!-- CONTEÚDO PRINCIPAL -->
            <main class="main-content col-md-9 ms-sm-auto col-lg-10">

                <!-- TOP BAR -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-0"><i class="fas fa-users text-primary me-2"></i>Gestão de Usuários</h4>
                        <p class="text-muted mb-0" style="font-size: 14px;">Gerencie os acessos ao sistema</p>
                    </div>
                    <?php
                    $foto_atual = !empty($_SESSION['foto']) ? $_SESSION['foto'] : '';
                    $img_url = !empty($foto_atual) ? $foto_atual : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['nome']) . '&background=FF6B35&color=fff&size=50';
                    ?>
                    <?php if (!empty($foto_atual)): ?>
                        <img src="<?php echo htmlspecialchars($foto_atual); ?>" class="avatar-lg" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nome']); ?>&background=FF6B35&color=fff&size=50'">
                    <?php else: ?>
                        <img src="<?php echo $img_url; ?>" class="avatar-lg">
                    <?php endif; ?>
                </div>

                <!-- AÇÕES -->
                <div class="card mb-4">
                    <div class="card-body">
                        <button class="btn btn-primary" onclick="abrirModal()"><i class="fas fa-plus me-2"></i>Novo Usuário</button>
                    </div>
                </div>

                <!-- TABELA -->
                <div class="card table-card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Usuário</th>
                                        <th>Email</th>
                                        <th>Perfil</th>
                                        <th>Status</th>
                                        <th>Cadastro</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $u): ?>
                                        <?php
                                        // montar URL da foto se houver
                                        $fotoUrl = '';
                                        if (!empty($u['foto'])) {
                                            $fotoUrl = (strpos($u['foto'], 'http') === 0)
                                                ? $u['foto']
                                                : $baseUrl . '/' . ltrim($u['foto'], '/');
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($fotoUrl): ?>
                                                        <a href="<?php echo htmlspecialchars($fotoUrl); ?>" target="_blank" class="me-3" title="Abrir foto em nova aba">
                                                            <div class="avatar-sm" style="background: transparent;">
                                                                <img src="<?php echo htmlspecialchars($fotoUrl); ?>" onerror="this.style.display='none'; this.parentElement.style.background='<?php echo $u['perfil'] == 'ADMIN' ? '#dc3545' : ($u['perfil'] == 'CAIXA' ? '#17a2b8' : '#ffc107'); ?>';">
                                                            </div>
                                                        </a>
                                                    <?php else: ?>
                                                        <div class="avatar-sm me-3" style="background: <?php echo $u['perfil'] == 'ADMIN' ? '#dc3545' : ($u['perfil'] == 'CAIXA' ? '#17a2b8' : '#ffc107'); ?>;">
                                                            <?php echo strtoupper(substr($u['nome'], 0, 1)); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($u['nome']); ?></strong>
                                                        <br>
                                                        <span class="badge bg-<?php echo $u['perfil'] == 'ADMIN' ? 'danger' : ($u['perfil'] == 'CAIXA' ? 'info' : 'warning'); ?>">
                                                            <?php echo $u['perfil']; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $u['perfil'] == 'ADMIN' ? 'danger' : ($u['perfil'] == 'CAIXA' ? 'info' : 'warning'); ?>">
                                                    <?php echo $u['perfil']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($u['ativo']): ?>
                                                    <span class="badge bg-success">Ativo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inativo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($u['criado_em'])); ?></td>
                                            <td>
                                                <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                                                    <button class="btn btn-sm btn-info" onclick="editarUsuario(<?php echo $u['id']; ?>)"><i class="fas fa-edit"></i></button>
                                                    <button class="btn btn-sm btn-warning" onclick="alterarSenha(<?php echo $u['id']; ?>)"><i class="fas fa-key"></i></button>
                                                    <button class="btn btn-sm btn-danger" onclick="deletarUsuario(<?php echo $u['id']; ?>)"><i class="fas fa-trash"></i></button>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Você</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($usuarios)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                                Nenhum usuário encontrado
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

    <!-- MODAL CADASTRO/EDIÇÃO -->
    <div class="modal fade" id="modalUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModal"><i class="fas fa-plus me-2"></i>Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert" id="alertModal" style="display: none;"></div>
                    <form id="formUsuario" enctype="multipart/form-data">
                        <input type="hidden" id="usuario_id" name="usuario_id">

                        <!-- Foto do usuário (apenas PNG) -->
                        <div class="foto-upload-wrapper">
                            <input type="file" id="foto" name="foto" accept="image/png,image/jpeg" style="display: none;" onchange="previewFoto(this)">
                            <img id="fotoPreview" class="foto-preview" src="https://ui-avatars.com/api/?name=?&background=FF6B35&color=fff&size=100" alt="Foto do usuário">
                            <p class="text-muted small mb-2">Apenas imagens PNG ou JPEG</p>
                            <button type="button" class="btn btn-outline-secondary btn-sm mt-1" onclick="document.getElementById('foto').click()">
                                <i class="fas fa-camera me-1"></i> Escolher Foto
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm mt-1" onclick="removerFoto()">
                                <i class="fas fa-times me-1"></i> Remover
                            </button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nome Completo *</label>
                            <input type="text" id="nome" name="nome" class="form-control" required placeholder="Ex: João Silva">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" id="email" name="email" class="form-control" required placeholder="email@exemplo.com">
                        </div>

                        <div class="mb-3" id="senhaGroup">
                            <label class="form-label">Senha *</label>
                            <input type="password" id="senha" name="senha" class="form-control" placeholder="Mínimo 6 caracteres">
                            <small class="text-muted" id="senhaHint">Deixe em branco para manter a senha atual (ao editar)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Perfil *</label>
                            <select id="perfil" name="perfil" class="form-select" required>
                                <option value="CAIXA">💵 Caixa</option>
                                <option value="GARCOM">🍽️ Garçom</option>
                                <option value="ADMIN">👑 Administrador</option>
                            </select>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" id="ativo" name="ativo" class="form-check-input" checked>
                            <label class="form-check-label">Usuário Ativo</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formUsuario" class="btn btn-primary"><i class="fas fa-save me-2"></i>Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL ALTERAR SENHA -->
    <div class="modal fade" id="modalSenha" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-key me-2"></i>Alterar Senha</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert" id="alertSenha" style="display: none;"></div>
                    <form id="formSenha">
                        <input type="hidden" id="senha_usuario_id" name="usuario_id">

                        <div class="mb-3">
                            <label class="form-label">Nova Senha *</label>
                            <input type="password" id="nova_senha" name="nova_senha" class="form-control" required placeholder="Mínimo 6 caracteres">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmar Senha *</label>
                            <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" required placeholder="Repita a senha">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formSenha" class="btn btn-warning"><i class="fas fa-key me-2"></i>Alterar Senha</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // make baseUrl available to javascript
        const BASE_URL = '<?php echo $baseUrl; ?>';
    </script>
    <script src="js/usuarios.js"></script>
    <script>
        // Preview da foto
        function previewFoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('fotoPreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Remover foto
        function removerFoto() {
            document.getElementById('foto').value = '';
            document.getElementById('fotoPreview').src = 'https://ui-avatars.com/api/?name=?&background=FF6B35&color=fff&size=100';
        }
    </script>
</body>

</html>
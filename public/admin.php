<?php

/**
 * ============================================
 * PÁGINA DE ADMINISTRAÇÃO - SUPER ADMIN
 * Gerencia todos os restaurantes do sistema
 * ============================================
 */

include_once '../config/super_admin_check.php';
include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Buscar dados do super admin
$super_admin_nome = $_SESSION['nome'] ?? 'Super Admin';
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração - Sistema de Restaurantes</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary: #6c5ce7;
            --secondary: #a29bfe;
            --dark: #2d3436;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--dark) 0%, #1a1a2e 100%);
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

        .sidebar .brand span {
            color: var(--secondary);
            font-size: 12px;
        }

        .sidebar .nav-item a {
            color: rgba(255, 255, 255, 0.7);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
        }

        .sidebar .nav-item a:hover,
        .sidebar .nav-item a.active {
            background: rgba(108, 92, 231, 0.3);
            color: white;
            border-left: 3px solid var(--primary);
        }

        .main-content {
            margin-left: 260px;
            padding: 25px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .table-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .badge-plano {
            font-size: 11px;
            padding: 5px 10px;
        }

        .badge-status {
            font-size: 11px;
            padding: 5px 10px;
        }

        .secao {
            display: none;
        }

        .secao.ativa {
            display: block;
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
                    <h3><i class="fas fa-crown me-2"></i>Super Admin</h3>
                    <span>Gestão de Restaurantes</span>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item"><a href="#" class="nav-link active" onclick="mostrarSecao('restaurantes')"><i class="fas fa-building"></i> Restaurantes</a></li>
                    <li class="nav-item"><a href="#" class="nav-link" onclick="mostrarSecao('usuarios')"><i class="fas fa-users"></i> Usuários</a></li>
                    <li class="nav-item"><a href="#" class="nav-link" onclick="mostrarSecao('planos')"><i class="fas fa-credit-card"></i> Planos</a></li>
                    <li class="nav-item"><a href="admin_dashboard.php" class="nav-link"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                    <li class="nav-item"><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                </ul>
            </nav>

            <main class="main-content col-md-9 ms-sm-auto col-lg-10">
                <!-- Alertas -->
                <div id="alertContainer"></div>

                <!-- Estatísticas Gerais -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted" style="font-size: 13px;">Total de Restaurantes</div>
                                    <div class="fs-3 fw-bold" id="statTotalRestaurantes">0</div>
                                </div>
                                <div class="stat-icon" style="background: rgba(108, 92, 231, 0.1); color: var(--primary);">
                                    <i class="fas fa-building"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted" style="font-size: 13px;">Restaurantes Ativos</div>
                                    <div class="fs-3 fw-bold text-success" id="statRestaurantesAtivos">0</div>
                                </div>
                                <div class="stat-icon" style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted" style="font-size: 13px;">Receita Mensal</div>
                                    <div class="fs-3 fw-bold text-info" id="statReceitaMensal">0 MZN</div>
                                </div>
                                <div class="stat-icon" style="background: rgba(23, 162, 184, 0.1); color: #17a2b8;">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted" style="font-size: 13px;">Assinaturas Expirando</div>
                                    <div class="fs-3 fw-bold text-warning" id="statAssinaturasExpirando">0</div>
                                </div>
                                <div class="stat-icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEÇÃO: RESTAURANTES -->
                <div id="secao-restaurantes" class="secao ativa">
                    <!-- Header com Seletor de Restaurante -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="mb-0"><i class="fas fa-building text-primary me-2"></i>Gerenciar Restaurantes</h4>
                            <p class="text-muted mb-0" style="font-size: 14px;">Cadastre e gerencie todos os restaurantes do sistema</p>
                        </div>
                        <div class="d-flex gap-2">
                            <form id="formSelecionarRestaurante" class="d-flex gap-2">
                                <select class="form-select" id="restauranteSelecionado" name="restaurante_id" style="width: 250px;">
                                    <option value="">Selecione um restaurante...</option>
                                </select>
                                <button type="button" class="btn btn-success" onclick="acessarDashboard()">
                                    <i class="fas fa-chart-line me-1"></i> Dashboard
                                </button>
                            </form>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCadastrar">
                                <i class="fas fa-plus me-2"></i>Novo Restaurante
                            </button>
                        </div>
                    </div>

                    <!-- Estatísticas -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted" style="font-size: 13px;">Total Restaurantes</div>
                                        <div class="fs-3 fw-bold" id="listaTotalRestaurantes">0</div>
                                    </div>
                                    <div class="stat-icon" style="background: rgba(108, 92, 231, 0.1); color: var(--primary);">
                                        <i class="fas fa-building"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted" style="font-size: 13px;">Ativos</div>
                                        <div class="fs-3 fw-bold text-success" id="listaAtivos">0</div>
                                    </div>
                                    <div class="stat-icon" style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted" style="font-size: 13px;">Pendentes</div>
                                        <div class="fs-3 fw-bold text-secondary" id="listaPendentes">0</div>
                                    </div>
                                    <div class="stat-icon" style="background: rgba(108, 117, 125, 0.1); color: #6c757d;">
                                        <i class="fas fa-hourglass-half"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted" style="font-size: 13px;">Bloqueados</div>
                                        <div class="fs-3 fw-bold text-warning" id="listaBloqueados">0</div>
                                    </div>
                                    <div class="stat-icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                                        <i class="fas fa-pause-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted" style="font-size: 13px;">Enterprise</div>
                                        <div class="fs-3 fw-bold text-info" id="listaEnterprise">0</div>
                                    </div>
                                    <div class="stat-icon" style="background: rgba(23, 162, 184, 0.1); color: #17a2b8;">
                                        <i class="fas fa-crown"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de Restaurantes -->
                    <div class="table-card">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Restaurantes</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">ID</th>
                                            <th>Nome</th>
                                            <th>Email</th>
                                            <th>Telefone</th>
                                            <th>Plano</th>
                                            <th>Status</th>
                                            <th>Validade</th>
                                            <th class="text-center">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabelaRestaurantes">
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                <i class="fas fa-spinner fa-spin me-2"></i>Carregando...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEÇÃO: USUÁRIOS -->
                <div id="secao-usuarios" class="secao">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="mb-0"><i class="fas fa-users text-primary me-2"></i>Gerenciar Usuários</h4>
                            <p class="text-muted mb-0" style="font-size: 14px;">Selecione um restaurante para gerenciar seus usuários</p>
                        </div>
                        <div class="d-flex gap-2">
                            <select class="form-select" id="restauranteSelecionadoUsuarios" style="width: 300px;" onchange="carregarUsuarios()">
                                <option value="">Selecione um restaurante...</option>
                            </select>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCadastrarUsuario">
                                <i class="fas fa-plus me-2"></i>Novo Usuário
                            </button>
                        </div>
                    </div>

                    <div class="table-card">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Usuários</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">ID</th>
                                            <th>Nome</th>
                                            <th>Email</th>
                                            <th>Perfil</th>
                                            <th>Status</th>
                                            <th class="text-center">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabelaUsuarios">
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                Selecione um restaurante para ver os usuários
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEÇÃO: PLANOS -->
                <div id="secao-planos" class="secao">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="mb-0"><i class="fas fa-credit-card text-primary me-2"></i>Gerenciar Planos</h4>
                            <p class="text-muted mb-0" style="font-size: 14px;">Acompanhe e aprove os pagamentos de planos dos restaurantes</p>
                        </div>
                        <button class="btn btn-success" onclick="carregarCompras()">
                            <i class="fas fa-sync me-2"></i>Atualizar
                        </button>
                    </div>

                    <!-- Estatísticas de Planos -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted" style="font-size: 13px;">Total Compras</div>
                                        <div class="fs-3 fw-bold" id="totalCompras">0</div>
                                    </div>
                                    <div class="stat-icon" style="background: rgba(108, 92, 231, 0.1); color: var(--primary);">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted" style="font-size: 13px;">Pendentes</div>
                                        <div class="fs-3 fw-bold text-warning" id="totalPendentes">0</div>
                                    </div>
                                    <div class="stat-icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted" style="font-size: 13px;">Aprovados</div>
                                        <div class="fs-3 fw-bold text-success" id="totalAprovados">0</div>
                                    </div>
                                    <div class="stat-icon" style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de Compras -->
                    <div class="table-card">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Histórico de Compras de Planos</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">ID</th>
                                            <th>Restaurante</th>
                                            <th>Plano Atual</th>
                                            <th>Plano Novo</th>
                                            <th>Valor</th>
                                            <th>Método</th>
                                            <th>Status</th>
                                            <th>Data</th>
                                            <th class="text-center">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabelaCompras">
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">
                                                <i class="fas fa-spinner fa-spin me-2"></i>Carregando...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Modal Aprovar Compra -->
    <div class="modal fade" id="modalAprovarCompra" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Aprovar Pagamento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="compra_id_aprovar">
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Você está aprovando</strong> um pagamento de plano. O plano será ativado automaticamente.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observação (opcional)</label>
                        <textarea class="form-control" id="obs_aprovar" rows="3" placeholder="Ex: Pagamento confirmado via M-Pesa, referência #123456"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="confirmarAprovacao()">
                        <i class="fas fa-check me-1"></i> Confirmar Aprovação
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Rejeitar Compra -->
    <div class="modal fade" id="modalRejeitarCompra" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Rejeitar Pagamento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="compra_id_rejeitar">
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Você está rejeitando</strong> uma compra de plano. O restaurante será notificado.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo da rejeição *</label>
                        <textarea class="form-control is-invalid" id="obs_rejeitar" rows="3" placeholder="Ex: Pagamento não confirmado, transação falhou, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" onclick="confirmarRejeicao()">
                        <i class="fas fa-ban me-1"></i> Confirmar Rejeição
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cadastrar Restaurante -->
    <div class="modal fade" id="modalCadastrar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Novo Restaurante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCadastrar">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome do Restaurante *</label>
                                <input type="text" class="form-control" name="nome" required placeholder="Ex: Restaurante Sabor">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" required placeholder="contato@restaurante.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Telefone</label>
                                <input type="text" class="form-control" name="telefone" placeholder="+258 84 000 0000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cidade</label>
                                <input type="text" class="form-control" name="cidade" placeholder="Maputo">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Endereço</label>
                                <input type="text" class="form-control" name="endereco" placeholder="Av. Principal, 123">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NUIT</label>
                                <input type="text" class="form-control" name="nuit" placeholder="400000000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Plano *</label>
                                <select class="form-select" name="plano" required>
                                    <option value="BASICO">Básico (Grátis)</option>
                                    <option value="PROFISSIONAL">Profissional (1.500 MZN/mês)</option>
                                    <option value="ENTERPRISE">Enterprise (3.000 MZN/mês)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Senha do Admin</label>
                                <input type="text" class="form-control" name="senha_admin" value="admin123" placeholder="Senha padrão">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Nome do Administrador</label>
                                <input type="text" class="form-control" name="nome_admin" value="Administrador" placeholder="Nome do administrador">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="cadastrarRestaurante()">
                        <i class="fas fa-save me-1"></i> Cadastrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Restaurante -->
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Restaurante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditar">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome do Restaurante *</label>
                                <input type="text" class="form-control" name="nome" id="edit_nome" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" id="edit_email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Telefone</label>
                                <input type="text" class="form-control" name="telefone" id="edit_telefone">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cidade</label>
                                <input type="text" class="form-control" name="cidade" id="edit_cidade">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Endereço</label>
                                <input type="text" class="form-control" name="endereco" id="edit_endereco">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NUIT</label>
                                <input type="text" class="form-control" name="nuit" id="edit_nuit">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Plano *</label>
                                <select class="form-select" name="plano" id="edit_plano" required>
                                    <option value="BASICO">Básico</option>
                                    <option value="PROFISSIONAL">Profissional</option>
                                    <option value="ENTERPRISE">Enterprise</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status *</label>
                                <select class="form-select" name="status" id="edit_status" required>
                                    <option value="PENDENTE">Pendente</option>
                                    <option value="ATIVO">Ativo</option>
                                    <option value="BLOQUEADO">Bloqueado</option>
                                    <option value="CANCELADO">Cancelado</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarEdicao()">
                        <i class="fas fa-save me-1"></i> Salvar Alterações
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cadastrar Usuário -->
    <div class="modal fade" id="modalCadastrarUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCadastrarUsuario">
                        <input type="hidden" id="usuario_restaurante_id" name="restaurante_id">
                        <div class="mb-3">
                            <label class="form-label">Nome *</label>
                            <input type="text" class="form-control" name="nome" required placeholder="Nome do usuário">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required placeholder="email@exemplo.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Senha</label>
                            <input type="text" class="form-control" name="senha" value="usuario123" placeholder="Senha padrão">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Perfil *</label>
                            <select class="form-select" name="perfil" required>
                                <option value="ADMIN">Administrador</option>
                                <option value="OPERADOR">Operador</option>
                                <option value="COZINHA">Cozinha</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="cadastrarUsuario()">
                        <i class="fas fa-save me-1"></i> Cadastrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ver Detalhes -->
    <div class="modal fade" id="modalVer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalhes do Restaurante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detalhesRestaurante">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin"></i> Carregando...
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let secaoAtual = 'restaurantes';

        // Função para mostrar seção
        function mostrarSecao(secao) {
            secaoAtual = secao;

            // Atualizar menu
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                link.classList.remove('active');
            });
            event.target.closest('.nav-link').classList.add('active');

            // Mostrar seção correta
            document.querySelectorAll('.secao').forEach(s => s.classList.remove('ativa'));
            document.getElementById('secao-' + secao).classList.add('ativa');

            // Carregar dados conforme seção
            if (secao === 'restaurantes') {
                carregarRestaurantes();
            } else if (secao === 'usuarios') {
                carregarRestaurantesParaUsuario();
            } else if (secao === 'planos') {
                carregarCompras();
            }
        }

        // Função para mostrar alerta
        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alertContainer');
            alertContainer.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }

        // ==================== RESTAURANTES ====================

        // Carregar restaurantes ao iniciar
        document.addEventListener('DOMContentLoaded', function() {
            carregarEstatisticasGerais();
            carregarRestaurantes();
            carregarCompras();
        });

        // Carregar estatísticas gerais
        function carregarEstatisticasGerais() {
            fetch('api/super_admin_estatisticas.php', {
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('statTotalRestaurantes').textContent = data.data.total_restaurantes;
                        document.getElementById('statRestaurantesAtivos').textContent = data.data.restaurantes_ativos;
                        document.getElementById('statReceitaMensal').textContent = data.data.receita_mensal.toFixed(0) + ' MZN';
                        document.getElementById('statAssinaturasExpirando').textContent = data.data.assinaturas_expirando;
                    }
                })
                .catch(err => console.error('Erro ao carregar estatísticas:', err));
        }

        // Função para acessar dashboard do restaurante selecionado
        function acessarDashboard() {
            const select = document.getElementById('restauranteSelecionado');
            const restauranteId = select.value;

            if (!restauranteId) {
                showAlert('Selecione um restaurante primeiro!', 'warning');
                return;
            }

            fetch('api/selecionar_restaurante.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'restaurante_id=' + restauranteId,
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'dashboard.php';
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(err => {
                    showAlert('Erro: ' + err.message, 'danger');
                });
        }

        // Função para popular seletor de restaurantes
        function popularSeletor(restaurantes) {
            const select = document.getElementById('restauranteSelecionado');
            let html = '<option value="">Selecione um restaurante...</option>';
            restaurantes.forEach(r => {
                html += `<option value="${r.id}">${r.nome}</option>`;
            });
            select.innerHTML = html;
        }

        // Carregar lista de restaurantes
        function carregarRestaurantes() {
            fetch('api/restaurante_listar.php', {
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        atualizarTabela(data.data);
                        atualizarEstatisticas(data.data);
                        popularSeletor(data.data);
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(err => {
                    console.error('Erro:', err);
                    showAlert('Erro ao carregar restaurantes', 'danger');
                });
        }

        // Atualizar tabela
        function atualizarTabela(restaurantes) {
            const tbody = document.getElementById('tabelaRestaurantes');

            if (restaurantes.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-inbox me-2"></i>Nenhum restaurante cadastrado</td></tr>`;
                return;
            }

            let html = '';
            restaurantes.forEach(r => {
                let badgePlano = r.plano === 'ENTERPRISE' ? '<span class="badge bg-warning badge-plano">Enterprise</span>' :
                    r.plano === 'PROFISSIONAL' ? '<span class="badge bg-info badge-plano">Profissional</span>' :
                    '<span class="badge bg-secondary badge-plano">Básico</span>';

                let badgeStatus = r.status === 'ATIVO' ? '<span class="badge bg-success badge-status">Ativo</span>' :
                    r.status === 'BLOQUEADO' ? '<span class="badge bg-warning badge-status">Bloqueado</span>' :
                    r.status === 'CANCELADO' ? '<span class="badge bg-danger badge-status">Cancelado</span>' :
                    '<span class="badge bg-secondary badge-status">Pendente</span>'; // PENDENTE

                const dataValidade = new Date(r.data_fim);
                const hoje = new Date();
                const diasRestantes = Math.ceil((dataValidade - hoje) / (1000 * 60 * 60 * 24));
                let validadeTexto = diasRestantes > 0 ? diasRestantes + ' dias' : 'Expirado';

                html += `<tr>
                    <td class="ps-4"><strong>#${r.id}</strong></td>
                    <td><strong>${r.nome}</strong></td>
                    <td>${r.email || '-'}</td>
                    <td>${r.telefone || '-'}</td>
                    <td>${badgePlano}</td>
                    <td>${badgeStatus}</td>
                    <td>${validadeTexto}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-info me-1" onclick="verDetalhes(${r.id})" title="Ver detalhes"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editarRestaurante(${r.id})" title="Editar"><i class="fas fa-edit"></i></button>
                        ${r.status === 'PENDENTE' ? `<button class="btn btn-sm btn-outline-success me-1" onclick="mudarStatus(${r.id}, 'ATIVO')" title="Aprovar"><i class="fas fa-check"></i></button>
                        <button class="btn btn-sm btn-outline-danger me-1" onclick="mudarStatus(${r.id}, 'CANCELADO')" title="Rejeitar"><i class="fas fa-ban"></i></button>` : ''}
                        <button class="btn btn-sm btn-outline-danger" onclick="deletarRestaurante(${r.id}, '${r.nome}')" title="Excluir"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
            });

            tbody.innerHTML = html;
        }

        // Atualizar estatísticas
        function atualizarEstatisticas(restaurantes) {
            document.getElementById('listaTotalRestaurantes').textContent = restaurantes.length;
            document.getElementById('listaAtivos').textContent = restaurantes.filter(r => r.status === 'ATIVO').length;
            document.getElementById('listaPendentes').textContent = restaurantes.filter(r => r.status === 'PENDENTE').length;
            document.getElementById('listaBloqueados').textContent = restaurantes.filter(r => r.status === 'BLOQUEADO').length;
            document.getElementById('listaEnterprise').textContent = restaurantes.filter(r => r.plano === 'ENTERPRISE').length;
        }

        // Cadastrar restaurante
        function cadastrarRestaurante() {
            const form = document.getElementById('formCadastrar');
            const formData = new FormData(form);

            fetch('api/restaurante_cadastrar.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('modalCadastrar')).hide();
                        form.reset();
                        carregarRestaurantes();
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(err => {
                    showAlert('Erro ao cadastrar: ' + err.message, 'danger');
                });
        }

        // Ver detalhes
        function verDetalhes(id) {
            const modal = new bootstrap.Modal(document.getElementById('modalVer'));
            const container = document.getElementById('detalhesRestaurante');

            container.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>';
            modal.show();

            fetch('api/restaurante_buscar.php?id=' + id, {
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const r = data.data;
                        container.innerHTML = `
                        <div class="text-center mb-3">
                            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; font-size: 32px; color: white;">
                                <i class="fas fa-utensils"></i>
                            </div>
                            <h5 class="mt-3">${r.nome}</h5>
                            <span class="badge bg-${r.status === 'ATIVO' ? 'success' : (r.status === 'BLOQUEADO' ? 'warning' : 'danger')}">${r.status}</span>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2"><small class="text-muted">Email</small><div>${r.email || '-'}</div></div>
                            <div class="col-6 mb-2"><small class="text-muted">Telefone</small><div>${r.telefone || '-'}</div></div>
                            <div class="col-6 mb-2"><small class="text-muted">Cidade</small><div>${r.cidade || '-'}</div></div>
                            <div class="col-6 mb-2"><small class="text-muted">NUIT</small><div>${r.nuit || '-'}</div></div>
                            <div class="col-6 mb-2"><small class="text-muted">Plano</small><div><span class="badge bg-${r.plano === 'ENTERPRISE' ? 'warning' : (r.plano === 'PROFISSIONAL' ? 'info' : 'secondary')}">${r.plano}</span></div></div>
                            <div class="col-6 mb-2"><small class="text-muted">Validade</small><div>${new Date(r.data_fim).toLocaleDateString('pt-BR')}</div></div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-4"><div class="fs-4 fw-bold text-primary">${r.total_usuarios}</div><small class="text-muted">Usuários</small></div>
                            <div class="col-4"><div class="fs-4 fw-bold text-success">${r.total_produtos}</div><small class="text-muted">Produtos</small></div>
                            <div class="col-4"><div class="fs-4 fw-bold text-info">${r.total_mesas}</div><small class="text-muted">Mesas</small></div>
                        </div>`;
                    } else {
                        container.innerHTML = '<div class="text-danger text-center">Erro ao carregar detalhes</div>';
                    }
                })
                .catch(err => {
                    container.innerHTML = '<div class="text-danger text-center">Erro: ' + err.message + '</div>';
                });
        }

        // Editar restaurante
        function editarRestaurante(id) {
            fetch('api/restaurante_buscar.php?id=' + id, {
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const r = data.data;
                        document.getElementById('edit_id').value = r.id;
                        document.getElementById('edit_nome').value = r.nome;
                        document.getElementById('edit_email').value = r.email;
                        document.getElementById('edit_telefone').value = r.telefone || '';
                        document.getElementById('edit_cidade').value = r.cidade || '';
                        document.getElementById('edit_endereco').value = r.endereco || '';
                        document.getElementById('edit_nuit').value = r.nuit || '';
                        document.getElementById('edit_plano').value = r.plano;
                        document.getElementById('edit_status').value = r.status;
                        new bootstrap.Modal(document.getElementById('modalEditar')).show();
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(err => {
                    showAlert('Erro ao carregar dados: ' + err.message, 'danger');
                });
        }

        // Salvar edição
        function salvarEdicao() {
            const form = document.getElementById('formEditar');
            const formData = new FormData(form);

            fetch('api/restaurante_editar.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('modalEditar')).hide();
                        carregarRestaurantes();
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(err => {
                    showAlert('Erro ao salvar: ' + err.message, 'danger');
                });
        }

        // Alterar status rapidamente (aprovar/rejeitar)
        function mudarStatus(id, novoStatus) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('status', novoStatus);

            fetch('api/restaurante_editar.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Status alterado com sucesso', 'success');
                        carregarRestaurantes();
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(err => {
                    showAlert('Erro ao alterar status: ' + err.message, 'danger');
                });
        }

        // Deletar restaurante
        function deletarRestaurante(id, nome) {
            if (!confirm(`Tem certeza que deseja excluir o restaurante "${nome}"? Esta ação não pode ser desfeita.`)) {
                return;
            }

            fetch('api/restaurante_deletar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id
                    }),
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        carregarRestaurantes();
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(err => {
                    showAlert('Erro ao deletar: ' + err.message, 'danger');
                });
        }

        // ==================== USUÁRIOS ====================

        // Carregar restaurantes para seletor de usuários
        function carregarRestaurantesParaUsuario() {
            fetch('api/restaurante_listar.php', {
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const select = document.getElementById('restauranteSelecionadoUsuarios');
                        let html = '<option value="">Selecione um restaurante...</option>';
                        data.data.forEach(r => {
                            html += `<option value="${r.id}">${r.nome}</option>`;
                        });
                        select.innerHTML = html;
                    }
                })
                .catch(err => console.error('Erro:', err));
        }

        // Carregar usuários
        function carregarUsuarios() {
            const restauranteId = document.getElementById('restauranteSelecionadoUsuarios').value;
            const tbody = document.getElementById('tabelaUsuarios');

            if (!restauranteId) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Selecione um restaurante para ver os usuários</td></tr>';
                return;
            }

            // Definir ID no form oculto
            document.getElementById('usuario_restaurante_id').value = restauranteId;

            fetch('api/super_admin_usuarios_listar.php?restaurante_id=' + restauranteId, {
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.data.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Nenhum usuário encontrado</td></tr>';
                            return;
                        }

                        let html = '';
                        data.data.forEach(u => {
                            const perfilBadge = u.perfil === 'ADMIN' ? '<span class="badge bg-primary">Admin</span>' :
                                u.perfil === 'COZINHA' ? '<span class="badge bg-warning">Cozinha</span>' :
                                '<span class="badge bg-info">Operador</span>';
                            const statusBadge = u.ativo ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>';

                            html += `<tr>
                                <td class="ps-4"><strong>#${u.id}</strong></td>
                                <td>${u.nome}</td>
                                <td>${u.email}</td>
                                <td>${perfilBadge}</td>
                                <td>${statusBadge}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger" onclick="deletarUsuario(${u.id}, '${u.nome}')" title="Excluir"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>`;
                        });
                        tbody.innerHTML = html;
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(err => {
                    console.error('Erro:', err);
                    showAlert('Erro ao carregar usuários', 'danger');
                });
        }

        // Cadastrar usuário
        function cadastrarUsuario() {
            const restauranteId = document.getElementById('restauranteSelecionadoUsuarios').value;
            if (!restauranteId) {
                showAlert('Selecione um restaurante primeiro!', 'warning');
                return;
            }

            const form = document.getElementById('formCadastrarUsuario');
            const formData = new FormData(form);
            formData.set('restaurante_id', restauranteId);

            fetch('api/super_admin_usuario_cadastrar.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('modalCadastrarUsuario')).hide();
                        form.reset();
                        carregarUsuarios();
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(err => {
                    showAlert('Erro ao cadastrar: ' + err.message, 'danger');
                });
        }

        // Deletar usuário
        function deletarUsuario(id, nome) {
            if (!confirm(`Tem certeza que deseja excluir o usuário "${nome}"?`)) {
                return;
            }

            fetch('api/super_admin_usuario_deletar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        usuario_id: id
                    }),
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        carregarUsuarios();
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(err => {
                    showAlert('Erro ao deletar: ' + err.message, 'danger');
                });
        }

        // ==================== PLANOS ====================

        // Carregar compras de planos
        function carregarCompras() {
            fetch('api/super_admin_compras_listar.php', {
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        atualizarTabelaCompras(data.data);
                        atualizarEstatisticasCompras(data.data);
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(err => {
                    console.error('Erro:', err);
                    showAlert('Erro ao carregar compras', 'danger');
                });
        }

        // Atualizar tabela de compras
        function atualizarTabelaCompras(compras) {
            const tbody = document.getElementById('tabelaCompras');

            if (compras.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">Nenhuma compra encontrada</td></tr>';
                return;
            }

            let html = '';
            compras.forEach(c => {
                let statusBadge = c.status === 'APROVADO' ? '<span class="badge bg-success">Aprovado</span>' :
                    c.status === 'PENDENTE' ? '<span class="badge bg-warning">Pendente</span>' :
                    '<span class="badge bg-danger">Cancelado</span>';

                const dataFormatada = new Date(c.criado_em).toLocaleDateString('pt-BR');

                let botoes = '';
                if (c.status === 'PENDENTE') {
                    botoes = `<button class="btn btn-sm btn-success me-1" onclick="aprovarCompra(${c.id})" title="Aprovar"><i class="fas fa-check"></i></button>
                              <button class="btn btn-sm btn-danger" onclick="rejeitarCompra(${c.id})" title="Rejeitar"><i class="fas fa-times"></i></button>`;
                } else {
                    botoes = '-';
                }

                html += `<tr>
                    <td class="ps-4"><strong>#${c.id}</strong></td>
                    <td>${c.restaurante_nome}</td>
                    <td>${c.plano_atual}</td>
                    <td>${c.plano_novo}</td>
                    <td>${parseFloat(c.valor).toFixed(2)} MZN</td>
                    <td>${c.metodo_pagamento}</td>
                    <td>${statusBadge}</td>
                    <td>${dataFormatada}</td>
                    <td class="text-center">${botoes}</td>
                </tr>`;
            });

            tbody.innerHTML = html;
        }

        // Atualizar estatísticas de compras
        function atualizarEstatisticasCompras(compras) {
            document.getElementById('totalCompras').textContent = compras.length;
            document.getElementById('totalPendentes').textContent = compras.filter(c => c.status === 'PENDENTE').length;
            document.getElementById('totalAprovados').textContent = compras.filter(c => c.status === 'APROVADO').length;
        }

        // Aprovar compra - abre modal
        function aprovarCompra(id) {
            document.getElementById('compra_id_aprovar').value = id;
            document.getElementById('obs_aprovar').value = '';
            new bootstrap.Modal(document.getElementById('modalAprovarCompra')).show();
        }

        // Confirmar aprovação com observação
        function confirmarAprovacao() {
            const compra_id = document.getElementById('compra_id_aprovar').value;
            const observacao = document.getElementById('obs_aprovar').value;

            fetch('api/super_admin_plano_aprovar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'compra_id=' + compra_id + '&acao=aprovar&observacao=' + encodeURIComponent(observacao),
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('modalAprovarCompra')).hide();
                        carregarCompras();
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(err => {
                    showAlert('Erro: ' + err.message, 'danger');
                });
        }

        // Rejeitar compra - abre modal
        function rejeitarCompra(id) {
            document.getElementById('compra_id_rejeitar').value = id;
            document.getElementById('obs_rejeitar').value = '';
            new bootstrap.Modal(document.getElementById('modalRejeitarCompra')).show();
        }

        // Confirmar rejeição com observação
        function confirmarRejeicao() {
            const compra_id = document.getElementById('compra_id_rejeitar').value;
            const observacao = document.getElementById('obs_rejeitar').value;

            if (!observacao.trim()) {
                showAlert('Informe o motivo da rejeição!', 'warning');
                return;
            }

            fetch('api/super_admin_plano_aprovar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'compra_id=' + compra_id + '&acao=rejeitar&observacao=' + encodeURIComponent(observacao),
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('modalRejeitarCompra')).hide();
                        carregarCompras();
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(err => {
                    showAlert('Erro: ' + err.message, 'danger');
                });
        }
    </script>
</body>

</html>
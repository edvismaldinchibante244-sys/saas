<?php
/**
 * ============================================
 * CARDÁPIO DIGITAL - PÁGINA PÚBLICA
 * Acessado via QR Code pelo cliente
 * ============================================
 */

$rid     = (int)($_GET['rid']  ?? 0);
$mesa_id = (int)($_GET['mesa'] ?? 0);

if (!$rid) {
    http_response_code(404);
    die('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Erro</title></head><body style="font-family:sans-serif;text-align:center;padding:60px;color:#666"><h2>QR Code inválido</h2><p>Por favor, escaneie o QR Code correto.</p></body></html>');
}

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Buscar restaurante
$stmt = $db->prepare("SELECT * FROM restaurantes WHERE id = :id LIMIT 1");
$stmt->bindParam(':id', $rid);
$stmt->execute();
$restaurante = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$restaurante) {
    http_response_code(404);
    die('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Erro</title></head><body style="font-family:sans-serif;text-align:center;padding:60px;color:#666"><h2>Restaurante não encontrado</h2></body></html>');
}

// Buscar mesa
$mesa = null;
if ($mesa_id) {
    $stmt = $db->prepare("SELECT * FROM mesas WHERE id = :id AND restaurante_id = :rid LIMIT 1");
    $stmt->bindParam(':id',  $mesa_id);
    $stmt->bindParam(':rid', $rid);
    $stmt->execute();
    $mesa = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Buscar categorias com produtos
$stmt = $db->prepare("SELECT * FROM categorias WHERE restaurante_id = :rid ORDER BY nome ASC");
$stmt->bindParam(':rid', $rid);
$stmt->execute();
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar produtos ativos
$stmt = $db->prepare("
    SELECT p.*, c.nome AS categoria_nome
    FROM produtos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    WHERE p.restaurante_id = :rid AND p.ativo = 1
    ORDER BY c.nome ASC, p.nome ASC
");
$stmt->bindParam(':rid', $rid);
$stmt->execute();
$todos_produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por categoria
$por_categoria = [];
foreach ($todos_produtos as $p) {
    $cat = $p['categoria_nome'] ?? 'Outros';
    $por_categoria[$cat][] = $p;
}

// Emojis por categoria (fallback visual)
$cat_emojis = [
    'Entradas'   => '🥗', 'Pratos'     => '🍽️', 'Principais' => '🍖',
    'Bebidas'    => '🥤', 'Sobremesas' => '🍰', 'Lanches'    => '🍔',
    'Pizzas'     => '🍕', 'Massas'     => '🍝', 'Grelhados'  => '🥩',
    'Frutos do Mar' => '🦐', 'Vegetariano' => '🥦', 'Outros'  => '🍴',
];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#FF6B35">
    <title>Cardápio — <?php echo htmlspecialchars($restaurante['nome']); ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        :root {
            --primary:   #FF6B35;
            --secondary: #F7931E;
            --gradient:  linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
            --dark:      #1a1a2e;
            --text:      #2d3748;
            --text-light:#718096;
            --border:    #e2e8f0;
            --bg:        #f8f9fa;
            --white:     #ffffff;
            --success:   #28a745;
            --radius:    12px;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding-bottom: 110px;
        }

        /* ===== HEADER ===== */
        .header {
            background: var(--gradient);
            color: white;
            padding: 22px 16px 18px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(255,107,53,0.35);
        }

        .header-logo { font-size: 32px; margin-bottom: 4px; line-height: 1; }

        .header h1 {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.3px;
            margin-bottom: 6px;
        }

        .mesa-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(255,255,255,0.22);
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            backdrop-filter: blur(4px);
        }

        /* ===== BUSCA ===== */
        .search-bar {
            background: white;
            padding: 12px 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .search-input {
            width: 100%;
            padding: 11px 16px 11px 40px;
            border: 2px solid var(--border);
            border-radius: 25px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            background: var(--bg) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23718096' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.099zm-5.242 1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z'/%3E%3C/svg%3E") no-repeat 14px center;
            transition: all 0.2s;
            color: var(--text);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255,107,53,0.12);
        }

        /* ===== TABS DE CATEGORIA ===== */
        .cat-tabs {
            background: white;
            padding: 10px 16px;
            display: flex;
            gap: 8px;
            overflow-x: auto;
            position: sticky;
            top: 100px;
            z-index: 99;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            scrollbar-width: none;
        }

        .cat-tabs::-webkit-scrollbar { display: none; }

        .cat-tab {
            padding: 7px 16px;
            border-radius: 20px;
            border: 2px solid var(--border);
            background: white;
            color: var(--text-light);
            font-size: 12.5px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .cat-tab.active,
        .cat-tab:hover {
            background: var(--gradient);
            color: white;
            border-color: transparent;
            box-shadow: 0 3px 10px rgba(255,107,53,0.3);
        }

        /* ===== SEÇÃO DE CATEGORIA ===== */
        .cat-section { margin-top: 8px; }

        .cat-section-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            padding: 18px 16px 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cat-section-title::after {
            content: '';
            flex: 1;
            height: 2px;
            background: var(--border);
            border-radius: 1px;
        }

        /* ===== CARD DE PRODUTO ===== */
        .produtos-lista { padding: 0 16px; display: flex; flex-direction: column; gap: 10px; }

        .produto-card {
            background: white;
            border-radius: var(--radius);
            padding: 14px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: all 0.2s;
            border: 2px solid transparent;
            cursor: pointer;
        }

        .produto-card:hover,
        .produto-card:active {
            border-color: var(--primary);
            box-shadow: 0 4px 16px rgba(255,107,53,0.15);
            transform: translateY(-1px);
        }

        .produto-thumb {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #fff3ed, #ffe5d0);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            flex-shrink: 0;
        }

        .produto-info { flex: 1; min-width: 0; }

        .produto-nome {
            font-weight: 700;
            font-size: 14.5px;
            color: var(--text);
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .produto-desc {
            font-size: 12px;
            color: var(--text-light);
            margin-bottom: 6px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
        }

        .produto-preco {
            font-weight: 700;
            font-size: 16px;
            color: var(--primary);
        }

        .btn-add {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--gradient);
            color: white;
            border: none;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(255,107,53,0.35);
            line-height: 1;
        }

        .btn-add:hover  { transform: scale(1.15); }
        .btn-add:active { transform: scale(0.92); }

        /* ===== ESTADO VAZIO ===== */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }

        .empty-state .icon { font-size: 60px; margin-bottom: 16px; }
        .empty-state h3 { font-size: 18px; font-weight: 600; margin-bottom: 8px; }
        .empty-state p  { font-size: 14px; }

        /* ===== BOTÃO FLUTUANTE DO CARRINHO ===== */
        .cart-fab {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--gradient);
            color: white;
            border: none;
            padding: 15px 28px;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            box-shadow: 0 8px 30px rgba(255,107,53,0.5);
            display: none;
            align-items: center;
            gap: 10px;
            z-index: 200;
            transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
            min-width: 230px;
            justify-content: center;
            white-space: nowrap;
        }

        .cart-fab.visible { display: flex; animation: fabIn 0.35s cubic-bezier(0.4,0,0.2,1); }

        @keyframes fabIn {
            from { opacity: 0; transform: translateX(-50%) translateY(20px); }
            to   { opacity: 1; transform: translateX(-50%) translateY(0); }
        }

        .cart-fab:hover { transform: translateX(-50%) translateY(-3px); box-shadow: 0 12px 35px rgba(255,107,53,0.55); }

        .cart-count {
            background: white;
            color: var(--primary);
            border-radius: 50%;
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
        }

        /* ===== OVERLAY ===== */
        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 300;
            -webkit-backdrop-filter: blur(3px);
            backdrop-filter: blur(3px);
        }

        .overlay.open { display: block; animation: fadeIn 0.25s ease; }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        /* ===== DRAWER DO CARRINHO ===== */
        .cart-drawer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-radius: 20px 20px 0 0;
            padding: 0 20px 30px;
            max-height: 88vh;
            overflow-y: auto;
            z-index: 301;
            transform: translateY(100%);
            transition: transform 0.35s cubic-bezier(0.4,0,0.2,1);
            box-shadow: 0 -10px 40px rgba(0,0,0,0.2);
        }

        .cart-drawer.open { transform: translateY(0); }

        .drawer-handle {
            width: 40px;
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            margin: 14px auto 0;
        }

        .drawer-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 0 14px;
            border-bottom: 2px solid var(--border);
            position: sticky;
            top: 0;
            background: white;
            z-index: 1;
        }

        .drawer-header h2 { font-size: 19px; font-weight: 700; }

        .btn-close-drawer {
            background: #f0f2f5;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            transition: all 0.2s;
        }

        .btn-close-drawer:hover { background: #f8d7da; color: #dc3545; }

        /* ===== ITENS DO CARRINHO ===== */
        .cart-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f0f2f5;
        }

        .cart-item:last-child { border-bottom: none; }

        .cart-item-info { flex: 1; min-width: 0; }
        .cart-item-nome { font-weight: 600; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cart-item-preco { color: var(--text-light); font-size: 12px; margin-top: 2px; }

        .cart-item-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .btn-qtd {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid var(--border);
            background: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            color: var(--text);
            line-height: 1;
        }

        .btn-qtd:hover { border-color: var(--primary); color: var(--primary); }

        .cart-item-qty { font-weight: 700; font-size: 15px; min-width: 20px; text-align: center; }

        .cart-item-sub { font-weight: 700; color: var(--primary); font-size: 14px; min-width: 75px; text-align: right; flex-shrink: 0; }

        /* ===== TOTAL ===== */
        .cart-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0 8px;
            border-top: 2px solid var(--border);
            margin-top: 4px;
        }

        .cart-total-row .label { font-size: 16px; font-weight: 600; color: var(--text-light); }
        .cart-total-row .value { font-size: 24px; font-weight: 700; color: var(--primary); }

        /* ===== FORMULÁRIO DO PEDIDO ===== */
        .order-form { margin-top: 16px; }

        .order-form .fg { margin-bottom: 14px; }

        .order-form label {
            display: block;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 6px;
            color: var(--text);
        }

        .order-form input,
        .order-form textarea,
        .order-form select {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: var(--text);
            background: #fafafa;
            transition: all 0.2s;
        }

        .order-form input:focus,
        .order-form textarea:focus,
        .order-form select:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(255,107,53,0.1);
        }

        .order-form textarea { resize: none; height: 80px; }

        .btn-fazer-pedido {
            width: 100%;
            padding: 16px;
            background: var(--gradient);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 6px;
            letter-spacing: 0.3px;
        }

        .btn-fazer-pedido:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(255,107,53,0.4); }
        .btn-fazer-pedido:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        /* ===== TELA DE SUCESSO ===== */
        .success-screen {
            display: none;
            text-align: center;
            padding: 40px 20px;
        }

        .success-icon { font-size: 72px; margin-bottom: 16px; animation: bounceIn 0.6s; }

        @keyframes bounceIn {
            0%   { transform: scale(0); }
            60%  { transform: scale(1.15); }
            100% { transform: scale(1); }
        }

        .success-screen h2 { font-size: 22px; font-weight: 700; margin-bottom: 8px; color: var(--success); }
        .success-screen p  { color: var(--text-light); font-size: 14px; line-height: 1.6; }

        .success-num {
            display: inline-block;
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 16px;
            margin: 12px 0;
        }

        .btn-novo-pedido {
            margin-top: 20px;
            padding: 13px 28px;
            background: var(--gradient);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-novo-pedido:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,107,53,0.35); }

        /* ===== ALERT ===== */
        .alert-msg {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            margin-top: 12px;
            display: none;
        }

        .alert-msg.error   { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .alert-msg.success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }

        /* ===== FOOTER ===== */
        .footer {
            text-align: center;
            padding: 20px;
            color: var(--text-light);
            font-size: 12px;
        }
    </style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <div class="header-logo">🍽️</div>
    <h1><?php echo htmlspecialchars($restaurante['nome']); ?></h1>
    <?php if ($mesa): ?>
        <div class="mesa-badge">🪑 Mesa <?php echo htmlspecialchars($mesa['numero']); ?></div>
    <?php else: ?>
        <div class="mesa-badge">📱 Cardápio Digital</div>
    <?php endif; ?>
</div>

<!-- BUSCA -->
<div class="search-bar">
    <input type="text" class="search-input" id="searchInput" placeholder="Buscar no cardápio..." oninput="buscarProduto(this.value)">
</div>

<!-- TABS DE CATEGORIA -->
<?php if (!empty($por_categoria)): ?>
<div class="cat-tabs" id="catTabs">
    <button class="cat-tab active" onclick="filtrarCat('todas', this)">🍽️ Todos</button>
    <?php foreach (array_keys($por_categoria) as $cat_nome): ?>
        <button class="cat-tab" onclick="filtrarCat('<?php echo htmlspecialchars(addslashes($cat_nome)); ?>', this)">
            <?php echo ($cat_emojis[$cat_nome] ?? '🍴') . ' ' . htmlspecialchars($cat_nome); ?>
        </button>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- CONTEÚDO DO CARDÁPIO -->
<div id="cardapioContent">
    <?php if (empty($todos_produtos)): ?>
        <div class="empty-state">
            <div class="icon">🍽️</div>
            <h3>Cardápio em preparação</h3>
            <p>Em breve teremos novidades para você!</p>
        </div>
    <?php else: ?>
        <?php foreach ($por_categoria as $cat_nome => $produtos): ?>
            <div class="cat-section" data-cat="<?php echo htmlspecialchars($cat_nome); ?>">
                <div class="cat-section-title">
                    <?php echo ($cat_emojis[$cat_nome] ?? '🍴') . ' ' . htmlspecialchars($cat_nome); ?>
                </div>
                <div class="produtos-lista">
                    <?php foreach ($produtos as $p): ?>
                        <div class="produto-card" data-nome="<?php echo strtolower(htmlspecialchars($p['nome'])); ?>"
                             onclick="adicionarItem(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars(addslashes($p['nome'])); ?>', <?php echo $p['preco']; ?>)">
                            <div class="produto-thumb">
                                <?php echo $cat_emojis[$cat_nome] ?? '🍴'; ?>
                            </div>
                            <div class="produto-info">
                                <div class="produto-nome"><?php echo htmlspecialchars($p['nome']); ?></div>
                                <?php if (!empty($p['descricao'])): ?>
                                    <div class="produto-desc"><?php echo htmlspecialchars($p['descricao']); ?></div>
                                <?php endif; ?>
                                <div class="produto-preco"><?php echo number_format($p['preco'], 2, ',', '.'); ?> MZN</div>
                            </div>
                            <button class="btn-add" onclick="event.stopPropagation(); adicionarItem(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars(addslashes($p['nome'])); ?>', <?php echo $p['preco']; ?>)" title="Adicionar">
                                +
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- FOOTER -->
<div class="footer">
    Powered by RestauranteSaaS 🍽️
</div>

<!-- BOTÃO FLUTUANTE DO CARRINHO -->
<button class="cart-fab" id="cartFab" onclick="abrirCarrinho()">
    🛒 Ver Carrinho
    <span class="cart-count" id="cartCount">0</span>
    &bull;
    <span id="cartTotal">0,00 MZN</span>
</button>

<!-- OVERLAY -->
<div class="overlay" id="overlay" onclick="fecharCarrinho()"></div>

<!-- DRAWER DO CARRINHO -->
<div class="cart-drawer" id="cartDrawer">
    <div class="drawer-handle"></div>

    <!-- TELA NORMAL DO CARRINHO -->
    <div id="cartScreen">
        <div class="drawer-header">
            <h2>🛒 Meu Pedido</h2>
            <button class="btn-close-drawer" onclick="fecharCarrinho()">✕</button>
        </div>

        <!-- ITENS -->
        <div id="cartItems"></div>

        <!-- TOTAL -->
        <div class="cart-total-row">
            <span class="label">Total</span>
            <span class="value" id="cartTotalDrawer">0,00 MZN</span>
        </div>

        <!-- FORMULÁRIO -->
        <div class="order-form">
            <div class="fg">
                <label>👤 Seu nome (opcional)</label>
                <input type="text" id="clienteNome" placeholder="Ex: João Silva">
            </div>
            <div class="fg">
                <label>📝 Observações (opcional)</label>
                <textarea id="observacao" placeholder="Ex: Sem cebola, bem passado..."></textarea>
            </div>

            <div class="alert-msg" id="alertPedido"></div>

            <button class="btn-fazer-pedido" id="btnFazerPedido" onclick="fazerPedido()">
                ✅ Confirmar Pedido
            </button>
        </div>
    </div>

    <!-- TELA DE SUCESSO -->
    <div class="success-screen" id="successScreen">
        <div class="success-icon">🎉</div>
        <h2>Pedido Enviado!</h2>
        <p>Seu pedido foi recebido com sucesso.</p>
        <div class="success-num" id="successNum"></div>
        <p>Aguarde, estamos preparando tudo com carinho! 😊</p>
        <button class="btn-novo-pedido" onclick="novoPedido()">
            🍽️ Fazer Novo Pedido
        </button>
    </div>
</div>

<script>
    var carrinho = [];
    var rid      = <?php echo (int)$rid; ?>;
    var mesaId   = <?php echo (int)$mesa_id; ?>;

    // ===== ADICIONAR ITEM =====
    function adicionarItem(id, nome, preco) {
        var idx = carrinho.findIndex(function(i) { return i.id === id; });
        if (idx !== -1) {
            carrinho[idx].qtd++;
        } else {
            carrinho.push({ id: id, nome: nome, preco: parseFloat(preco), qtd: 1 });
        }
        atualizarFAB();
        animarBotao(id);
    }

    // ===== ANIMAÇÃO DO BOTÃO ADD =====
    function animarBotao(id) {
        var btns = document.querySelectorAll('.btn-add');
        btns.forEach(function(btn) {
            var card = btn.closest('.produto-card');
            if (card && card.getAttribute('onclick') && card.getAttribute('onclick').includes(',' + id + ',')) {
                btn.style.transform = 'scale(1.4)';
                btn.style.background = '#28a745';
                setTimeout(function() {
                    btn.style.transform = '';
                    btn.style.background = '';
                }, 300);
            }
        });
    }

    // ===== ATUALIZAR FAB =====
    function atualizarFAB() {
        var fab   = document.getElementById('cartFab');
        var total = carrinho.reduce(function(s, i) { return s + i.preco * i.qtd; }, 0);
        var qtd   = carrinho.reduce(function(s, i) { return s + i.qtd; }, 0);

        document.getElementById('cartCount').textContent = qtd;
        document.getElementById('cartTotal').textContent = formatMZN(total);

        if (qtd > 0) {
            fab.classList.add('visible');
        } else {
            fab.classList.remove('visible');
        }
    }

    // ===== ABRIR CARRINHO =====
    function abrirCarrinho() {
        renderCarrinho();
        document.getElementById('overlay').classList.add('open');
        document.getElementById('cartDrawer').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    // ===== FECHAR CARRINHO =====
    function fecharCarrinho() {
        document.getElementById('overlay').classList.remove('open');
        document.getElementById('cartDrawer').classList.remove('open');
        document.body.style.overflow = '';
    }

    // ===== RENDERIZAR CARRINHO =====
    function renderCarrinho() {
        var container = document.getElementById('cartItems');
        var html = '';

        if (carrinho.length === 0) {
            html = '<div style="text-align:center;padding:30px;color:#718096;font-size:14px;">🛒 Carrinho vazio</div>';
        } else {
            carrinho.forEach(function(item, idx) {
                var sub = item.preco * item.qtd;
                html += '<div class="cart-item">';
                html += '<div class="cart-item-info">';
                html += '<div class="cart-item-nome">' + item.nome + '</div>';
                html += '<div class="cart-item-preco">' + formatMZN(item.preco) + ' cada</div>';
                html += '</div>';
                html += '<div class="cart-item-controls">';
                html += '<button class="btn-qtd" onclick="alterarQtd(' + idx + ', -1)">−</button>';
                html += '<span class="cart-item-qty">' + item.qtd + '</span>';
                html += '<button class="btn-qtd" onclick="alterarQtd(' + idx + ', 1)">+</button>';
                html += '</div>';
                html += '<div class="cart-item-sub">' + formatMZN(sub) + '</div>';
                html += '</div>';
            });
        }

        container.innerHTML = html;

        var total = carrinho.reduce(function(s, i) { return s + i.preco * i.qtd; }, 0);
        document.getElementById('cartTotalDrawer').textContent = formatMZN(total);
    }

    // ===== ALTERAR QUANTIDADE =====
    function alterarQtd(idx, delta) {
        carrinho[idx].qtd += delta;
        if (carrinho[idx].qtd <= 0) {
            carrinho.splice(idx, 1);
        }
        renderCarrinho();
        atualizarFAB();
    }

    // ===== FAZER PEDIDO =====
    function fazerPedido() {
        if (carrinho.length === 0) {
            mostrarAlerta('Adicione pelo menos um item ao carrinho.', 'error');
            return;
        }

        var btn = document.getElementById('btnFazerPedido');
        btn.disabled = true;
        btn.textContent = '⏳ Enviando...';

        var total = carrinho.reduce(function(s, i) { return s + i.preco * i.qtd; }, 0);

        var dados = {
            rid:         rid,
            mesa_id:     mesaId,
            cliente_nome: document.getElementById('clienteNome').value.trim(),
            observacao:  document.getElementById('observacao').value.trim(),
            total:       total,
            itens:       carrinho
        };

        fetch('api/pedido_novo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                document.getElementById('successNum').textContent = '# ' + data.numero_pedido;
                document.getElementById('cartScreen').style.display = 'none';
                document.getElementById('successScreen').style.display = 'block';
                carrinho = [];
                atualizarFAB();
            } else {
                mostrarAlerta(data.message || 'Erro ao enviar pedido.', 'error');
                btn.disabled = false;
                btn.textContent = '✅ Confirmar Pedido';
            }
        })
        .catch(function() {
            mostrarAlerta('Erro de conexão. Tente novamente.', 'error');
            btn.disabled = false;
            btn.textContent = '✅ Confirmar Pedido';
        });
    }

    // ===== NOVO PEDIDO =====
    function novoPedido() {
        document.getElementById('cartScreen').style.display = 'block';
        document.getElementById('successScreen').style.display = 'none';
        document.getElementById('clienteNome').value = '';
        document.getElementById('observacao').value = '';
        document.getElementById('btnFazerPedido').disabled = false;
        document.getElementById('btnFazerPedido').textContent = '✅ Confirmar Pedido';
        fecharCarrinho();
    }

    // ===== FILTRAR CATEGORIA =====
    function filtrarCat(cat, btn) {
        document.querySelectorAll('.cat-tab').forEach(function(t) { t.classList.remove('active'); });
        btn.classList.add('active');

        document.querySelectorAll('.cat-section').forEach(function(sec) {
            if (cat === 'todas' || sec.getAttribute('data-cat') === cat) {
                sec.style.display = '';
            } else {
                sec.style.display = 'none';
            }
        });

        document.getElementById('searchInput').value = '';
    }

    // ===== BUSCAR PRODUTO =====
    function buscarProduto(q) {
        q = q.toLowerCase().trim();

        document.querySelectorAll('.cat-tab').forEach(function(t) { t.classList.remove('active'); });
        document.querySelector('.cat-tab').classList.add('active');

        document.querySelectorAll('.cat-section').forEach(function(sec) { sec.style.display = ''; });

        document.querySelectorAll('.produto-card').forEach(function(card) {
            var nome = card.getAttribute('data-nome') || '';
            card.style.display = (!q || nome.includes(q)) ? '' : 'none';
        });
    }

    // ===== MOSTRAR ALERTA =====
    function mostrarAlerta(msg, tipo) {
        var el = document.getElementById('alertPedido');
        el.textContent = msg;
        el.className = 'alert-msg ' + tipo;
        el.style.display = 'block';
        setTimeout(function() { el.style.display = 'none'; }, 4000);
    }

    // ===== FORMATAR MOEDA =====
    function formatMZN(v) {
        return parseFloat(v).toFixed(2).replace('.', ',') + ' MZN';
    }
</script>
</body>
</html>

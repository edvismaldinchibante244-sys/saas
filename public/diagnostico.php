<?php

/**
 * ============================================
 * DIAGNÓSTICO DO SISTEMA
 * ============================================
 */

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Diagnóstico - Sistema Restaurant</title>";
echo "<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
    h1 { color: #333; }
    .test { padding: 10px; margin: 10px 0; border-radius: 4px; }
    .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    pre { background: #f8f9fa; padding: 10px; overflow-x: auto; }
</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔧 Diagnóstico do Sistema</h1>";

$todos_ok = true;

// Teste 1: Conexão com banco
echo "<h2>1. Conexão com Banco de Dados</h2>";
try {
    $host = "localhost";
    $db_name = "restaurante_saas";
    $username = "root";
    $password = "";

    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div class='test success'>✓ Conexão estabelecida com sucesso!</div>";
} catch (PDOException $e) {
    echo "<div class='test error'>✗ Erro de conexão: " . $e->getMessage() . "</div>";
    $todos_ok = false;
}

// Teste 2: Verificar tabelas
if ($todos_ok) {
    echo "<h2>2. Verificando Tabelas</h2>";
    $tables = ['usuarios', 'restaurantes', 'produtos', 'categorias', 'mesas', 'vendas', 'caixa', 'pedidos', 'itens_pedido'];
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT 1 FROM $table LIMIT 1");
            echo "<div class='test success'>✓ Tabela '$table' existe</div>";
        } catch (PDOException $e) {
            echo "<div class='test error'>✗ Tabela '$table' não existe</div>";
            $todos_ok = false;
        }
    }
}

// Teste 3: Verificar usuários
if ($todos_ok) {
    echo "<h2>3. Verificando Usuários</h2>";
    $stmt = $conn->query("SELECT id, nome, email, ativo, perfil FROM usuarios");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($usuarios) > 0) {
        echo "<div class='test success'>✓ " . count($usuarios) . " usuário(s) encontrado(s)</div>";
        echo "<pre>";
        print_r($usuarios);
        echo "</pre>";
    } else {
        echo "<div class='test error'>✗ Nenhum usuário encontrado!</div>";
        $todos_ok = false;
    }
}

// Teste 4: Verificar restaurante
if ($todos_ok) {
    echo "<h2>4. Verificando Restaurante</h2>";
    $stmt = $conn->query("SELECT id, nome, status, plano, data_fim FROM restaurantes");
    $rest = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($rest) {
        echo "<div class='test info'>Restaurante: " . $rest['nome'] . "</div>";
        echo "<div class='test info'>Status: " . $rest['status'] . "</div>";
        echo "<div class='test info'>Plano: " . $rest['plano'] . "</div>";
        echo "<div class='test info'>Data fim: " . $rest['data_fim'] . "</div>";

        if ($rest['status'] != 'ATIVO') {
            echo "<div class='test error'>✗ Restaurante está bloqueado!</div>";
            $todos_ok = false;
        }

        if (strtotime($rest['data_fim']) < time()) {
            echo "<div class='test warning'>⚠ Assinatura expirada! data_fim: " . $rest['data_fim'] . "</div>";
        }
    } else {
        echo "<div class='test error'>✗ Nenhum restaurante encontrado!</div>";
        $todos_ok = false;
    }
}

// Teste 5: Testar login
if ($todos_ok) {
    echo "<h2>5. Testando Login</h2>";
    $stmt = $conn->prepare("SELECT u.id, u.nome, u.email, u.senha, u.ativo, u.perfil, r.status as restaurante_status, r.plano, r.data_fim 
                           FROM usuarios u 
                           INNER JOIN restaurantes r ON u.restaurante_id = r.id 
                           WHERE u.email = :email LIMIT 1");
    $stmt->execute([':email' => 'admin@sabormoz.co.mz']);

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "<div class='test info'>Usuário encontrado: " . $row['nome'] . "</div>";
        echo "<div class='test info'>Ativo: " . ($row['ativo'] ? 'Sim' : 'Não') . "</div>";
        echo "<div class='test info'>Perfil: " . $row['perfil'] . "</div>";

        // Testar senha
        if (password_verify('admin123', $row['senha'])) {
            echo "<div class='test success'>✓ Senha 'admin123' está correta!</div>";
        } else {
            echo "<div class='test warning'>⚠ Senha 'admin123' NÃO confere!</div>";
        }
    } else {
        echo "<div class='test error'>✗ Usuário admin@sabormoz.co.mz não encontrado!</div>";
    }
}

echo "<h2>Resultado Final</h2>";
if ($todos_ok) {
    echo "<div class='test success'>✓ Sistema OK! Tente fazer login novamente.</div>";
} else {
    echo "<div class='test error'>✗ Problemas encontrados! Execute o setup.</div>";
}

echo "<h2>Ações</h2>";
echo "<p><a href='setup.php' style='display:inline-block;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:4px;'>▶ Executar Setup/Reparar Banco</a></p>";
echo "<p><a href='index.php' style='display:inline-block;padding:10px 20px;background:#28a745;color:white;text-decoration:none;border-radius:4px;'>← Voltar ao Login</a></p>";

echo "</div></body></html>";

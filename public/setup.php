<?php

/**
 * ============================================
 * SETUP DO SISTEMA
 * Cria banco, tabelas e usuário admin
 * ============================================
 */

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Setup - Sistema Restaurant</title>";
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
    .btn { display:inline-block;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:4px;margin:5px; }
    .btn-green { background:#28a745; }
</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔧 Setup do Sistema</h1>";

$host = "localhost";
$db_name = "restaurante_saas";
$username = "root";
$password = "";

try {
    // Conectar sem banco
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div class='test info'>✓ Conectado ao MySQL</div>";

    // Criar banco
    $conn->exec("CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<div class='test success'>✓ Banco '$db_name' criado/existente</div>";

    // Selecionar banco
    $conn->exec("USE $db_name");

    // Criar tabelas
    echo "<h2>Criando tabelas...</h2>";

    // Restaurantes (estrutura completa)
    $conn->exec("CREATE TABLE IF NOT EXISTS restaurantes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(150) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        telefone VARCHAR(20),
        endereco VARCHAR(255),
        cidade VARCHAR(100),
        nuit VARCHAR(20),
        logo VARCHAR(255),
        plano ENUM('BASICO', 'PROFISSIONAL', 'ENTERPRISE') DEFAULT 'BASICO',
        status ENUM('PENDENTE','ATIVO', 'BLOQUEADO', 'CANCELADO') DEFAULT 'PENDENTE',
        data_inicio DATE NOT NULL,
        data_fim DATE NOT NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class='test success'>✓ Tabela 'restaurantes'</div>";

    // Usuários
    $conn->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        restaurante_id INT NOT NULL,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        senha VARCHAR(255) NOT NULL,
        perfil ENUM('ADMIN', 'CAIXA', 'GARCOM') DEFAULT 'CAIXA',
        ativo TINYINT(1) DEFAULT 1,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class='test success'>✓ Tabela 'usuarios'</div>";

    // Categorias
    $conn->exec("CREATE TABLE IF NOT EXISTS categorias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        restaurante_id INT NOT NULL,
        nome VARCHAR(100) NOT NULL,
        descricao VARCHAR(255),
        ativo TINYINT(1) DEFAULT 1,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class='test success'>✓ Tabela 'categorias'</div>";

    // Produtos (estrutura completa)
    $conn->exec("CREATE TABLE IF NOT EXISTS produtos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        restaurante_id INT NOT NULL,
        categoria_id INT,
        nome VARCHAR(150) NOT NULL,
        descricao TEXT,
        preco DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        custo DECIMAL(10,2) DEFAULT 0.00,
        estoque INT DEFAULT 0,
        estoque_minimo INT DEFAULT 5,
        imagem VARCHAR(255),
        ativo TINYINT(1) DEFAULT 1,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE,
        FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class='test success'>✓ Tabela 'produtos'</div>";

    // Mesas
    $conn->exec("CREATE TABLE IF NOT EXISTS mesas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        restaurante_id INT NOT NULL,
        numero INT NOT NULL,
        capacidade INT DEFAULT 4,
        status ENUM('LIVRE', 'OCUPADA', 'RESERVADA') DEFAULT 'LIVRE',
        qrcode VARCHAR(255),
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class='test success'>✓ Tabela 'mesas'</div>";

    // Caixa (singular)
    $conn->exec("CREATE TABLE IF NOT EXISTS caixa (
        id INT AUTO_INCREMENT PRIMARY KEY,
        restaurante_id INT NOT NULL,
        usuario_id INT NOT NULL,
        data DATE NOT NULL,
        abertura DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        fechamento DECIMAL(10,2),
        status ENUM('ABERTO', 'FECHADO') DEFAULT 'ABERTO',
        observacao TEXT,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fechado_em TIMESTAMP NULL,
        FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class='test success'>✓ Tabela 'caixa'</div>";

    // Vendas
    $conn->exec("CREATE TABLE IF NOT EXISTS vendas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        restaurante_id INT NOT NULL,
        usuario_id INT NOT NULL,
        caixa_id INT NOT NULL,
        mesa_id INT,
        numero_fatura VARCHAR(30) NOT NULL,
        total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        desconto DECIMAL(10,2) DEFAULT 0.00,
        total_final DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        forma_pagamento ENUM('DINHEIRO', 'MPESA', 'CARTAO', 'TRANSFERENCIA') DEFAULT 'DINHEIRO',
        status ENUM('PAGO', 'PENDENTE', 'CANCELADO') DEFAULT 'PAGO',
        observacao TEXT,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
        FOREIGN KEY (caixa_id) REFERENCES caixa(id),
        FOREIGN KEY (mesa_id) REFERENCES mesas(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class='test success'>✓ Tabela 'vendas'</div>";

    // Itens venda
    $conn->exec("CREATE TABLE IF NOT EXISTS itens_venda (
        id INT AUTO_INCREMENT PRIMARY KEY,
        venda_id INT NOT NULL,
        produto_id INT NOT NULL,
        quantidade INT NOT NULL DEFAULT 1,
        preco_unitario DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (venda_id) REFERENCES vendas(id) ON DELETE CASCADE,
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class='test success'>✓ Tabela 'itens_venda'</div>";

    // Pedidos
    $conn->exec("CREATE TABLE IF NOT EXISTS pedidos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        restaurante_id INT NOT NULL,
        mesa_id INT,
        numero_pedido VARCHAR(30) NOT NULL,
        status ENUM('PENDENTE', 'CONFIRMADO', 'PREPARANDO', 'PRONTO', 'ENTREGUE', 'CANCELADO') DEFAULT 'PENDENTE',
        total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        observacao TEXT,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE,
        FOREIGN KEY (mesa_id) REFERENCES mesas(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class='test success'>✓ Tabela 'pedidos'</div>";

    // Itens pedido
    $conn->exec("CREATE TABLE IF NOT EXISTS itens_pedido (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pedido_id INT NOT NULL,
        produto_id INT NOT NULL,
        quantidade INT NOT NULL DEFAULT 1,
        preco_unitario DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        observacao VARCHAR(255),
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class='test success'>✓ Tabela 'itens_pedido'</div>";

    // Criar restaurante padrão
    echo "<h2>Criando dados iniciais...</h2>";

    $stmt = $conn->query("SELECT id FROM restaurantes LIMIT 1");
    if ($stmt->rowCount() == 0) {
        $data_inicio = date('Y-m-d');
        $data_fim = date('Y-m-d', strtotime('+1 year'));

        $conn->exec("INSERT INTO restaurantes (nome, email, telefone, endereco, cidade, nuit, plano, status, data_inicio, data_fim) 
                     VALUES ('Restaurante Sabor Moz', 'admin@sabormoz.co.mz', '+258 84 000 0000', 'Av. Eduardo Mondlane, 123', 'Maputo', '400123456', 'PROFISSIONAL', 'ATIVO', '$data_inicio', '$data_fim')");
        echo "<div class='test success'>✓ Restaurante criado</div>";
    }

    // Primeiro, verificar se a coluna super_admin existe
    try {
        $conn->query("SELECT super_admin FROM usuarios LIMIT 1");
    } catch (PDOException $e) {
        // Criar coluna super_admin se não existir
        $conn->exec("ALTER TABLE usuarios ADD COLUMN super_admin TINYINT(1) DEFAULT 0 AFTER ativo");
        echo "<div class='test success'>✓ Coluna 'super_admin' adicionada</div>";
    }

    // Criar usuário admin
    $stmt = $conn->query("SELECT id FROM usuarios WHERE email = 'admin@sabormoz.co.mz' LIMIT 1");
    if ($stmt->rowCount() == 0) {
        $restaurante_id = $conn->query("SELECT id FROM restaurantes LIMIT 1")->fetchColumn();
        $senha_hash = password_hash('admin123', PASSWORD_BCRYPT);

        $conn->exec("INSERT INTO usuarios (restaurante_id, nome, email, senha, perfil, ativo, super_admin) 
                     VALUES ($restaurante_id, 'Administrador', 'admin@sabormoz.co.mz', '$senha_hash', 'ADMIN', 1, 0)");
        echo "<div class='test success'>✓ Usuário admin criado</div>";
    } else {
        // Atualizar senha e garantir ativo
        $restaurante_id = $conn->query("SELECT id FROM restaurantes LIMIT 1")->fetchColumn();
        $senha_hash = password_hash('admin123', PASSWORD_BCRYPT);
        $conn->exec("UPDATE usuarios SET senha = '$senha_hash', ativo = 1, restaurante_id = $restaurante_id WHERE email = 'admin@sabormoz.co.mz'");
        echo "<div class='test warning'>⚠ Usuário admin atualizado (senha redefinida)</div>";
    }

    // Criar super admin (sem restaurante - restaurante_id = 0)
    $stmtSuper = $conn->query("SELECT id FROM usuarios WHERE email = 'admin@sistema.com' LIMIT 1");
    if ($stmtSuper->rowCount() == 0) {
        $senha_hash_super = password_hash('admin123', PASSWORD_BCRYPT);
        $conn->exec("INSERT INTO usuarios (restaurante_id, nome, email, senha, perfil, ativo, super_admin) 
                     VALUES (0, 'Super Admin', 'admin@sistema.com', '$senha_hash_super', 'ADMIN', 1, 1)");
        echo "<div class='test success'>✓ Super Admin criado (admin@sistema.com / admin123)</div>";
    } else {
        // Atualizar senha do super admin
        $senha_hash_super = password_hash('admin123', PASSWORD_BCRYPT);
        $conn->exec("UPDATE usuarios SET senha = '$senha_hash_super', ativo = 1, super_admin = 1 WHERE email = 'admin@sistema.com'");
        echo "<div class='test warning'>⚠ Super Admin atualizado (senha redefinida)</div>";
    }

    // Criar categorias padrão
    $stmt = $conn->query("SELECT id FROM categorias LIMIT 1");
    if ($stmt->rowCount() == 0) {
        $restaurante_id = $conn->query("SELECT id FROM restaurantes LIMIT 1")->fetchColumn();
        $conn->exec("INSERT INTO categorias (restaurante_id, nome, ativo) VALUES ($restaurante_id, 'Bebidas', 1)");
        $conn->exec("INSERT INTO categorias (restaurante_id, nome, ativo) VALUES ($restaurante_id, 'Pratos Principais', 1)");
        $conn->exec("INSERT INTO categorias (restaurante_id, nome, ativo) VALUES ($restaurante_id, 'Sobremesas', 1)");
        $conn->exec("INSERT INTO categorias (restaurante_id, nome, ativo) VALUES ($restaurante_id, 'Entradas', 1)");
        echo "<div class='test success'>✓ Categorias criadas</div>";
    }

    // Criar mesas padrão
    $stmt = $conn->query("SELECT id FROM mesas LIMIT 1");
    if ($stmt->rowCount() == 0) {
        $restaurante_id = $conn->query("SELECT id FROM restaurantes LIMIT 1")->fetchColumn();
        for ($i = 1; $i <= 8; $i++) {
            $capacidade = ($i == 3 || $i == 6 || $i == 7) ? 6 : 4;
            $conn->exec("INSERT INTO mesas (restaurante_id, numero, capacidade, status) VALUES ($restaurante_id, $i, $capacidade, 'LIVRE')");
        }
        echo "<div class='test success'>✓ 8 Mesas criadas</div>";
    }

    echo "<h2 style='color:green;'>✓ Setup concluído com sucesso!</h2>";
    echo "<div class='test success'>";
    echo "<strong>Credenciais de acesso:</strong><br>";
    echo "Email: <strong>admin@sabormoz.co.mz</strong><br>";
    echo "Senha: <strong>admin123</strong>";
    echo "</div>";
} catch (PDOException $e) {
    echo "<div class='test error'>✗ Erro: " . $e->getMessage() . "</div>";
}

echo "<br>";
echo "<a href='index.php' class='btn btn-green'>← Ir para Login</a>";
echo "<a href='diagnostico.php' class='btn'>🔧 Ver Diagnóstico</a>";

echo "</div></body></html>";

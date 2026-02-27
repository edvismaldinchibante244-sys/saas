-- ============================================
-- BANCO DE DADOS - SISTEMA DE RESTAURANTE SAAS
-- ============================================
-- Execute este script no MySQL/phpMyAdmin
-- Banco: restaurante_saas
-- ============================================

CREATE DATABASE IF NOT EXISTS restaurante_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE restaurante_saas;

-- ============================================
-- TABELA: restaurantes
-- ============================================
CREATE TABLE IF NOT EXISTS restaurantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    endereco VARCHAR(255),
    cidade VARCHAR(100),
    nuit VARCHAR(20) COMMENT 'Número Único de Identificação Tributária',
    logo VARCHAR(255),
    plano ENUM('BASICO', 'PROFISSIONAL', 'ENTERPRISE') DEFAULT 'BASICO',
    status ENUM('ATIVO', 'BLOQUEADO', 'CANCELADO') DEFAULT 'ATIVO',
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABELA: usuarios
-- ============================================
CREATE TABLE IF NOT EXISTS usuarios (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABELA: categorias
-- ============================================
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurante_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(255),
    ativo TINYINT(1) DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABELA: produtos
-- ============================================
CREATE TABLE IF NOT EXISTS produtos (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABELA: mesas
-- ============================================
CREATE TABLE IF NOT EXISTS mesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurante_id INT NOT NULL,
    numero INT NOT NULL,
    capacidade INT DEFAULT 4,
    status ENUM('LIVRE', 'OCUPADA', 'RESERVADA') DEFAULT 'LIVRE',
    qrcode VARCHAR(255),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABELA: caixa
-- ============================================
CREATE TABLE IF NOT EXISTS caixa (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABELA: vendas
-- ============================================
CREATE TABLE IF NOT EXISTS vendas (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABELA: itens_venda
-- ============================================
CREATE TABLE IF NOT EXISTS itens_venda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venda_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (venda_id) REFERENCES vendas(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABELA: pedidos (Pedidos Online via QR Code)
-- ============================================
CREATE TABLE IF NOT EXISTS pedidos (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABELA: itens_pedido
-- ============================================
CREATE TABLE IF NOT EXISTS itens_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    observacao VARCHAR(255),
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- DADOS INICIAIS
-- ============================================

-- Restaurantes de teste
INSERT INTO restaurantes (nome, email, telefone, endereco, cidade, nuit, plano, status, data_inicio, data_fim) VALUES
('Restaurante Sabor Moz', 'admin@sabormoz.co.mz', '+258 84 000 0000', 'Av. Eduardo Mondlane, 123', 'Maputo', '400123456', 'PROFISSIONAL', 'ATIVO', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('Picaria Centro', 'admin@picaria.mz', '+258 87 111 1111', 'Av. Samora Machel, 456', 'Maputo', '400123457', 'BASICO', 'ATIVO', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('Pizzaria Premium', 'admin@pizzaria.mz', '+258 82 222 2222', 'Rua da Paz, 789', 'Beira', '400123458', 'ENTERPRISE', 'ATIVO', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('Churrascaria do Bairro', 'admin@churrascaria.mz', '+258 84 333 3333', 'Rua Moçambique, 321', 'Inhambane', '400123459', 'PROFISSIONAL', 'ATIVO', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('Sabor Macuti', 'admin@sabormacuti.mz', '+258 86 444 4444', 'Avenida da Marginal, 999', 'Maputo', '400123460', 'BASICO', 'ATIVO', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR));

-- Usuários para Restaurante Sabor Moz
INSERT INTO usuarios (restaurante_id, nome, email, senha, perfil, ativo) VALUES
(1, 'Administrador', 'admin@sabormoz.co.mz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN', 1),
(1, 'Operador Caixa', 'caixa@sabormoz.co.mz', '$2y$10$TKh8H1.PkfqRPEnGvpinmuGLI2sdmAfNiazSCnhwhhpiV3tqIGSWu', 'CAIXA', 1);

-- Usuários para Picaria Centro
INSERT INTO usuarios (restaurante_id, nome, email, senha, perfil, ativo) VALUES
(2, 'Admin Picaria', 'admin@picaria.mz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN', 1),
(2, 'Caixa Picaria', 'caixa@picaria.mz', '$2y$10$TKh8H1.PkfqRPEnGvpinmuGLI2sdmAfNiazSCnhwhhpiV3tqIGSWu', 'CAIXA', 1);

-- Usuários para Pizzaria Premium
INSERT INTO usuarios (restaurante_id, nome, email, senha, perfil, ativo) VALUES
(3, 'Admin Pizzaria', 'admin@pizzaria.mz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN', 1),
(3, 'Caixa Pizzaria', 'caixa@pizzaria.mz', '$2y$10$TKh8H1.PkfqRPEnGvpinmuGLI2sdmAfNiazSCnhwhhpiV3tqIGSWu', 'CAIXA', 1);

-- Usuários para Churrascaria do Bairro
INSERT INTO usuarios (restaurante_id, nome, email, senha, perfil, ativo) VALUES
(4, 'Admin Churrascaria', 'admin@churrascaria.mz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN', 1),
(4, 'Caixa Churrascaria', 'caixa@churrascaria.mz', '$2y$10$TKh8H1.PkfqRPEnGvpinmuGLI2sdmAfNiazSCnhwhhpiV3tqIGSWu', 'CAIXA', 1);

-- Usuários para Sabor Macuti
INSERT INTO usuarios (restaurante_id, nome, email, senha, perfil, ativo) VALUES
(5, 'Admin Macuti', 'admin@sabormacuti.mz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN', 1),
(5, 'Caixa Macuti', 'caixa@sabormacuti.mz', '$2y$10$TKh8H1.PkfqRPEnGvpinmuGLI2sdmAfNiazSCnhwhhpiV3tqIGSWu', 'CAIXA', 1);

-- Categorias para Restaurante Sabor Moz
INSERT INTO categorias (restaurante_id, nome) VALUES
(1, 'Entradas'),
(1, 'Pratos Principais'),
(1, 'Bebidas'),
(1, 'Sobremesas'),
(1, 'Lanches');

-- Categorias para Picaria Centro
INSERT INTO categorias (restaurante_id, nome) VALUES
(2, 'Acompanhamentos'),
(2, 'Pães'),
(2, 'Molhos'),
(2, 'Combinados');

-- Categorias para Pizzaria Premium
INSERT INTO categorias (restaurante_id, nome) VALUES
(3, 'Pizzas Doces'),
(3, 'Pizzas Salgadas'),
(3, 'Pastas'),
(3, 'Sobremesas'),
(3, 'Vinhos');

-- Categorias para Churrascaria do Bairro
INSERT INTO categorias (restaurante_id, nome) VALUES
(4, 'Carnes Vermelhas'),
(4, 'Carnes Brancas'),
(4, 'Acompanhamentos'),
(4, 'Bebidas'),
(4, 'Sobremesas');

-- Produtos para Restaurante Sabor Moz
INSERT INTO produtos (restaurante_id, categoria_id, nome, descricao, preco, custo, estoque, estoque_minimo, ativo) VALUES
(1, 2, 'Frango Grelhado', 'Frango grelhado com batata frita e salada', 350.00, 150.00, 50, 5, 1),
(1, 2, 'Peixe Frito', 'Peixe frito com arroz e feijão', 400.00, 180.00, 30, 5, 1),
(1, 2, 'Camarão Grelhado', 'Camarão grelhado com mandioca', 550.00, 250.00, 20, 3, 1),
(1, 1, 'Salada Mista', 'Salada com tomate, alface e cenoura', 120.00, 40.00, 100, 10, 1),
(1, 1, 'Sopa do Dia', 'Sopa caseira do dia', 80.00, 25.00, 50, 5, 1),
(1, 3, 'Coca-Cola 330ml', 'Refrigerante gelado', 50.00, 20.00, 200, 20, 1),
(1, 3, 'Água Mineral', 'Água mineral 500ml', 30.00, 10.00, 300, 30, 1),
(1, 3, 'Sumo Natural', 'Sumo de fruta natural', 80.00, 30.00, 50, 10, 1),
(1, 3, '2M Cerveja', 'Cerveja 2M 340ml', 70.00, 30.00, 150, 20, 1),
(1, 4, 'Pudim', 'Pudim de leite caseiro', 100.00, 35.00, 20, 5, 1),
(1, 5, 'Hambúrguer', 'Hambúrguer artesanal com batata frita', 280.00, 120.00, 30, 5, 1),
(1, 5, 'Cachorro Quente', 'Cachorro quente com molho especial', 150.00, 60.00, 40, 5, 1);

-- Produtos para Picaria Centro
INSERT INTO produtos (restaurante_id, categoria_id, nome, descricao, preco, custo, estoque, estoque_minimo, ativo) VALUES
(2, 7, 'Ovo Frito', 'Ovo frito servido com pão quente', 60.00, 20.00, 80, 10, 1),
(2, 7, 'Frango Assado', 'Frango assado com especiarias', 420.00, 180.00, 40, 5, 1),
(2, 6, 'Pão Caseiro', 'Pão fresco do dia', 25.00, 10.00, 150, 20, 1),
(2, 6, 'Broa de Milho', 'Broa tradicional de milho', 35.00, 12.00, 100, 10, 1),
(2, 8, 'Molho de Tomate', 'Molho de tomate caseiro', 45.00, 15.00, 60, 5, 1),
(2, 8, 'Molho Piri Piri', 'Molho picante tradicional', 50.00, 18.00, 50, 5, 1),
(2, 9, 'Combo Frango + Pão', 'Frango assado com 2 pães', 150.00, 65.00, 120, 15, 1);

-- Produtos para Pizzaria Premium
INSERT INTO produtos (restaurante_id, categoria_id, nome, descricao, preco, custo, estoque, estoque_minimo, ativo) VALUES
(3, 12, 'Pizza Margherita', 'Tomate, mozzarela e basílico', 650.00, 250.00, 40, 5, 1),
(3, 12, 'Pizza Pepperoni', 'Pepperoni e queijo mozzarela', 700.00, 280.00, 35, 5, 1),
(3, 12, 'Pizza Carbonara', 'Bacon, ovo e queijo', 750.00, 300.00, 30, 5, 1),
(3, 11, 'Pizza Chocolate', 'Chocolate belga com morango', 550.00, 200.00, 25, 3, 1),
(3, 11, 'Pizza Nutella', 'Nutella com banana', 600.00, 220.00, 20, 3, 1),
(3, 13, 'Pasta à Bolonhesa', 'Massa fresca com molho bolonhesa', 450.00, 180.00, 50, 10, 1),
(3, 13, 'Pasta Alfredo', 'Massa com molho cremoso', 480.00, 190.00, 45, 10, 1),
(3, 14, 'Tiramisu', 'Sobremesa italiana clássica', 180.00, 70.00, 30, 5, 1),
(3, 15, 'Vinho Tinto Reserva', 'Vinho português de qualidade', 450.00, 180.00, 25, 3, 1);

-- Produtos para Churrascaria do Bairro
INSERT INTO produtos (restaurante_id, categoria_id, nome, descricao, preco, custo, estoque, estoque_minimo, ativo) VALUES
(4, 16, 'Picanha Grelhada', 'Picanha premium grelhada', 850.00, 380.00, 35, 5, 1),
(4, 16, 'Alcatra', 'Alcatra suculenta grelhada', 750.00, 320.00, 40, 5, 1),
(4, 16, 'Costela Bovina', 'Costela macia e suculenta', 800.00, 350.00, 30, 3, 1),
(4, 17, 'Frango no Espeto', 'Frango inteiro grelhado', 500.00, 200.00, 50, 10, 1),
(4, 17, 'Peito de Frango', 'Peito de frango grelhado', 400.00, 160.00, 60, 10, 1),
(4, 18, 'Arroz Branco', 'Arroz cozido no caldo', 80.00, 30.00, 100, 20, 1),
(4, 18, 'Batata Doce Assada', 'Batata doce assada na brasa', 120.00, 40.00, 80, 10, 1),
(4, 18, 'Mandioca Frita', 'Mandioca crocante frita', 90.00, 35.00, 100, 10, 1),
(4, 19, 'Refrigerante 2L', 'Refrigerante 2 litros', 120.00, 50.00, 80, 10, 1),
(4, 19, 'Cerveja Premium', 'Cerveja importada gelada', 150.00, 60.00, 120, 20, 1),
(4, 20, 'Pavê', 'Pavê tradicional de leite', 150.00, 55.00, 40, 5, 1);

-- Mesas para Restaurante Sabor Moz
INSERT INTO mesas (restaurante_id, numero, capacidade, status) VALUES
(1, 1, 4, 'LIVRE'),
(1, 2, 4, 'LIVRE'),
(1, 3, 6, 'LIVRE'),
(1, 4, 2, 'LIVRE'),
(1, 5, 4, 'LIVRE'),
(1, 6, 6, 'LIVRE'),
(1, 7, 8, 'LIVRE'),
(1, 8, 4, 'LIVRE');

-- Mesas para Picaria Centro
INSERT INTO mesas (restaurante_id, numero, capacidade, status) VALUES
(2, 1, 2, 'LIVRE'),
(2, 2, 2, 'LIVRE'),
(2, 3, 4, 'LIVRE'),
(2, 4, 4, 'LIVRE');

-- Mesas para Pizzaria Premium
INSERT INTO mesas (restaurante_id, numero, capacidade, status) VALUES
(3, 1, 4, 'LIVRE'),
(3, 2, 4, 'LIVRE'),
(3, 3, 6, 'LIVRE'),
(3, 4, 6, 'LIVRE'),
(3, 5, 8, 'LIVRE'),
(3, 6, 2, 'LIVRE');

-- Mesas para Churrascaria do Bairro
INSERT INTO mesas (restaurante_id, numero, capacidade, status) VALUES
(4, 1, 8, 'LIVRE'),
(4, 2, 8, 'LIVRE'),
(4, 3, 6, 'LIVRE'),
(4, 4, 6, 'LIVRE'),
(4, 5, 4, 'LIVRE'),
(4, 6, 4, 'LIVRE'),
(4, 7, 10, 'LIVRE');

-- Mesas para Sabor Macuti
INSERT INTO mesas (restaurante_id, numero, capacidade, status) VALUES
(5, 1, 4, 'LIVRE'),
(5, 2, 4, 'LIVRE'),
(5, 3, 6, 'LIVRE'),
(5, 4, 2, 'LIVRE');

-- ============================================
-- ÍNDICES PARA PERFORMANCE
-- ============================================
CREATE INDEX idx_produtos_restaurante ON produtos(restaurante_id);
CREATE INDEX idx_vendas_restaurante ON vendas(restaurante_id);
CREATE INDEX idx_vendas_data ON vendas(criado_em);
CREATE INDEX idx_caixa_restaurante ON caixa(restaurante_id);
CREATE INDEX idx_caixa_data ON caixa(data);
CREATE INDEX idx_pedidos_restaurante ON pedidos(restaurante_id);
CREATE INDEX idx_pedidos_status ON pedidos(status);

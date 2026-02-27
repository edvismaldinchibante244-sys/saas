<?php

/**
 * Script para criar a tabela de compras de planos
 */
include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Erro de conexão com o banco de dados\n");
}

echo "=== Criando tabela de compras de planos ===\n\n";

// Criar tabela
$sql = "CREATE TABLE IF NOT EXISTS compras_planos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurante_id INT NOT NULL,
    plano_atual VARCHAR(20) NOT NULL,
    plano_novo VARCHAR(20) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    metodo_pagamento ENUM('DINHEIRO', 'MPESA', 'CARTAO', 'TRANSFERENCIA') NOT NULL,
    status ENUM('PENDENTE', 'PAGO', 'CANCELADO', 'APROVADO') DEFAULT 'PENDENTE',
    referencia_pagamento VARCHAR(100),
    observacao TEXT,
    data_pagamento DATETIME,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    $db->exec($sql);
    echo "✓ Tabela 'compras_planos' criada com sucesso!\n";

    // Verificar se existe
    $stmt = $db->query("SHOW TABLES LIKE 'compras_planos'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Tabela verificada!\n";
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

echo "\n=== Concluído! ===\n";

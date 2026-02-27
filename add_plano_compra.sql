-- ============================================
-- TABELA: compras_planos (Registrar compras de planos)
-- ============================================
CREATE TABLE IF NOT EXISTS compras_planos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurante_id INT NOT NULL,
    plano_atual VARCHAR(20) NOT NULL,
    plano_novo VARCHAR(20) NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    metodo_pagamento ENUM('DINHEIRO', 'MPESA', 'CARTAO', 'TRANSFERENCIA') NOT NULL,
    status ENUM('PENDENTE', 'PAGO', 'CANCELADO', 'APROVADO') DEFAULT 'PENDENTE',
    referencia_pagamento VARCHAR(100),
    observacao TEXT,
    data_pagamento DATETIME,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- ============================================
-- PREÇOS DOS PLANOS
-- ============================================
-- BASIC: Grátis
-- PROFISSIONAL: 1.500 MZN/mês
-- ENTERPRISE: 3.000 MZN/mês
-- Inserir dados de exemplo (opcional)
-- INSERT INTO compras_planos (restaurante_id, plano_atual, plano_novo, valor, metodo_pagamento, status) 
-- VALUES (1, 'BASICO', 'PROFISSIONAL', 1500.00, 'MPESA', 'PENDENTE');
-- Tabela para tokens de recuperação de senha
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    used TINYINT(1) DEFAULT 0,
    INDEX idx_token (token),
    INDEX idx_email (email)
);
-- Configurações de email (adicionar na tabela config se não existir)
INSERT IGNORE INTO config (chave, valor)
VALUES ('smtp_host', 'smtp.gmail.com'),
    ('smtp_port', '587'),
    ('smtp_username', 'seu-email@gmail.com'),
    ('smtp_password', 'sua-senha-app'),
    (
        'smtp_from',
        'Sistema RestaurantESA <noreply@seusite.com>'
    );
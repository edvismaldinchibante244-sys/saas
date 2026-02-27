<?php
include 'config/database.php';

$db = (new Database())->getConnection();

// Criar tabela password_resets
$sql = "CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    used TINYINT(1) DEFAULT 0,
    INDEX idx_token (token),
    INDEX idx_email (email)
)";

try {
    $db->exec($sql);
    echo "Tabela password_resets criada com sucesso!";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}

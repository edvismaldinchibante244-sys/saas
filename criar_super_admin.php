<?php

/**
 * Script para criar o Super Admin
 * Este script modifica a estrutura para permitir super admin
 */

$host = "localhost";
$db_name = "restaurante_saas";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Primeiro verificar se a coluna super_admin existe
    try {
        $conn->query("SELECT super_admin FROM usuarios LIMIT 1");
    } catch (PDOException $e) {
        $conn->exec("ALTER TABLE usuarios ADD COLUMN super_admin TINYINT(1) DEFAULT 0 AFTER ativo");
        echo "Coluna super_admin criada!<br>";
    }

    // Remover a constraint de foreign key temporariamente para permitir restaurante_id = 0
    // Primeiro, verificar se existe
    $checkConstraint = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = '$db_name' AND TABLE_NAME = 'usuarios' AND REFERENCED_TABLE_NAME = 'restaurantes'");

    if ($checkConstraint->rowCount() > 0) {
        // Remover constraint
        $conn->exec("ALTER TABLE usuarios DROP FOREIGN KEY usuarios_ibfk_1");
        echo "Foreign key removida!<br>";
    }

    // Modificar coluna para permitir 0
    $conn->exec("ALTER TABLE usuarios MODIFY COLUMN restaurante_id INT NOT NULL DEFAULT 1");
    echo "Coluna restaurante_id modificada!<br>";

    // Criar senha hash
    $senha_hash = password_hash('admin123', PASSWORD_BCRYPT);

    // Verificar se já existe
    $check = $conn->query("SELECT id FROM usuarios WHERE email = 'admin@sistema.com' LIMIT 1");

    if ($check->rowCount() == 0) {
        // Criar super admin com restaurante_id = 0
        $stmt = $conn->prepare("INSERT INTO usuarios (restaurante_id, nome, email, senha, perfil, ativo, super_admin) VALUES (0, 'Super Admin', 'admin@sistema.com', ?, 'ADMIN', 1, 1)");
        $stmt->execute([$senha_hash]);
        echo "Super Admin criado com sucesso!<br>";
    } else {
        // Atualizar
        $stmt = $conn->prepare("UPDATE usuarios SET senha = ?, super_admin = 1, ativo = 1, restaurante_id = 0 WHERE email = 'admin@sistema.com'");
        $stmt->execute([$senha_hash]);
        echo "Super Admin atualizado!<br>";
    }

    echo "<br>Credenciais:<br>";
    echo "Email: admin@sistema.com<br>";
    echo "Senha: admin123<br>";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}

<?php

/**
 * Diagnóstico de fotos de usuário
 */
include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "<h2>Diagnóstico de Fotos</h2>";

// Verificar se a coluna existe
echo "<h3>1. Verificar coluna 'foto'</h3>";
try {
    $stmt = $db->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $foto_exists = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'foto') {
            $foto_exists = true;
            echo "<p style='color:green'>✓ Coluna 'foto' existe</p>";
        }
    }
    if (!$foto_exists) {
        echo "<p style='color:red'>✗ Coluna 'foto' NÃO existe! Execute add_foto_coluna.php</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

// Verificar usuários e fotos
echo "<h3>2. Usuários com fotos</h3>";
try {
    $stmt = $db->query("SELECT id, nome, foto FROM usuarios");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($usuarios) > 0) {
        foreach ($usuarios as $u) {
            echo "<p>";
            echo "ID: " . $u['id'] . " | ";
            echo "Nome: " . $u['nome'] . " | ";
            echo "Foto: '" . ($u['foto'] ?? 'NULL') . "'";
            echo "</p>";

            // Verificar se arquivo existe
            if (!empty($u['foto'])) {
                $caminho = '../' . $u['foto'];
                if (file_exists($caminho)) {
                    echo "<p style='color:green'>✓ Arquivo existe: $caminho</p>";
                } else {
                    echo "<p style='color:red'>✗ Arquivo NÃO existe: $caminho</p>";
                }
            }
        }
    } else {
        echo "<p>Nenhum usuário encontrado</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

// Verificar diretório uploads
echo "<h3>3. Diretório uploads</h3>";
$dir = '../public/uploads/usuarios';
if (is_dir($dir)) {
    echo "<p style='color:green'>✓ Diretório existe: $dir</p>";
    $files = scandir($dir);
    echo "<p>Arquivos: " . count($files) . "</p>";
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..') {
            echo "- $f<br>";
        }
    }
} else {
    echo "<p style='color:red'>✗ Diretório NÃO existe: $dir</p>";
}

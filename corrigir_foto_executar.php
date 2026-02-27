<?php

/**
 * Script para corrigir valores da coluna foto
 */
include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Erro de conexão com o banco de dados\n");
}

echo "=== Corrigindo valores da coluna 'foto' ===\n\n";

// Verificar valores antes
echo "1. Verificando valores atuais:\n";
$stmt = $db->query("SELECT id, nome, foto FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($usuarios as $u) {
    $status = '';
    if ($u['foto'] === '' || $u['foto'] === null) {
        $status = 'VAZIO/NULL';
    } elseif ($u['foto'] === 'NULL') {
        $status = 'STRING NULL';
    } else {
        $status = 'TEM VALOR';
    }
    echo "  ID: " . $u['id'] . " | Nome: " . $u['nome'] . " | Foto: " . ($u['foto'] ?? 'NULL') . " [$status]\n";
}

echo "\n2. Corrigindo valores:\n";

// Corrigir: Converter strings vazias e 'NULL' para NULL
$sql = "UPDATE usuarios SET foto = NULL WHERE foto = '' OR foto = 'NULL'";
$stmt = $db->query($sql);
$linhas = $stmt->rowCount();
echo "  Registros corrigidos: $linhas\n";

echo "\n3. Verificando valores após correção:\n";
$stmt = $db->query("SELECT id, nome, foto FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($usuarios as $u) {
    echo "  ID: " . $u['id'] . " | Nome: " . $u['nome'] . " | Foto: " . ($u['foto'] ?? 'NULL') . "\n";
}

echo "\n=== Correção concluída! ===\n";

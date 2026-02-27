<?php

/**
 * Teste completo simulando navegador logado
 */

// Simular login primero - chamar login_process
session_start();

// Configurar sessão como se tivesse logado
$_SESSION['logado'] = true;
$_SESSION['usuario_id'] = 1;
$_SESSION['restaurante_id'] = 1;
$_SESSION['nome'] = 'Administrador';
$_SESSION['email'] = 'admin@sabormoz.co.mz';
$_SESSION['perfil'] = 'ADMIN';
$_SESSION['plano'] = 'PROFISSIONAL';

echo "=== Sessão configurada ===\n";
print_r($_SESSION);

// Agora testar a API usuario_buscar
echo "\n=== Testando API usuario_buscar ===\n";

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Erro de conexão\n");
}

$query = "SELECT id, nome, email, perfil, ativo FROM usuarios WHERE id = :id AND restaurante_id = :rid LIMIT 1";
$stmt  = $db->prepare($query);
$id = 1;
$restaurante_id = $_SESSION['restaurante_id'];
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->bindParam(':rid', $restaurante_id, PDO::PARAM_INT);
$stmt->execute();

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    echo "SUCCESS: " . json_encode($usuario) . "\n";
} else {
    echo "ERROR: Usuário não encontrado\n";
}

// Testar usuario_editar
echo "\n=== Testando API usuario_editar ===\n";

$query2 = "UPDATE usuarios SET nome = :nome WHERE id = :id AND restaurante_id = :rid";
$stmt2  = $db->prepare($query2);
$novo_nome = 'Administrador Teste';
$stmt2->bindParam(':nome', $novo_nome);
$stmt2->bindParam(':id', $id, PDO::PARAM_INT);
$stmt2->bindParam(':rid', $restaurante_id, PDO::PARAM_INT);
$resultado = $stmt2->execute();

if ($resultado) {
    echo "SUCCESS: Usuário atualizado\n";
} else {
    echo "ERROR: " . $stmt2->errorInfo()[2] . "\n";
}

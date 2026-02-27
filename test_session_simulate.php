<?php
// Simular sessão como se fosse o navegador logado
session_start();
$_SESSION['logado'] = true;
$_SESSION['usuario_id'] = 1;
$_SESSION['restaurante_id'] = 1;
$_SESSION['perfil'] = 'ADMIN';

include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Erro de conexão");
}

// Simular a chamada da API usuario_buscar.php
$id = intval($_GET['id'] ?? 1);
$restaurante_id = $_SESSION['restaurante_id'];

$query = "SELECT id, nome, email, perfil, ativo FROM usuarios WHERE id = :id AND restaurante_id = :rid LIMIT 1";
$stmt  = $db->prepare($query);
$stmt->bindParam(':id',  $id, PDO::PARAM_INT);
$stmt->bindParam(':rid', $restaurante_id, PDO::PARAM_INT);
$stmt->execute();

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    echo json_encode(array("success" => true, "data" => $usuario));
} else {
    echo json_encode(array("success" => false, "message" => "Usuário não encontrado"));
}

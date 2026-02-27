<?php
include 'config/database.php';
$db = (new Database())->getConnection();

// Inserir uma compra PENDENTE para testar
$stmt = $db->prepare('INSERT INTO compras_planos (restaurante_id, plano_atual, plano_novo, valor, metodo_pagamento, status, criado_em) VALUES (?, ?, ?, ?, ?, ?, NOW())');
$stmt->execute([1, 'PROFISSIONAL', 'ENTERPRISE', 3000.00, 'MPESA', 'PENDENTE']);

echo 'Compra PENDENTE criada com sucesso! Recarregue o painel para ver os botões.';
?>

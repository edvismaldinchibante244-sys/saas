<?php
include 'config/database.php';

$db = (new Database())->getConnection();

$stmt = $db->query('SELECT COUNT(*) as total FROM compras_planos');
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo 'Total de compras: ' . $result['total'] . "\n";

// Mostrar todas as compras
$stmt = $db->query('SELECT * FROM compras_planos ORDER BY criado_em DESC LIMIT 10');
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($compras as $c) {
    echo "ID: {$c['id']} - Restaurante: {$c['restaurante_id']} - Plano: {$c['plano_novo']} - Status: {$c['status']}\n";
}

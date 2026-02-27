<?php
$pdo = new PDO("mysql:host=localhost;dbname=restaurante_saas", "root", "");
$stmt = $pdo->query("SELECT senha FROM usuarios WHERE id = 1");
$hash = $stmt->fetchColumn();

echo "Hash: $hash\n";
echo "Teste 'admin123': " . (password_verify("admin123", $hash) ? "OK" : "FALHO") . "\n";
echo "Teste 'admin': " . (password_verify("admin", $hash) ? "OK" : "FALHO") . "\n";

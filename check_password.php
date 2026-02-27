<?php
$conn = new PDO('mysql:host=localhost;dbname=restaurante_saas', 'root', '');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $conn->query("SELECT id, nome, email, senha, ativo FROM usuarios WHERE email = 'admin@sabormoz.co.mz'");
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "Usuário: " . $user['nome'] . "\n";
    echo "Email: " . $user['email'] . "\n";
    echo "Ativo: " . ($user['ativo'] ? 'Sim' : 'Não') . "\n";
    echo "Hash: " . $user['senha'] . "\n";

    // Testar senhas comuns
    $senhas = ['admin123', 'admin', '123456', 'password', 'caixa123'];
    foreach ($senhas as $senha) {
        if (password_verify($senha, $user['senha'])) {
            echo "✓ SENHA CORRETA: '$senha'\n";
        }
    }
} else {
    echo "Usuário não encontrado";
}

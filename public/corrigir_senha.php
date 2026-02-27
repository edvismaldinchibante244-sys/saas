<?php
/**
 * ============================================
 * CORRIGIR SENHA DO ADMINISTRADOR
 * ============================================
 */

// Configuração do banco
$host = "localhost";
$db_name = "restaurante_saas";
$username = "root";
$password = ""; // Sua senha do MySQL (se tiver)

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Gerar hash correto para a senha "admin123"
    $senha_correta = password_hash("admin123", PASSWORD_BCRYPT);

    // Atualizar a senha no banco
    $query = "UPDATE usuarios SET senha = :senha WHERE email = 'admin@sabormoz.co.mz'";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":senha", $senha_correta);
    
    if($stmt->execute()) {
        echo "<h2 style='color: green;'>✅ SENHA ATUALIZADA COM SUCESSO!</h2>";
        echo "<p><strong>Email:</strong> admin@sabormoz.co.mz</p>";
        echo "<p><strong>Senha:</strong> admin123</p>";
        echo "<br>";
        echo "<p>Agora você pode fazer login!</p>";
        echo "<br>";
        echo "<a href='index.php' style='padding: 10px 20px; background: #FF6B35; color: white; text-decoration: none; border-radius: 5px;'>IR PARA LOGIN</a>";
    } else {
        echo "<h2 style='color: red;'>❌ Erro ao atualizar senha!</h2>";
    }

} catch(PDOException $e) {
    echo "<h2 style='color: red;'>❌ ERRO DE CONEXÃO</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<br>";
    echo "<h3>Verifique:</h3>";
    echo "<ul>";
    echo "<li>Se o MySQL está rodando</li>";
    echo "<li>Se a base de dados 'restaurante_saas' existe</li>";
    echo "<li>Se a senha do MySQL está correta no arquivo</li>";
    echo "</ul>";
}
?>

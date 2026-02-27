<?php

/**
 * API - Esqueci a Senha
 * Envia email com link de recuperação
 */

session_start();
include_once '../../config/database.php';

header('Content-Type: application/json');

// Criar tabela automaticamente se não existir
$dbTest = new Database();
$dbConn = $dbTest->getConnection();
try {
    $dbConn->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        token VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        used TINYINT(1) DEFAULT 0,
        INDEX idx_token (token),
        INDEX idx_email (email)
    )");
} catch (Exception $e) {
    // Ignore errors - table might already exist
}

// Configurações do email (em produção, ler do banco de dados)
$smtp_host = 'smtp.gmail.com';
$smtp_port = '587';
$smtp_username = 'seu-email@gmail.com';
$smtp_password = 'sua-senha-app'; // Use senha de app do Google
$smtp_from = 'Sistema RestaurantESA <noreply@gmail.com>';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (empty($email)) {
        echo json_encode(["success" => false, "message" => "Por favor, insira seu email."]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Email inválido."]);
        exit;
    }

    // Conectar ao banco
    $database = new Database();
    $db = $database->getConnection();

    // Verificar se email existe
    $stmt = $db->prepare("SELECT id, nome FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        // Não revelar se o email existe ou não por segurança
        echo json_encode(["success" => true, "message" => "Se o email estiver cadastrado, você receberá um link de recuperação."]);
        exit;
    }

    // Gerar token único
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Salvar token no banco
    $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$email, $token, $expires_at]);

    // Gerar link de recuperação
    $link = "http://localhost/restaurante-saas/public/nova_senha.php?token=" . $token;

    // Enviar email
    $subject = "Recuperação de Senha - Sistema RestaurantESA";
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .btn { display: inline-block; padding: 12px 24px; background: #6c5ce7; color: white; text-decoration: none; border-radius: 5px; }
            .footer { margin-top: 20px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Olá, {$usuario['nome']}!</h2>
            <p>Recebemos uma solicitação para redefinir a senha da sua conta.</p>
            <p>Clique no botão abaixo para criar uma nova senha:</p>
            <p style='text-align: center;'>
                <a href='{$link}' class='btn'>Redefinir Senha</a>
            </p>
            <p>Ou copie e cole este link no seu navegador:</p>
            <p>{$link}</p>
            <p><strong>Este link expira em 1 hora.</strong></p>
            <p>Se você não solicitou esta recuperação, ignore este email.</p>
            <div class='footer'>
                <p>Este é um email automático do Sistema RestaurantESA. Por favor, não responda.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Tentar enviar email
    $emailEnviado = enviarEmail($email, $subject, $body, $smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_from);

    if ($emailEnviado) {
        echo json_encode(["success" => true, "message" => "Se o email estiver cadastrado, você receberá um link de recuperação."]);
    } else {
        // Em desenvolvimento, mostrar o link para testar
        echo json_encode(["success" => true, "message" => "Se o email estiver cadastrado, você receberá um link de recuperação. (Em desenvolvimento: {$link})"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método não permitido."]);
}

/**
 * Função para enviar email usando PHPMailer ou mail()
 */
function enviarEmail($para, $assunto, $mensagem, $smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_from)
{
    // Cabeçalhos do email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . $smtp_from . "\r\n";
    $headers .= "Reply-To: " . $smtp_from . "\r\n";

    // Tentar usar mail() do PHP
    // Em produção, recomendo usar PHPMailer ou SwiftMailer
    return @mail($para, $assunto, $mensagem, $headers);
}

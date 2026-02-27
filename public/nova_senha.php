<?php

/**
 * ============================================
 * PÁGINA DE NOVA SENHA
 * Usuário define nova senha após clicar no link
 * ============================================
 */

session_start();
include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$message = '';
$messageType = '';
$tokenValido = false;
$token = isset($_GET['token']) ? $_GET['token'] : '';

// Se já está logado, redireciona
if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header("Location: dashboard.php");
    exit;
}

// Verificar se token é válido
if (!empty($token)) {
    $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW() LIMIT 1");
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reset) {
        $tokenValido = true;
    }
}

// Processar formulário de nova senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValido) {
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
    $confirmarSenha = isset($_POST['confirmar_senha']) ? $_POST['confirmar_senha'] : '';

    if (strlen($senha) < 6) {
        $message = "A senha deve ter pelo menos 6 caracteres.";
        $messageType = "danger";
    } elseif ($senha !== $confirmarSenha) {
        $message = "As senhas não conferem.";
        $messageType = "danger";
    } else {
        // Atualizar senha do usuário
        $senhaHash = password_hash($senha, PASSWORD_BCRYPT);

        $stmt = $db->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
        $stmt->execute([$senhaHash, $reset['email']]);

        // Marcar token como usado
        $stmt = $db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
        $stmt->execute([$token]);

        $message = "Senha redefinida com sucesso! Você já pode fazer login.";
        $messageType = "success";
        $tokenValido = false; // Para mostrar mensagem de sucesso

        // Redirecionar após 3 segundos
        header("Refresh: 3;url=index.php");
    }
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Senha - Sistema RestaurantESA</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary: #6c5ce7;
            --secondary: #a29bfe;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 30px;
            text-align: center;
            color: white;
        }

        .login-header h2 {
            margin: 0;
            font-weight: 600;
        }

        .login-header p {
            margin: 5px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .login-body {
            padding: 40px;
        }

        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 5px;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: #5b4cdb;
            transform: translateY(-2px);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 10px;
        }

        .token-invalido {
            text-align: center;
            padding: 40px;
        }

        .token-invalido i {
            font-size: 48px;
            color: #dc3545;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="login-header">
            <h2><i class="fas fa-lock me-2"></i>Nova Senha</h2>
            <p>Crie uma nova senha segura</p>
        </div>

        <div class="login-body">
            <?php if ($message): ?>
                <?php if ($messageType === 'success'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                    </div>
                    <div class="back-link">
                        <a href="index.php" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Ir para Login
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!$tokenValido && empty($message)): ?>
                <div class="token-invalido">
                    <i class="fas fa-times-circle"></i>
                    <h4>Link Inválido ou Expirado</h4>
                    <p class="text-muted">O link de recuperação de senha é inválido ou expirou.</p>
                    <a href="esqueci_senha.php" class="btn btn-primary">
                        <i class="fas fa-redo me-2"></i>Solicitar Novo Link
                    </a>
                </div>
            <?php elseif ($tokenValido): ?>
                <form id="formNovaSenha" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nova Senha</label>
                        <input type="password" class="form-control" name="senha" id="senha" required placeholder="Mínimo 6 caracteres" minlength="6">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirmar Nova Senha</label>
                        <input type="password" class="form-control" name="confirmar_senha" id="confirmar_senha" required placeholder="Digite a senha novamente" minlength="6">
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="btnSalvar">
                        <i class="fas fa-save me-2"></i>Salvar Nova Senha
                    </button>
                </form>
            <?php endif; ?>

            <?php if (!isset($message) || $messageType !== 'success'): ?>
                <div class="back-link">
                    <a href="index.php"><i class="fas fa-arrow-left me-1"></i> Voltar para Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if ($tokenValido): ?>
            document.getElementById('formNovaSenha').addEventListener('submit', function(e) {
                const senha = document.getElementById('senha').value;
                const confirmar = document.getElementById('confirmar_senha').value;

                if (senha !== confirmar) {
                    e.preventDefault();
                    alert('As senhas não conferem!');
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>
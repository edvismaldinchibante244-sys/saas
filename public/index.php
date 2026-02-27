<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Restaurante</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary: #FF6B35;
            --primary-dark: #e55a25;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }

        .login-header {
            background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
            padding: 40px 40px 30px;
            text-align: center;
            color: white;
        }

        .login-header .icon {
            font-size: 50px;
            margin-bottom: 10px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .login-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .login-header p {
            font-size: 13px;
            opacity: 0.9;
        }

        .login-body {
            padding: 40px;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .login-footer {
            text-align: center;
            padding: 20px 40px 40px;
            color: #777;
            font-size: 13px;
        }

        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="login-header">
            <div class="icon">🍽️</div>
            <h1>RestauranteSaaS</h1>
            <p>Sistema de Gestão para Restaurantes</p>
        </div>

        <div class="login-body">
            <form id="loginForm" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-envelope text-muted"></i>
                        </span>
                        <input type="email" id="email" name="email" class="form-control border-start-0" placeholder="seu@email.com" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-lock text-muted"></i>
                        </span>
                        <input type="password" id="senha" name="senha" class="form-control border-start-0" placeholder="Digite sua senha" required>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="lembrar" name="lembrar">
                    <label class="form-check-label" for="lembrar">Lembrar-me</label>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Entrar
                </button>

                <div class="alert mt-3" id="alert" style="display: none;"></div>
            </form>
        </div>

        <div class="login-footer">
            <p>Esqueceu a senha? <a href="esqueci_senha.php">Recuperar</a></p>
            <p class="mb-0 text-muted">Sistema de Restaurante 2026</p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/login.js"></script>
</body>

</html>
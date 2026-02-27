# 🍽️ Sistema de Gestão de Restaurante (SaaS)

Sistema completo de gestão para restaurantes com modelo de assinatura.

## 📋 Funcionalidades Implementadas

### ✅ Sistema de Login
- Autenticação segura com PHP e MySQL
- Senha com hash (bcrypt)
- Proteção contra SQL Injection
- Verificação de status da assinatura
- Controle de sessão com timeout
- Sistema de permissões (Admin, Caixa, Garçom)

## 🗂️ Estrutura do Projeto

```
restaurante-saas/
│
├── config/
│   ├── database.php          # Configuração do banco de dados
│   └── auth_check.php         # Proteção de rotas
│
├── app/
│   └── Auth.php               # Classe de autenticação
│
├── public/
│   ├── index.php              # Página de login
│   ├── dashboard.php          # Dashboard principal
│   ├── login_process.php      # Processamento do login
│   ├── logout.php             # Logout
│   │
│   ├── css/
│   │   └── style.css          # Estilos CSS
│   │
│   └── js/
│       └── login.js           # JavaScript do login
│
└── database.sql               # Script da base de dados
```

## 🚀 Como Instalar

### 1️⃣ **Configurar o Banco de Dados**

1. Abra o **phpMyAdmin** ou **MySQL Workbench**
2. Crie uma nova base de dados chamada `restaurante_saas`
3. Importe o arquivo `C:\Users\HP EliteBook 650\database.sql`

OU execute este comando no MySQL:

```sql
mysql -u root -p < "C:\Users\HP EliteBook 650\database.sql"
```

### 2️⃣ **Configurar a Conexão**

Edite o arquivo `config/database.php` se necessário:

```php
private $host = "localhost";
private $db_name = "restaurante_saas";
private $username = "root";
private $password = "";  // Sua senha do MySQL
```

### 3️⃣ **Iniciar o Servidor**

Opção 1 - **XAMPP/WAMP**:
- Coloque a pasta `restaurante-saas` dentro de `htdocs`
- Acesse: `http://localhost/restaurante-saas/public/`

Opção 2 - **Servidor PHP Embutido**:
```bash
cd "C:\Users\HP EliteBook 650\restaurante-saas\public"
php -S localhost:8000
```
- Acesse: `http://localhost:8000`

## 🔐 Login de Teste

Após importar o banco de dados, use:

- **Email**: `admin@sabormoz.co.mz`
- **Senha**: `admin123`

## 📦 Módulos do Sistema

### ✅ Implementados
- [x] Sistema de Login
- [x] Dashboard
- [x] Proteção de rotas
- [x] Controle de sessão

### 🔜 Próximos Passos
- [ ] Cadastro de produtos
- [ ] Sistema de vendas
- [ ] Controle de caixa
- [ ] Pedidos online (QR Code)
- [ ] Emissão de faturas
- [ ] Relatórios
- [ ] Backup automático

## 🛡️ Segurança

- ✅ Senha com hash bcrypt
- ✅ PDO com prepared statements (anti SQL Injection)
- ✅ Verificação de status da assinatura
- ✅ Timeout de sessão (2 horas)
- ✅ Proteção de rotas

## 🎨 Tecnologias Usadas

- **Backend**: PHP 7.4+
- **Banco de Dados**: MySQL 8
- **Frontend**: HTML5, CSS3, JavaScript
- **Autenticação**: Sessions + Password Hash

## 📞 Suporte

Para dúvidas ou problemas, verifique:
1. Se o MySQL está rodando
2. Se as credenciais em `config/database.php` estão corretas
3. Se o arquivo `database.sql` foi importado corretamente

---

**Desenvolvido para gestão profissional de restaurantes em Moçambique** 🇲🇿

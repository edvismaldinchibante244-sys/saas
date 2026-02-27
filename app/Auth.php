<?php

/**
 * ============================================
 * CLASSE DE AUTENTICAÇÃO
 * ============================================
 */

class Auth
{
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $restaurante_id;
    public $nome;
    public $email;
    public $senha;
    public $perfil;
    public $ativo;
    public $foto;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Login do usuário
     */
    public function login($email, $senha)
    {
        // Primeiro, verificar se é Super Admin
        $querySuper = "SELECT id, nome, email, senha, super_admin, ativo FROM " . $this->table_name . " WHERE email = :email AND super_admin = 1 LIMIT 1";
        $stmtSuper = $this->conn->prepare($querySuper);
        $stmtSuper->bindParam(":email", $email);
        $stmtSuper->execute();

        if ($stmtSuper->rowCount() > 0) {
            $rowSuper = $stmtSuper->fetch(PDO::FETCH_ASSOC);

            // Verificar se o super admin está ativo
            if ($rowSuper['ativo'] != 1) {
                return array("success" => false, "message" => "Usuário desativado.");
            }

            // Verificar a senha
            if (password_verify($senha, $rowSuper['senha'])) {
                // Login de Super Admin bem-sucedido
                return array(
                    "success" => true,
                    "data" => array(
                        "id" => $rowSuper['id'],
                        "restaurante_id" => 0,
                        "nome" => $rowSuper['nome'],
                        "email" => $rowSuper['email'],
                        "perfil" => 'SUPER_ADMIN',
                        "plano" => 'ENTERPRISE',
                        "foto" => '',
                        "super_admin" => 1
                    )
                );
            } else {
                return array("success" => false, "message" => "Senha incorreta.");
            }
        }

        // Se não é super admin, verificar login normal
        $query = "SELECT u.id, u.restaurante_id, u.nome, u.email, u.senha, u.perfil, u.ativo, u.foto,
                         r.status as restaurante_status, r.plano, r.data_fim
                  FROM " . $this->table_name . " u
                  INNER JOIN restaurantes r ON u.restaurante_id = r.id
                  WHERE u.email = :email
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar se o usuário está ativo
            if ($row['ativo'] != 1) {
                return array("success" => false, "message" => "Usuário desativado.");
            }

            // Verificar se o restaurante está ativo
            if ($row['restaurante_status'] != 'ATIVO') {
                return array("success" => false, "message" => "Restaurante bloqueado. Entre em contato com o suporte.");
            }

            // Verificar se a assinatura está válida
            if (strtotime($row['data_fim']) < time()) {
                return array("success" => false, "message" => "Assinatura expirada. Renove para continuar.");
            }

            // Verificar a senha
            if (password_verify($senha, $row['senha'])) {
                // Login bem-sucedido
                return array(
                    "success" => true,
                    "data" => array(
                        "id" => $row['id'],
                        "restaurante_id" => $row['restaurante_id'],
                        "nome" => $row['nome'],
                        "email" => $row['email'],
                        "perfil" => $row['perfil'],
                        "plano" => $row['plano'],
                        "foto" => $row['foto'] ?? ''
                    )
                );
            } else {
                return array("success" => false, "message" => "Senha incorreta.");
            }
        }

        return array("success" => false, "message" => "Email não encontrado.");
    }

    /**
     * Cadastrar novo usuário
     */
    public function cadastrar()
    {
        $query = "INSERT INTO " . $this->table_name . "
                  SET restaurante_id = :restaurante_id,
                      nome = :nome,
                      email = :email,
                      senha = :senha,
                      perfil = :perfil,
                      ativo = 1,
                      foto = :foto";

        $stmt = $this->conn->prepare($query);

        // Hash da senha
        $senha_hash = password_hash($this->senha, PASSWORD_BCRYPT);

        // Bind dos parâmetros
        $stmt->bindParam(":restaurante_id", $this->restaurante_id);
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":senha", $senha_hash);
        $stmt->bindParam(":perfil", $this->perfil);
        $stmt->bindParam(":foto", $this->foto);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Verificar se email já existe
     */
    public function emailExiste($email)
    {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Atualizar senha
     */
    public function atualizarSenha($usuario_id, $senha_nova)
    {
        $query = "UPDATE " . $this->table_name . " SET senha = :senha WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $senha_hash = password_hash($senha_nova, PASSWORD_BCRYPT);

        $stmt->bindParam(":senha", $senha_hash);
        $stmt->bindParam(":id", $usuario_id);

        return $stmt->execute();
    }
}

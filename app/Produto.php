<?php

/**
 * ============================================
 * CLASSE DE PRODUTOS
 * ============================================
 */

class Produto
{
    private $conn;
    private $table_name = "produtos";

    public $id;
    public $restaurante_id;
    public $categoria_id;
    public $nome;
    public $descricao;
    public $preco;
    public $custo;
    public $estoque;
    public $estoque_minimo;
    public $ativo;
    public $imagem;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Listar todos os produtos do restaurante
     */
    public function listar($restaurante_id)
    {
        $query = "SELECT p.*, c.nome as categoria_nome 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categorias c ON p.categoria_id = c.id
                  WHERE p.restaurante_id = :restaurante_id
                  ORDER BY p.nome ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":restaurante_id", $restaurante_id);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Buscar produto por ID
     */
    public function buscarPorId($id, $restaurante_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE id = :id AND restaurante_id = :restaurante_id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":restaurante_id", $restaurante_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cadastrar novo produto
     */
    public function cadastrar()
    {
        $query = "INSERT INTO " . $this->table_name . "
                  SET restaurante_id = :restaurante_id,
                      categoria_id   = :categoria_id,
                      nome           = :nome,
                      descricao      = :descricao,
                      preco          = :preco,
                      custo          = :custo,
                      estoque        = :estoque,
                      estoque_minimo = :estoque_minimo,
                      ativo          = :ativo,
                      imagem         = :imagem";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":restaurante_id", $this->restaurante_id);
        $stmt->bindParam(":categoria_id",   $this->categoria_id);
        $stmt->bindParam(":nome",           $this->nome);
        $stmt->bindParam(":descricao",      $this->descricao);
        $stmt->bindParam(":preco",          $this->preco);
        $stmt->bindParam(":custo",          $this->custo);
        $stmt->bindParam(":estoque",        $this->estoque);
        $stmt->bindParam(":estoque_minimo", $this->estoque_minimo);
        $stmt->bindParam(":ativo",          $this->ativo);
        $stmt->bindParam(":imagem",         $this->imagem);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Editar produto
     */
    public function editar()
    {
        $query = "UPDATE " . $this->table_name . "
                  SET categoria_id   = :categoria_id,
                      nome           = :nome,
                      descricao      = :descricao,
                      preco          = :preco,
                      custo          = :custo,
                      estoque        = :estoque,
                      estoque_minimo = :estoque_minimo,
                      ativo          = :ativo,
                      imagem         = :imagem
                  WHERE id = :id AND restaurante_id = :restaurante_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":categoria_id",   $this->categoria_id);
        $stmt->bindParam(":nome",           $this->nome);
        $stmt->bindParam(":descricao",      $this->descricao);
        $stmt->bindParam(":preco",          $this->preco);
        $stmt->bindParam(":custo",          $this->custo);
        $stmt->bindParam(":estoque",        $this->estoque);
        $stmt->bindParam(":estoque_minimo", $this->estoque_minimo);
        $stmt->bindParam(":ativo",          $this->ativo);
        $stmt->bindParam(":imagem",         $this->imagem);
        $stmt->bindParam(":id",             $this->id);
        $stmt->bindParam(":restaurante_id", $this->restaurante_id);

        return $stmt->execute();
    }

    /**
     * Deletar produto
     */
    public function deletar($id, $restaurante_id)
    {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE id = :id AND restaurante_id = :restaurante_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":restaurante_id", $restaurante_id);

        return $stmt->execute();
    }

    /**
     * Atualizar estoque
     */
    public function atualizarEstoque($id, $quantidade, $tipo = 'ENTRADA')
    {
        if ($tipo == 'ENTRADA') {
            $query = "UPDATE " . $this->table_name . " 
                      SET estoque = estoque + :quantidade 
                      WHERE id = :id";
        } else {
            $query = "UPDATE " . $this->table_name . " 
                      SET estoque = estoque - :quantidade 
                      WHERE id = :id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantidade", $quantidade);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    /**
     * Produtos com estoque baixo
     */
    public function estoqueBaixo($restaurante_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE restaurante_id = :restaurante_id 
                  AND estoque <= estoque_minimo 
                  AND ativo = 1
                  ORDER BY estoque ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":restaurante_id", $restaurante_id);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Contar total de produtos ativos
     */
    public function contarAtivos($restaurante_id)
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . "
                  WHERE restaurante_id = :restaurante_id AND ativo = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":restaurante_id", $restaurante_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }
}

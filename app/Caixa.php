<?php
/**
 * ============================================
 * CLASSE DE CAIXA
 * ============================================
 */

class Caixa {
    private $conn;
    private $table_name = "caixa";

    public $id;
    public $restaurante_id;
    public $usuario_id;
    public $data;
    public $abertura;
    public $fechamento;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Verificar se existe caixa aberto hoje
     */
    public function caixaAbertoHoje($restaurante_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE restaurante_id = :restaurante_id 
                  AND data = CURDATE() 
                  AND status = 'ABERTO' 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":restaurante_id", $restaurante_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Abrir caixa
     */
    public function abrir() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET restaurante_id = :restaurante_id,
                      usuario_id = :usuario_id,
                      data = CURDATE(),
                      abertura = :abertura,
                      status = 'ABERTO'";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":restaurante_id", $this->restaurante_id);
        $stmt->bindParam(":usuario_id", $this->usuario_id);
        $stmt->bindParam(":abertura", $this->abertura);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Fechar caixa
     */
    public function fechar($id, $fechamento) {
        $query = "UPDATE " . $this->table_name . " 
                  SET fechamento = :fechamento,
                      status = 'FECHADO',
                      fechado_em = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fechamento", $fechamento);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    /**
     * Listar histórico de caixas
     */
    public function listar($restaurante_id, $limite = 30) {
        $query = "SELECT c.*, u.nome as usuario_nome 
                  FROM " . $this->table_name . " c
                  LEFT JOIN usuarios u ON c.usuario_id = u.id
                  WHERE c.restaurante_id = :restaurante_id
                  ORDER BY c.data DESC, c.criado_em DESC
                  LIMIT :limite";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":restaurante_id", $restaurante_id);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Total de vendas do caixa
     */
    public function totalVendas($caixa_id) {
        $query = "SELECT SUM(total_final) as total 
                  FROM vendas 
                  WHERE caixa_id = :caixa_id 
                  AND status = 'PAGO'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":caixa_id", $caixa_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }
}
?>

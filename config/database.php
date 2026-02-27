<?php

/**
 * ============================================
 * CONFIGURAÇÃO DA BASE DE DADOS
 * ============================================
 */

class Database
{
    private $host = "localhost";
    private $db_name = "restaurante_saas";
    private $username = "root";
    private $password = "";
    public $conn;

    /**
     * Conectar à base de dados
     */
    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            // Log do erro para debugging
            error_log("Erro de conexão BD: " . $exception->getMessage());
            $this->conn = null;
        }

        return $this->conn;
    }

    /**
     * Verificar se a conexão está ativa
     */
    public function isConnected()
    {
        return $this->conn !== null;
    }
}

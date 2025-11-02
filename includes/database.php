<?php
/**
 * ProAgenda - Classe Database
 * 
 * @version 1.0
 * @author Leandro de Paula
 */

if (!defined('APP_ROOT')) {
    die('Acesso direto não permitido');
}

class Database {
    private $pdo;
    private $connected = false;
    
    public function __construct() {
        $this->conectar();
    }
    
    private function conectar() {
        if ($this->connected) return;
        
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ]);
            
            // Selecionar banco se especificado
            if (DB_NAME) {
                $this->pdo->exec("USE `" . DB_NAME . "`");
            }
            
            $this->connected = true;
            
        } catch (PDOException $e) {
            throw new Exception('Erro de conexão: ' . $e->getMessage());
        }
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception('Erro na consulta: ' . $e->getMessage());
        }
    }
    
    public function buscar($tabela, $where = '1=1', $params = [], $campos = '*', $order = '', $limit = '') {
        $sql = "SELECT $campos FROM " . DB_PREFIX . "$tabela WHERE $where";
        
        if ($order) $sql .= " ORDER BY $order";
        if ($limit) $sql .= " LIMIT $limit";
        
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function buscarUm($tabela, $where, $params = [], $campos = '*') {
        $sql = "SELECT $campos FROM " . DB_PREFIX . "$tabela WHERE $where LIMIT 1";
        return $this->query($sql, $params)->fetch() ?: null;
    }
    
    public function inserir($tabela, $dados) {
        $campos = implode(',', array_keys($dados));
        $valores = ':' . implode(', :', array_keys($dados));
        
        $sql = "INSERT INTO " . DB_PREFIX . "$tabela ($campos) VALUES ($valores)";
        
        $stmt = $this->query($sql, $dados);
        return $this->pdo->lastInsertId();
    }
    
    public function atualizar($tabela, $dados, $where, $params = []) {
        $sets = [];
        foreach (array_keys($dados) as $campo) {
            $sets[] = "$campo = :$campo";
        }
        
        $sql = "UPDATE " . DB_PREFIX . "$tabela SET " . implode(', ', $sets) . " WHERE $where";
        
        return $this->query($sql, array_merge($dados, $params))->rowCount();
    }
    
    public function deletar($tabela, $where, $params = []) {
        $sql = "DELETE FROM " . DB_PREFIX . "$tabela WHERE $where";
        return $this->query($sql, $params)->rowCount();
    }
    
    public function contar($tabela, $where = '1=1', $params = []) {
        $sql = "SELECT COUNT(*) as total FROM " . DB_PREFIX . "$tabela WHERE $where";
        $result = $this->query($sql, $params)->fetch();
        return (int) $result['total'];
    }
    
    public function iniciarTransacao() {
        return $this->pdo->beginTransaction();
    }
    
    public function confirmarTransacao() {
        return $this->pdo->commit();
    }
    
    public function cancelarTransacao() {
        return $this->pdo->rollBack();
    }
}
?>
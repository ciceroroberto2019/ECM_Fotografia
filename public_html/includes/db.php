<?php
// includes/db.php

// Carrega as constantes do config
require_once __DIR__ . '/../config.php';

/**
 * Classe de Conexão Singleton (Garante uma única conexão por requisição)
 */
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * O método estático que controla o acesso à instância.
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Método para retornar a conexão PDO
     */
    public function getConnection() {
        return $this->pdo;
    }
}

/**
 * Função helper para facilitar o acesso em nossos scripts
 */
function getDb() {
    return Database::getInstance()->getConnection();
}
?>
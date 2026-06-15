<?php
namespace Core;

use PDO;
use PDOException;

/**
 * C.E.F PDV Motel - Gestion de la Connexion DB
 */
class Database {
    private static $conn;
    private $stmt;

    public static function getConnection() {
        if (self::$conn === null) {
            // Utiliser le fichier de configuration centralisé
            require_once dirname(__DIR__) . '/../config/database.php';
            
            // Le fichier config/database.php définit $pdo et les constantes DB_HOST, etc.
            if (isset($pdo)) {
                self::$conn = $pdo;
            } else {
                // Au cas où $pdo n'est pas défini dans config/database.php
                try {
                    self::$conn = new PDO(
                        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                        DB_USER,
                        DB_PASS
                    );
                    self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (PDOException $e) {
                    die("Erreur de connexion : " . $e->getMessage());
                }
            }

            try {
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                // S'assurer que les échanges sont en UTF-8
                self::$conn->exec("set names utf8mb4");
            } catch (PDOException $exception) {
                die("Erreur de configuration PDO : " . $exception->getMessage());
            }
        }

        return self::$conn;
    }

    // --- Wrapper methods for backward compatibility with existing models ---

    public function __construct() {
        self::getConnection();
    }

    public function query($sql) {
        $this->stmt = self::$conn->prepare($sql);
    }

    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute() {
        return $this->stmt->execute();
    }

    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    public function rowCount() {
        return $this->stmt->rowCount();
    }

    public function lastInsertId() {
        return self::$conn->lastInsertId();
    }
}

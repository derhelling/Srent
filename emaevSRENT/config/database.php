<?php
// Подключение к базе данных MySQL

class Database {
    private $host = "MySQL-8.0";
    private $db_name = "sport_rental";
    private $username = "root"; 
    private $password = "";      
    public $conn;

    // Метод для получения соединения с БД
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            echo "Ошибка подключения: " . $e->getMessage();
        }
        
        return $this->conn;
    }
}
?>
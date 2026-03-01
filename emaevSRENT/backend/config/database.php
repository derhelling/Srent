<?php
/**
 * Файл: backend/config/database.php
 * Конфигурация подключения к базе данных MySQL
 * 
 * Класс Database реализует паттерн Singleton для
 * единственного подключения к БД
 * 
 * @author Студент
 * @version 1.0
 */

class Database {
    /**
     * @var string Хост базы данных (для OSPanel)
     */
    private $host = "MySQL-8.0";
    
    /**
     * @var string Имя базы данных
     */
    private $db_name = "sport_rental";
    
    /**
     * @var string Имя пользователя БД
     */
    private $username = "root";
    
    /**
     * @var string Пароль пользователя БД
     */
    private $password = "";
    
    /**
     * @var PDO|null Объект подключения к БД
     */
    private static $instance = null;
    
    /**
     * @var PDO Текущее соединение
     */
    public $conn;

    /**
     * Получение подключения к базе данных
     * 
     * Создаёт новое подключение или возвращает существующее
     * 
     * @return PDO Объект PDO для работы с БД
     */
    public function getConnection() {
        // Если соединение уже существует, возвращаем его
        if (self::$instance !== null) {
            return self::$instance;
        }
        
        try {
            // Создаём новое PDO соединение
            self::$instance = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    // Режим обработки ошибок - исключения
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    // Возвращать ассоциативные массивы по умолчанию
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // Отключаем эмуляцию подготовленных запросов
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
            $this->conn = self::$instance;
            
        } catch (PDOException $e) {
            // В случае ошибки возвращаем JSON с описанием
            jsonResponse([
                'success' => false,
                'error' => 'Ошибка подключения к базе данных',
                'message' => $e->getMessage()
            ], 500);
        }
        
        return self::$instance;
    }
    
    /**
     * Закрытие соединения с БД
     */
    public static function closeConnection() {
        self::$instance = null;
    }
}

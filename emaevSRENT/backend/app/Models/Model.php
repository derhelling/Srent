<?php
/**
 * Файл: backend/app/Models/Model.php
 * Базовый класс модели (аналог Eloquent Model)
 * 
 * Предоставляет базовые методы для работы с БД:
 * - CRUD операции
 * - Построение запросов
 * 
 * @author Студент
 * @version 1.0
 */

namespace App\Models;

use Database;
use PDO;

/**
 * Абстрактный базовый класс модели
 * 
 * Все модели приложения наследуются от этого класса
 */
abstract class Model {
    /**
     * @var PDO Подключение к базе данных
     */
    protected $db;
    
    /**
     * @var string Имя таблицы в БД (переопределяется в дочерних классах)
     */
    protected $table;
    
    /**
     * @var string Первичный ключ таблицы
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array Поля, разрешённые для массового заполнения
     */
    protected $fillable = [];
    
    /**
     * Конструктор - устанавливает подключение к БД
     */
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Получение всех записей из таблицы
     * 
     * @param string|null $orderBy Поле для сортировки
     * @param string $direction Направление сортировки (ASC/DESC)
     * @return array Массив всех записей
     */
    public function all($orderBy = null, $direction = 'ASC') {
        $sql = "SELECT * FROM {$this->table}";
        
        // Добавляем сортировку если указана
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$direction}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Поиск записи по первичному ключу
     * 
     * @param int $id Значение первичного ключа
     * @return array|null Найденная запись или null
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Поиск записей по условию
     * 
     * @param string $column Имя столбца
     * @param mixed $value Значение для поиска
     * @return array Массив найденных записей
     */
    public function where($column, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$value]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Поиск первой записи по условию
     * 
     * @param string $column Имя столбца
     * @param mixed $value Значение для поиска
     * @return array|null Найденная запись или null
     */
    public function findBy($column, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$value]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Создание новой записи
     * 
     * @param array $data Данные для создания
     * @return int ID созданной записи
     */
    public function create($data) {
        // Фильтруем данные по разрешённым полям
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        // Формируем SQL запрос
        $columns = implode(', ', array_keys($filteredData));
        $placeholders = implode(', ', array_fill(0, count($filteredData), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($filteredData));
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Обновление записи по ID
     * 
     * @param int $id ID записи
     * @param array $data Данные для обновления
     * @return bool Успешность операции
     */
    public function update($id, $data) {
        // Фильтруем данные по разрешённым полям
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        if (empty($filteredData)) {
            return false;
        }
        
        // Формируем SET часть запроса
        $setParts = [];
        foreach (array_keys($filteredData) as $column) {
            $setParts[] = "{$column} = ?";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        
        // Добавляем ID в конец массива значений
        $values = array_values($filteredData);
        $values[] = $id;
        
        return $stmt->execute($values);
    }
    
    /**
     * Удаление записи по ID
     * 
     * @param int $id ID записи
     * @return bool Успешность операции
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$id]);
    }
    
    /**
     * Подсчёт количества записей
     * 
     * @param string|null $column Столбец для условия
     * @param mixed $value Значение условия
     * @return int Количество записей
     */
    public function count($column = null, $value = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if ($column && $value !== null) {
            $sql .= " WHERE {$column} = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$value]);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }
        
        $result = $stmt->fetch();
        return (int) $result['count'];
    }
    
    /**
     * Выполнение произвольного SQL запроса
     * 
     * @param string $sql SQL запрос
     * @param array $params Параметры запроса
     * @return array Результат запроса
     */
    public function raw($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Получение PDO подключения
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->db;
    }
}

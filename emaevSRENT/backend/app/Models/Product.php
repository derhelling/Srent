<?php
/**
 * Файл: backend/app/Models/Product.php
 * Модель товара (спортивного инвентаря)
 * 
 * Представляет сущность товара для проката
 * и обеспечивает работу с таблицей products
 * 
 * @author Студент
 * @version 1.0
 */

namespace App\Models;

/**
 * Класс Product - модель товара
 * 
 * Поля таблицы products:
 * - id: первичный ключ
 * - category_id: ID категории (FK -> categories.id)
 * - name: название товара
 * - description: описание товара
 * - price_per_day: цена за день аренды
 * - stock: количество на складе
 * - is_available: доступность для аренды
 */
class Product extends Model {
    /**
     * @var string Имя таблицы в БД
     */
    protected $table = 'products';
    
    /**
     * @var array Поля, разрешённые для массового заполнения
     */
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price_per_day',
        'stock',
        'is_available'
    ];
    
    /**
     * Получение всех доступных товаров с информацией о категории
     * 
     * @param int|null $categoryId Фильтр по категории
     * @param string|null $search Поисковый запрос
     * @return array Массив товаров
     */
    public function getAvailable($categoryId = null, $search = null) {
        // Базовый запрос с JOIN для получения названия категории
        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.is_available = 1";
        
        $params = [];
        
        // Добавляем фильтр по категории
        if ($categoryId) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }
        
        // Добавляем поиск по названию и описанию
        if ($search) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        // Сортировка по названию
        $sql .= " ORDER BY p.name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Получение товара по ID с информацией о категории
     * 
     * @param int $id ID товара
     * @return array|null Данные товара или null
     */
    public function getWithCategory($id) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Получение популярных товаров для главной страницы
     * 
     * @param int $limit Количество товаров
     * @return array Массив популярных товаров
     */
    public function getPopular($limit = 6) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.is_available = 1 
                ORDER BY p.id DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Получение товаров по категории
     * 
     * @param int $categoryId ID категории
     * @return array Массив товаров
     */
    public function getByCategory($categoryId) {
        return $this->getAvailable($categoryId);
    }
    
    /**
     * Проверка доступности товара для аренды
     * 
     * @param int $id ID товара
     * @return bool True если товар доступен
     */
    public function isAvailable($id) {
        $product = $this->find($id);
        return $product && $product['is_available'] && $product['stock'] > 0;
    }
    
    /**
     * Уменьшение количества товара на складе
     * 
     * @param int $id ID товара
     * @param int $quantity Количество для уменьшения
     * @return bool Успешность операции
     */
    public function decreaseStock($id, $quantity = 1) {
        $sql = "UPDATE {$this->table} SET stock = stock - ? WHERE id = ? AND stock >= ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$quantity, $id, $quantity]);
    }
    
    /**
     * Увеличение количества товара на складе
     * 
     * @param int $id ID товара
     * @param int $quantity Количество для увеличения
     * @return bool Успешность операции
     */
    public function increaseStock($id, $quantity = 1) {
        $sql = "UPDATE {$this->table} SET stock = stock + ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$quantity, $id]);
    }
}

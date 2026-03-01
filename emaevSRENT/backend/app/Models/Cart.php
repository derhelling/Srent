<?php
/**
 * Файл: backend/app/Models/Cart.php
 * Модель корзины пользователя
 * 
 * Представляет сущность элемента корзины
 * и обеспечивает работу с таблицей cart
 * 
 * @author Студент
 * @version 1.0
 */

namespace App\Models;

/**
 * Класс Cart - модель корзины
 * 
 * Поля таблицы cart:
 * - id: первичный ключ
 * - user_id: ID пользователя (FK -> users.id)
 * - product_id: ID товара (FK -> products.id)
 * - quantity: количество единиц товара
 * - rental_days: количество дней аренды
 * - added_at: дата добавления в корзину
 */
class Cart extends Model {
    /**
     * @var string Имя таблицы в БД
     */
    protected $table = 'cart';
    
    /**
     * @var array Поля, разрешённые для массового заполнения
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'rental_days'
    ];
    
    /**
     * Получение содержимого корзины пользователя с информацией о товарах
     * 
     * @param int $userId ID пользователя
     * @return array Массив элементов корзины
     */
    public function getByUser($userId) {
        $sql = "SELECT c.*, p.name, p.price_per_day, p.stock, p.description 
                FROM {$this->table} c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ? 
                ORDER BY c.added_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Получение элемента корзины по пользователю и товару
     * 
     * @param int $userId ID пользователя
     * @param int $productId ID товара
     * @return array|null Элемент корзины или null
     */
    public function findByUserAndProduct($userId, $productId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? AND product_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $productId]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Добавление товара в корзину
     * 
     * Если товар уже есть в корзине - обновляет количество
     * 
     * @param int $userId ID пользователя
     * @param int $productId ID товара
     * @param int $days Количество дней аренды
     * @return int ID записи в корзине
     */
    public function addItem($userId, $productId, $days = 1) {
        // Проверяем, есть ли уже такой товар в корзине
        $existing = $this->findByUserAndProduct($userId, $productId);
        
        if ($existing) {
            // Обновляем количество и дни
            $this->updateItem($existing['id'], $userId, $existing['quantity'] + 1, $days);
            return $existing['id'];
        }
        
        // Добавляем новый товар
        return $this->create([
            'user_id' => $userId,
            'product_id' => $productId,
            'quantity' => 1,
            'rental_days' => $days
        ]);
    }
    
    /**
     * Обновление элемента корзины
     * 
     * @param int $cartId ID записи в корзине
     * @param int $userId ID пользователя (для проверки владельца)
     * @param int|null $quantity Новое количество
     * @param int|null $days Новое количество дней
     * @return bool Успешность операции
     */
    public function updateItem($cartId, $userId, $quantity = null, $days = null) {
        $updates = [];
        $params = [];
        
        if ($quantity !== null) {
            $updates[] = "quantity = ?";
            $params[] = $quantity;
        }
        
        if ($days !== null) {
            $updates[] = "rental_days = ?";
            $params[] = $days;
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $cartId;
        $params[] = $userId;
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($params);
    }
    
    /**
     * Удаление элемента из корзины
     * 
     * @param int $cartId ID записи в корзине
     * @param int $userId ID пользователя
     * @return bool Успешность операции
     */
    public function removeItem($cartId, $userId) {
        $sql = "DELETE FROM {$this->table} WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$cartId, $userId]);
    }
    
    /**
     * Очистка всей корзины пользователя
     * 
     * @param int $userId ID пользователя
     * @return bool Успешность операции
     */
    public function clearByUser($userId) {
        $sql = "DELETE FROM {$this->table} WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$userId]);
    }
    
    /**
     * Подсчёт общей стоимости корзины
     * 
     * @param int $userId ID пользователя
     * @return float Общая стоимость
     */
    public function getTotalByUser($userId) {
        $sql = "SELECT SUM(p.price_per_day * c.rental_days * c.quantity) as total 
                FROM {$this->table} c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        $result = $stmt->fetch();
        return (float) ($result['total'] ?? 0);
    }
    
    /**
     * Подсчёт количества товаров в корзине
     * 
     * @param int $userId ID пользователя
     * @return int Количество товаров
     */
    public function getCountByUser($userId) {
        $sql = "SELECT SUM(quantity) as count FROM {$this->table} WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        $result = $stmt->fetch();
        return (int) ($result['count'] ?? 0);
    }
}

<?php
/**
 * Файл: backend/app/Models/Order.php
 * Модель заказа
 * 
 * Представляет сущность заказа на аренду
 * и обеспечивает работу с таблицей orders
 * 
 * @author Студент
 * @version 1.0
 */

namespace App\Models;

/**
 * Класс Order - модель заказа
 * 
 * Поля таблицы orders:
 * - id: первичный ключ
 * - user_id: ID пользователя (FK -> users.id)
 * - order_date: дата создания заказа
 * - total_amount: общая сумма заказа
 * - status: статус (pending/confirmed/completed/cancelled)
 * - rental_start_date: дата начала аренды
 * - rental_end_date: дата окончания аренды
 */
class Order extends Model {
    /**
     * @var string Имя таблицы в БД
     */
    protected $table = 'orders';
    
    /**
     * @var array Поля, разрешённые для массового заполнения
     */
    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
        'rental_start_date',
        'rental_end_date'
    ];
    
    /**
     * @var array Возможные статусы заказа
     */
    public static $statuses = [
        'pending' => 'Ожидает подтверждения',
        'confirmed' => 'Подтверждён',
        'completed' => 'Завершён',
        'cancelled' => 'Отменён'
    ];
    
    /**
     * Получение заказов пользователя
     * 
     * @param int $userId ID пользователя
     * @return array Массив заказов
     */
    public function getByUser($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY order_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Получение заказа с элементами
     * 
     * @param int $orderId ID заказа
     * @param int $userId ID пользователя (для проверки владельца)
     * @return array|null Заказ с элементами или null
     */
    public function getWithItems($orderId, $userId) {
        // Получаем заказ
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId, $userId]);
        
        $order = $stmt->fetch();
        
        if (!$order) {
            return null;
        }
        
        // Получаем элементы заказа
        $sql = "SELECT oi.*, p.name as product_name 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        
        $order['items'] = $stmt->fetchAll();
        
        // Добавляем текстовый статус
        $order['status_text'] = self::$statuses[$order['status']] ?? $order['status'];
        
        return $order;
    }
    
    /**
     * Создание заказа из корзины
     * 
     * @param int $userId ID пользователя
     * @param string $startDate Дата начала аренды
     * @param string $endDate Дата окончания аренды
     * @param array $cartItems Элементы корзины
     * @return int ID созданного заказа
     */
    public function createFromCart($userId, $startDate, $endDate, $cartItems) {
        // Начинаем транзакцию
        $this->db->beginTransaction();
        
        try {
            // Подсчитываем общую сумму
            $total = 0;
            foreach ($cartItems as $item) {
                $total += $item['price_per_day'] * $item['rental_days'] * $item['quantity'];
            }
            
            // Создаём заказ
            $sql = "INSERT INTO {$this->table} (user_id, total_amount, status, rental_start_date, rental_end_date) 
                    VALUES (?, ?, 'pending', ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $total, $startDate, $endDate]);
            
            $orderId = $this->db->lastInsertId();
            
            // Создаём элементы заказа
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price_per_day, rental_days, subtotal) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            
            foreach ($cartItems as $item) {
                $subtotal = $item['price_per_day'] * $item['rental_days'] * $item['quantity'];
                $stmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price_per_day'],
                    $item['rental_days'],
                    $subtotal
                ]);
            }
            
            // Фиксируем транзакцию
            $this->db->commit();
            
            return $orderId;
            
        } catch (\Exception $e) {
            // Откатываем транзакцию при ошибке
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Обновление статуса заказа
     * 
     * @param int $orderId ID заказа
     * @param string $status Новый статус
     * @return bool Успешность операции
     */
    public function updateStatus($orderId, $status) {
        if (!array_key_exists($status, self::$statuses)) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} SET status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$status, $orderId]);
    }
    
    /**
     * Отмена заказа
     * 
     * @param int $orderId ID заказа
     * @param int $userId ID пользователя
     * @return bool Успешность операции
     */
    public function cancel($orderId, $userId) {
        $sql = "UPDATE {$this->table} SET status = 'cancelled' 
                WHERE id = ? AND user_id = ? AND status = 'pending'";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$orderId, $userId]);
    }
}

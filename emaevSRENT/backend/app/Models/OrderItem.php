<?php
/**
 * Файл: backend/app/Models/OrderItem.php
 * Модель элемента заказа
 * 
 * Представляет сущность товара в заказе
 * и обеспечивает работу с таблицей order_items
 * 
 * @author Студент
 * @version 1.0
 */

namespace App\Models;

/**
 * Класс OrderItem - модель элемента заказа
 * 
 * Поля таблицы order_items:
 * - id: первичный ключ
 * - order_id: ID заказа (FK -> orders.id)
 * - product_id: ID товара (FK -> products.id)
 * - quantity: количество единиц товара
 * - price_per_day: цена за день на момент заказа
 * - rental_days: количество дней аренды
 * - subtotal: подитог (price_per_day * rental_days * quantity)
 */
class OrderItem extends Model {
    /**
     * @var string Имя таблицы в БД
     */
    protected $table = 'order_items';
    
    /**
     * @var array Поля, разрешённые для массового заполнения
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price_per_day',
        'rental_days',
        'subtotal'
    ];
    
    /**
     * Получение элементов заказа с информацией о товарах
     * 
     * @param int $orderId ID заказа
     * @return array Массив элементов заказа
     */
    public function getByOrder($orderId) {
        $sql = "SELECT oi.*, p.name as product_name, p.description as product_description 
                FROM {$this->table} oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Создание элемента заказа
     * 
     * @param int $orderId ID заказа
     * @param int $productId ID товара
     * @param int $quantity Количество
     * @param float $pricePerDay Цена за день
     * @param int $rentalDays Дней аренды
     * @return int ID созданного элемента
     */
    public function createItem($orderId, $productId, $quantity, $pricePerDay, $rentalDays) {
        $subtotal = $pricePerDay * $rentalDays * $quantity;
        
        return $this->create([
            'order_id' => $orderId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'price_per_day' => $pricePerDay,
            'rental_days' => $rentalDays,
            'subtotal' => $subtotal
        ]);
    }
}

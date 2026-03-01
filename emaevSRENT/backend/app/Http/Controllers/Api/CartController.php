<?php
/**
 * Файл: backend/app/Http/Controllers/Api/CartController.php
 * Контроллер корзины покупок
 * 
 * Обрабатывает все операции с корзиной:
 * - Просмотр содержимого корзины
 * - Добавление товаров
 * - Изменение количества дней аренды
 * - Удаление товаров
 * - Очистка корзины
 * 
 * Все методы требуют авторизации!
 * 
 * @author Студент
 * @version 1.0
 */

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Product;

/**
 * Класс CartController - контроллер корзины
 * 
 * Маршруты:
 * GET /api/cart - содержимое корзины
 * POST /api/cart - добавить товар
 * PUT /api/cart/{id} - обновить количество дней
 * DELETE /api/cart/{id} - удалить товар
 * DELETE /api/cart - очистить корзину
 */
class CartController extends Controller {
    
    /**
     * @var Cart Модель корзины
     */
    private $cartModel;
    
    /**
     * @var Product Модель товара
     */
    private $productModel;
    
    /**
     * Конструктор - инициализация моделей
     */
    public function __construct() {
        $this->cartModel = new Cart();
        $this->productModel = new Product();
    }
    
    /**
     * Получение содержимого корзины текущего пользователя
     * 
     * GET /api/cart
     * 
     * @return void Отправляет JSON ответ с содержимым корзины
     */
    public function index() {
        // Проверяем авторизацию
        $this->requireAuth();
        
        $userId = $this->userId();
        
        // Получаем элементы корзины
        $items = $this->cartModel->getByUser($userId);
        
        // Подсчитываем итоги
        $total = 0;
        $totalItems = 0;
        
        foreach ($items as &$item) {
            // Рассчитываем стоимость каждого товара
            $item['subtotal'] = $item['price_per_day'] * $item['rental_days'] * $item['quantity'];
            $total += $item['subtotal'];
            $totalItems += $item['quantity'];
        }
        
        $this->success([
            'items' => $items,
            'total' => $total,
            'total_items' => $totalItems,
            'count' => count($items)
        ]);
    }
    
    /**
     * Добавление товара в корзину
     * 
     * POST /api/cart
     * Body: { product_id, days }
     * 
     * @return void Отправляет JSON ответ
     */
    public function store() {
        // Проверяем авторизацию
        $this->requireAuth();
        
        // Получаем данные запроса
        $data = getRequestBody();
        
        // Валидация
        $validation = $this->validate($data, [
            'product_id' => 'required|integer',
            'days' => 'required|integer'
        ]);
        
        if ($validation !== true) {
            $this->error('Ошибка валидации', 422, $validation);
        }
        
        $productId = (int) $data['product_id'];
        $days = (int) $data['days'];
        
        // Проверяем диапазон дней
        if ($days < 1 || $days > 30) {
            $this->error('Количество дней должно быть от 1 до 30', 422);
        }
        
        // Проверяем существование и доступность товара
        $product = $this->productModel->getWithCategory($productId);
        
        if (!$product) {
            $this->error('Товар не найден', 404);
        }
        
        if (!$product['is_available'] || $product['stock'] < 1) {
            $this->error('Товар недоступен для аренды', 400);
        }
        
        // Добавляем товар в корзину
        $cartId = $this->cartModel->addItem($this->userId(), $productId, $days);
        
        // Получаем обновлённую корзину
        $items = $this->cartModel->getByUser($this->userId());
        $total = $this->cartModel->getTotalByUser($this->userId());
        
        $this->success([
            'message' => 'Товар добавлен в корзину',
            'cart_id' => $cartId,
            'items' => $items,
            'total' => $total
        ], null, 201);
    }
    
    /**
     * Обновление количества дней аренды для товара в корзине
     * 
     * PUT /api/cart/{id}
     * Body: { days }
     * 
     * @param int $cartId ID записи в корзине
     * @return void Отправляет JSON ответ
     */
    public function update($cartId) {
        // Проверяем авторизацию
        $this->requireAuth();
        
        // Получаем данные запроса
        $data = getRequestBody();
        
        // Получаем количество дней
        $days = isset($data['days']) ? (int) $data['days'] : null;
        
        if ($days === null) {
            $this->error('Укажите количество дней аренды', 422);
        }
        
        // Проверяем диапазон
        if ($days < 1 || $days > 30) {
            $this->error('Количество дней должно быть от 1 до 30', 422);
        }
        
        // Обновляем запись
        $result = $this->cartModel->updateItem($cartId, $this->userId(), null, $days);
        
        if (!$result) {
            $this->error('Не удалось обновить корзину', 400);
        }
        
        // Получаем обновлённую корзину
        $items = $this->cartModel->getByUser($this->userId());
        $total = $this->cartModel->getTotalByUser($this->userId());
        
        $this->success([
            'message' => 'Корзина обновлена',
            'items' => $items,
            'total' => $total
        ]);
    }
    
    /**
     * Удаление товара из корзины
     * 
     * DELETE /api/cart/{id}
     * 
     * @param int $cartId ID записи в корзине
     * @return void Отправляет JSON ответ
     */
    public function destroy($cartId) {
        // Проверяем авторизацию
        $this->requireAuth();
        
        // Удаляем элемент
        $result = $this->cartModel->removeItem($cartId, $this->userId());
        
        if (!$result) {
            $this->error('Не удалось удалить товар из корзины', 400);
        }
        
        // Получаем обновлённую корзину
        $items = $this->cartModel->getByUser($this->userId());
        $total = $this->cartModel->getTotalByUser($this->userId());
        
        $this->success([
            'message' => 'Товар удалён из корзины',
            'items' => $items,
            'total' => $total
        ]);
    }
    
    /**
     * Полная очистка корзины
     * 
     * DELETE /api/cart
     * 
     * @return void Отправляет JSON ответ
     */
    public function clear() {
        // Проверяем авторизацию
        $this->requireAuth();
        
        // Очищаем корзину
        $this->cartModel->clearByUser($this->userId());
        
        $this->success([
            'message' => 'Корзина очищена',
            'items' => [],
            'total' => 0
        ]);
    }
}

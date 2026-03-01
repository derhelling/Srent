<?php
/**
 * Файл: backend/app/Http/Controllers/Api/OrderController.php
 * Контроллер заказов
 * 
 * Обрабатывает операции с заказами:
 * - Создание заказа из корзины
 * - Просмотр списка заказов
 * - Просмотр деталей заказа
 * 
 * Все методы требуют авторизации!
 * 
 * @author Студент
 * @version 1.0
 */

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Cart;

/**
 * Класс OrderController - контроллер заказов
 * 
 * Маршруты:
 * GET /api/orders - список заказов пользователя
 * POST /api/orders - создать заказ
 * GET /api/orders/{id} - детали заказа
 */
class OrderController extends Controller {
    
    /**
     * @var Order Модель заказа
     */
    private $orderModel;
    
    /**
     * @var Cart Модель корзины
     */
    private $cartModel;
    
    /**
     * Конструктор - инициализация моделей
     */
    public function __construct() {
        $this->orderModel = new Order();
        $this->cartModel = new Cart();
    }
    
    /**
     * Получение списка заказов текущего пользователя
     * 
     * GET /api/orders
     * 
     * @return void Отправляет JSON ответ со списком заказов
     */
    public function index() {
        // Проверяем авторизацию
        $this->requireAuth();
        
        // Получаем заказы пользователя
        $orders = $this->orderModel->getByUser($this->userId());
        
        // Добавляем текстовые статусы
        foreach ($orders as &$order) {
            $order['status_text'] = Order::$statuses[$order['status']] ?? $order['status'];
        }
        
        $this->success([
            'orders' => $orders,
            'total' => count($orders)
        ]);
    }
    
    /**
     * Создание нового заказа из корзины
     * 
     * POST /api/orders
     * Body: { start_date, end_date }
     * 
     * @return void Отправляет JSON ответ с данными заказа
     */
    public function store() {
        // Проверяем авторизацию
        $this->requireAuth();
        
        // Получаем данные запроса
        $data = getRequestBody();
        
        // Валидация дат
        $validation = $this->validate($data, [
            'start_date' => 'required',
            'end_date' => 'required'
        ]);
        
        if ($validation !== true) {
            $this->error('Ошибка валидации', 422, $validation);
        }
        
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        
        // Проверяем корректность дат
        if (strtotime($startDate) < strtotime(date('Y-m-d'))) {
            $this->error('Дата начала не может быть в прошлом', 422);
        }
        
        if (strtotime($endDate) <= strtotime($startDate)) {
            $this->error('Дата окончания должна быть позже даты начала', 422);
        }
        
        // Получаем содержимое корзины
        $cartItems = $this->cartModel->getByUser($this->userId());
        
        if (empty($cartItems)) {
            $this->error('Корзина пуста', 400);
        }
        
        try {
            // Создаём заказ
            $orderId = $this->orderModel->createFromCart(
                $this->userId(),
                $startDate,
                $endDate,
                $cartItems
            );
            
            // Очищаем корзину после создания заказа
            $this->cartModel->clearByUser($this->userId());
            
            // Получаем созданный заказ с деталями
            $order = $this->orderModel->getWithItems($orderId, $this->userId());
            
            $this->success([
                'message' => 'Заказ успешно оформлен!',
                'order' => $order
            ], null, 201);
            
        } catch (\Exception $e) {
            $this->error('Ошибка при создании заказа: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Получение деталей конкретного заказа
     * 
     * GET /api/orders/{id}
     * 
     * @param int $orderId ID заказа
     * @return void Отправляет JSON ответ с деталями заказа
     */
    public function show($orderId) {
        // Проверяем авторизацию
        $this->requireAuth();
        
        // Получаем заказ с элементами
        $order = $this->orderModel->getWithItems($orderId, $this->userId());
        
        if (!$order) {
            $this->error('Заказ не найден', 404);
        }
        
        $this->success($order);
    }
}

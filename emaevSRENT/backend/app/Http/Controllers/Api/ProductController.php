<?php
/**
 * Файл: backend/app/Http/Controllers/Api/ProductController.php
 * Контроллер товаров
 * 
 * Обрабатывает запросы связанные с товарами:
 * - Получение списка товаров
 * - Получение товара по ID
 * - Фильтрация и поиск
 * 
 * @author Студент
 * @version 1.0
 */

namespace App\Http\Controllers\Api;

use App\Models\Product;

/**
 * Класс ProductController - контроллер товаров
 * 
 * Маршруты:
 * GET /api/products - список товаров
 * GET /api/products/{id} - товар по ID
 */
class ProductController extends Controller {
    
    /**
     * @var Product Модель товара
     */
    private $productModel;
    
    /**
     * Конструктор - инициализация модели
     */
    public function __construct() {
        $this->productModel = new Product();
    }
    
    /**
     * Получение списка товаров
     * 
     * GET /api/products
     * Query параметры:
     * - category_id: фильтр по категории
     * - search: поисковый запрос
     * - popular: если true - только популярные товары
     * 
     * @return void Отправляет JSON ответ
     */
    public function index() {
        // Получаем параметры фильтрации из URL
        $categoryId = $_GET['category_id'] ?? null;
        $search = $_GET['search'] ?? null;
        $popular = isset($_GET['popular']) && $_GET['popular'] === 'true';
        
        // Если запрошены популярные товары
        if ($popular) {
            $limit = (int) ($_GET['limit'] ?? 6);
            $products = $this->productModel->getPopular($limit);
        } else {
            // Получаем товары с фильтрацией
            $products = $this->productModel->getAvailable($categoryId, $search);
        }
        
        // Формируем ответ
        $this->success([
            'products' => $products,
            'total' => count($products),
            'filters' => [
                'category_id' => $categoryId,
                'search' => $search
            ]
        ]);
    }
    
    /**
     * Получение товара по ID
     * 
     * GET /api/products/{id}
     * 
     * @param int $id ID товара
     * @return void Отправляет JSON ответ
     */
    public function show($id) {
        // Получаем товар с информацией о категории
        $product = $this->productModel->getWithCategory($id);
        
        // Если товар не найден
        if (!$product) {
            $this->error('Товар не найден', 404);
        }
        
        $this->success($product);
    }
}

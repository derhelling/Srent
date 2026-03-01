<?php
/**
 * Файл: backend/app/Http/Controllers/Api/CategoryController.php
 * Контроллер категорий товаров
 * 
 * Обрабатывает запросы связанные с категориями:
 * - Получение списка категорий
 * 
 * @author Студент
 * @version 1.0
 */

namespace App\Http\Controllers\Api;

use App\Models\Category;

/**
 * Класс CategoryController - контроллер категорий
 * 
 * Маршруты:
 * GET /api/categories - список категорий
 */
class CategoryController extends Controller {
    
    /**
     * @var Category Модель категории
     */
    private $categoryModel;
    
    /**
     * Конструктор - инициализация модели
     */
    public function __construct() {
        $this->categoryModel = new Category();
    }
    
    /**
     * Получение списка всех категорий
     * 
     * GET /api/categories
     * 
     * Возвращает категории с количеством товаров в каждой
     * 
     * @return void Отправляет JSON ответ
     */
    public function index() {
        // Получаем все категории с подсчётом товаров
        $categories = $this->categoryModel->getAllWithProductCount();
        
        $this->success([
            'categories' => $categories,
            'total' => count($categories)
        ]);
    }
}

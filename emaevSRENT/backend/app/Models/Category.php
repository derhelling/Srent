<?php
/**
 * Файл: backend/app/Models/Category.php
 * Модель категории товаров
 * 
 * Представляет сущность категории спортивного инвентаря
 * и обеспечивает работу с таблицей categories
 * 
 * @author Студент
 * @version 1.0
 */

namespace App\Models;

/**
 * Класс Category - модель категории
 * 
 * Поля таблицы categories:
 * - id: первичный ключ
 * - name: название категории
 * - description: описание категории
 */
class Category extends Model {
    /**
     * @var string Имя таблицы в БД
     */
    protected $table = 'categories';
    
    /**
     * @var array Поля, разрешённые для массового заполнения
     */
    protected $fillable = [
        'name',
        'description'
    ];
    
    /**
     * Получение всех категорий с количеством товаров
     * 
     * @return array Массив категорий с количеством товаров
     */
    public function getAllWithProductCount() {
        $sql = "SELECT c.*, COUNT(p.id) as products_count 
                FROM {$this->table} c 
                LEFT JOIN products p ON c.id = p.category_id AND p.is_available = 1 
                GROUP BY c.id 
                ORDER BY c.name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Получение категории с её товарами
     * 
     * @param int $id ID категории
     * @return array|null Категория с товарами или null
     */
    public function getWithProducts($id) {
        // Получаем категорию
        $category = $this->find($id);
        
        if (!$category) {
            return null;
        }
        
        // Получаем товары категории
        $sql = "SELECT * FROM products WHERE category_id = ? AND is_available = 1 ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        $category['products'] = $stmt->fetchAll();
        
        return $category;
    }
}

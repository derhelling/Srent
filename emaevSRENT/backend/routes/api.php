<?php
/**
 * Файл: backend/routes/api.php
 * Маршрутизатор API запросов
 * 
 * Определяет все доступные маршруты API и связывает их
 * с соответствующими контроллерами и методами
 * 
 * @author Студент
 * @version 1.0
 */

/**
 * Класс Router - простой маршрутизатор для API
 * 
 * Реализует базовую маршрутизацию HTTP запросов
 * аналогично Laravel Router
 */
class Router {
    /**
     * @var array Массив зарегистрированных маршрутов
     */
    private $routes = [];
    
    /**
     * Конструктор - регистрирует все маршруты API
     */
    public function __construct() {
        // ============================================
        // МАРШРУТЫ АУТЕНТИФИКАЦИИ
        // ============================================
        
        // POST /api/auth/register - Регистрация нового пользователя
        $this->addRoute('POST', '/api/auth/register', 'AuthController', 'register');
        
        // POST /api/auth/login - Вход в систему
        $this->addRoute('POST', '/api/auth/login', 'AuthController', 'login');
        
        // POST /api/auth/logout - Выход из системы
        $this->addRoute('POST', '/api/auth/logout', 'AuthController', 'logout');
        
        // GET /api/auth/user - Получение данных текущего пользователя
        $this->addRoute('GET', '/api/auth/user', 'AuthController', 'user');
        
        // POST /api/auth/forgot - Запрос на восстановление пароля
        $this->addRoute('POST', '/api/auth/forgot', 'AuthController', 'forgot');
        
        // POST /api/auth/reset - Сброс пароля
        $this->addRoute('POST', '/api/auth/reset', 'AuthController', 'reset');
        
        // ============================================
        // МАРШРУТЫ ТОВАРОВ
        // ============================================
        
        // GET /api/products - Список всех товаров
        $this->addRoute('GET', '/api/products', 'ProductController', 'index');
        
        // GET /api/products/{id} - Получение товара по ID
        $this->addRoute('GET', '/api/products/{id}', 'ProductController', 'show');
        
        // ============================================
        // МАРШРУТЫ КАТЕГОРИЙ
        // ============================================
        
        // GET /api/categories - Список всех категорий
        $this->addRoute('GET', '/api/categories', 'CategoryController', 'index');
        
        // ============================================
        // МАРШРУТЫ КОРЗИНЫ (требуют аутентификации)
        // ============================================
        
        // GET /api/cart - Получение содержимого корзины
        $this->addRoute('GET', '/api/cart', 'CartController', 'index');
        
        // POST /api/cart - Добавление товара в корзину
        $this->addRoute('POST', '/api/cart', 'CartController', 'store');
        
        // PUT /api/cart/{id} - Обновление количества дней аренды
        $this->addRoute('PUT', '/api/cart/{id}', 'CartController', 'update');
        
        // DELETE /api/cart/{id} - Удаление товара из корзины
        $this->addRoute('DELETE', '/api/cart/{id}', 'CartController', 'destroy');
        
        // DELETE /api/cart - Очистка всей корзины
        $this->addRoute('DELETE', '/api/cart', 'CartController', 'clear');
        
        // ============================================
        // МАРШРУТЫ ЗАКАЗОВ (требуют аутентификации)
        // ============================================
        
        // GET /api/orders - Список заказов пользователя
        $this->addRoute('GET', '/api/orders', 'OrderController', 'index');
        
        // POST /api/orders - Создание нового заказа
        $this->addRoute('POST', '/api/orders', 'OrderController', 'store');
        
        // GET /api/orders/{id} - Детали заказа
        $this->addRoute('GET', '/api/orders/{id}', 'OrderController', 'show');
    }
    
    /**
     * Добавление маршрута в таблицу маршрутов
     * 
     * @param string $method HTTP метод (GET, POST, PUT, DELETE)
     * @param string $path Путь маршрута
     * @param string $controller Имя контроллера
     * @param string $action Имя метода контроллера
     */
    private function addRoute($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    /**
     * Обработка входящего запроса и вызов соответствующего контроллера
     * 
     * @param string $method HTTP метод запроса
     * @param string $uri URI запроса
     */
    public function dispatch($method, $uri) {
        // Перебираем все зарегистрированные маршруты
        foreach ($this->routes as $route) {
            // Проверяем совпадение метода
            if ($route['method'] !== $method) {
                continue;
            }
            
            // Преобразуем шаблон маршрута в регулярное выражение
            // {id} -> (\d+) для числовых параметров
            $pattern = preg_replace('/\{(\w+)\}/', '(\d+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';
            
            // Проверяем совпадение URI с шаблоном
            if (preg_match($pattern, $uri, $matches)) {
                // Удаляем полное совпадение, оставляем только параметры
                array_shift($matches);
                
                // Формируем полное имя класса контроллера
                $controllerClass = 'App\\Http\\Controllers\\Api\\' . $route['controller'];
                $controllerFile = BASE_PATH . '/app/Http/Controllers/Api/' . $route['controller'] . '.php';
                
                // Проверяем существование файла контроллера
                if (!file_exists($controllerFile)) {
                    jsonResponse([
                        'success' => false,
                        'error' => 'Контроллер не найден: ' . $route['controller']
                    ], 500);
                }
                
                // Подключаем файл контроллера
                require_once $controllerFile;
                
                // Создаём экземпляр контроллера и вызываем метод
                $controller = new $controllerClass();
                $action = $route['action'];
                
                // Вызываем метод с параметрами из URL
                call_user_func_array([$controller, $action], $matches);
                return;
            }
        }
        
        // Если маршрут не найден - возвращаем 404
        jsonResponse([
            'success' => false,
            'error' => 'Маршрут не найден',
            'path' => $uri,
            'method' => $method
        ], 404);
    }
}

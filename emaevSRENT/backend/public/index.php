<?php
/**
 * Файл: backend/public/index.php
 * Точка входа Laravel-подобного API приложения
 * 
 * Этот файл инициализирует приложение и маршрутизирует запросы
 * к соответствующим контроллерам API
 * 
 * @author Студент
 * @version 1.0
 */

// Включаем отображение ошибок для разработки
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Устанавливаем заголовки для JSON API и CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Обработка preflight запросов (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Запускаем сессию для хранения данных аутентификации
session_start();

// Определяем базовый путь приложения
define('BASE_PATH', dirname(__DIR__));

// Подключаем автозагрузчик классов
require_once BASE_PATH . '/app/autoload.php';

// Подключаем конфигурацию базы данных
require_once BASE_PATH . '/config/database.php';

// Подключаем маршрутизатор
require_once BASE_PATH . '/routes/api.php';

// Получаем URI запроса и метод
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Убираем query string из URI
$requestUri = parse_url($requestUri, PHP_URL_PATH);

// Убираем базовый путь если есть (для работы в подпапке)
$basePath = '/backend/public';
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Если URI пустой, устанавливаем корневой путь
if (empty($requestUri)) {
    $requestUri = '/';
}

// Запускаем маршрутизацию
try {
    $router = new Router();
    $router->dispatch($requestMethod, $requestUri);
} catch (Exception $e) {
    // Обработка ошибок - возвращаем JSON с ошибкой
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Внутренняя ошибка сервера',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

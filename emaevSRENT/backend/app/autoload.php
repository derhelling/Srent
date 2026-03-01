<?php
/**
 * Файл: backend/app/autoload.php
 * Автозагрузчик классов для Laravel-подобной структуры
 * 
 * Реализует автоматическую загрузку классов по пространству имён
 * без использования Composer
 * 
 * @author Студент
 * @version 1.0
 */

// Регистрируем функцию автозагрузки
spl_autoload_register(function ($className) {
    // Базовая директория для классов приложения
    $baseDir = dirname(__DIR__) . '/app/';
    
    // Преобразуем пространство имён в путь к файлу
    // App\Models\User -> app/Models/User.php
    $className = str_replace('App\\', '', $className);
    $filePath = $baseDir . str_replace('\\', '/', $className) . '.php';
    
    // Если файл существует, подключаем его
    if (file_exists($filePath)) {
        require_once $filePath;
        return true;
    }
    
    return false;
});

/**
 * Вспомогательная функция для отправки JSON ответа
 * 
 * @param mixed $data Данные для отправки
 * @param int $statusCode HTTP статус код
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

/**
 * Вспомогательная функция для получения данных из тела запроса
 * 
 * @return array Декодированные JSON данные
 */
function getRequestBody() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    return $data ?? [];
}

/**
 * Вспомогательная функция для проверки аутентификации
 * 
 * @return bool True если пользователь авторизован
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Вспомогательная функция для получения ID текущего пользователя
 * 
 * @return int|null ID пользователя или null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

<?php
/**
 * Файл: backend/app/Http/Controllers/Api/Controller.php
 * Базовый класс контроллера
 * 
 * Предоставляет общие методы для всех API контроллеров
 * 
 * @author Студент
 * @version 1.0
 */

namespace App\Http\Controllers\Api;

/**
 * Абстрактный базовый класс контроллера
 * 
 * Все API контроллеры наследуются от этого класса
 */
abstract class Controller {
    
    /**
     * Отправка успешного JSON ответа
     * 
     * @param mixed $data Данные для отправки
     * @param string|null $message Сообщение
     * @param int $statusCode HTTP статус код
     */
    protected function success($data = null, $message = null, $statusCode = 200) {
        $response = ['success' => true];
        
        if ($message) {
            $response['message'] = $message;
        }
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        jsonResponse($response, $statusCode);
    }
    
    /**
     * Отправка ответа с ошибкой
     * 
     * @param string $message Сообщение об ошибке
     * @param int $statusCode HTTP статус код
     * @param array|null $errors Детали ошибок
     */
    protected function error($message, $statusCode = 400, $errors = null) {
        $response = [
            'success' => false,
            'error' => $message
        ];
        
        if ($errors) {
            $response['errors'] = $errors;
        }
        
        jsonResponse($response, $statusCode);
    }
    
    /**
     * Проверка, что пользователь авторизован
     * Возвращает ошибку 401 если не авторизован
     */
    protected function requireAuth() {
        if (!isAuthenticated()) {
            $this->error('Необходима авторизация', 401);
        }
    }
    
    /**
     * Получение ID текущего авторизованного пользователя
     * 
     * @return int|null ID пользователя
     */
    protected function userId() {
        return getCurrentUserId();
    }
    
    /**
     * Валидация входных данных
     * 
     * @param array $data Данные для проверки
     * @param array $rules Правила валидации
     * @return array|true Массив ошибок или true если всё верно
     */
    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $ruleList = explode('|', $rule);
            
            foreach ($ruleList as $r) {
                // Правило required - поле обязательно
                if ($r === 'required') {
                    if (!isset($data[$field]) || trim($data[$field]) === '') {
                        $errors[$field][] = "Поле {$field} обязательно для заполнения";
                    }
                }
                
                // Правило email - проверка формата email
                if ($r === 'email' && isset($data[$field])) {
                    if (!filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                        $errors[$field][] = "Неверный формат email";
                    }
                }
                
                // Правило min:N - минимальная длина
                if (strpos($r, 'min:') === 0 && isset($data[$field])) {
                    $min = (int) substr($r, 4);
                    if (strlen($data[$field]) < $min) {
                        $errors[$field][] = "Минимальная длина поля {$field}: {$min} символов";
                    }
                }
                
                // Правило max:N - максимальная длина
                if (strpos($r, 'max:') === 0 && isset($data[$field])) {
                    $max = (int) substr($r, 4);
                    if (strlen($data[$field]) > $max) {
                        $errors[$field][] = "Максимальная длина поля {$field}: {$max} символов";
                    }
                }
                
                // Правило numeric - число
                if ($r === 'numeric' && isset($data[$field])) {
                    if (!is_numeric($data[$field])) {
                        $errors[$field][] = "Поле {$field} должно быть числом";
                    }
                }
                
                // Правило integer - целое число
                if ($r === 'integer' && isset($data[$field])) {
                    if (!filter_var($data[$field], FILTER_VALIDATE_INT)) {
                        $errors[$field][] = "Поле {$field} должно быть целым числом";
                    }
                }
            }
        }
        
        return empty($errors) ? true : $errors;
    }
}

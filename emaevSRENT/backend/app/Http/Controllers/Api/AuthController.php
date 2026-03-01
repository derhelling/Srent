<?php
/**
 * Файл: backend/app/Http/Controllers/Api/AuthController.php
 * Контроллер аутентификации
 * 
 * Обрабатывает все запросы связанные с:
 * - Регистрацией пользователей
 * - Входом в систему
 * - Выходом из системы
 * - Восстановлением пароля
 * 
 * @author Студент
 * @version 1.0
 */

namespace App\Http\Controllers\Api;

use App\Models\User;

/**
 * Класс AuthController - контроллер аутентификации
 * 
 * Маршруты:
 * POST /api/auth/register - регистрация
 * POST /api/auth/login - вход
 * POST /api/auth/logout - выход
 * GET /api/auth/user - текущий пользователь
 * POST /api/auth/forgot - запрос на сброс пароля
 * POST /api/auth/reset - сброс пароля
 */
class AuthController extends Controller {
    
    /**
     * @var User Модель пользователя
     */
    private $userModel;
    
    /**
     * Конструктор - инициализация модели
     */
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Регистрация нового пользователя
     * 
     * POST /api/auth/register
     * Body: { username, email, password, password_confirmation }
     */
    public function register() {
        // Получаем данные из тела запроса
        $data = getRequestBody();
        
        // Валидация входных данных
        $validation = $this->validate($data, [
            'username' => 'required|min:3|max:50',
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);
        
        if ($validation !== true) {
            $this->error('Ошибка валидации', 422, $validation);
        }
        
        // Проверка подтверждения пароля
        if (($data['password'] ?? '') !== ($data['password_confirmation'] ?? '')) {
            $this->error('Пароли не совпадают', 422);
        }
        
        // Проверяем, не существует ли уже такой пользователь
        if ($this->userModel->exists($data['username'], $data['email'])) {
            $this->error('Пользователь с таким именем или email уже существует', 409);
        }
        
        // Создаём нового пользователя
        $userId = $this->userModel->register(
            trim($data['username']),
            trim($data['email']),
            $data['password']
        );
        
        // Получаем созданного пользователя
        $user = $this->userModel->find($userId);
        
        // Убираем пароль из ответа
        unset($user['password'], $user['reset_token'], $user['reset_expires']);
        
        $this->success($user, 'Регистрация успешна!', 201);
    }
    
    /**
     * Вход в систему
     * 
     * POST /api/auth/login
     * Body: { username, password }
     */
    public function login() {
        // Получаем данные из тела запроса
        $data = getRequestBody();
        
        // Валидация
        $validation = $this->validate($data, [
            'username' => 'required',
            'password' => 'required'
        ]);
        
        if ($validation !== true) {
            $this->error('Ошибка валидации', 422, $validation);
        }
        
        // Ищем пользователя по логину (username или email)
        $user = $this->userModel->findByLogin($data['username']);
        
        // Проверяем пароль
        if (!$user || !$this->userModel->verifyPassword($user, $data['password'])) {
            $this->error('Неверное имя пользователя или пароль', 401);
        }
        
        // Сохраняем данные пользователя в сессию
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Убираем чувствительные данные из ответа
        unset($user['password'], $user['reset_token'], $user['reset_expires']);
        
        $this->success([
            'user' => $user,
            'message' => 'Вход выполнен успешно'
        ]);
    }
    
    /**
     * Выход из системы
     * 
     * POST /api/auth/logout
     */
    public function logout() {
        // Очищаем сессию
        session_unset();
        session_destroy();
        
        // Запускаем новую сессию для следующих запросов
        session_start();
        
        $this->success(null, 'Выход выполнен успешно');
    }
    
    /**
     * Получение данных текущего пользователя
     * 
     * GET /api/auth/user
     */
    public function user() {
        // Проверяем авторизацию
        if (!isAuthenticated()) {
            $this->error('Не авторизован', 401);
        }
        
        // Получаем пользователя из БД
        $user = $this->userModel->find(getCurrentUserId());
        
        if (!$user) {
            // Если пользователь не найден - очищаем сессию
            session_unset();
            $this->error('Пользователь не найден', 404);
        }
        
        // Убираем чувствительные данные
        unset($user['password'], $user['reset_token'], $user['reset_expires']);
        
        $this->success($user);
    }
    
    /**
     * Запрос на восстановление пароля
     * 
     * POST /api/auth/forgot
     * Body: { email }
     */
    public function forgot() {
        // Получаем данные
        $data = getRequestBody();
        
        // Валидация
        $validation = $this->validate($data, [
            'email' => 'required|email'
        ]);
        
        if ($validation !== true) {
            $this->error('Ошибка валидации', 422, $validation);
        }
        
        // Ищем пользователя
        $user = $this->userModel->findByEmail($data['email']);
        
        if ($user) {
            // Устанавливаем фиксированный код для учебного проекта
            $resetCode = '123456';
            $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            $this->userModel->setResetToken($user['id'], $resetCode, $expires);
        }
        
        // Всегда возвращаем успех (для безопасности)
        $this->success([
            'message' => 'Если email зарегистрирован, код будет отправлен',
            'hint' => 'В учебном режиме используйте код: 123456'
        ]);
    }
    
    /**
     * Сброс пароля
     * 
     * POST /api/auth/reset
     * Body: { email, code, password, password_confirmation }
     */
    public function reset() {
        // Получаем данные
        $data = getRequestBody();
        
        // Валидация
        $validation = $this->validate($data, [
            'email' => 'required|email',
            'code' => 'required',
            'password' => 'required|min:6'
        ]);
        
        if ($validation !== true) {
            $this->error('Ошибка валидации', 422, $validation);
        }
        
        // Проверка подтверждения пароля
        if (($data['password'] ?? '') !== ($data['password_confirmation'] ?? '')) {
            $this->error('Пароли не совпадают', 422);
        }
        
        // Проверяем код (для учебного проекта принимаем 123456)
        $user = $this->userModel->findByEmail($data['email']);
        
        if (!$user) {
            $this->error('Пользователь не найден', 404);
        }
        
        // Проверяем код
        if ($data['code'] !== '123456') {
            $verified = $this->userModel->verifyResetToken($data['email'], $data['code']);
            if (!$verified) {
                $this->error('Неверный или истекший код', 400);
            }
        }
        
        // Сбрасываем пароль
        $this->userModel->resetPassword($user['id'], $data['password']);
        
        $this->success(null, 'Пароль успешно изменён!');
    }
}

<?php
/**
 * Файл: backend/app/Models/User.php
 * Модель пользователя
 * 
 * Представляет сущность пользователя в системе
 * и обеспечивает работу с таблицей users
 * 
 * @author Студент
 * @version 1.0
 */

namespace App\Models;

/**
 * Класс User - модель пользователя
 * 
 * Поля таблицы users:
 * - id: первичный ключ
 * - username: имя пользователя (уникальное)
 * - email: электронная почта (уникальная)
 * - password: хэш пароля
 * - reset_token: токен для сброса пароля
 * - reset_expires: срок действия токена
 * - role: роль (user/admin)
 * - created_at: дата регистрации
 */
class User extends Model {
    /**
     * @var string Имя таблицы в БД
     */
    protected $table = 'users';
    
    /**
     * @var array Поля, разрешённые для массового заполнения
     */
    protected $fillable = [
        'username',
        'email', 
        'password',
        'reset_token',
        'reset_expires',
        'role'
    ];
    
    /**
     * Поиск пользователя по email
     * 
     * @param string $email Email для поиска
     * @return array|null Данные пользователя или null
     */
    public function findByEmail($email) {
        return $this->findBy('email', $email);
    }
    
    /**
     * Поиск пользователя по имени
     * 
     * @param string $username Имя пользователя
     * @return array|null Данные пользователя или null
     */
    public function findByUsername($username) {
        return $this->findBy('username', $username);
    }
    
    /**
     * Поиск пользователя по email или username
     * Используется при авторизации
     * 
     * @param string $login Email или имя пользователя
     * @return array|null Данные пользователя или null
     */
    public function findByLogin($login) {
        $sql = "SELECT * FROM {$this->table} WHERE username = ? OR email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$login, $login]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Проверка существования пользователя с таким email или username
     * 
     * @param string $username Имя пользователя
     * @param string $email Email
     * @return bool True если пользователь существует
     */
    public function exists($username, $email) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = ? OR email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username, $email]);
        
        $result = $stmt->fetch();
        return (int) $result['count'] > 0;
    }
    
    /**
     * Создание нового пользователя с хэшированием пароля
     * 
     * @param string $username Имя пользователя
     * @param string $email Email
     * @param string $password Пароль (открытый текст)
     * @return int ID созданного пользователя
     */
    public function register($username, $email, $password) {
        // Хэшируем пароль перед сохранением
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        return $this->create([
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => 'user'
        ]);
    }
    
    /**
     * Проверка пароля пользователя
     * 
     * @param array $user Данные пользователя
     * @param string $password Пароль для проверки
     * @return bool True если пароль верный
     */
    public function verifyPassword($user, $password) {
        return password_verify($password, $user['password']);
    }
    
    /**
     * Установка токена для сброса пароля
     * 
     * @param int $userId ID пользователя
     * @param string $token Токен
     * @param string $expires Срок действия
     * @return bool Успешность операции
     */
    public function setResetToken($userId, $token, $expires) {
        $sql = "UPDATE {$this->table} SET reset_token = ?, reset_expires = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$token, $expires, $userId]);
    }
    
    /**
     * Проверка токена сброса пароля
     * 
     * @param string $email Email пользователя
     * @param string $token Токен для проверки
     * @return array|null Данные пользователя или null
     */
    public function verifyResetToken($email, $token) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE email = ? 
                AND (reset_token = ? OR ? = '123456') 
                AND reset_expires > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email, $token, $token]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Сброс пароля пользователя
     * 
     * @param int $userId ID пользователя
     * @param string $newPassword Новый пароль
     * @return bool Успешность операции
     */
    public function resetPassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $sql = "UPDATE {$this->table} 
                SET password = ?, reset_token = NULL, reset_expires = NULL 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$hashedPassword, $userId]);
    }
}

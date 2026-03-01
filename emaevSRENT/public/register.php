<?php
// Файл: register.php
// Страница регистрации нового пользователя

require_once (__DIR__ . '/../config/database.php');

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Валидация данных
    if(empty($username) || empty($email) || empty($password)) {
        $error = 'Все поля обязательны для заполнения';
    } elseif($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
    } elseif(strlen($password) < 6) {
        $error = 'Пароль должен быть не менее 6 символов';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Проверка, существует ли уже пользователь
        $query = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$username, $email]);
        
        if($stmt->rowCount() > 0) {
            $error = 'Пользователь с таким именем или email уже существует';
        } else {
            // Хеширование пароля и сохранение пользователя
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt = $db->prepare($query);
            
            if($stmt->execute([$username, $email, $hashed_password])) {
                $success = 'Регистрация успешна! <a href="login.php">Войдите</a> в аккаунт.';
            } else {
                $error = 'Ошибка при регистрации';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация - СпортПрокат</title>
    <link rel="stylesheet" href="/../css/style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Регистрация</h2>
        
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" class="auth-form">
            <div class="form-group">
                <label>Имя пользователя:</label>
                <input type="text" name="username" required>
            </div>
            
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Пароль:</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label>Подтвердите пароль:</label>
                <input type="password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn">Зарегистрироваться</button>
        </form>
        
        <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
    </div>
</body>
</html>
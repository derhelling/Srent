<?php
// Страница входа с редиректом на запрошенную страницу

session_start();
require_once (__DIR__ . '/../config/database.php');

// Если пользователь уже авторизован, отправляем на главную
if(isset($_SESSION['user_id'])) {
    header('Location: /../index.php');
    exit;
}

$error = '';

// Показываем сообщение, если пользователь пытался добавить товар без авторизации
if(isset($_SESSION['login_message'])) {
    $login_message = $_SESSION['login_message'];
    unset($_SESSION['login_message']);
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if(empty($username) || empty($password)) {
        $error = 'Заполните все поля';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Поиск пользователя по имени или email
        $query = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user && password_verify($password, $user['password'])) {
            // Успешный вход
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Проверяем, есть ли сохраненный товар для добавления в корзину
            if(isset($_SESSION['pending_cart_item'])) {
                $pending_item = $_SESSION['pending_cart_item'];
                unset($_SESSION['pending_cart_item']);
                
                // Перенаправляем на страницу добавления в корзину
                header('Location: cart.php?add=' . $pending_item['product_id'] . '&days=' . $pending_item['days']);
                exit;
            }
            
            // Редирект на запрошенную страницу или на главную
            $redirect = $_SESSION['redirect_after_login'] ?? '/../index.php';
            unset($_SESSION['redirect_after_login']);
            
            header("Location: $redirect");
            exit;
        } else {
            $error = 'Неверное имя пользователя или пароль';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - СпортПрокат</title>
    <link rel="stylesheet" href="/../css/style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Вход в аккаунт</h2>
        
        <?php if(isset($login_message)): ?>
            <div class="info-message">
                <?php echo htmlspecialchars($login_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" class="auth-form">
            <div class="form-group">
                <label for="username">Имя пользователя или Email:</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Войти</button>
        </form>
        
        <div class="auth-links">
            <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
            <p><a href="forgot.php">Забыли пароль?</a></p>
        </div>
    </div>
</body>
</html>
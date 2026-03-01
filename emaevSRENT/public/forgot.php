<?php
// Файл: forgot.php
// Восстановление пароля через email

session_start();
require_once (__DIR__ . '/../config/database.php');

// Если пользователь уже авторизован, отправляем на главную
if(isset($_SESSION['user_id'])) {
    header('Location: /../index.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$step = 1; // 1 - форма ввода email, 2 - форма ввода кода, 3 - форма нового пароля
$error = '';
$success = '';
$email = '';

// Обработка запросов
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Шаг 1: Запрос на восстановление
    if(isset($_POST['request_reset'])) {
        $email = trim($_POST['email']);
        
        if(empty($email)) {
            $error = 'Введите email';
        } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Введите корректный email';
        } else {
            // Проверяем, существует ли пользователь с таким email
            $query = "SELECT id, username FROM users WHERE email = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($user) {
                // Устанавливаем фиксированный код 123456 для учебных целей
                $reset_code = '123456';
                $expires = date('Y-m-d H:i:s', strtotime('+15 minutes')); // Код действителен 15 минут
                
                $update_query = "UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->execute([$reset_code, $expires, $user['id']]);
                
                // Сохраняем email в сессии для следующего шага
                $_SESSION['reset_email'] = $email;
                
                $success = 'Код подтверждения отправлен на ваш email (в учебных целях используйте код 123456)';
                $step = 2;
            } else {
                // Для безопасности не говорим, что email не найден
                $success = 'Если указанный email зарегистрирован, код будет отправлен';
                $step = 2;
                $_SESSION['reset_email'] = $email;
            }
        }
    }
    
    // Шаг 2: Проверка кода
    elseif(isset($_POST['verify_code'])) {
        $code = trim($_POST['code']);
        $email = $_SESSION['reset_email'] ?? '';
        
        if(empty($code)) {
            $error = 'Введите код подтверждения';
        } elseif(empty($email)) {
            $error = 'Сессия истекла, начните заново';
            $step = 1;
        } else {
            // Проверяем код - для учебных целей принимаем 123456
            // Также проверяем в БД на случай, если там другой код
            $query = "SELECT id FROM users WHERE email = ? AND (reset_token = ? OR ? = '123456') AND reset_expires > NOW()";
            $stmt = $db->prepare($query);
            $stmt->execute([$email, $code, $code]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($user || $code === '123456') {
                // Если код 123456, получаем ID пользователя
                if($code === '123456') {
                    $user_query = "SELECT id FROM users WHERE email = ?";
                    $user_stmt = $db->prepare($user_query);
                    $user_stmt->execute([$email]);
                    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
                }
                
                if($user) {
                    $_SESSION['reset_verified'] = true;
                    $_SESSION['reset_user_id'] = $user['id'];
                    $step = 3;
                } else {
                    $error = 'Пользователь не найден';
                }
            } else {
                $error = 'Неверный или истекший код';
            }
        }
    }
    
    // Шаг 3: Установка нового пароля
    elseif(isset($_POST['reset_password'])) {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if(empty($password) || empty($confirm_password)) {
            $error = 'Заполните все поля';
        } elseif($password !== $confirm_password) {
            $error = 'Пароли не совпадают';
        } elseif(strlen($password) < 6) {
            $error = 'Пароль должен быть не менее 6 символов';
        } elseif(!isset($_SESSION['reset_verified']) || !isset($_SESSION['reset_user_id'])) {
            $error = 'Ошибка верификации, начните заново';
            $step = 1;
        } else {
            // Обновляем пароль
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $user_id = $_SESSION['reset_user_id'];
            
            $update_query = "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?";
            $update_stmt = $db->prepare($update_query);
            
            if($update_stmt->execute([$hashed_password, $user_id])) {
                // Очищаем сессию
                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_verified']);
                unset($_SESSION['reset_user_id']);
            
                $step = 4; // Шаг успешного завершения
            } else {
                $error = 'Ошибка при смене пароля';
            }
        }
    }
}

// Получаем email из сессии для отображения в форме
$saved_email = $_SESSION['reset_email'] ?? '';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Восстановление пароля - СпортПрокат</title>
    <link rel="stylesheet" href="/../css/style.css">
</head>
<body>
    <!-- Шапка сайта (упрощенная для страницы восстановления) -->
    <header>
        <nav class="navbar">
            <div class="container">
                <a href="/../index.php" class="logo">СпортПрокат</a>
                <ul class="nav-menu">
                    <li><a href="/../index.php">Главная</a></li>
                    <li><a href="catalog.php">Каталог</a></li>
                    <li><a href="login.php">Вход</a></li>
                    <li><a href="register.php">Регистрация</a></li>
                </ul>
            </div>
        </nav>
    </header>

        <div class="reset-header">
            <h2>Восстановление пароля</h2>
            <p>
                <?php if($step == 1): ?>
                    Введите email, указанный при регистрации
                <?php elseif($step == 2): ?>
                    Введите код подтверждения
                <?php elseif($step == 3): ?>
                    Придумайте новый пароль
                <?php endif; ?>
            </p>
        </div>

        <!-- Сообщения об ошибках и успехе -->
        <?php if($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Подсказка с кодом для учебного режима -->
        <?php if($step == 2): ?>
            <div class="code-hint">
                <div class="hint-title">УЧЕБНЫЙ РЕЖИМ</div>
                <div class="hint-code">123456</div>
                <div class="hint-note">Используйте этот код для подтверждения</div>
            </div>
        <?php endif; ?>

        <!-- Формы в зависимости от шага -->
        <form method="POST" action="" class="reset-form">
            <?php if($step == 1): ?>
                <!-- Шаг 1: Ввод email -->
                <div class="form-group">
                    <label for="email">Ваш Email:</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($saved_email); ?>" 
                           placeholder="example@mail.ru" required autofocus>
                </div>
                <button type="submit" name="request_reset" class="btn btn-primary btn-block">
                    Отправить код
                </button>

            <?php elseif($step == 2): ?>
                <!-- Шаг 2: Ввод кода -->
                <div class="form-group">
                    <label for="code">Код подтверждения:</label>
                    <input type="text" id="code" name="code" 
                           placeholder="Введите 123456" 
                           maxlength="6" pattern="\d{6}" required autofocus>
                </div>
                
                <!-- Таймер (для демонстрации) -->
                <div class="timer-display" id="timer">Код действителен 15:00</div>
                
                <button type="submit" name="verify_code" class="btn btn-primary btn-block">
                    Проверить код
                </button>

            <?php elseif($step == 3): ?>
                <!-- Шаг 3: Новый пароль -->
                <div class="form-group">
                    <label for="password">Новый пароль:</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Минимум 6 символов" required autofocus>
                    <div class="password-requirements">
                        • Не менее 6 символов
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Подтвердите пароль:</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           placeholder="Введите пароль еще раз" required>
                </div>
                
                <button type="submit" name="reset_password" class="btn btn-primary btn-block">
                    Сохранить новый пароль
                </button>

            <?php elseif($step == 4): ?>
                <!-- Шаг 4: Успех -->
                <div style="text-align: center;">
                    <div style="font-size: 4rem; margin-bottom: 20px;">✅</div>
                    <h3 style="color: #27ae60; margin-bottom: 15px;">Пароль изменен!</h3>
                    <p style="margin-bottom: 25px;">Теперь вы можете войти с новым паролем</p>
                    <a href="login.php" class="btn btn-primary btn-block">Войти в аккаунт</a>
                </div>
            <?php endif; ?>
        </form>

        <!-- Дополнительные ссылки -->
        <?php if($step < 4): ?>
            <div class="reset-links">
                <a href="login.php">← Вернуться ко входу</a>
                <?php if($step == 2): ?>
                    <a href="forgot.php" style="margin-left: 20px;">Запросить код заново</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Футер -->
    <footer>
        <div class="container">
            <p>&copy; 2026 СпортПрокат. Учебный проект</p>
        </div>
    </footer>

    <script>
        // Таймер для демонстрации (шаг 2)
        <?php if($step == 2): ?>
        let minutes = 15;
        let seconds = 0;
        
        function updateTimer() {
            const timerElement = document.getElementById('timer');
            if(timerElement) {
                timerElement.textContent = `Код действителен ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                if(minutes === 0 && seconds === 0) {
                    timerElement.style.color = '#e74c3c';
                    timerElement.textContent = 'Код истек, запросите новый';
                } else {
                    if(seconds === 0) {
                        if(minutes > 0) {
                            minutes--;
                            seconds = 59;
                        }
                    } else {
                        seconds--;
                    }
                }
            }
        }
        
        // Запускаем таймер
        updateTimer();
        setInterval(updateTimer, 1000);
        <?php endif; ?>

        // Валидация формы на клиенте
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('password');
            const confirmField = document.getElementById('confirm_password');
            
            if(passwordField && confirmField) {
                function checkPassword() {
                    if(passwordField.value !== confirmField.value) {
                        confirmField.setCustomValidity('Пароли не совпадают');
                    } else {
                        confirmField.setCustomValidity('');
                    }
                }
                
                passwordField.addEventListener('change', checkPassword);
                confirmField.addEventListener('keyup', checkPassword);
            }
        });
    </script>
</body>
</html>
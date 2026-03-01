<?php
// Файл: checkout.php
// Упрощенное оформление заказа с сообщением об успехе

session_start();
require_once (__DIR__ . '/../config/database.php');

// Проверка авторизации
if(!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Получаем данные из корзины
$cart_query = "SELECT c.*, p.name, p.price_per_day, p.id as product_id
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = ?";
$cart_stmt = $db->prepare($cart_query);
$cart_stmt->execute([$_SESSION['user_id']]);
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

if(empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

// Обработка оформления заказа
$order_success = false;
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Простая валидация дат
    $rental_start = $_POST['start_date'] ?? '';
    $rental_end = $_POST['end_date'] ?? '';
    
    if(empty($rental_start) || empty($rental_end)) {
        $error = 'Выберите даты аренды';
    } elseif(strtotime($rental_start) < strtotime(date('Y-m-d'))) {
        $error = 'Дата начала не может быть в прошлом';
    } elseif(strtotime($rental_end) <= strtotime($rental_start)) {
        $error = 'Дата окончания должна быть позже даты начала';
    } else {
        // В учебных целях просто показываем сообщение об успехе
        $order_success = true;
        
        // Очищаем корзину после "оформления" заказа
        $clear_query = "DELETE FROM cart WHERE user_id = ?";
        $clear_stmt = $db->prepare($clear_query);
        $clear_stmt->execute([$_SESSION['user_id']]);
    }
}

// Подсчет итогов
$total = 0;
foreach($cart_items as $item) {
    $total += $item['price_per_day'] * $item['rental_days'];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформление заказа - СпортПрокат</title>
    <link rel="stylesheet" href="/../css/style.css">
</head>
<body>
    <!-- Шапка сайта -->
    <header>
        <nav class="navbar">
            <div class="container">
                <a href="/../index.php" class="logo">СпортПрокат</a>
                <ul class="nav-menu">
                    <li><a href="/../index.php">Главная</a></li>
                    <li><a href="catalog.php">Каталог</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="cart.php">Корзина</a></li>
                        <li><a href="/../routes\logout.php">Выход</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Вход</a></li>
                        <li><a href="register.php">Регистрация</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>

    <div class="container">
        <?php if($order_success): ?>
            <!-- Сообщение об успешном оформлении заказа -->
            <div class="order-success">
                <h1>Заказ успешно оформлен!</h1>
                <p>Спасибо за аренду в нашем прокате!</p>
                <div class="order-details">
                    <h3>Детали заказа:</h3>
                    <p>Номер заказа: #<?php echo rand(1000, 9999); ?></p>
                    <p>Дата оформления: <?php echo date('d.m.Y H:i'); ?></p>
                    <p>Сумма заказа: <?php echo number_format($total, 0, '', ' '); ?> ₽</p>
                    <p>Период аренды: с <?php echo date('d.m.Y', strtotime($_POST['start_date'])); ?> 
                       по <?php echo date('d.m.Y', strtotime($_POST['end_date'])); ?></p>
                </div>
                
                <div class="order-items-success">
                    <h3>Арендованные товары:</h3>
                    <div class="success-items-grid">
                        <?php foreach($cart_items as $item): ?>
                            <div class="success-item">
                                     <?php echo htmlspecialchars($item['name']); ?>
                                <div class="item-info">
                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                    <p><?php echo $item['rental_days']; ?> дн. × <?php echo number_format($item['price_per_day'], 0, '', ' '); ?> ₽</p>
                                    <p class="item-total">= <?php echo number_format($item['price_per_day'] * $item['rental_days'], 0, '', ' '); ?> ₽</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="success-actions">
                    <a href="catalog.php" class="btn">Продолжить покупки</a>
                    <a href="/../index.php" class="btn btn-secondary">На главную</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Форма оформления заказа -->
            <div class="checkout-form">
                <h2>Оформление заказа</h2>
                
                <?php if($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <div class="checkout-content">
                    <!-- Список товаров -->
                    <div class="checkout-items">
                        <h3>Ваш заказ:</h3>
                        <table class="checkout-table">
                            <?php foreach($cart_items as $item): ?>
                                <tr>
                                    <td class="item-name">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </td>
                                    <td><?php echo $item['rental_days']; ?> дн.</td>
                                    <td><?php echo number_format($item['price_per_day'], 0, '', ' '); ?> ₽/день</td>
                                    <td class="item-subtotal">
                                        <?php echo number_format($item['price_per_day'] * $item['rental_days'], 0, '', ' '); ?> ₽
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td colspan="3"><strong>Итого:</strong></td>
                                <td><strong><?php echo number_format($total, 0, '', ' '); ?> ₽</strong></td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Форма с датами -->
                    <form method="POST" action="" class="rental-form">
                        <h3>Выберите даты аренды:</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="start_date">Дата начала:</label>
                                <input type="date" id="start_date" name="start_date" 
                                       min="<?php echo date('Y-m-d'); ?>" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="end_date">Дата окончания:</label>
                                <input type="date" id="end_date" name="end_date" 
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" 
                                       value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                            </div>
                        </div>
                        
                        <div class="rental-info">
                            <p>📅 Минимальный срок аренды - 1 день</p>
                            <p>💰 Оплата при получении</p>
                            <p>🆔 При получении необходимо предъявить паспорт</p>
                        </div>
                        
                        <div class="form-actions">
                            <a href="cart.php" class="btn btn-secondary">Вернуться в корзину</a>
                            <button type="submit" class="btn btn-primary">Подтвердить заказ</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Футер -->
    <footer>
        <div class="container">
            <p>&copy; 2026 СпортПрокат. Учебный проект</p>
        </div>
    </footer>

    <style>
        /* Временные стили для страницы успеха (потом перенеси в style.css) */

    </style>
</body>
</html>
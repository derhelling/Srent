<?php
// Файл: cart.php
// Управление корзиной пользователя

session_start();

// ПРОВЕРКА АВТОРИЗАЦИИ
if(!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'cart.php';
    $_SESSION['login_message'] = 'Необходимо войти в систему, чтобы работать с корзиной';
    header('Location: login.php');
    exit;
}

require_once (__DIR__ . '/../config/database.php');

$database = new Database();
$db = $database->getConnection();

// ИСПРАВЛЕННАЯ ОБРАБОТКА ДОБАВЛЕНИЯ ТОВАРА В КОРЗИНУ
if(isset($_POST['action']) && $_POST['action'] == 'add_to_cart') {
    
    $product_id = (int)($_POST['product_id'] ?? 0);
    $days = (int)($_POST['days'] ?? 1);
    $user_id = $_SESSION['user_id'];
    
    // Валидация
    if($product_id <= 0) {
        $_SESSION['cart_error'] = 'Ошибка: не выбран товар';
        header('Location: catalog.php');
        exit;
    }
    
    if($days < 1 || $days > 30) {
        $_SESSION['cart_error'] = 'Количество дней должно быть от 1 до 30';
        header('Location: catalog.php');
        exit;
    }
    
    try {
        // Проверяем существование товара
        $check_product = "SELECT id, name, stock FROM products WHERE id = ? AND is_available = 1";
        $check_stmt = $db->prepare($check_product);
        $check_stmt->execute([$product_id]);
        $product = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$product) {
            $_SESSION['cart_error'] = 'Товар не найден или недоступен';
            header('Location: catalog.php');
            exit;
        }
        
        // Проверяем, есть ли уже такой товар в корзине
        $check_query = "SELECT id, quantity, rental_days FROM cart WHERE user_id = ? AND product_id = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$user_id, $product_id]);
        $existing_item = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if($existing_item) {
            // Обновляем существующий товар
            $new_quantity = $existing_item['quantity'] + 1;
            $update_query = "UPDATE cart SET quantity = ?, rental_days = ? WHERE id = ?";
            $update_stmt = $db->prepare($update_query);
            
            if($update_stmt->execute([$new_quantity, $days, $existing_item['id']])) {
                $_SESSION['cart_success'] = 'Количество товара обновлено в корзине';
            } else {
                $_SESSION['cart_error'] = 'Ошибка при обновлении корзины';
            }
        } else {
            // Добавляем новый товар
            $insert_query = "INSERT INTO cart (user_id, product_id, quantity, rental_days) VALUES (?, ?, 1, ?)";
            $insert_stmt = $db->prepare($insert_query);
            
            if($insert_stmt->execute([$user_id, $product_id, $days])) {
                $_SESSION['cart_success'] = 'Товар добавлен в корзину';
            } else {
                $_SESSION['cart_error'] = 'Ошибка при добавлении в корзину';
            }
        }
    } catch(PDOException $e) {
        $_SESSION['cart_error'] = 'Ошибка базы данных: ' . $e->getMessage();
    }
    
    // Перенаправляем обратно в каталог или в корзину?
    // Я перенаправлю в корзину, чтобы пользователь видел результат
    header('Location: cart.php');
    exit;
}

// Удаление товара из корзины
if(isset($_GET['remove'])) {
    $cart_id = (int)$_GET['remove'];
    $user_id = $_SESSION['user_id'];
    
    $delete_query = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $delete_stmt = $db->prepare($delete_query);
    
    if($delete_stmt->execute([$cart_id, $user_id])) {
        $_SESSION['cart_success'] = 'Товар удален из корзины';
    }
    
    header('Location: cart.php');
    exit;
}

// Очистка всей корзины
if(isset($_GET['clear'])) {
    $clear_query = "DELETE FROM cart WHERE user_id = ?";
    $clear_stmt = $db->prepare($clear_query);
    
    if($clear_stmt->execute([$_SESSION['user_id']])) {
        $_SESSION['cart_success'] = 'Корзина очищена';
    }
    
    header('Location: cart.php');
    exit;
}

// Обновление количества дней аренды
if(isset($_POST['update_days'])) {
    $cart_id = (int)$_POST['cart_id'];
    $new_days = (int)$_POST['days'];
    $user_id = $_SESSION['user_id'];
    
    if($new_days >= 1 && $new_days <= 30) {
        $update_query = "UPDATE cart SET rental_days = ? WHERE id = ? AND user_id = ?";
        $update_stmt = $db->prepare($update_query);
        
        if($update_stmt->execute([$new_days, $cart_id, $user_id])) {
            $_SESSION['cart_success'] = 'Количество дней обновлено';
        }
    }
    
    header('Location: cart.php');
    exit;
}

// ПОЛУЧАЕМ СОДЕРЖИМОЕ КОРЗИНЫ
$cart_query = "SELECT c.*, p.name, p.price_per_day, p.stock, p.description 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = ? 
               ORDER BY c.added_at DESC";
$cart_stmt = $db->prepare($cart_query);
$cart_stmt->execute([$_SESSION['user_id']]);
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

// Подсчет итоговой суммы и количества товаров
$total = 0;
$total_items = 0;
foreach($cart_items as $item) {
    $item_total = $item['price_per_day'] * $item['rental_days'];
    $total += $item_total;
    $total_items += $item['quantity'];
}

// Получаем сообщения из сессии
$success_message = $_SESSION['cart_success'] ?? '';
$error_message = $_SESSION['cart_error'] ?? '';

// Очищаем сообщения после получения
unset($_SESSION['cart_success']);
unset($_SESSION['cart_error']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина - СпортПрокат</title>
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
                        <li><a href="cart.php" class="active">Корзина <?php echo $total_items > 0 ? "($total_items)" : ''; ?></a></li>
                        <li><a href="/../routes\logout.php">Выход (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Вход</a></li>
                        <li><a href="register.php">Регистрация</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>

    <div class="cart-container">
        <h2>Корзина</h2>
        
        <!-- Сообщения для пользователя -->
        <?php if($success_message): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if($error_message): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if(count($cart_items) > 0): ?>
            <div class="cart-content">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Товар</th>
                            <th>Цена/день</th>
                            <th>Дней аренды</th>
                            <th>Стоимость</th>
                            <th>Действие</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($cart_items as $item): ?>
                            <tr>
                                <td class="product-info">
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                        <br>
                                        <small><?php echo htmlspecialchars(mb_substr($item['description'], 0, 50)); ?>...</small>
                                    </div>
                                </td>
                                <td class="price"><?php echo number_format($item['price_per_day'], 0, '', ' '); ?> ₽</td>
                                <td>
                                    <form method="POST" action="" class="update-days-form">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <input type="number" name="days" value="<?php echo $item['rental_days']; ?>" 
                                               min="1" max="30" class="days-input-small">
                                        <button type="submit" name="update_days" class="btn-update">Обновить</button>
                                    </form>
                                </td>
                                <td class="total-price">
                                    <strong><?php echo number_format($item['price_per_day'] * $item['rental_days'], 0, '', ' '); ?> ₽</strong>
                                </td>
                                <td>
                                    <a href="?remove=<?php echo $item['id']; ?>" 
                                       class="btn-remove" 
                                       onclick="return confirm('Удалить товар из корзины?')">Удалить</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="total-label"><strong>Итого:</strong></td>
                            <td class="grand-total"><strong><?php echo number_format($total, 0, '', ' '); ?> ₽</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="cart-actions">
                    <a href="catalog.php" class="btn btn-secondary">Продолжить покупки</a>
                    <a href="?clear=1" class="btn btn-danger" 
                       onclick="return confirm('Очистить всю корзину?')">Очистить корзину</a>
                    <a href="checkout.php" class="btn btn-primary">Оформить заказ</a>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <p>Ваша корзина пуста</p>
                <p>Перейдите в <a href="catalog.php">каталог</a>, чтобы выбрать товары для аренды</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Футер -->
    <footer>
        <div class="container">
            <p>&copy; 2026 СпортПрокат. Учебный проект</p>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
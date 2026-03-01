<?php
// Файл: catalog.php
// Каталог товаров с фильтрацией

session_start();
require_once (__DIR__ . '/../config/database.php');

$database = new Database();
$db = $database->getConnection();

// ПОЛУЧАЕМ КАТЕГОРИИ ДЛЯ ФИЛЬТРА
$cat_query = "SELECT * FROM categories";
$cat_stmt = $db->prepare($cat_query);
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// ФИЛЬТРАЦИЯ ТОВАРОВ
$where = ["p.is_available = 1"];
$params = [];

if(isset($_GET['category']) && !empty($_GET['category'])) {
    $where[] = "p.category_id = ?";
    $params[] = $_GET['category'];
}

if(isset($_GET['search']) && !empty($_GET['search'])) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%{$_GET['search']}%";
    $params[] = "%{$_GET['search']}%";
}

// Формируем запрос
$where_clause = implode(" AND ", $where);
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE $where_clause 
          ORDER BY p.name";

$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Сохраняем текущий URL для редиректа после входа
if(!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог - СпортПрокат</title>
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
                        <li><a href="/../routes\logout.php">Выход (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Вход</a></li>
                        <li><a href="register.php">Регистрация</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>
    
    <div class="catalog-container">
        <aside class="filters">
            <h3>Фильтры</h3>
            <form method="GET" action="">
                <div class="filter-group">
                    <label>Поиск:</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                
                <div class="filter-group">
                    <label>Категория:</label>
                    <select name="category">
                        <option value="">Все категории</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn">Применить</button>
            </form>
        </aside>
        
        <main class="products-list">
            <h2>Каталог товаров</h2>
            
            <!-- Сообщение для неавторизованных пользователей -->
            <?php if(!isset($_SESSION['user_id'])): ?>
                <div class="info-message">
                    <p>🔐 Чтобы арендовать товар, пожалуйста, <a href="login.php">войдите</a> или <a href="register.php">зарегистрируйтесь</a>.</p>
                </div>
            <?php endif; ?>
            
            <div class="products-grid">
                <?php foreach($products as $product): ?>
                    <div class="product-card">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                        <p class="description"><?php echo htmlspecialchars(mb_substr($product['description'], 0, 100)); ?>...</p>
                        <p class="price"><?php echo htmlspecialchars($product['price_per_day']); ?> ₽/день</p>
                        <p class="stock">В наличии: <?php echo htmlspecialchars($product['stock']); ?></p>
                        
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <!-- ФОРМА ДЛЯ АВТОРИЗОВАННЫХ ПОЛЬЗОВАТЕЛЕЙ - ИСПРАВЛЕНО -->
                            <form method="POST" action="cart.php" class="add-to-cart-form">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="action" value="add_to_cart">
                                <div class="rental-controls">
                                    <input type="number" name="days" value="1" min="1" max="30" class="days-input" required>
                                    <button type="submit" class="btn-small">В корзину</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <!-- Кнопка для неавторизованных пользователей -->
                            <a href="login.php" class="btn-small login-required">
                                🔑 Войдите для аренды
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <?php if(count($products) == 0): ?>
                    <p class="no-products">Товары не найдены</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Футер -->
    <footer>
        <div class="container">
            <p>&copy; 2026 СпортПрокат. Все права защищены.</p>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
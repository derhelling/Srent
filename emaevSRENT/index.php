<?php
// Файл: index.php
// Главная страница сайта проката спортивного инвентаря

session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Получаем популярные товары для главной страницы
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.is_available = 1 
          ORDER BY p.id DESC LIMIT 6";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>СпортПрокат - Аренда спортивного инвентаря</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Шапка сайта с навигацией -->
    <header>
        <nav class="navbar">
            <div class="container">
                <a href="index.php" class="logo">СпортПрокат</a>
                <ul class="nav-menu">
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="public\catalog.php">Каталог</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="public\cart.php">Корзина</a></li>
                        <li><a href="routes\logout.php">Выход</a></li>
                    <?php else: ?>
                        <li><a href="public\login.php">Вход</a></li>
                        <li><a href="public\register.php">Регистрация</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Блок с приветствием -->
    <section class="hero">
        <div class="container">
            <h1>Прокат спортивного инвентаря</h1>
            <p>Все для активного отдыха по доступным ценам</p>
            <a href="public\catalog.php" class="btn">Посмотреть каталог</a>
        </div>
    </section>

    <!-- Популярные товары -->
    <section class="featured-products">
        <div class="container">
            <h2>Популярные товары</h2>
            <div class="products-grid">
                <?php foreach($products as $product): ?>
                    <div class="product-card">
                        <h3><?php echo $product['name']; ?></h3>
                        <p class="category"><?php echo $product['category_name']; ?></p>
                        <p class="price"><?php echo $product['price_per_day']; ?> ₽/день</p>
                        <a href="public\catalog.php?add=<?php echo $product['id']; ?>" class="btn-small">В корзину</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Футер сайта -->
    <footer>
        <div class="container">
            <p>&copy; 2026 СпортПрокат. Все права защищены.</p>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
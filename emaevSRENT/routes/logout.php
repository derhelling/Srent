<?php
// Начинаем сессию
session_start();
// Удаляем все переменные сессии
session_unset();
// Уничтожаем сессию
session_destroy();
// Перенаправляем на страницу входа
header("Location: /../index.php");
?>

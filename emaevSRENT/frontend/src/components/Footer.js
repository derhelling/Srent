/**
 * Файл: frontend/src/components/Footer.js
 * Компонент подвала сайта
 * 
 * Содержит копирайт и дополнительную информацию
 * 
 * @author Студент
 * @version 1.0
 */

/**
 * Компонент Footer - подвал сайта
 * 
 * Простой компонент без состояния (stateless)
 */
function Footer() {
    // Получаем текущий год для копирайта
    const currentYear = new Date().getFullYear();
    
    return (
        <footer>
            <div className="container">
                {/* Информация о копирайте */}
                <p>
                    &copy; {currentYear} СпортПрокат. 
                    Учебный проект на React + Laravel.
                </p>
            </div>
        </footer>
    );
}

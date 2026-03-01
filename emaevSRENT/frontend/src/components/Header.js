/**
 * Файл: frontend/src/components/Header.js
 * Компонент шапки сайта
 * 
 * Содержит логотип, навигационное меню и
 * информацию о пользователе
 * 
 * @author Студент
 * @version 1.0
 */

/**
 * Компонент Header - шапка сайта
 * 
 * @param {object} props - Свойства компонента
 * @param {object} props.user - Данные авторизованного пользователя
 * @param {string} props.currentPage - Текущая активная страница
 * @param {function} props.onNavigate - Callback навигации
 * @param {function} props.onLogout - Callback выхода из системы
 * @param {number} props.cartCount - Количество товаров в корзине
 */
function Header({ user, currentPage, onNavigate, onLogout, cartCount = 0 }) {
    
    /**
     * Обработчик клика по ссылке навигации
     * Предотвращает стандартное поведение и вызывает onNavigate
     */
    const handleNavClick = (e, page) => {
        e.preventDefault();
        onNavigate(page);
    };
    
    /**
     * Обработчик выхода из системы
     */
    const handleLogout = async (e) => {
        e.preventDefault();
        if (onLogout) {
            await onLogout();
        }
    };
    
    return (
        <header>
            {/* Навигационная панель */}
            <nav className="navbar">
                <div className="container">
                    {/* Логотип - ссылка на главную */}
                    <a 
                        href="#" 
                        className="logo"
                        onClick={(e) => handleNavClick(e, 'home')}
                    >
                        СпортПрокат
                    </a>
                    
                    {/* Меню навигации */}
                    <ul className="nav-menu">
                        {/* Главная - всегда видна */}
                        <li>
                            <a 
                                href="#"
                                className={currentPage === 'home' ? 'active' : ''}
                                onClick={(e) => handleNavClick(e, 'home')}
                            >
                                Главная
                            </a>
                        </li>
                        
                        {/* Каталог - всегда виден */}
                        <li>
                            <a 
                                href="#"
                                className={currentPage === 'catalog' ? 'active' : ''}
                                onClick={(e) => handleNavClick(e, 'catalog')}
                            >
                                Каталог
                            </a>
                        </li>
                        
                        {/* Пункты меню для авторизованных пользователей */}
                        {user ? (
                            <>
                                {/* Корзина с счётчиком */}
                                <li>
                                    <a 
                                        href="#"
                                        className={currentPage === 'cart' ? 'active' : ''}
                                        onClick={(e) => handleNavClick(e, 'cart')}
                                    >
                                        Корзина
                                        {cartCount > 0 && (
                                            <span className="cart-badge">
                                                ({cartCount})
                                            </span>
                                        )}
                                    </a>
                                </li>
                                
                                {/* Кнопка выхода с именем пользователя */}
                                <li>
                                    <a 
                                        href="#"
                                        onClick={handleLogout}
                                    >
                                        Выход ({user.username})
                                    </a>
                                </li>
                            </>
                        ) : (
                            <>
                                {/* Пункты меню для гостей */}
                                <li>
                                    <a 
                                        href="#"
                                        className={currentPage === 'login' ? 'active' : ''}
                                        onClick={(e) => handleNavClick(e, 'login')}
                                    >
                                        Вход
                                    </a>
                                </li>
                                
                                <li>
                                    <a 
                                        href="#"
                                        className={currentPage === 'register' ? 'active' : ''}
                                        onClick={(e) => handleNavClick(e, 'register')}
                                    >
                                        Регистрация
                                    </a>
                                </li>
                            </>
                        )}
                    </ul>
                </div>
            </nav>
        </header>
    );
}

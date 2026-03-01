/**
 * Файл: frontend/src/App.js
 * Главный компонент React приложения
 * 
 * Управляет состоянием всего приложения:
 * - Аутентификация пользователя
 * - Навигация между страницами
 * - Корзина покупок
 * - Уведомления
 * 
 * @author Студент
 * @version 1.0
 */

/**
 * Компонент App - корневой компонент приложения
 * 
 * Использует React Hooks для управления состоянием
 */
function App() {
    // ============================================
    // СОСТОЯНИЕ ПРИЛОЖЕНИЯ
    // ============================================
    
    // Текущая страница (навигация)
    const [currentPage, setCurrentPage] = React.useState('home');
    
    // Данные авторизованного пользователя (null если не авторизован)
    const [user, setUser] = React.useState(null);
    
    // Содержимое корзины
    const [cartItems, setCartItems] = React.useState([]);
    
    // Общая сумма корзины
    const [cartTotal, setCartTotal] = React.useState(0);
    
    // Уведомление
    const [notification, setNotification] = React.useState(null);
    
    // Загрузка при инициализации
    const [isInitializing, setIsInitializing] = React.useState(true);
    
    // ============================================
    // ИНИЦИАЛИЗАЦИЯ ПРИЛОЖЕНИЯ
    // ============================================
    
    /**
     * При загрузке приложения проверяем авторизацию
     */
    React.useEffect(() => {
        initializeApp();
    }, []);
    
    /**
     * Инициализация приложения
     * - Проверка авторизации
     * - Загрузка корзины если авторизован
     */
    const initializeApp = async () => {
        try {
            // Пробуем получить текущего пользователя
            const userData = await api.getCurrentUser();
            
            if (userData) {
                setUser(userData);
                // Загружаем корзину
                await loadCart();
            }
        } catch (error) {
            console.log('Пользователь не авторизован');
        } finally {
            setIsInitializing(false);
        }
    };
    
    // ============================================
    // РАБОТА С КОРЗИНОЙ
    // ============================================
    
    /**
     * Загрузка содержимого корзины
     */
    const loadCart = async () => {
        try {
            const response = await api.getCart();
            setCartItems(response.data.items || []);
            setCartTotal(response.data.total || 0);
        } catch (error) {
            console.error('Ошибка загрузки корзины:', error);
        }
    };
    
    /**
     * Добавление товара в корзину
     */
    const addToCart = async (productId, days) => {
        try {
            const response = await api.addToCart(productId, days);
            setCartItems(response.data.items || []);
            setCartTotal(response.data.total || 0);
            return response;
        } catch (error) {
            throw error;
        }
    };
    
    /**
     * Обновление количества дней в корзине
     */
    const updateCart = async (cartId, days) => {
        try {
            const response = await api.updateCartItem(cartId, days);
            setCartItems(response.data.items || []);
            setCartTotal(response.data.total || 0);
            return response;
        } catch (error) {
            throw error;
        }
    };
    
    /**
     * Удаление товара из корзины
     */
    const removeFromCart = async (cartId) => {
        try {
            const response = await api.removeFromCart(cartId);
            setCartItems(response.data.items || []);
            setCartTotal(response.data.total || 0);
            return response;
        } catch (error) {
            throw error;
        }
    };
    
    /**
     * Очистка корзины
     */
    const clearCart = async () => {
        try {
            await api.clearCart();
            setCartItems([]);
            setCartTotal(0);
        } catch (error) {
            throw error;
        }
    };
    
    // ============================================
    // АУТЕНТИФИКАЦИЯ
    // ============================================
    
    /**
     * Обработчик успешного входа
     */
    const handleLogin = async (userData) => {
        setUser(userData);
        await loadCart();
    };
    
    /**
     * Обработчик выхода
     */
    const handleLogout = async () => {
        try {
            await api.logout();
            setUser(null);
            setCartItems([]);
            setCartTotal(0);
            setCurrentPage('home');
            showNotification('Вы вышли из системы', 'info');
        } catch (error) {
            console.error('Ошибка выхода:', error);
        }
    };
    
    /**
     * Обработчик завершения оформления заказа
     */
    const handleOrderComplete = () => {
        setCartItems([]);
        setCartTotal(0);
    };
    
    // ============================================
    // УВЕДОМЛЕНИЯ
    // ============================================
    
    /**
     * Показать уведомление
     */
    const showNotification = (message, type = 'info') => {
        setNotification({ message, type });
    };
    
    /**
     * Скрыть уведомление
     */
    const hideNotification = () => {
        setNotification(null);
    };
    
    // ============================================
    // НАВИГАЦИЯ
    // ============================================
    
    /**
     * Переход на другую страницу
     */
    const navigate = (page) => {
        // Для страниц требующих авторизации
        const authRequiredPages = ['cart', 'checkout'];
        
        if (authRequiredPages.includes(page) && !user) {
            showNotification('Необходима авторизация', 'info');
            setCurrentPage('login');
            return;
        }
        
        setCurrentPage(page);
        // Прокручиваем страницу наверх
        window.scrollTo(0, 0);
    };
    
    // ============================================
    // РЕНДЕРИНГ ТЕКУЩЕЙ СТРАНИЦЫ
    // ============================================
    
    /**
     * Рендер страницы в зависимости от currentPage
     */
    const renderPage = () => {
        switch (currentPage) {
            case 'home':
                return (
                    <HomePage 
                        isLoggedIn={!!user}
                        onNavigate={navigate}
                        onAddToCart={addToCart}
                        showNotification={showNotification}
                    />
                );
                
            case 'catalog':
                return (
                    <CatalogPage 
                        isLoggedIn={!!user}
                        onNavigate={navigate}
                        onAddToCart={addToCart}
                        showNotification={showNotification}
                    />
                );
                
            case 'cart':
                return (
                    <CartPage 
                        cartItems={cartItems}
                        cartTotal={cartTotal}
                        onNavigate={navigate}
                        onUpdateCart={updateCart}
                        onRemoveFromCart={removeFromCart}
                        onClearCart={clearCart}
                        showNotification={showNotification}
                    />
                );
                
            case 'checkout':
                return (
                    <CheckoutPage 
                        cartItems={cartItems}
                        cartTotal={cartTotal}
                        onNavigate={navigate}
                        onOrderComplete={handleOrderComplete}
                        showNotification={showNotification}
                    />
                );
                
            case 'login':
                return (
                    <LoginPage 
                        onNavigate={navigate}
                        onLogin={handleLogin}
                        showNotification={showNotification}
                    />
                );
                
            case 'register':
                return (
                    <RegisterPage 
                        onNavigate={navigate}
                        showNotification={showNotification}
                    />
                );
                
            case 'forgot':
                return (
                    <ForgotPage 
                        onNavigate={navigate}
                        showNotification={showNotification}
                    />
                );
                
            default:
                return (
                    <HomePage 
                        isLoggedIn={!!user}
                        onNavigate={navigate}
                        onAddToCart={addToCart}
                        showNotification={showNotification}
                    />
                );
        }
    };
    
    // ============================================
    // РЕНДЕР ПРИЛОЖЕНИЯ
    // ============================================
    
    // Показываем загрузку при инициализации
    if (isInitializing) {
        return <Loader message="Загрузка приложения..." />;
    }
    
    return (
        <div className="app">
            {/* Уведомления */}
            {notification && (
                <Notification 
                    message={notification.message}
                    type={notification.type}
                    onClose={hideNotification}
                />
            )}
            
            {/* Шапка сайта */}
            <Header 
                user={user}
                currentPage={currentPage}
                onNavigate={navigate}
                onLogout={handleLogout}
                cartCount={cartItems.length}
            />
            
            {/* Основной контент - текущая страница */}
            <main className="main-content">
                {renderPage()}
            </main>
            
            {/* Подвал сайта */}
            <Footer />
        </div>
    );
}

// ============================================
// ИНИЦИАЛИЗАЦИЯ REACT ПРИЛОЖЕНИЯ
// ============================================

/**
 * Точка входа - рендерим App в DOM
 */
const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(<App />);

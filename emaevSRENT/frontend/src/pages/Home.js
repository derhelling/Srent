/**
 * Файл: frontend/src/pages/Home.js
 * Главная страница сайта
 * 
 * Отображает приветственный баннер и популярные товары
 * 
 * @author Студент
 * @version 1.0
 */

/**
 * Компонент HomePage - главная страница
 * 
 * @param {object} props - Свойства компонента
 * @param {boolean} props.isLoggedIn - Авторизован ли пользователь
 * @param {function} props.onNavigate - Callback навигации
 * @param {function} props.onAddToCart - Callback добавления в корзину
 * @param {function} props.showNotification - Callback для уведомлений
 */
function HomePage({ isLoggedIn, onNavigate, onAddToCart, showNotification }) {
    // Состояние для популярных товаров
    const [products, setProducts] = React.useState([]);
    
    // Состояние загрузки
    const [isLoading, setIsLoading] = React.useState(true);
    
    /**
     * Загрузка популярных товаров при монтировании компонента
     */
    React.useEffect(() => {
        loadPopularProducts();
    }, []);
    
    /**
     * Функция загрузки популярных товаров с API
     */
    const loadPopularProducts = async () => {
        try {
            setIsLoading(true);
            const response = await api.getProducts({ popular: true, limit: 6 });
            setProducts(response.data.products || []);
        } catch (error) {
            console.error('Ошибка загрузки товаров:', error);
            showNotification('Ошибка загрузки товаров', 'error');
        } finally {
            setIsLoading(false);
        }
    };
    
    /**
     * Обработчик добавления в корзину
     */
    const handleAddToCart = async (productId, days) => {
        try {
            await onAddToCart(productId, days);
            showNotification('Товар добавлен в корзину!', 'success');
        } catch (error) {
            showNotification(error.message || 'Ошибка добавления', 'error');
        }
    };
    
    /**
     * Обработчик для неавторизованных пользователей
     */
    const handleLoginRequired = () => {
        showNotification('Войдите для добавления в корзину', 'info');
        onNavigate('login');
    };
    
    return (
        <div>
            {/* Главный баннер с приветствием */}
            <section className="hero">
                <div className="container">
                    <h1>Прокат спортивного инвентаря</h1>
                    <p>Все для активного отдыха по доступным ценам</p>
                    
                    {/* Кнопка перехода в каталог */}
                    <button 
                        className="btn"
                        onClick={() => onNavigate('catalog')}
                    >
                        Посмотреть каталог
                    </button>
                </div>
            </section>
            
            {/* Секция с популярными товарами */}
            <section className="featured-products">
                <div className="container">
                    <h2>Популярные товары</h2>
                    
                    {/* Индикатор загрузки */}
                    {isLoading ? (
                        <Loader message="Загрузка товаров..." />
                    ) : (
                        /* Сетка товаров */
                        <div className="products-grid">
                            {products.map(product => (
                                <ProductCard
                                    key={product.id}
                                    product={product}
                                    isLoggedIn={isLoggedIn}
                                    onAddToCart={handleAddToCart}
                                    onLoginRequired={handleLoginRequired}
                                />
                            ))}
                            
                            {/* Сообщение если товаров нет */}
                            {products.length === 0 && (
                                <p className="no-products">
                                    Товары не найдены
                                </p>
                            )}
                        </div>
                    )}
                </div>
            </section>
        </div>
    );
}

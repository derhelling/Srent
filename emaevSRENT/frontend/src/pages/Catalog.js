/**
 * Файл: frontend/src/pages/Catalog.js
 * Страница каталога товаров
 * 
 * Отображает все товары с возможностью фильтрации
 * по категориям и поиска
 * 
 * @author Студент
 * @version 1.0
 */

/**
 * Компонент CatalogPage - страница каталога
 * 
 * @param {object} props - Свойства компонента
 * @param {boolean} props.isLoggedIn - Авторизован ли пользователь
 * @param {function} props.onNavigate - Callback навигации
 * @param {function} props.onAddToCart - Callback добавления в корзину
 * @param {function} props.showNotification - Callback для уведомлений
 */
function CatalogPage({ isLoggedIn, onNavigate, onAddToCart, showNotification }) {
    // Состояние для списка товаров
    const [products, setProducts] = React.useState([]);
    
    // Состояние для категорий
    const [categories, setCategories] = React.useState([]);
    
    // Состояние загрузки
    const [isLoading, setIsLoading] = React.useState(true);
    
    // Состояние фильтров
    const [filters, setFilters] = React.useState({
        category_id: '',
        search: ''
    });
    
    /**
     * Загрузка данных при монтировании
     */
    React.useEffect(() => {
        loadCategories();
        loadProducts();
    }, []);
    
    /**
     * Перезагрузка товаров при изменении фильтров
     */
    React.useEffect(() => {
        loadProducts();
    }, [filters.category_id]);
    
    /**
     * Загрузка категорий
     */
    const loadCategories = async () => {
        try {
            const response = await api.getCategories();
            setCategories(response.data.categories || []);
        } catch (error) {
            console.error('Ошибка загрузки категорий:', error);
        }
    };
    
    /**
     * Загрузка товаров с фильтрами
     */
    const loadProducts = async () => {
        try {
            setIsLoading(true);
            const response = await api.getProducts(filters);
            setProducts(response.data.products || []);
        } catch (error) {
            console.error('Ошибка загрузки товаров:', error);
            showNotification('Ошибка загрузки товаров', 'error');
        } finally {
            setIsLoading(false);
        }
    };
    
    /**
     * Обработчик изменения категории
     */
    const handleCategoryChange = (e) => {
        setFilters(prev => ({
            ...prev,
            category_id: e.target.value
        }));
    };
    
    /**
     * Обработчик изменения поискового запроса
     */
    const handleSearchChange = (e) => {
        setFilters(prev => ({
            ...prev,
            search: e.target.value
        }));
    };
    
    /**
     * Обработчик отправки формы фильтров
     */
    const handleFilterSubmit = (e) => {
        e.preventDefault();
        loadProducts();
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
     * Обработчик для неавторизованных
     */
    const handleLoginRequired = () => {
        showNotification('Войдите для добавления в корзину', 'info');
        onNavigate('login');
    };
    
    return (
        <div className="catalog-container">
            {/* Боковая панель с фильтрами */}
            <aside className="filters">
                <h3>Фильтры</h3>
                
                <form onSubmit={handleFilterSubmit}>
                    {/* Поиск по названию */}
                    <div className="filter-group">
                        <label>Поиск:</label>
                        <input 
                            type="text"
                            value={filters.search}
                            onChange={handleSearchChange}
                            placeholder="Название товара..."
                        />
                    </div>
                    
                    {/* Выбор категории */}
                    <div className="filter-group">
                        <label>Категория:</label>
                        <select 
                            value={filters.category_id}
                            onChange={handleCategoryChange}
                        >
                            <option value="">Все категории</option>
                            {categories.map(cat => (
                                <option key={cat.id} value={cat.id}>
                                    {cat.name} ({cat.products_count})
                                </option>
                            ))}
                        </select>
                    </div>
                    
                    {/* Кнопка применения фильтров */}
                    <button type="submit" className="btn">
                        Применить
                    </button>
                </form>
            </aside>
            
            {/* Основной контент - список товаров */}
            <main className="products-list">
                <h2>Каталог товаров</h2>
                
                {/* Сообщение для неавторизованных */}
                {!isLoggedIn && (
                    <div className="info-message" style={{
                        background: '#e3f2fd',
                        padding: '15px',
                        borderRadius: '8px',
                        marginBottom: '20px'
                    }}>
                        <p>
                            🔐 Чтобы арендовать товар, пожалуйста,{' '}
                            <a 
                                href="#" 
                                onClick={(e) => { e.preventDefault(); onNavigate('login'); }}
                                style={{color: '#3498db'}}
                            >
                                войдите
                            </a>{' '}
                            или{' '}
                            <a 
                                href="#"
                                onClick={(e) => { e.preventDefault(); onNavigate('register'); }}
                                style={{color: '#3498db'}}
                            >
                                зарегистрируйтесь
                            </a>.
                        </p>
                    </div>
                )}
                
                {/* Индикатор загрузки или сетка товаров */}
                {isLoading ? (
                    <Loader message="Загрузка товаров..." />
                ) : (
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
                            <p className="no-products" style={{
                                gridColumn: '1 / -1',
                                textAlign: 'center',
                                padding: '40px',
                                color: '#666'
                            }}>
                                Товары не найдены
                            </p>
                        )}
                    </div>
                )}
            </main>
        </div>
    );
}

/**
 * Файл: frontend/src/components/ProductCard.js
 * Компонент карточки товара
 * 
 * Отображает информацию о товаре и форму добавления в корзину
 * 
 * @author Студент
 * @version 1.0
 */

/**
 * Компонент ProductCard - карточка товара
 * 
 * @param {object} props - Свойства компонента
 * @param {object} props.product - Данные товара
 * @param {boolean} props.isLoggedIn - Авторизован ли пользователь
 * @param {function} props.onAddToCart - Callback добавления в корзину
 * @param {function} props.onLoginRequired - Callback если нужна авторизация
 */
function ProductCard({ product, isLoggedIn, onAddToCart, onLoginRequired }) {
    // Локальное состояние для количества дней аренды
    const [days, setDays] = React.useState(1);
    
    // Состояние загрузки при добавлении
    const [isLoading, setIsLoading] = React.useState(false);
    
    /**
     * Обработчик изменения количества дней
     */
    const handleDaysChange = (e) => {
        const value = parseInt(e.target.value) || 1;
        // Ограничиваем диапазон от 1 до 30
        setDays(Math.max(1, Math.min(30, value)));
    };
    
    /**
     * Обработчик добавления в корзину
     */
    const handleAddToCart = async (e) => {
        e.preventDefault();
        
        // Если пользователь не авторизован
        if (!isLoggedIn) {
            if (onLoginRequired) {
                onLoginRequired();
            }
            return;
        }
        
        // Добавляем товар в корзину
        setIsLoading(true);
        try {
            await onAddToCart(product.id, days);
        } finally {
            setIsLoading(false);
        }
    };
    
    // Рассчитываем примерную стоимость
    const estimatedCost = (product.price_per_day * days).toFixed(0);
    
    return (
        <div className="product-card">
            {/* Название товара */}
            <h3>{product.name}</h3>
            
            {/* Категория */}
            <p className="category">
                {product.category_name || 'Без категории'}
            </p>
            
            {/* Описание (укороченное) */}
            {product.description && (
                <p className="description">
                    {product.description.substring(0, 100)}
                    {product.description.length > 100 ? '...' : ''}
                </p>
            )}
            
            {/* Цена за день */}
            <p className="price">
                {Number(product.price_per_day).toLocaleString('ru-RU')} ₽/день
            </p>
            
            {/* Наличие на складе */}
            <p className="stock">
                В наличии: {product.stock} шт.
            </p>
            
            {/* Форма добавления в корзину */}
            {isLoggedIn ? (
                <form className="add-to-cart-form" onSubmit={handleAddToCart}>
                    {/* Поле ввода количества дней */}
                    <input 
                        type="number"
                        className="days-input"
                        value={days}
                        onChange={handleDaysChange}
                        min="1"
                        max="30"
                        disabled={isLoading}
                        title="Количество дней аренды"
                    />
                    
                    {/* Кнопка добавления */}
                    <button 
                        type="submit" 
                        className="btn-small"
                        disabled={isLoading || product.stock < 1}
                    >
                        {isLoading ? '...' : 'В корзину'}
                    </button>
                </form>
            ) : (
                /* Кнопка для неавторизованных */
                <button 
                    className="btn-small login-required"
                    onClick={onLoginRequired}
                >
                    🔑 Войдите для аренды
                </button>
            )}
            
            {/* Подсказка с примерной стоимостью */}
            {days > 1 && isLoggedIn && (
                <p className="estimate" style={{fontSize: '0.85rem', color: '#666', marginTop: '5px'}}>
                    ≈ {Number(estimatedCost).toLocaleString('ru-RU')} ₽ за {days} дн.
                </p>
            )}
        </div>
    );
}

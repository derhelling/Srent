/**
 * Файл: frontend/src/pages/Cart.js
 * Страница корзины
 * 
 * Отображает содержимое корзины пользователя с возможностью
 * изменения количества дней и удаления товаров
 * 
 * @author Студент
 * @version 1.0
 */

/**
 * Компонент CartPage - страница корзины
 * 
 * @param {object} props - Свойства компонента
 * @param {array} props.cartItems - Элементы корзины
 * @param {number} props.cartTotal - Общая сумма
 * @param {function} props.onNavigate - Callback навигации
 * @param {function} props.onUpdateCart - Callback обновления корзины
 * @param {function} props.onRemoveFromCart - Callback удаления из корзины
 * @param {function} props.onClearCart - Callback очистки корзины
 * @param {function} props.showNotification - Callback уведомлений
 */
function CartPage({ 
    cartItems, 
    cartTotal, 
    onNavigate, 
    onUpdateCart, 
    onRemoveFromCart, 
    onClearCart,
    showNotification 
}) {
    
    /**
     * Обработчик обновления количества дней
     */
    const handleUpdateDays = async (cartId, days) => {
        try {
            await onUpdateCart(cartId, days);
            showNotification('Корзина обновлена', 'success');
        } catch (error) {
            showNotification(error.message || 'Ошибка обновления', 'error');
        }
    };
    
    /**
     * Обработчик удаления товара
     */
    const handleRemove = async (cartId) => {
        try {
            await onRemoveFromCart(cartId);
            showNotification('Товар удалён из корзины', 'success');
        } catch (error) {
            showNotification(error.message || 'Ошибка удаления', 'error');
        }
    };
    
    /**
     * Обработчик очистки корзины
     */
    const handleClear = async () => {
        if (!confirm('Очистить всю корзину?')) return;
        
        try {
            await onClearCart();
            showNotification('Корзина очищена', 'success');
        } catch (error) {
            showNotification(error.message || 'Ошибка очистки', 'error');
        }
    };
    
    return (
        <div className="cart-container">
            <h2>Корзина</h2>
            
            {/* Если корзина пуста */}
            {cartItems.length === 0 ? (
                <div className="empty-cart">
                    <p style={{fontSize: '3rem'}}>🛒</p>
                    <p>Ваша корзина пуста</p>
                    <p>
                        Перейдите в{' '}
                        <a 
                            href="#"
                            onClick={(e) => { e.preventDefault(); onNavigate('catalog'); }}
                            style={{color: '#3498db'}}
                        >
                            каталог
                        </a>
                        , чтобы выбрать товары для аренды
                    </p>
                </div>
            ) : (
                /* Содержимое корзины */
                <div className="cart-content">
                    {/* Таблица товаров */}
                    <table className="cart-table">
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
                            {cartItems.map(item => (
                                <CartItem
                                    key={item.id}
                                    item={item}
                                    onUpdateDays={handleUpdateDays}
                                    onRemove={handleRemove}
                                />
                            ))}
                        </tbody>
                        
                        {/* Итоговая сумма */}
                        <tfoot>
                            <tr>
                                <td colSpan="3" className="total-label">
                                    <strong>Итого:</strong>
                                </td>
                                <td className="grand-total">
                                    <strong>
                                        {Number(cartTotal).toLocaleString('ru-RU')} ₽
                                    </strong>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    {/* Кнопки действий */}
                    <div className="cart-actions">
                        {/* Продолжить покупки */}
                        <button 
                            className="btn btn-secondary"
                            onClick={() => onNavigate('catalog')}
                        >
                            Продолжить покупки
                        </button>
                        
                        {/* Очистить корзину */}
                        <button 
                            className="btn btn-danger"
                            onClick={handleClear}
                        >
                            Очистить корзину
                        </button>
                        
                        {/* Оформить заказ */}
                        <button 
                            className="btn btn-primary"
                            onClick={() => onNavigate('checkout')}
                        >
                            Оформить заказ
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}

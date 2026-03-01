/**
 * Файл: frontend/src/pages/Checkout.js
 * Страница оформления заказа
 * 
 * Позволяет пользователю выбрать даты аренды
 * и подтвердить заказ
 * 
 * @author Студент
 * @version 1.0
 */

/**
 * Компонент CheckoutPage - страница оформления заказа
 * 
 * @param {object} props - Свойства компонента
 * @param {array} props.cartItems - Элементы корзины
 * @param {number} props.cartTotal - Общая сумма
 * @param {function} props.onNavigate - Callback навигации
 * @param {function} props.onOrderComplete - Callback после оформления заказа
 * @param {function} props.showNotification - Callback уведомлений
 */
function CheckoutPage({ 
    cartItems, 
    cartTotal, 
    onNavigate, 
    onOrderComplete,
    showNotification 
}) {
    // Получаем сегодняшнюю дату в формате YYYY-MM-DD
    const today = new Date().toISOString().split('T')[0];
    const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];
    
    // Состояние формы
    const [startDate, setStartDate] = React.useState(today);
    const [endDate, setEndDate] = React.useState(tomorrow);
    
    // Состояние отправки
    const [isSubmitting, setIsSubmitting] = React.useState(false);
    
    // Состояние успешного заказа
    const [orderSuccess, setOrderSuccess] = React.useState(null);
    
    // Ошибка валидации
    const [error, setError] = React.useState('');
    
    /**
     * Редирект если корзина пуста
     */
    React.useEffect(() => {
        if (cartItems.length === 0 && !orderSuccess) {
            onNavigate('cart');
        }
    }, [cartItems, orderSuccess]);
    
    /**
     * Обработчик отправки формы
     */
    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        
        // Валидация дат
        if (new Date(startDate) < new Date(today)) {
            setError('Дата начала не может быть в прошлом');
            return;
        }
        
        if (new Date(endDate) <= new Date(startDate)) {
            setError('Дата окончания должна быть позже даты начала');
            return;
        }
        
        setIsSubmitting(true);
        
        try {
            // Отправляем заказ
            const response = await api.createOrder(startDate, endDate);
            
            // Сохраняем данные успешного заказа
            setOrderSuccess({
                order: response.data.order,
                items: cartItems,
                total: cartTotal,
                startDate,
                endDate
            });
            
            // Уведомляем родительский компонент
            if (onOrderComplete) {
                onOrderComplete();
            }
            
            showNotification('Заказ успешно оформлен!', 'success');
            
        } catch (err) {
            setError(err.message || 'Ошибка при оформлении заказа');
            showNotification(err.message || 'Ошибка оформления', 'error');
        } finally {
            setIsSubmitting(false);
        }
    };
    
    // Если заказ успешно оформлен - показываем страницу успеха
    if (orderSuccess) {
        return (
            <div className="container">
                <div className="order-success">
                    <h1>✅ Заказ успешно оформлен!</h1>
                    <p>Спасибо за аренду в нашем прокате!</p>
                    
                    <div className="order-details">
                        <h3>Детали заказа:</h3>
                        <p>
                            <strong>Номер заказа:</strong> #{orderSuccess.order?.id || Math.floor(Math.random() * 9000) + 1000}
                        </p>
                        <p>
                            <strong>Дата оформления:</strong> {new Date().toLocaleString('ru-RU')}
                        </p>
                        <p>
                            <strong>Сумма заказа:</strong> {Number(orderSuccess.total).toLocaleString('ru-RU')} ₽
                        </p>
                        <p>
                            <strong>Период аренды:</strong> с {new Date(orderSuccess.startDate).toLocaleDateString('ru-RU')} по {new Date(orderSuccess.endDate).toLocaleDateString('ru-RU')}
                        </p>
                    </div>
                    
                    <div className="order-items-success">
                        <h3>Арендованные товары:</h3>
                        <div className="success-items-grid">
                            {orderSuccess.items.map(item => (
                                <div key={item.id} className="success-item" style={{
                                    display: 'flex',
                                    justifyContent: 'space-between',
                                    padding: '10px',
                                    background: 'white',
                                    borderRadius: '5px',
                                    marginBottom: '10px'
                                }}>
                                    <strong>{item.name}</strong>
                                    <span>
                                        {item.rental_days} дн. × {Number(item.price_per_day).toLocaleString('ru-RU')} ₽ = {Number(item.price_per_day * item.rental_days).toLocaleString('ru-RU')} ₽
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>
                    
                    <div className="success-actions" style={{marginTop: '30px'}}>
                        <button 
                            className="btn"
                            onClick={() => onNavigate('catalog')}
                        >
                            Продолжить покупки
                        </button>
                        <button 
                            className="btn btn-secondary"
                            onClick={() => onNavigate('home')}
                            style={{marginLeft: '10px'}}
                        >
                            На главную
                        </button>
                    </div>
                </div>
            </div>
        );
    }
    
    return (
        <div className="container">
            <div className="checkout-form" style={{
                maxWidth: '800px',
                margin: '30px auto',
                padding: '30px',
                background: '#f8f9fa',
                borderRadius: '10px'
            }}>
                <h2>Оформление заказа</h2>
                
                {/* Ошибка */}
                {error && (
                    <div className="error-message" style={{
                        background: '#f8d7da',
                        color: '#721c24',
                        padding: '10px 15px',
                        borderRadius: '5px',
                        marginBottom: '20px'
                    }}>
                        {error}
                    </div>
                )}
                
                <div className="checkout-content" style={{
                    display: 'grid',
                    gridTemplateColumns: '1fr 1fr',
                    gap: '30px'
                }}>
                    {/* Список товаров */}
                    <div className="checkout-items">
                        <h3>Ваш заказ:</h3>
                        <table className="checkout-table" style={{
                            width: '100%',
                            borderCollapse: 'collapse'
                        }}>
                            <tbody>
                                {cartItems.map(item => (
                                    <tr key={item.id} style={{borderBottom: '1px solid #dee2e6'}}>
                                        <td style={{padding: '10px'}}>{item.name}</td>
                                        <td style={{padding: '10px', textAlign: 'right'}}>
                                            {item.rental_days} дн. × {Number(item.price_per_day).toLocaleString('ru-RU')} ₽
                                        </td>
                                        <td style={{padding: '10px', textAlign: 'right', fontWeight: 'bold'}}>
                                            {Number(item.price_per_day * item.rental_days).toLocaleString('ru-RU')} ₽
                                        </td>
                                    </tr>
                                ))}
                                <tr style={{background: '#e9ecef', fontWeight: 'bold'}}>
                                    <td colSpan="2" style={{padding: '10px'}}>Итого:</td>
                                    <td style={{padding: '10px', textAlign: 'right'}}>
                                        {Number(cartTotal).toLocaleString('ru-RU')} ₽
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    {/* Форма с датами */}
                    <form onSubmit={handleSubmit} className="rental-form">
                        <h3>Выберите даты аренды:</h3>
                        
                        <div style={{marginBottom: '15px'}}>
                            <label style={{display: 'block', marginBottom: '5px', fontWeight: 'bold'}}>
                                Дата начала:
                            </label>
                            <input 
                                type="date"
                                value={startDate}
                                onChange={(e) => setStartDate(e.target.value)}
                                min={today}
                                required
                                style={{
                                    width: '100%',
                                    padding: '10px',
                                    border: '1px solid #ddd',
                                    borderRadius: '5px'
                                }}
                            />
                        </div>
                        
                        <div style={{marginBottom: '15px'}}>
                            <label style={{display: 'block', marginBottom: '5px', fontWeight: 'bold'}}>
                                Дата окончания:
                            </label>
                            <input 
                                type="date"
                                value={endDate}
                                onChange={(e) => setEndDate(e.target.value)}
                                min={tomorrow}
                                required
                                style={{
                                    width: '100%',
                                    padding: '10px',
                                    border: '1px solid #ddd',
                                    borderRadius: '5px'
                                }}
                            />
                        </div>
                        
                        <div className="rental-info" style={{
                            background: '#e3f2fd',
                            padding: '15px',
                            borderRadius: '8px',
                            marginBottom: '20px'
                        }}>
                            <p>📅 Минимальный срок аренды - 1 день</p>
                            <p>💰 Оплата при получении</p>
                            <p>🆔 При получении необходимо предъявить паспорт</p>
                        </div>
                        
                        <div className="form-actions" style={{
                            display: 'flex',
                            gap: '10px'
                        }}>
                            <button 
                                type="button"
                                className="btn btn-secondary"
                                onClick={() => onNavigate('cart')}
                            >
                                Назад в корзину
                            </button>
                            <button 
                                type="submit"
                                className="btn btn-primary"
                                disabled={isSubmitting}
                            >
                                {isSubmitting ? 'Оформление...' : 'Подтвердить заказ'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}

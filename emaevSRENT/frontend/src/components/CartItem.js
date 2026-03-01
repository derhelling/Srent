/**
 * Файл: frontend/src/components/CartItem.js
 * Компонент элемента корзины
 * 
 * Отображает один товар в корзине с возможностью
 * изменения количества дней и удаления
 * 
 * @author Студент
 * @version 1.0
 */

/**
 * Компонент CartItem - элемент корзины
 * 
 * @param {object} props - Свойства компонента
 * @param {object} props.item - Данные элемента корзины
 * @param {function} props.onUpdateDays - Callback обновления дней
 * @param {function} props.onRemove - Callback удаления
 */
function CartItem({ item, onUpdateDays, onRemove }) {
    // Локальное состояние для редактирования дней
    const [days, setDays] = React.useState(item.rental_days);
    
    // Состояние загрузки
    const [isUpdating, setIsUpdating] = React.useState(false);
    
    /**
     * Обработчик изменения количества дней
     */
    const handleDaysChange = (e) => {
        const value = parseInt(e.target.value) || 1;
        setDays(Math.max(1, Math.min(30, value)));
    };
    
    /**
     * Обработчик применения изменений
     */
    const handleUpdate = async () => {
        if (days === item.rental_days) return;
        
        setIsUpdating(true);
        try {
            await onUpdateDays(item.id, days);
        } finally {
            setIsUpdating(false);
        }
    };
    
    /**
     * Обработчик удаления из корзины
     */
    const handleRemove = async () => {
        if (!confirm('Удалить товар из корзины?')) return;
        
        setIsUpdating(true);
        try {
            await onRemove(item.id);
        } finally {
            setIsUpdating(false);
        }
    };
    
    // Рассчитываем стоимость позиции
    const subtotal = item.price_per_day * item.rental_days * item.quantity;
    
    return (
        <tr className={isUpdating ? 'updating' : ''}>
            {/* Информация о товаре */}
            <td className="product-info">
                <div>
                    <strong>{item.name}</strong>
                    <br />
                    <small>
                        {item.description 
                            ? item.description.substring(0, 50) + '...'
                            : ''
                        }
                    </small>
                </div>
            </td>
            
            {/* Цена за день */}
            <td className="price">
                {Number(item.price_per_day).toLocaleString('ru-RU')} ₽
            </td>
            
            {/* Количество дней аренды с возможностью редактирования */}
            <td>
                <form 
                    className="update-days-form"
                    onSubmit={(e) => { e.preventDefault(); handleUpdate(); }}
                    style={{display: 'flex', gap: '5px', alignItems: 'center'}}
                >
                    <input 
                        type="number"
                        className="days-input-small"
                        value={days}
                        onChange={handleDaysChange}
                        min="1"
                        max="30"
                        disabled={isUpdating}
                        style={{width: '60px', padding: '5px', textAlign: 'center'}}
                    />
                    
                    {/* Показываем кнопку только если значение изменилось */}
                    {days !== item.rental_days && (
                        <button 
                            type="submit"
                            className="btn-update"
                            disabled={isUpdating}
                            style={{
                                padding: '5px 10px',
                                fontSize: '0.8rem',
                                background: '#3498db',
                                color: 'white',
                                border: 'none',
                                borderRadius: '3px',
                                cursor: 'pointer'
                            }}
                        >
                            ✓
                        </button>
                    )}
                </form>
            </td>
            
            {/* Итоговая стоимость позиции */}
            <td className="total-price">
                <strong>
                    {Number(subtotal).toLocaleString('ru-RU')} ₽
                </strong>
            </td>
            
            {/* Кнопка удаления */}
            <td>
                <button 
                    className="btn-remove"
                    onClick={handleRemove}
                    disabled={isUpdating}
                    style={{
                        color: '#e74c3c',
                        background: 'none',
                        border: 'none',
                        cursor: 'pointer',
                        textDecoration: 'underline'
                    }}
                >
                    Удалить
                </button>
            </td>
        </tr>
    );
}

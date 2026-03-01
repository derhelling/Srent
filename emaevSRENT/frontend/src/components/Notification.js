/**
 * Файл: frontend/src/components/Notification.js
 * Компонент уведомлений
 * 
 * Отображает всплывающие уведомления (toast) для
 * информирования пользователя о результатах действий
 * 
 * @author Студент
 * @version 1.0
 */

/**
 * Компонент Notification - всплывающее уведомление
 * 
 * @param {object} props - Свойства компонента
 * @param {string} props.message - Текст уведомления
 * @param {string} props.type - Тип (success, error, info)
 * @param {function} props.onClose - Callback при закрытии
 */
function Notification({ message, type = 'info', onClose }) {
    // Автоматически скрываем уведомление через 3 секунды
    React.useEffect(() => {
        const timer = setTimeout(() => {
            if (onClose) onClose();
        }, 3000);
        
        // Очистка таймера при размонтировании
        return () => clearTimeout(timer);
    }, [onClose]);
    
    // Если нет сообщения - не рендерим
    if (!message) return null;
    
    return (
        <div className={`notification ${type}`} onClick={onClose}>
            {/* Иконка в зависимости от типа */}
            {type === 'success' && '✅ '}
            {type === 'error' && '❌ '}
            {type === 'info' && 'ℹ️ '}
            
            {/* Текст уведомления */}
            {message}
        </div>
    );
}

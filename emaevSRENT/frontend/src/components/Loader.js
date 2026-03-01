/**
 * Файл: frontend/src/components/Loader.js
 * Компонент индикатора загрузки
 * 
 * Отображает спиннер во время загрузки данных
 * 
 * @author Студент
 * @version 1.0
 */

/**
 * Компонент Loader - индикатор загрузки
 * 
 * @param {object} props - Свойства компонента
 * @param {string} props.message - Текст сообщения (опционально)
 */
function Loader({ message = 'Загрузка...' }) {
    return (
        <div className="loader">
            <div style={{textAlign: 'center'}}>
                {/* Анимированный спиннер */}
                <div className="loader-spinner"></div>
                
                {/* Текст под спиннером */}
                <p style={{marginTop: '15px', color: '#666'}}>
                    {message}
                </p>
            </div>
        </div>
    );
}

/**
 * Файл: frontend/src/pages/Login.js
 * Страница входа в систему
 * 
 * Форма авторизации пользователя
 * 
 * @author Студент
 * @version 1.0
 */

/**
 * Компонент LoginPage - страница входа
 * 
 * @param {object} props - Свойства компонента
 * @param {function} props.onNavigate - Callback навигации
 * @param {function} props.onLogin - Callback успешной авторизации
 * @param {function} props.showNotification - Callback уведомлений
 */
function LoginPage({ onNavigate, onLogin, showNotification }) {
    // Состояние формы
    const [username, setUsername] = React.useState('');
    const [password, setPassword] = React.useState('');
    
    // Состояние отправки
    const [isLoading, setIsLoading] = React.useState(false);
    
    // Ошибка
    const [error, setError] = React.useState('');
    
    /**
     * Обработчик отправки формы
     */
    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        
        // Простая валидация
        if (!username.trim() || !password) {
            setError('Заполните все поля');
            return;
        }
        
        setIsLoading(true);
        
        try {
            // Отправляем запрос на авторизацию
            const response = await api.login(username, password);
            
            // Уведомляем об успехе
            showNotification('Добро пожаловать!', 'success');
            
            // Вызываем callback с данными пользователя
            if (onLogin) {
                onLogin(response.data.user);
            }
            
            // Переходим на главную
            onNavigate('home');
            
        } catch (err) {
            setError(err.message || 'Ошибка авторизации');
        } finally {
            setIsLoading(false);
        }
    };
    
    return (
        <div className="auth-container">
            <h2>Вход в аккаунт</h2>
            
            {/* Сообщение об ошибке */}
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
            
            {/* Форма входа */}
            <form onSubmit={handleSubmit} className="auth-form">
                {/* Поле логина */}
                <div className="form-group">
                    <label htmlFor="username">Имя пользователя или Email:</label>
                    <input 
                        type="text"
                        id="username"
                        value={username}
                        onChange={(e) => setUsername(e.target.value)}
                        placeholder="Введите логин или email"
                        required
                        disabled={isLoading}
                    />
                </div>
                
                {/* Поле пароля */}
                <div className="form-group">
                    <label htmlFor="password">Пароль:</label>
                    <input 
                        type="password"
                        id="password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        placeholder="Введите пароль"
                        required
                        disabled={isLoading}
                    />
                </div>
                
                {/* Кнопка входа */}
                <button 
                    type="submit" 
                    className="btn btn-primary"
                    disabled={isLoading}
                    style={{width: '100%', marginTop: '10px'}}
                >
                    {isLoading ? 'Вход...' : 'Войти'}
                </button>
            </form>
            
            {/* Ссылки */}
            <div className="auth-links" style={{
                marginTop: '20px',
                textAlign: 'center'
            }}>
                <p>
                    Нет аккаунта?{' '}
                    <a 
                        href="#"
                        onClick={(e) => { e.preventDefault(); onNavigate('register'); }}
                        style={{color: '#3498db'}}
                    >
                        Зарегистрироваться
                    </a>
                </p>
                <p style={{marginTop: '10px'}}>
                    <a 
                        href="#"
                        onClick={(e) => { e.preventDefault(); onNavigate('forgot'); }}
                        style={{color: '#3498db'}}
                    >
                        Забыли пароль?
                    </a>
                </p>
            </div>
        </div>
    );
}

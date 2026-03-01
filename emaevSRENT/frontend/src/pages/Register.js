/**
 * Файл: frontend/src/pages/Register.js
 * Страница регистрации
 * 
 * Форма создания нового аккаунта пользователя
 * 
 * @author Студент
 * @version 1.0
 */

/**
 * Компонент RegisterPage - страница регистрации
 * 
 * @param {object} props - Свойства компонента
 * @param {function} props.onNavigate - Callback навигации
 * @param {function} props.showNotification - Callback уведомлений
 */
function RegisterPage({ onNavigate, showNotification }) {
    // Состояние формы
    const [formData, setFormData] = React.useState({
        username: '',
        email: '',
        password: '',
        confirmPassword: ''
    });
    
    // Состояние отправки
    const [isLoading, setIsLoading] = React.useState(false);
    
    // Ошибки валидации
    const [errors, setErrors] = React.useState({});
    
    // Успешная регистрация
    const [success, setSuccess] = React.useState(false);
    
    /**
     * Обработчик изменения полей формы
     */
    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
        
        // Очищаем ошибку поля при изменении
        if (errors[name]) {
            setErrors(prev => ({
                ...prev,
                [name]: ''
            }));
        }
    };
    
    /**
     * Валидация формы
     */
    const validateForm = () => {
        const newErrors = {};
        
        if (!formData.username.trim()) {
            newErrors.username = 'Введите имя пользователя';
        } else if (formData.username.length < 3) {
            newErrors.username = 'Минимум 3 символа';
        }
        
        if (!formData.email.trim()) {
            newErrors.email = 'Введите email';
        } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
            newErrors.email = 'Неверный формат email';
        }
        
        if (!formData.password) {
            newErrors.password = 'Введите пароль';
        } else if (formData.password.length < 6) {
            newErrors.password = 'Минимум 6 символов';
        }
        
        if (formData.password !== formData.confirmPassword) {
            newErrors.confirmPassword = 'Пароли не совпадают';
        }
        
        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };
    
    /**
     * Обработчик отправки формы
     */
    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (!validateForm()) return;
        
        setIsLoading(true);
        
        try {
            await api.register(
                formData.username,
                formData.email,
                formData.password,
                formData.confirmPassword
            );
            
            setSuccess(true);
            showNotification('Регистрация успешна!', 'success');
            
        } catch (err) {
            // Обработка ошибок от сервера
            if (err.errors) {
                const serverErrors = {};
                Object.keys(err.errors).forEach(key => {
                    serverErrors[key] = err.errors[key][0];
                });
                setErrors(serverErrors);
            } else {
                setErrors({ general: err.message || 'Ошибка регистрации' });
            }
        } finally {
            setIsLoading(false);
        }
    };
    
    // Если регистрация успешна
    if (success) {
        return (
            <div className="auth-container">
                <div style={{textAlign: 'center'}}>
                    <h2 style={{color: '#27ae60'}}>✅ Регистрация успешна!</h2>
                    <p style={{margin: '20px 0'}}>
                        Теперь вы можете войти в свой аккаунт.
                    </p>
                    <button 
                        className="btn btn-primary"
                        onClick={() => onNavigate('login')}
                    >
                        Войти
                    </button>
                </div>
            </div>
        );
    }
    
    return (
        <div className="auth-container">
            <h2>Регистрация</h2>
            
            {/* Общая ошибка */}
            {errors.general && (
                <div className="error" style={{
                    background: '#f8d7da',
                    color: '#721c24',
                    padding: '10px 15px',
                    borderRadius: '5px',
                    marginBottom: '20px'
                }}>
                    {errors.general}
                </div>
            )}
            
            {/* Форма регистрации */}
            <form onSubmit={handleSubmit} className="auth-form">
                {/* Имя пользователя */}
                <div className="form-group">
                    <label>Имя пользователя:</label>
                    <input 
                        type="text"
                        name="username"
                        value={formData.username}
                        onChange={handleChange}
                        placeholder="Минимум 3 символа"
                        disabled={isLoading}
                        style={errors.username ? {borderColor: '#e74c3c'} : {}}
                    />
                    {errors.username && (
                        <small style={{color: '#e74c3c'}}>{errors.username}</small>
                    )}
                </div>
                
                {/* Email */}
                <div className="form-group">
                    <label>Email:</label>
                    <input 
                        type="email"
                        name="email"
                        value={formData.email}
                        onChange={handleChange}
                        placeholder="example@mail.ru"
                        disabled={isLoading}
                        style={errors.email ? {borderColor: '#e74c3c'} : {}}
                    />
                    {errors.email && (
                        <small style={{color: '#e74c3c'}}>{errors.email}</small>
                    )}
                </div>
                
                {/* Пароль */}
                <div className="form-group">
                    <label>Пароль:</label>
                    <input 
                        type="password"
                        name="password"
                        value={formData.password}
                        onChange={handleChange}
                        placeholder="Минимум 6 символов"
                        disabled={isLoading}
                        style={errors.password ? {borderColor: '#e74c3c'} : {}}
                    />
                    {errors.password && (
                        <small style={{color: '#e74c3c'}}>{errors.password}</small>
                    )}
                </div>
                
                {/* Подтверждение пароля */}
                <div className="form-group">
                    <label>Подтвердите пароль:</label>
                    <input 
                        type="password"
                        name="confirmPassword"
                        value={formData.confirmPassword}
                        onChange={handleChange}
                        placeholder="Повторите пароль"
                        disabled={isLoading}
                        style={errors.confirmPassword ? {borderColor: '#e74c3c'} : {}}
                    />
                    {errors.confirmPassword && (
                        <small style={{color: '#e74c3c'}}>{errors.confirmPassword}</small>
                    )}
                </div>
                
                {/* Кнопка регистрации */}
                <button 
                    type="submit"
                    className="btn"
                    disabled={isLoading}
                    style={{width: '100%', marginTop: '10px'}}
                >
                    {isLoading ? 'Регистрация...' : 'Зарегистрироваться'}
                </button>
            </form>
            
            {/* Ссылка на вход */}
            <p style={{marginTop: '20px', textAlign: 'center'}}>
                Уже есть аккаунт?{' '}
                <a 
                    href="#"
                    onClick={(e) => { e.preventDefault(); onNavigate('login'); }}
                    style={{color: '#3498db'}}
                >
                    Войти
                </a>
            </p>
        </div>
    );
}

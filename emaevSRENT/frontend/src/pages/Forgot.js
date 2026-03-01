/**
 * Файл: frontend/src/pages/Forgot.js
 * Страница восстановления пароля
 * 
 * Многошаговая форма для сброса пароля:
 * 1. Ввод email
 * 2. Ввод кода подтверждения
 * 3. Ввод нового пароля
 * 
 * @author Студент
 * @version 1.0
 */

/**
 * Компонент ForgotPage - страница восстановления пароля
 * 
 * @param {object} props - Свойства компонента
 * @param {function} props.onNavigate - Callback навигации
 * @param {function} props.showNotification - Callback уведомлений
 */
function ForgotPage({ onNavigate, showNotification }) {
    // Текущий шаг (1-3)
    const [step, setStep] = React.useState(1);
    
    // Данные формы
    const [email, setEmail] = React.useState('');
    const [code, setCode] = React.useState('');
    const [password, setPassword] = React.useState('');
    const [confirmPassword, setConfirmPassword] = React.useState('');
    
    // Состояние загрузки
    const [isLoading, setIsLoading] = React.useState(false);
    
    // Сообщения
    const [error, setError] = React.useState('');
    const [success, setSuccess] = React.useState('');
    
    /**
     * Шаг 1: Отправка email для получения кода
     */
    const handleRequestCode = async (e) => {
        e.preventDefault();
        setError('');
        
        if (!email.trim()) {
            setError('Введите email');
            return;
        }
        
        setIsLoading(true);
        
        try {
            await api.forgotPassword(email);
            setSuccess('Код отправлен! В учебном режиме используйте код: 123456');
            setStep(2);
        } catch (err) {
            setError(err.message || 'Ошибка отправки');
        } finally {
            setIsLoading(false);
        }
    };
    
    /**
     * Шаг 2: Проверка кода
     */
    const handleVerifyCode = (e) => {
        e.preventDefault();
        setError('');
        
        if (!code.trim()) {
            setError('Введите код');
            return;
        }
        
        // В учебном режиме принимаем код 123456
        if (code === '123456') {
            setStep(3);
            setSuccess('');
        } else {
            setError('Неверный код. Используйте 123456');
        }
    };
    
    /**
     * Шаг 3: Сброс пароля
     */
    const handleResetPassword = async (e) => {
        e.preventDefault();
        setError('');
        
        if (password.length < 6) {
            setError('Пароль должен быть минимум 6 символов');
            return;
        }
        
        if (password !== confirmPassword) {
            setError('Пароли не совпадают');
            return;
        }
        
        setIsLoading(true);
        
        try {
            await api.resetPassword(email, code, password, confirmPassword);
            setStep(4); // Успех
            showNotification('Пароль успешно изменён!', 'success');
        } catch (err) {
            setError(err.message || 'Ошибка сброса пароля');
        } finally {
            setIsLoading(false);
        }
    };
    
    return (
        <div className="reset-container" style={{
            maxWidth: '450px',
            margin: '50px auto',
            padding: '30px',
            background: 'white',
            borderRadius: '10px',
            boxShadow: '0 2px 20px rgba(0,0,0,0.1)'
        }}>
            {/* Индикатор шагов */}
            <div className="steps" style={{
                display: 'flex',
                justifyContent: 'space-between',
                marginBottom: '30px'
            }}>
                {[1, 2, 3].map(s => (
                    <div 
                        key={s}
                        className={`step ${step >= s ? 'active' : ''} ${step > s ? 'completed' : ''}`}
                        style={{textAlign: 'center', flex: 1}}
                    >
                        <div style={{
                            width: '32px',
                            height: '32px',
                            borderRadius: '50%',
                            background: step >= s ? (step > s ? '#27ae60' : '#3498db') : '#e1e1e1',
                            color: step >= s ? 'white' : '#666',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            margin: '0 auto 5px',
                            fontWeight: 'bold'
                        }}>
                            {step > s ? '✓' : s}
                        </div>
                        <div style={{fontSize: '0.8rem', color: step >= s ? '#3498db' : '#666'}}>
                            {s === 1 && 'Email'}
                            {s === 2 && 'Код'}
                            {s === 3 && 'Пароль'}
                        </div>
                    </div>
                ))}
            </div>
            
            <h2 style={{textAlign: 'center', marginBottom: '20px'}}>
                Восстановление пароля
            </h2>
            
            {/* Сообщения */}
            {error && (
                <div style={{
                    background: '#f8d7da',
                    color: '#721c24',
                    padding: '10px 15px',
                    borderRadius: '5px',
                    marginBottom: '20px'
                }}>
                    {error}
                </div>
            )}
            
            {success && (
                <div style={{
                    background: '#d4edda',
                    color: '#155724',
                    padding: '10px 15px',
                    borderRadius: '5px',
                    marginBottom: '20px'
                }}>
                    {success}
                </div>
            )}
            
            {/* Шаг 1: Email */}
            {step === 1 && (
                <form onSubmit={handleRequestCode}>
                    <p style={{color: '#666', marginBottom: '20px', textAlign: 'center'}}>
                        Введите email, указанный при регистрации
                    </p>
                    
                    <div className="form-group" style={{marginBottom: '20px'}}>
                        <label style={{display: 'block', marginBottom: '5px', fontWeight: 'bold'}}>
                            Ваш Email:
                        </label>
                        <input 
                            type="email"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            placeholder="example@mail.ru"
                            disabled={isLoading}
                            style={{
                                width: '100%',
                                padding: '12px',
                                border: '2px solid #e1e1e1',
                                borderRadius: '6px'
                            }}
                        />
                    </div>
                    
                    <button 
                        type="submit"
                        className="btn btn-primary"
                        disabled={isLoading}
                        style={{width: '100%'}}
                    >
                        {isLoading ? 'Отправка...' : 'Отправить код'}
                    </button>
                </form>
            )}
            
            {/* Шаг 2: Код подтверждения */}
            {step === 2 && (
                <form onSubmit={handleVerifyCode}>
                    {/* Подсказка для учебного режима */}
                    <div style={{
                        background: '#e8f4fd',
                        border: '2px dashed #3498db',
                        padding: '15px',
                        borderRadius: '8px',
                        marginBottom: '20px',
                        textAlign: 'center'
                    }}>
                        <div style={{color: '#666', marginBottom: '5px'}}>🔐 УЧЕБНЫЙ РЕЖИМ</div>
                        <div style={{
                            fontSize: '2rem',
                            fontWeight: 'bold',
                            color: '#2980b9',
                            letterSpacing: '5px',
                            fontFamily: 'monospace'
                        }}>
                            123456
                        </div>
                        <div style={{color: '#e67e22', fontSize: '0.9rem', marginTop: '5px'}}>
                            Используйте этот код
                        </div>
                    </div>
                    
                    <div className="form-group" style={{marginBottom: '20px'}}>
                        <label style={{display: 'block', marginBottom: '5px', fontWeight: 'bold'}}>
                            Код подтверждения:
                        </label>
                        <input 
                            type="text"
                            value={code}
                            onChange={(e) => setCode(e.target.value)}
                            placeholder="Введите 6-значный код"
                            maxLength="6"
                            style={{
                                width: '100%',
                                padding: '12px',
                                border: '2px solid #e1e1e1',
                                borderRadius: '6px',
                                textAlign: 'center',
                                fontSize: '1.5rem',
                                letterSpacing: '5px'
                            }}
                        />
                    </div>
                    
                    <button 
                        type="submit"
                        className="btn btn-primary"
                        style={{width: '100%'}}
                    >
                        Проверить код
                    </button>
                </form>
            )}
            
            {/* Шаг 3: Новый пароль */}
            {step === 3 && (
                <form onSubmit={handleResetPassword}>
                    <p style={{color: '#666', marginBottom: '20px', textAlign: 'center'}}>
                        Придумайте новый пароль
                    </p>
                    
                    <div className="form-group" style={{marginBottom: '15px'}}>
                        <label style={{display: 'block', marginBottom: '5px', fontWeight: 'bold'}}>
                            Новый пароль:
                        </label>
                        <input 
                            type="password"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            placeholder="Минимум 6 символов"
                            disabled={isLoading}
                            style={{
                                width: '100%',
                                padding: '12px',
                                border: '2px solid #e1e1e1',
                                borderRadius: '6px'
                            }}
                        />
                    </div>
                    
                    <div className="form-group" style={{marginBottom: '20px'}}>
                        <label style={{display: 'block', marginBottom: '5px', fontWeight: 'bold'}}>
                            Подтвердите пароль:
                        </label>
                        <input 
                            type="password"
                            value={confirmPassword}
                            onChange={(e) => setConfirmPassword(e.target.value)}
                            placeholder="Повторите пароль"
                            disabled={isLoading}
                            style={{
                                width: '100%',
                                padding: '12px',
                                border: '2px solid #e1e1e1',
                                borderRadius: '6px'
                            }}
                        />
                    </div>
                    
                    <button 
                        type="submit"
                        className="btn btn-primary"
                        disabled={isLoading}
                        style={{width: '100%'}}
                    >
                        {isLoading ? 'Сохранение...' : 'Сохранить новый пароль'}
                    </button>
                </form>
            )}
            
            {/* Шаг 4: Успех */}
            {step === 4 && (
                <div style={{textAlign: 'center'}}>
                    <div style={{fontSize: '4rem', marginBottom: '20px'}}>✅</div>
                    <h3 style={{color: '#27ae60', marginBottom: '15px'}}>Пароль изменен!</h3>
                    <p style={{marginBottom: '25px'}}>
                        Теперь вы можете войти с новым паролем
                    </p>
                    <button 
                        className="btn btn-primary"
                        onClick={() => onNavigate('login')}
                        style={{width: '100%'}}
                    >
                        Войти в аккаунт
                    </button>
                </div>
            )}
            
            {/* Ссылка назад */}
            {step < 4 && (
                <div style={{marginTop: '20px', textAlign: 'center'}}>
                    <a 
                        href="#"
                        onClick={(e) => { e.preventDefault(); onNavigate('login'); }}
                        style={{color: '#3498db'}}
                    >
                        ← Вернуться ко входу
                    </a>
                </div>
            )}
        </div>
    );
}

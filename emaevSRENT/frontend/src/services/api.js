/**
 * Файл: frontend/src/services/api.js
 * Сервис для работы с API бекенда
 * 
 * Содержит все методы для взаимодействия с Laravel API:
 * - Аутентификация (login, register, logout)
 * - Товары (getProducts, getProduct)
 * - Категории (getCategories)
 * - Корзина (getCart, addToCart, updateCart, removeFromCart)
 * - Заказы (createOrder, getOrders)
 * 
 * @author Студент
 * @version 1.0
 */

// Базовый URL API (относительный путь для работы в OSPanel)
const API_BASE_URL = '/backend/public/api';

/**
 * Класс ApiService - сервис для работы с REST API
 * 
 * Реализует паттерн Singleton для единого доступа к API
 */
class ApiService {
    
    /**
     * Выполнение HTTP запроса к API
     * 
     * @param {string} endpoint - Путь API (без базового URL)
     * @param {object} options - Настройки запроса (method, body, headers)
     * @returns {Promise} - Промис с результатом запроса
     */
    async request(endpoint, options = {}) {
        // Формируем полный URL
        const url = `${API_BASE_URL}${endpoint}`;
        
        // Настройки по умолчанию
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'include' // Для передачи cookies (сессии)
        };
        
        // Объединяем настройки
        const fetchOptions = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...options.headers
            }
        };
        
        // Если есть тело запроса - сериализуем в JSON
        if (options.body && typeof options.body === 'object') {
            fetchOptions.body = JSON.stringify(options.body);
        }
        
        try {
            // Выполняем запрос
            const response = await fetch(url, fetchOptions);
            
            // Парсим JSON ответ
            const data = await response.json();
            
            // Если ответ не успешный - выбрасываем ошибку
            if (!response.ok) {
                throw {
                    status: response.status,
                    message: data.error || 'Ошибка сервера',
                    errors: data.errors || null
                };
            }
            
            return data;
            
        } catch (error) {
            // Если это наша ошибка - пробрасываем дальше
            if (error.status) {
                throw error;
            }
            
            // Если ошибка сети
            throw {
                status: 0,
                message: 'Ошибка сети. Проверьте подключение к серверу.'
            };
        }
    }
    
    // ============================================
    // МЕТОДЫ АУТЕНТИФИКАЦИИ
    // ============================================
    
    /**
     * Регистрация нового пользователя
     * 
     * @param {string} username - Имя пользователя
     * @param {string} email - Email
     * @param {string} password - Пароль
     * @param {string} passwordConfirmation - Подтверждение пароля
     * @returns {Promise} - Данные созданного пользователя
     */
    async register(username, email, password, passwordConfirmation) {
        return this.request('/auth/register', {
            method: 'POST',
            body: {
                username,
                email,
                password,
                password_confirmation: passwordConfirmation
            }
        });
    }
    
    /**
     * Вход в систему
     * 
     * @param {string} username - Имя пользователя или email
     * @param {string} password - Пароль
     * @returns {Promise} - Данные пользователя
     */
    async login(username, password) {
        return this.request('/auth/login', {
            method: 'POST',
            body: { username, password }
        });
    }
    
    /**
     * Выход из системы
     * 
     * @returns {Promise}
     */
    async logout() {
        return this.request('/auth/logout', {
            method: 'POST'
        });
    }
    
    /**
     * Получение данных текущего пользователя
     * 
     * @returns {Promise} - Данные пользователя или null
     */
    async getCurrentUser() {
        try {
            const response = await this.request('/auth/user');
            return response.data;
        } catch (error) {
            // Если 401 - пользователь не авторизован
            if (error.status === 401) {
                return null;
            }
            throw error;
        }
    }
    
    /**
     * Запрос на восстановление пароля
     * 
     * @param {string} email - Email пользователя
     * @returns {Promise}
     */
    async forgotPassword(email) {
        return this.request('/auth/forgot', {
            method: 'POST',
            body: { email }
        });
    }
    
    /**
     * Сброс пароля
     * 
     * @param {string} email - Email
     * @param {string} code - Код подтверждения
     * @param {string} password - Новый пароль
     * @param {string} passwordConfirmation - Подтверждение пароля
     * @returns {Promise}
     */
    async resetPassword(email, code, password, passwordConfirmation) {
        return this.request('/auth/reset', {
            method: 'POST',
            body: {
                email,
                code,
                password,
                password_confirmation: passwordConfirmation
            }
        });
    }
    
    // ============================================
    // МЕТОДЫ ТОВАРОВ
    // ============================================
    
    /**
     * Получение списка товаров
     * 
     * @param {object} filters - Фильтры (category_id, search, popular)
     * @returns {Promise} - Массив товаров
     */
    async getProducts(filters = {}) {
        // Формируем query string из фильтров
        const params = new URLSearchParams();
        
        if (filters.category_id) {
            params.append('category_id', filters.category_id);
        }
        if (filters.search) {
            params.append('search', filters.search);
        }
        if (filters.popular) {
            params.append('popular', 'true');
            if (filters.limit) {
                params.append('limit', filters.limit);
            }
        }
        
        const queryString = params.toString();
        const endpoint = queryString ? `/products?${queryString}` : '/products';
        
        return this.request(endpoint);
    }
    
    /**
     * Получение товара по ID
     * 
     * @param {number} id - ID товара
     * @returns {Promise} - Данные товара
     */
    async getProduct(id) {
        return this.request(`/products/${id}`);
    }
    
    // ============================================
    // МЕТОДЫ КАТЕГОРИЙ
    // ============================================
    
    /**
     * Получение списка категорий
     * 
     * @returns {Promise} - Массив категорий
     */
    async getCategories() {
        return this.request('/categories');
    }
    
    // ============================================
    // МЕТОДЫ КОРЗИНЫ
    // ============================================
    
    /**
     * Получение содержимого корзины
     * 
     * @returns {Promise} - Данные корзины
     */
    async getCart() {
        return this.request('/cart');
    }
    
    /**
     * Добавление товара в корзину
     * 
     * @param {number} productId - ID товара
     * @param {number} days - Количество дней аренды
     * @returns {Promise}
     */
    async addToCart(productId, days = 1) {
        return this.request('/cart', {
            method: 'POST',
            body: {
                product_id: productId,
                days: days
            }
        });
    }
    
    /**
     * Обновление количества дней аренды
     * 
     * @param {number} cartId - ID записи в корзине
     * @param {number} days - Новое количество дней
     * @returns {Promise}
     */
    async updateCartItem(cartId, days) {
        return this.request(`/cart/${cartId}`, {
            method: 'PUT',
            body: { days }
        });
    }
    
    /**
     * Удаление товара из корзины
     * 
     * @param {number} cartId - ID записи в корзине
     * @returns {Promise}
     */
    async removeFromCart(cartId) {
        return this.request(`/cart/${cartId}`, {
            method: 'DELETE'
        });
    }
    
    /**
     * Очистка всей корзины
     * 
     * @returns {Promise}
     */
    async clearCart() {
        return this.request('/cart', {
            method: 'DELETE'
        });
    }
    
    // ============================================
    // МЕТОДЫ ЗАКАЗОВ
    // ============================================
    
    /**
     * Создание заказа
     * 
     * @param {string} startDate - Дата начала аренды
     * @param {string} endDate - Дата окончания аренды
     * @returns {Promise} - Данные созданного заказа
     */
    async createOrder(startDate, endDate) {
        return this.request('/orders', {
            method: 'POST',
            body: {
                start_date: startDate,
                end_date: endDate
            }
        });
    }
    
    /**
     * Получение списка заказов пользователя
     * 
     * @returns {Promise} - Массив заказов
     */
    async getOrders() {
        return this.request('/orders');
    }
    
    /**
     * Получение деталей заказа
     * 
     * @param {number} orderId - ID заказа
     * @returns {Promise} - Данные заказа
     */
    async getOrder(orderId) {
        return this.request(`/orders/${orderId}`);
    }
}

// Создаём глобальный экземпляр API сервиса
const api = new ApiService();

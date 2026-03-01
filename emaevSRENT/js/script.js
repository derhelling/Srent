// Файл: js/script.js
// Интерактивные функции для сайта

document.addEventListener('DOMContentLoaded', function () {
	// Валидация форм на клиенте
	const forms = document.querySelectorAll('.auth-form')
	forms.forEach(form => {
		form.addEventListener('submit', function (e) {
			const password = this.querySelector('input[type="password"]')
			const confirm = this.querySelector('input[name="confirm_password"]')

			if (confirm && password.value !== confirm.value) {
				e.preventDefault()
				alert('Пароли не совпадают!')
			}
		})
	})

	// Калькулятор стоимости аренды
	const rentalDays = document.querySelectorAll('input[name="days"]')
	rentalDays.forEach(input => {
		input.addEventListener('change', function () {
			const priceElement = this.closest('.product-card').querySelector('.price')
			if (priceElement) {
				const price = parseFloat(priceElement.textContent)
				const days = parseInt(this.value)
				const total = price * days

				// Показываем подсказку с итоговой стоимостью
				const tooltip = document.createElement('span')
				tooltip.className = 'tooltip'
				tooltip.textContent = `Итого: ${total} ₽`

				// Удаляем старую подсказку если есть
				const oldTooltip = this.parentNode.querySelector('.tooltip')
				if (oldTooltip) oldTooltip.remove()

				this.parentNode.appendChild(tooltip)

				// Автоматически скрываем через 3 секунды
				setTimeout(() => {
					if (tooltip) tooltip.remove()
				}, 3000)
			}
		})
	})

	// Подтверждение удаления
	const deleteButtons = document.querySelectorAll('.btn-remove')
	deleteButtons.forEach(button => {
		button.addEventListener('click', function (e) {
			if (!confirm('Вы уверены, что хотите удалить этот товар?')) {
				e.preventDefault()
			}
		})
	})

	// Фильтр цен (динамический)
	const priceFilter = document.querySelector('#price-range')
	if (priceFilter) {
		const priceValue = document.querySelector('#price-value')
		priceFilter.addEventListener('input', function () {
			priceValue.textContent = this.value
		})
	}

	// Слайдер для отзывов (если добавим позже)
	// Анимация появления элементов при скролле
	const animateOnScroll = function () {
		const elements = document.querySelectorAll('.product-card')
		elements.forEach(element => {
			const position = element.getBoundingClientRect()

			if (position.top < window.innerHeight - 100) {
				element.style.opacity = '1'
				element.style.transform = 'translateY(0)'
			}
		})
	}

	// Устанавливаем начальные стили для анимации
	document.querySelectorAll('.product-card').forEach(card => {
		card.style.opacity = '0'
		card.style.transform = 'translateY(20px)'
		card.style.transition = 'opacity 0.5s, transform 0.5s'
	})

	// Запускаем при загрузке и скролле
	window.addEventListener('scroll', animateOnScroll)
	animateOnScroll()

	// Сохранение данных формы в localStorage при перезагрузке
	const saveFormData = function () {
		const forms = document.querySelectorAll('form')
		forms.forEach(form => {
			const inputs = form.querySelectorAll(
				'input[type="text"], input[type="email"]',
			)
			inputs.forEach(input => {
				const savedValue = localStorage.getItem(input.name)
				if (savedValue) {
					input.value = savedValue
				}

				input.addEventListener('input', function () {
					localStorage.setItem(this.name, this.value)
				})
			})
		})
	}
	saveFormData()
})

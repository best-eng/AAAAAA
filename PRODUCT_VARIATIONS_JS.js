/**
 * JavaScript для системы вариаций товаров
 * Улучшает работу вариаций и добавляет интерактивность
 */

(function() {
    'use strict';
    
    console.log('=== PRODUCT VARIATIONS JS LOADED ===');
    
    // Инициализация вариаций
    function initVariations() {
        console.log('Initializing product variations...');
        
        const variationsContainer = document.querySelector('.product-variations');
        if (!variationsContainer) {
            console.log('No variations container found');
            return;
        }
        
        console.log('Found variations container:', variationsContainer);
        
        // Добавляем обработчики кликов
        const variationOptions = variationsContainer.querySelectorAll('.variation-option');
        variationOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                
                const url = this.getAttribute('href');
                console.log('Navigating to variation:', url);
                
                // Показываем индикатор загрузки
                showLoadingIndicator();
                
                // Переходим на страницу вариации
                window.location.href = url;
            });
        });
        
        // Добавляем анимации
        addVariationAnimations();
        
        // Добавляем фильтрацию
        addVariationFiltering();
    }
    
    // Показ индикатора загрузки
    function showLoadingIndicator() {
        const loadingDiv = document.createElement('div');
        loadingDiv.id = 'variation-loading';
        loadingDiv.innerHTML = '<div class="loading-spinner">Загрузка...</div>';
        loadingDiv.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 20px;
            border-radius: 5px;
            z-index: 9999;
        `;
        
        document.body.appendChild(loadingDiv);
        
        // Убираем через 3 секунды
        setTimeout(() => {
            if (loadingDiv.parentNode) {
                loadingDiv.parentNode.removeChild(loadingDiv);
            }
        }, 3000);
    }
    
    // Добавление анимаций
    function addVariationAnimations() {
        const variationsContainer = document.querySelector('.product-variations');
        if (!variationsContainer) return;
        
        // Анимация появления
        variationsContainer.style.opacity = '0';
        variationsContainer.style.transform = 'translateY(20px)';
        variationsContainer.style.transition = 'all 0.5s ease';
        
        setTimeout(() => {
            variationsContainer.style.opacity = '1';
            variationsContainer.style.transform = 'translateY(0)';
        }, 100);
        
        // Анимация при наведении на опции
        const options = variationsContainer.querySelectorAll('.variation-option');
        options.forEach(option => {
            option.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05)';
                this.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.2)';
            });
            
            option.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
                this.style.boxShadow = 'none';
            });
        });
    }
    
    // Добавление фильтрации вариаций
    function addVariationFiltering() {
        const variationsContainer = document.querySelector('.product-variations');
        if (!variationsContainer) return;
        
        // Создаем поле поиска
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Поиск по параметрам...';
        searchInput.style.cssText = `
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
        `;
        
        variationsContainer.insertBefore(searchInput, variationsContainer.firstChild);
        
        // Обработчик поиска
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const groups = variationsContainer.querySelectorAll('.variation-group');
            
            groups.forEach(group => {
                const options = group.querySelectorAll('.variation-option');
                let hasVisibleOptions = false;
                
                options.forEach(option => {
                    const text = option.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        option.style.display = 'inline-block';
                        hasVisibleOptions = true;
                    } else {
                        option.style.display = 'none';
                    }
                });
                
                // Скрываем группу, если нет видимых опций
                group.style.display = hasVisibleOptions ? 'block' : 'none';
            });
        });
    }
    
    // Добавление статистики вариаций
    function addVariationStats() {
        const variationsContainer = document.querySelector('.product-variations');
        if (!variationsContainer) return;
        
        const groups = variationsContainer.querySelectorAll('.variation-group');
        let totalVariations = 0;
        
        groups.forEach(group => {
            const options = group.querySelectorAll('.variation-option');
            totalVariations += options.length;
        });
        
        // Создаем статистику
        const statsDiv = document.createElement('div');
        statsDiv.className = 'variation-stats';
        statsDiv.innerHTML = `
            <small style="color: #666;">
                Найдено ${totalVariations} вариантов в ${groups.length} категориях
            </small>
        `;
        
        variationsContainer.appendChild(statsDiv);
    }
    
    // Добавление быстрого перехода
    function addQuickNavigation() {
        const variationsContainer = document.querySelector('.product-variations');
        if (!variationsContainer) return;
        
        // Создаем кнопки быстрого перехода
        const quickNav = document.createElement('div');
        quickNav.className = 'quick-navigation';
        quickNav.style.cssText = `
            margin-bottom: 15px;
            text-align: center;
        `;
        
        const groups = variationsContainer.querySelectorAll('.variation-group');
        groups.forEach(group => {
            const title = group.querySelector('h4');
            if (title) {
                const button = document.createElement('button');
                button.textContent = title.textContent;
                button.style.cssText = `
                    margin: 0 5px;
                    padding: 5px 10px;
                    border: 1px solid #ddd;
                    background: white;
                    cursor: pointer;
                    border-radius: 3px;
                `;
                
                button.addEventListener('click', function() {
                    group.scrollIntoView({ behavior: 'smooth' });
                });
                
                quickNav.appendChild(button);
            }
        });
        
        variationsContainer.insertBefore(quickNav, variationsContainer.firstChild);
    }
    
    // Основная функция инициализации
    function init() {
        console.log('Initializing product variations system...');
        
        // Ждем загрузки DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(initVariations, 100);
                setTimeout(addVariationStats, 200);
                setTimeout(addQuickNavigation, 300);
            });
        } else {
            setTimeout(initVariations, 100);
            setTimeout(addVariationStats, 200);
            setTimeout(addQuickNavigation, 300);
        }
    }
    
    // Запускаем
    init();
    
})();

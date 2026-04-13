/**
 * Принудительное управление видимостью атрибутов
 * Этот скрипт будет принудительно скрывать/показывать элементы
 */

(function() {
    'use strict';
    
    console.log('=== FORCE ATTRIBUTE VISIBILITY LOADED ===');
    
    // Конфигурация из бэкенда
    let config = window.TSAttrVisibility || null;
    console.log('Config loaded:', config);
    
    if (!config) {
        console.log('No config found, creating default');
        config = {
            type_attr: 'pa_product_type',
            rules: []
        };
    }
    
    // Принудительное скрытие всех групп атрибутов
    function hideAllAttributeGroups() {
        console.log('Hiding all attribute groups...');
        
        const selectors = [
            '.thickness-options',
            '.product-card__thickness-options', 
            '.product-card__section',
            '[data-attr-select]',
            '.variations',
            '.woocommerce-variation-add-to-cart',
            '.single_variation_wrap'
        ];
        
        selectors.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(el => {
                if (el.style) {
                    el.style.display = 'none !important';
                    el.style.visibility = 'hidden !important';
                    el.setAttribute('data-force-hidden', 'true');
                    console.log('Hidden element:', selector, el);
                }
            });
        });
    }
    
    // Показ только нужных групп
    function showOnlyRequiredGroups() {
        console.log('Showing only required groups...');
        
        if (!config.rules || config.rules.length === 0) {
            console.log('No rules configured, showing all groups');
            showAllGroups();
            return;
        }
        
        // Определяем тип товара
        const productType = getProductType();
        console.log('Detected product type:', productType);
        
        if (!productType) {
            console.log('No product type found, showing all groups');
            showAllGroups();
            return;
        }
        
        // Находим правило для этого типа
        const rule = config.rules.find(r => r.type === productType);
        if (!rule) {
            console.log('No rule found for type:', productType);
            showAllGroups();
            return;
        }
        
        console.log('Found rule for type:', productType, rule);
        
        // Показываем только нужные группы
        rule.show.forEach(attrSlug => {
            showAttributeGroup(attrSlug);
        });
        
        // Скрываем ненужные группы
        rule.hide.forEach(attrSlug => {
            hideAttributeGroup(attrSlug);
        });
    }
    
    // Показать все группы
    function showAllGroups() {
        console.log('Showing all groups...');
        
        const hiddenElements = document.querySelectorAll('[data-force-hidden="true"]');
        hiddenElements.forEach(el => {
            el.style.display = '';
            el.style.visibility = '';
            el.removeAttribute('data-force-hidden');
            console.log('Shown element:', el);
        });
    }
    
    // Определение типа товара
    function getProductType() {
        // Из заголовка
        const title = document.querySelector('h1, .product-title, .entry-title');
        if (title) {
            const titleText = title.textContent.toLowerCase();
            console.log('Product title:', titleText);
            
            if (titleText.includes('шина')) return 'шина';
            if (titleText.includes('труба')) return 'труба';
            if (titleText.includes('лист')) return 'лист';
            if (titleText.includes('круг')) return 'круг';
            if (titleText.includes('проволока')) return 'проволока';
        }
        
        // Из URL
        const url = window.location.href.toLowerCase();
        if (url.includes('шина')) return 'шина';
        if (url.includes('труба')) return 'труба';
        if (url.includes('лист')) return 'лист';
        if (url.includes('круг')) return 'круг';
        if (url.includes('проволока')) return 'проволока';
        
        return null;
    }
    
    // Показать группу атрибутов
    function showAttributeGroup(attrSlug) {
        console.log('Showing group for:', attrSlug);
        
        const selectors = [
            `[data-attr-select="${attrSlug}"]`,
            `[data-attr-select*="${attrSlug.replace('pa_', '')}"]`,
            '.thickness-options',
            '.product-card__thickness-options'
        ];
        
        selectors.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(el => {
                el.style.display = '';
                el.style.visibility = '';
                el.removeAttribute('data-force-hidden');
                console.log('Shown group:', selector, el);
            });
        });
    }
    
    // Скрыть группу атрибутов
    function hideAttributeGroup(attrSlug) {
        console.log('Hiding group for:', attrSlug);
        
        const selectors = [
            `[data-attr-select="${attrSlug}"]`,
            `[data-attr-select*="${attrSlug.replace('pa_', '')}"]`
        ];
        
        selectors.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(el => {
                el.style.display = 'none !important';
                el.style.visibility = 'hidden !important';
                el.setAttribute('data-force-hidden', 'true');
                console.log('Hidden group:', selector, el);
            });
        });
    }
    
    // Основная функция
    function applyForceVisibility() {
        console.log('Applying force visibility...');
        
        // Сначала скрываем все
        hideAllAttributeGroups();
        
        // Затем показываем только нужные
        setTimeout(() => {
            showOnlyRequiredGroups();
        }, 100);
    }
    
    // Запуск
    function init() {
        console.log('Initializing force visibility...');
        
        // Применяем сразу
        applyForceVisibility();
        
        // Повторяем через интервалы
        setTimeout(applyForceVisibility, 1000);
        setTimeout(applyForceVisibility, 3000);
        setTimeout(applyForceVisibility, 5000);
        
        // Отслеживаем изменения DOM
        const observer = new MutationObserver(() => {
            console.log('DOM changed, re-applying force visibility...');
            setTimeout(applyForceVisibility, 100);
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class', 'style']
        });
        
        // Применяем при клике
        document.addEventListener('click', () => {
            setTimeout(applyForceVisibility, 200);
        });
    }
    
    // Запускаем
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();
